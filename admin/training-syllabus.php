<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all Training Syllabus records with joins
$records = [];
$res = $db->query("
    SELECT ts.*, f.formation_name, t.type_name, ac.name as ac_category_name 
    FROM training_syllabus ts 
    LEFT JOIN formation f ON ts.formation_id = f.formation_id 
    LEFT JOIN type t ON ts.type_id = t.type_id 
    LEFT JOIN ac_categories ac ON ts.ac_categories_id = ac.id 
    ORDER BY ts.id DESC
");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching Training Syllabus records: ' . $db->error);
    $records = [];
}

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <?php include "template/preloader.php"; ?>

    <div id="main-wrapper">
        <?php include "template/nav.php"; include "template/header.php"; ?>
        <?php include "template/desnav.php"; ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Training Syllabus</h4>
                                <a href="training-syllabus-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New Syllabus
                                </a>
                            </div>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-primary mx-5">
                                    <?= $_SESSION['success'] ?>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php elseif (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    <?= $_SESSION['error'] ?>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="trainingSyllabusTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Syllabus No</th>
                                                <th>Description</th>
                                                <th>Formation</th>
                                                <th>Type</th>
                                                <th>Trade</th>
                                                <th>Syllabus Type</th>
                                                <th>Issue</th>
                                                <th>Revision</th>
                                                <th>Branch</th>
                                                <th>File</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['syllabus_no']) ?></td>
                                                        <td><?= htmlspecialchars(substr($r['description'], 0, 50)) . (strlen($r['description']) > 50 ? '...' : '') ?></td>
                                                        <td><?= htmlspecialchars($r['formation_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['type_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['trade'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['syllabus_type'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['issue']) ?></td>
                                                        <td><?= htmlspecialchars($r['revision'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($r['ac_category_name'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <?php if (!empty($r['file_path'])): ?>
                                                                <a href="../view_document.php?file=<?= urlencode($r['file_path']) ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View">
                                                                    <i class="fas fa-download"></i> View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No file</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-training-syllabus.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/training-syllabus-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this syllabus?');">
                                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
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

    <!-- Required scripts -->
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables/responsive/responsive.js"></script>
    <script src="assets/js/plugins-init/datatables.init.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/deznav-init.js"></script>
</body>
</html>