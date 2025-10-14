<?php
require_once '../includes/config.php';
session_start();

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all aircraft_competency records (do not JOIN to avoid schema mismatch)
$records = [];
$res = $db->query("SELECT * FROM aircraft_competency ORDER BY created_at DESC");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching aircraft_competency records: ' . $db->error);
    $records = [];
}

// Load lookup maps (types, formations, ac_categories) to resolve ids to names if available
$types_map = [];
$types_res = $db->query("SELECT type_id, type_name FROM type");
if ($types_res !== false) {
    foreach ($types_res->fetch_all(MYSQLI_ASSOC) as $t) $types_map[$t['type_id']] = $t['type_name'];
}

$formations_map = [];
$forms_res = $db->query("SELECT formation_id, formation_name FROM formation");
if ($forms_res !== false) {
    foreach ($forms_res->fetch_all(MYSQLI_ASSOC) as $f) $formations_map[$f['formation_id']] = $f['formation_name'];
}

$ac_cat_map = [];
$ac_res = $db->query("SELECT id, name FROM ac_categories");
if ($ac_res !== false) {
    foreach ($ac_res->fetch_all(MYSQLI_ASSOC) as $c) $ac_cat_map[$c['id']] = $c['name'];
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
                                <h4 class="card-title">Aircraft Competency Records</h4>
                                <a href="aircraft-competency-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New Record
                                </a>
                            </div>

                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-primary mx-5">
                                    Record saved successfully!
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    Error occurred while processing your request.
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="competencyTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>SVC No</th>
                                                <th>Name</th>
                                                <th>Rank</th>
                                                <th>Aircraft Type</th>
                                                <th>Competency Level</th>
                                                <th>Branch</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['svc_no']) ?></td>
                                                        <td><?= htmlspecialchars($r['name']) ?></td>
                                                        <td><?= htmlspecialchars($r['rank']) ?></td>
                                                        <td><?= htmlspecialchars(isset($types_map[$r['type_id']]) ? $types_map[$r['type_id']] : ($r['aircraft_type'] ?? $r['type_id'] ?? '')) ?></td>
                                                        <td><?= htmlspecialchars($r['competency_level']) ?></td>
                                                        <td><?= htmlspecialchars(isset($ac_cat_map[$r['branch']]) ? $ac_cat_map[$r['branch']] : ($r['branch'] ?? 'N/A')) ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-aircraft-competency.php?id=<?= $r['record_id'] ?? $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/aircraft-competency-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                                    <input type="hidden" name="id" value="<?= $r['record_id'] ?? $r['id'] ?>">
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