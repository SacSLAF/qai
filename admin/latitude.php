<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all latitude records
$records = [];
$res = $db->query("SELECT l.*, f.formation_name, t.type_name 
                   FROM latitude l 
                   LEFT JOIN formation f ON l.formation_id = f.formation_id 
                   LEFT JOIN type t ON l.aircraft_type_id = t.type_id 
                   ORDER BY l.created_at DESC");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching latitude records: ' . $db->error);
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
                                <h4 class="card-title">Latitude Records</h4>
                                <a href="latitude-form.php" class="btn btn-sm btn-outline-primary">
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
                                    <table id="latitudeTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Active</th>
                                                <th>Formation</th>
                                                <th>Aircraft Type</th>
                                                <th>Tail No</th>
                                                <th>Part No</th>
                                                <th>Present Latitude</th>
                                                <th>Status</th>
                                                <th>Expiry Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge badge-<?= $r['active'] == 'YES' ? 'success' : 'danger' ?>">
                                                                <?= htmlspecialchars($r['active']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['formation_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['type_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['tail_no'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['part_no'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['present_latitude'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <span class="badge badge-<?= $r['status'] == 'Approved' ? 'success' : ($r['status'] == 'Pending' ? 'warning' : 'secondary') ?>">
                                                                <?= htmlspecialchars($r['status'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $r['latitude_expiry'] ? htmlspecialchars($r['latitude_expiry']) : 'N/A' ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-latitude.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/latitude-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this record?');">
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