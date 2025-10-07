<!DOCTYPE html>
<html lang="en">

<?php
require_once '../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch documents with proper joins
$documents = $db->query("
    SELECT d.id, d.file_path, d.is_active, d.service_category_id, d.title, d.uploaded_at, 
           c.name AS category_name, 
           b.name AS branch_name, 
           a.username AS uploaded_by
    FROM service_documents d
    LEFT JOIN service_categories c ON d.service_category_id = c.id
    LEFT JOIN branches b ON d.branch_id = b.id
    LEFT JOIN admins a ON d.uploaded_by = a.id
    ORDER BY d.uploaded_at DESC
")->fetch_all(MYSQLI_ASSOC);

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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Document List</h4>
                                <a href="services-docs-upload.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Upload Document
                                </a>
                            </div>

                            <?php if (isset($_GET['success'])): ?>
                                <?php if ($_GET['success'] == 1): ?>
                                    <div class="alert alert-primary mx-5">
                                        Document uploaded successfully!
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    Error occurred while processing your request.
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
                                                <!-- <th>Uploaded By</th> -->
                                                <th>Date</th>
                                                <th>Status</th>
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
                                                        <td><?= htmlspecialchars($doc['category_name']) ?></td>
                                                        <td><?= !empty($doc['branch_name']) ? htmlspecialchars($doc['branch_name']) : '<span class="text-muted">N/A</span>' ?></td>
                                                        <!-- <td><?= htmlspecialchars($doc['uploaded_by']) ?></td> -->
                                                        <td><?= date('M j, Y', strtotime($doc['uploaded_at'])) ?></td>

                                                        <td>
                                                            <?php if ($doc['is_active'] == 1): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Disabled</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="View Document" target="_blank">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="edit.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="tooltip" title="Edit Document">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <!-- <form action="delete-services.php" method="post" class="d-inline">
                                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this document?')" data-bs-toggle="tooltip" title="Delete Document">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form> -->
                                                                <!-- Toggle Active/Inactive Status -->
                                                                <form action="action/toggle-status.php" method="post" class="d-inline">
                                                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                                    <input type="hidden" name="service_category_id" value="<?= $doc['service_category_id'] ?>">
                                                                    <input type="hidden" name="current_status" value="<?= $doc['is_active'] ?>">

                                                                    <?php if ($doc['is_active'] == 1): ?>
                                                                        <!-- Active document - show Disable button -->
                                                                        <button type="submit" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Disable Document" onclick="return confirm('Are you sure you want to disable this document?')">
                                                                            <i class="fas fa-ban"></i>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <!-- Disabled document - show Enable button -->
                                                                        <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Enable Document" onclick="return confirm('Are you sure you want to enable this document?')">
                                                                            <i class="fas fa-check"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </form>
                                                            </div>
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
    <script src="assets/vendor/global/global.min.js" type="text/javascript"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/responsive/responsive.js" type="text/javascript"></script>
    <script src="assets/js/plugins-init/datatables.init.js" type="text/javascript"></script>
    <script src="assets/js/custom.min.js" type="text/javascript"></script>
    <script src="assets/js/deznav-init.js" type="text/javascript"></script>

    <script>
        // Initialize tooltips
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();


            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>

</html>