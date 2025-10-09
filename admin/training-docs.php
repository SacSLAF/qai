<!DOCTYPE html>
<html lang="en">

<?php
require_once '../includes/config.php';
$documents = $db->query("SELECT d.title, d.uploaded_by, d.uploaded_at, c.name AS category_name, b.name AS branch_name, a.username AS uploaded_by
    FROM training_documents d
    JOIN training_categories c ON d.training_category_id = c.id
    JOIN branches b ON d.branch_id = b.id
    JOIN admins a ON d.uploaded_by = a.id")->fetch_all(MYSQLI_ASSOC);

include "template/head.php";
?>

<body>

    <?php
    include "template/preloader.php";
    ?>

    <div id="main-wrapper">

        <?php
        include "template/nav.php";
        // include "template/chatbox.php";
        include "template/header.php";
        ?>

        <?php
        include "template/desnav.php";
        ?> <!--**********************************
            Sidebar end
        ***********************************-->

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Document List</h4>
                                <div class="col-md-3">
                                    <a href="training-docs-upload.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus"></i> Upload Document
                                    </a>
                                </div>
                                
                            </div>
                            <?php if (isset($_GET['success'])): ?>
                                <?php if($_GET['success'] == 1): ?>
                                <div class="alert alert-primary mx-5" >
                                    Document uploaded successfully!
                                </div>
                                <?php endif; ?>
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
                                                    <td colspan="5" class="text-center py-4">No documents found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($documents as $doc): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['category_name']) ?></td>
                                                        <td><?= htmlspecialchars($doc['branch_name']) ?></td>
                                                        <td><?= htmlspecialchars($doc['uploaded_by']) ?></td>
                                                        <td><?= date('M j, Y', strtotime($doc['uploaded_at'])) ?></td>
                                                        <td>
                                                            <a href="../view_document.php?file=<?= urlencode($doc['file_path']) ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="edit.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="delete.php" method="post" class="d-inline">
                                                                <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
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
        <!--**********************************
            Content body end
        ***********************************-->


        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developed by <a href="http://dexignzone.com/" target="_blank">DexignZone</a> 2025</p>
            </div>
        </div> <!--**********************************
            Footer end
        ***********************************-->

        <!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script>
        var enableSupportButton = 'false'
    </script>
    <script>
        var asset_url = 'assets/'
    </script>

    <script src="assets/vendor/global/global.min.js" type="text/javascript"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="assets/vendor/datatables/responsive/responsive.js" type="text/javascript"></script>
    <script src="assets/js/plugins-init/datatables.init.js" type="text/javascript"></script>
    <script src="assets/js/custom.min.js" type="text/javascript"></script>
    <script src="assets/js/deznav-init.js" type="text/javascript"></script>
</body>

</html>