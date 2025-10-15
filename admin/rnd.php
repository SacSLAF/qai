<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all rnd records
$records = [];
$res = $db->query("SELECT r.*, f.formation_name, t.type_name 
                   FROM rnd r 
                   LEFT JOIN formation f ON r.formation_id = f.formation_id 
                   LEFT JOIN type t ON r.type_id = t.type_id 
                   ORDER BY r.created_at DESC");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching rnd records: ' . $db->error);
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
                                <h4 class="card-title">R&D Records</h4>
                                <a href="rnd-form.php" class="btn btn-sm btn-outline-primary">
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
                                    <table id="rndTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>R&D No</th>
                                                <th>Directorate</th>
                                                <th>Formation</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Issue Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($r['rnd_no'] ?? 'N/A') ?></strong></td>
                                                        <td><?= htmlspecialchars($r['directorate'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['formation_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['type_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['description'] ?? 'N/A') ?></td>
                                                        <td><?= $r['issue_date'] ? date('M d, Y', strtotime($r['issue_date'])) : 'N/A' ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-rnd.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/rnd-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this record?');">
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