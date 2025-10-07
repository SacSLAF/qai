<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Fetch all productivity documents for category 4 (Awards)
$stmt = $db->prepare("SELECT d.vehicle_no, d.test_performed_date, d.state, d.remarks, d.file_path 
    FROM vehicle_emission_test d
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
            <h3 class="colour-defult">Vehicle Emission Test<i class="fa fa-tasks"></i>
            </h3>
        </div>
        
        <div class="row">

            <!-- Right Side Content -->
        <div class="row">
            
            <div class="col-lg-12">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Vehicle No</th>
                            <th>Test Performed Date</th>
                            <th>State</th>
                            <th>Remarks</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($documents) > 0): ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['vehicle_no']) ?></td>
                                    <td><?= htmlspecialchars($doc['test_performed_date']) ?></td>
                                    <td><?= htmlspecialchars($doc['state']) ?></td>
                                    <td><?= htmlspecialchars($doc['remarks']) ?></td>
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