<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all Training Syllabus CPD records with joins
$records = [];
$res = $db->query("
    SELECT tsc.*, ac.name as category_name, a.username as created_by_name
    FROM training_syllabus_cpd tsc 
    LEFT JOIN ac_categories ac ON tsc.ac_categories_id = ac.id 
    LEFT JOIN admins a ON tsc.created_by = a.id 
    ORDER BY tsc.syllabus_no ASC
");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching Training Syllabus CPD records: ' . $db->error);
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
                                <h4 class="card-title">Training Syllabus CPD Management</h4>
                                <a href="training-syllabus-cpd-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New CPD Syllabus
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
                                    <table id="trainingSyllabusCpdTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Syllabus No</th>
                                                <th>Trade</th>
                                                <th>Course Description</th>
                                                <th>Issue</th>
                                                <th>Revision</th>
                                                <th>Revised Date</th>
                                                <th>Category</th>
                                                <th>File</th>
                                                <th>Created By</th>
                                                <th>Created Date</th>
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
                                                        <td><strong><?= htmlspecialchars($r['syllabus_no']) ?></strong></td>
                                                        <td><?= htmlspecialchars($r['trade'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($r['course_description']) ?>">
                                                                <?= htmlspecialchars(substr($r['course_description'], 0, 100)) . (strlen($r['course_description']) > 100 ? '...' : '') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['issue'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['revision'] ?? 'N/A') ?></td>
                                                        <td><?= $r['revised_date'] ? htmlspecialchars($r['revised_date']) : 'N/A' ?></td>
                                                        <td>
                                                            <span class="badge badge-primary">
                                                                <?= htmlspecialchars($r['category_name'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($r['file_path'])): ?>
                                                                <a href="../view_document.php?file=<?= urlencode($r['file_path']) ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View">
                                                                    <i class="fas fa-download"></i> View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No file</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['created_by_name'] ?? 'System') ?></td>
                                                        <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="training-syllabus-cpd-form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/training-syllabus-cpd-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this CPD syllabus?');">
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