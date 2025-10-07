<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all productivity documents for category 4 (Awards)
$stmt = $db->prepare("SELECT d.id, d.title, d.description, d.related_aircraft, d.subscription_period, d.file_path 
    FROM online_subscription d
    ORDER BY d.uploaded_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);

// Get unique aircraft for filter dropdown
$aircrafts = array_unique(array_column($documents, 'related_aircraft'));
sort($aircrafts);

// Get filter from URL parameter
$selected_aircraft = $_GET['aircraft'] ?? 'all';
?>

<body>
    
    <!-- Main Content -->
    <div class="col-lg-12">
        <!-- Filter Dropdown -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="aircraftFilter" class="form-label">Select your Aircraft:</label>
                <select class="form-select" id="aircraftFilter">
                    <option value="all" <?= $selected_aircraft === 'all' ? 'selected' : '' ?>>All Aircraft</option>
                    <?php foreach ($aircrafts as $aircraft): ?>
                        <?php if (!empty($aircraft)): ?>
                            <option value="<?= htmlspecialchars($aircraft) ?>" 
                                <?= $selected_aircraft === $aircraft ? 'selected' : '' ?>>
                                <?= htmlspecialchars($aircraft) ?>
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
                    <?php if ($selected_aircraft !== 'all'): ?>
                        for <strong><?= htmlspecialchars($selected_aircraft) ?></strong>
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
                if ($selected_aircraft !== 'all') {
                    $filtered_documents = array_filter($documents, function($doc) use ($selected_aircraft) {
                        return $doc['related_aircraft'] === $selected_aircraft;
                    });
                }
                ?>
                
                <?php if (count($filtered_documents) > 0): ?>
                    <?php foreach ($filtered_documents as $doc): ?>
                        <tr class="document-row" data-aircraft="<?= htmlspecialchars($doc['related_aircraft']) ?>">
                            <td><?= htmlspecialchars($doc['description']) ?></td>
                            <td>
                                <?= htmlspecialchars($doc['related_aircraft']) ?>
                            </td>
                            <td><?= htmlspecialchars($doc['subscription_period']) ?></td>
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
                            <?php if ($selected_aircraft !== 'all'): ?>
                                No documents found for aircraft: <strong><?= htmlspecialchars($selected_aircraft) ?></strong>
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
            const aircraftFilter = document.getElementById('aircraftFilter');
            const documentRows = document.querySelectorAll('.document-row');
            const noResultsRow = document.getElementById('noResults');
            const showingCount = document.getElementById('showingCount');
            
            // Filter function
            function filterDocuments() {
                const selectedValue = aircraftFilter.value;
                let visibleCount = 0;
                
                // Update URL without page reload (for bookmarking/sharing)
                const url = new URL(window.location);
                if (selectedValue === 'all') {
                    url.searchParams.delete('aircraft');
                } else {
                    url.searchParams.set('aircraft', selectedValue);
                }
                history.replaceState(null, '', url);
                
                // Show/hide rows based on filter
                documentRows.forEach(row => {
                    const rowAircraft = row.getAttribute('data-aircraft');
                    
                    if (selectedValue === 'all' || rowAircraft === selectedValue) {
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
                            : `No documents found for aircraft: <strong>${selectedValue}</strong>`;
                        noResultsRow.querySelector('td').innerHTML = message;
                    } else {
                        noResultsRow.style.display = 'none';
                    }
                }
                
                // Update showing count
                showingCount.textContent = visibleCount;
            }
            
            // Event listener for filter change
            aircraftFilter.addEventListener('change', filterDocuments);
            
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
        
        #aircraftFilter {
            max-width: 300px;
        }
        
        .form-text {
            margin-left: 15px;
        }
        
        .document-row {
            transition: all 0.3s ease;
        }
    </style>
</body>
</html>