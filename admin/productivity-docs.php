<!DOCTYPE html>
<html lang="en">

<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
// Fixed SQL query - added d.id, d.file_path and fixed the JOIN conditions
$documents = $db->query("SELECT d.id, d.title, d.file_path, d.uploaded_at, 
                         c.name AS category_name, 
                         b.name AS branch_name, 
                         a.username AS uploaded_by_name
    FROM productivity_documents d
    LEFT JOIN productivity_categories c ON d.productivity_category_id = c.id
    LEFT JOIN branches b ON d.branch_id = b.id
    LEFT JOIN admins a ON d.uploaded_by = a.id
    ORDER BY d.uploaded_at DESC")->fetch_all(MYSQLI_ASSOC);

include "template/head.php";
?>

<body>

    <?php
    include "template/preloader.php";
    ?>

    <div id="main-wrapper">

        <?php
        include "template/nav.php";
        include "template/header.php";
        ?>

        <?php
        include "template/desnav.php";
        ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Document List</h4>
                                <div class="col-md-3">
                                    <a href="productivity-docs-upload.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus"></i> Upload Document
                                    </a>
                                </div>

                            </div>
                            <?php if (isset($_GET['success'])): ?>
                                <?php if ($_GET['success'] == 1): ?>
                                    <div class="alert alert-primary mx-5">
                                        Document uploaded successfully!
                                    </div>
                                <?php elseif ($_GET['success'] == 2): ?>
                                    <div class="alert alert-success mx-5">
                                        Document deleted successfully!
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    <?php
                                    $error_codes = [
                                        1 => 'Error deleting document from database.',
                                        2 => 'Document not found.',
                                        3 => 'An exception occurred during deletion.',
                                        4 => 'Invalid request.'
                                    ];
                                    echo $error_codes[$_GET['error']] ?? 'Error occurred while processing your request.';
                                    ?>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="documentTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Branch</th>
                                                <th>Uploaded By</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($documents)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No documents found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($documents as $doc): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['category_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($doc['branch_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($doc['uploaded_by_name'] ?? 'Unknown') ?></td>
                                                        <td><?= date('M j, Y', strtotime($doc['uploaded_at'])) ?></td>
                                                        <td>
                                                            <?php
                                                            // Get file extension for proper viewing
                                                            $file_path = $doc['file_path'] ?? '';
                                                            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
                                                            ?>
                                                            <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>

                                                            <a href="edit.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="delete.php" method="post" class="d-inline delete-form">
                                                                <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Directorate of Information Technology. Sri Lanka Air Force.</a> 2025</p>
            </div>
        </div>

    </div>

    <!-- Required vendors -->
    <script>
        var enableSupportButton = 'false'
    </script>
    <script>
        var asset_url = 'assets/productivity/'
    </script>

    <script src="assets/vendor/global/global.min.js" type="text/javascript"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/responsive/responsive.js" type="text/javascript"></script>
    <script src="assets/js/plugins-init/datatables.init.js" type="text/javascript"></script>
    <script src="assets/js/custom.min.js" type="text/javascript"></script>
    <script src="assets/js/deznav-init.js" type="text/javascript"></script>

    <!-- Delete confirmation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to all delete forms
            const deleteForms = document.querySelectorAll('.delete-form');

            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent immediate submission

                    // Show confirmation dialog
                    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                        this.submit(); // Submit if confirmed
                    }
                });
            });
        });
    </script>
</body>

</html>