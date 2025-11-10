<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all Active QCC records with joins
$records = [];
$res = $db->query("
    SELECT aq.*, se.name as establishment_name, se.code as establishment_code, 
           pc.name as category_name, s.name as section_name, a.username as created_by_name
    FROM active_qcc aq 
    LEFT JOIN slaf_establishments se ON aq.slaf_establishment_id = se.id 
    LEFT JOIN productivity_categories pc ON aq.category_id = pc.id 
    LEFT JOIN sections s ON aq.section_id = s.id 
    LEFT JOIN admins a ON aq.created_by = a.id 
    ORDER BY aq.id DESC
");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching Active QCC records: ' . $db->error);
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
                                <h4 class="card-title">Active QCC Registrations</h4>
                                <a href="active-qcc-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New QCC
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
                                    <table id="activeQccTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S/No</th>
                                                <th>QCC Name</th>
                                                <th>SLAF Establishment</th>
                                                <th>Location</th>
                                                <th>Team Members</th>
                                                <th>Category</th>
                                                <th>Section</th>
                                                <th>Created By</th>
                                                <th>Created Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['sno']) ?></td>
                                                        <td><?= htmlspecialchars($r['qcc_name']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($r['establishment_name']) ?>
                                                            <?php if (!empty($r['establishment_code'])): ?>
                                                                <br><small class="text-muted">(<?= htmlspecialchars($r['establishment_code']) ?>)</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['location']) ?></td>
                                                        <td>
                                                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($r['team_members']) ?>">
                                                                <?= htmlspecialchars(substr($r['team_members'], 0, 30)) . (strlen($r['team_members']) > 30 ? '...' : '') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">
                                                                <?= htmlspecialchars($r['category_name'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                <?= htmlspecialchars($r['section_name'] ?? 'N/A') ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($r['created_by_name'] ?? 'System') ?></td>
                                                        <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-active-qcc.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/active-qcc-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this QCC?');">
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