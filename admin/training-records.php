<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all Training Records with joins
$records = [];
$res = $db->query("
    SELECT tr.*, r.rank_name, ac.name as category_name, a.username as created_by_name
    FROM training_records tr 
    LEFT JOIN ranks r ON tr.rank_id = r.id 
    LEFT JOIN ac_categories ac ON tr.ac_category_id = ac.id 
    LEFT JOIN admins a ON tr.created_by = a.id 
    ORDER BY tr.start_date DESC, tr.id DESC
");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching Training Records: ' . $db->error);
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
                                <h4 class="card-title">Training Records Management</h4>
                                <a href="training-records-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New Training Record
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
                                    <table id="trainingRecordsTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Service No</th>
                                                <th>Rank</th>
                                                <th>Name</th>
                                                <th>Course Name</th>
                                                <th>Training Type</th>
                                                <th>Category</th>
                                                <th>Trade</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Certificate No</th>
                                                <th>Created By</th>
                                                <th>Created Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="14" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['sno'] ?? 'N/A') ?></td>
                                                        <td><strong><?= htmlspecialchars($r['svc_no'] ?? 'N/A') ?></strong></td>
                                                        <td><?= htmlspecialchars($r['rank_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['name']) ?></td>
                                                        <td>
                                                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($r['course_name']) ?>">
                                                                <?= htmlspecialchars(substr($r['course_name'], 0, 50)) . (strlen($r['course_name']) > 50 ? '...' : '') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $type_badge = [
                                                                'cpd-inhouse' => 'badge badge-primary',
                                                                'cpd-outside' => 'badge badge-success',
                                                                'workshop-inhouse' => 'badge badge-info',
                                                                'workshop-outside' => 'badge badge-warning'
                                                            ];
                                                            $type_text = [
                                                                'cpd-inhouse' => 'CPD In-House',
                                                                'cpd-outside' => 'CPD Outside',
                                                                'workshop-inhouse' => 'Workshop In-House',
                                                                'workshop-outside' => 'Workshop Outside'
                                                            ];
                                                            ?>
                                                            <span class="<?= $type_badge[$r['training_type']] ?? 'badge badge-secondary' ?>">
                                                                <?= $type_text[$r['training_type']] ?? $r['training_type'] ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-light">
                                                                <?= htmlspecialchars($r['category_name'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['trade'] ?? 'N/A') ?></td>
                                                        <td><?= $r['start_date'] ? htmlspecialchars($r['start_date']) : 'N/A' ?></td>
                                                        <td><?= $r['end_date'] ? htmlspecialchars($r['end_date']) : 'N/A' ?></td>
                                                        <td><?= htmlspecialchars($r['cert_number'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['created_by_name'] ?? 'System') ?></td>
                                                        <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="training-records-form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/training-records-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this training record?');">
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