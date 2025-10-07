<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Fetch all productivity documents for category 4 (Awards)
$stmt = $db->prepare("SELECT d.svc_no, d.rank,d.name, d.aircraft_type, d.last_level_of_competency, d.renewal_date, d.currency, d.squadron, d.file_path 
    FROM aircraft_competency d
    ORDER BY d.uploaded_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
?>
<head>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body> 
    <!-- Main Content -->
    <main class="container my-5 pt-5">
        <div class="page-header mb-4">
            <h3 class="colour-defult">Aircraft Competency<i class="fa fa-tasks"></i>
            </h3>
        </div>
        
        <div class="row">

            <!-- Right Side Content -->
        <div class="row">
            
            <div class="col-lg-12">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>SVC No</th>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Aircraft Type</th>
                            <th>Last Level of Competency</th>
                            <th>Renewal Date</th>
                            <th>Currency</th>
                            <th>Squadron</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($documents) > 0): ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['svc_no']) ?></td>
                                    <td><?= htmlspecialchars($doc['rank']) ?></td>
                                    <td><?= htmlspecialchars($doc['name']) ?></td>
                                    <td><?= htmlspecialchars($doc['aircraft_type']) ?></td>
                                    <td><?= htmlspecialchars($doc['last_level_of_competency']) ?></td>
                                    <td><?= htmlspecialchars($doc['renewal_date']) ?></td>
                                    <td><?= htmlspecialchars($doc['currency']) ?></td>
                                    <td><?= htmlspecialchars($doc['squadron']) ?></td>
                                    <td>
                                        <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" 
                                           class="btn btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No documents found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        <hr>
    </main>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle URL hash for direct tab access
        document.addEventListener("DOMContentLoaded", function() {
            var hash = window.location.hash;
            if (hash) {
                var tabTrigger = document.querySelector('a[href="' + hash + '"]');
                if (tabTrigger) {
                    var tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }
            
            // Update URL hash when tabs are shown
            var tabEls = document.querySelectorAll('a[data-bs-toggle="pill"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (e) {
                    history.replaceState(null, null, e.target.getAttribute('href'));
                });
            });
        });
    </script>
</body>
</html>