<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all productivity documents for category 4 (Awards)
$stmt = $db->prepare("SELECT d.id, d.title, d.description, d.file_path 
    FROM ad_bulletins d
    ORDER BY d.uploaded_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);

// Get unique descriptions for filter dropdown
$descriptions = array_unique(array_column($documents, 'description'));
sort($descriptions);

// Get filter from URL parameter
$selected_description = $_GET['description'] ?? 'all';
?>

<body>
    
    <!-- Main Content -->
    <div class="col-lg-12">
        <!-- Filter Dropdown -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="descriptionFilter" class="form-label">Filter by Description:</label>
                <select class="form-select" id="descriptionFilter">
                    <option value="all" <?= $selected_description === 'all' ? 'selected' : '' ?>>All Descriptions</option>
                    <?php foreach ($descriptions as $description): ?>
                        <?php if (!empty($description)): ?>
                            <option value="<?= htmlspecialchars($description) ?>" 
                                <?= $selected_description === $description ? 'selected' : '' ?>>
                                <?= htmlspecialchars($description) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <div class="form-text">
                    Showing: 
                    <span id="showingCount"><?= count($documents) ?></span> 
                    of <?= count($documents) ?> documents
                    <?php if ($selected_description !== 'all'): ?>
                        for <strong><?= htmlspecialchars($selected_description) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Description</th>
                    <th>Related Aircraft</th>
                    <th>Subscription Periods</th>
                    <th>Document</th>
                </tr>
            </thead>
            <tbody id="documentsTable">
                <?php 
                // Filter documents based on selection
                $filtered_documents = $documents;
                if ($selected_description !== 'all') {
                    $filtered_documents = array_filter($documents, function($doc) use ($selected_description) {
                        return $doc['description'] === $selected_description;
                    });
                }
                ?>
                
                <?php if (count($filtered_documents) > 0): ?>
                    <?php foreach ($filtered_documents as $doc): ?>
                        <tr class="document-row" data-description="<?= htmlspecialchars($doc['description']) ?>">
                            <td><?= htmlspecialchars($doc['description']) ?></td>
                            <td>
                                <?= htmlspecialchars($doc['related_aircraft'] ?? 'N/A') ?>
                            </td>
                            <td><?= htmlspecialchars($doc['subscription_period'] ?? 'N/A') ?></td>
                            <td>
                                <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" 
                                   class="btn btn-primary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="noResults" style="display: <?= count($filtered_documents) === 0 ? 'table-row' : 'none' ?>;">
                        <td colspan="4" class="text-center py-4">
                            <?php if ($selected_description !== 'all'): ?>
                                No documents found for description: <strong><?= htmlspecialchars($selected_description) ?></strong>
                            <?php else: ?>
                                No documents found
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const descriptionFilter = document.getElementById('descriptionFilter');
            const documentRows = document.querySelectorAll('.document-row');
            const noResultsRow = document.getElementById('noResults');
            const showingCount = document.getElementById('showingCount');
            
            // Filter function
            function filterDocuments() {
                const selectedValue = descriptionFilter.value;
                let visibleCount = 0;
                
                // Update URL without page reload (for bookmarking/sharing)
                const url = new URL(window.location);
                if (selectedValue === 'all') {
                    url.searchParams.delete('description');
                } else {
                    url.searchParams.set('description', selectedValue);
                }
                history.replaceState(null, '', url);
                
                // Show/hide rows based on filter
                documentRows.forEach(row => {
                    const rowDescription = row.getAttribute('data-description');
                    
                    if (selectedValue === 'all' || rowDescription === selectedValue) {
                        row.style.display = 'table-row';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                if (noResultsRow) {
                    if (visibleCount === 0) {
                        noResultsRow.style.display = 'table-row';
                        // Update no results message
                        const message = selectedValue === 'all' 
                            ? 'No documents found' 
                            : `No documents found for description: <strong>${selectedValue}</strong>`;
                        noResultsRow.querySelector('td').innerHTML = message;
                    } else {
                        noResultsRow.style.display = 'none';
                    }
                }
                
                // Update showing count
                showingCount.textContent = visibleCount;
            }
            
            // Event listener for filter change
            descriptionFilter.addEventListener('change', filterDocuments);
            
            // Handle URL hash for direct tab access (your existing code)
            var hash = window.location.hash;
            if (hash) {
                var tabTrigger = document.querySelector('a[href="' + hash + '"]');
                if (tabTrigger) {
                    var tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }
            
            // Update URL hash when tabs are shown (your existing code)
            var tabEls = document.querySelectorAll('a[data-bs-toggle="pill"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (e) {
                    history.replaceState(null, null, e.target.getAttribute('href'));
                });
            });
        });
    </script>
    
    <style>
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        
        #descriptionFilter {
            max-width: 400px;
        }
        
        .form-text {
            margin-left: 15px;
        }
        
        .document-row {
            transition: all 0.3s ease;
        }
        
        /* Optional: Add some styling for long descriptions */
        td:first-child {
            max-width: 300px;
            word-wrap: break-word;
        }
    </style>
</body>
</html>