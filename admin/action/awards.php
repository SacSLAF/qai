<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all Awards records with joins
$records = [];
$res = $db->query("
    SELECT a.*, se.name as establishment_name, se.code as establishment_code, 
           pc.name as category_name, s.name as section_name, ad.username as created_by_name
    FROM awards a 
    LEFT JOIN slaf_establishments se ON a.slaf_establishment_id = se.id 
    LEFT JOIN productivity_categories pc ON a.category_id = pc.id 
    LEFT JOIN sections s ON a.section_id = s.id 
    LEFT JOIN admins ad ON a.created_by = ad.id 
    ORDER BY a.year DESC, a.sno ASC
");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching Awards records: ' . $db->error);
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
                                <h4 class="card-title">Awards Management</h4>
                                <a href="awards-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New Award
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
                                    <table id="awardsTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S/NO</th>
                                                <th>Year</th>
                                                <th>Award Type</th>
                                                <th>SLAF Establishment</th>
                                                <th>QCC/Project Name</th>
                                                <th>Placement</th>
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
                                                    <td colspan="12" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($r['sno']) ?></strong></td>
                                                        <td><?= htmlspecialchars($r['year']) ?></td>
                                                        <td>
                                                            <span class="badge badge-<?= $r['award_type'] === 'qcc' ? 'primary' : 'success' ?>">
                                                                <?= $r['award_type'] === 'qcc' ? 'Best QCC' : 'Best Environment' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($r['establishment_name']) ?>
                                                            <?php if (!empty($r['establishment_code'])): ?>
                                                                <br><small class="text-muted">(<?= htmlspecialchars($r['establishment_code']) ?>)</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?= $r['award_type'] === 'qcc' 
                                                                ? htmlspecialchars($r['qcc_name'] ?? 'N/A')
                                                                : 'Best Environment Project'
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?= 
                                                                $r['placement'] === '1st' ? 'warning' : 
                                                                ($r['placement'] === '2nd' ? 'secondary' : 'info')
                                                            ?>">
                                                                <?= htmlspecialchars($r['placement']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($r['team_members']) ?>">
                                                                <?= htmlspecialchars(substr($r['team_members'], 0, 50)) . (strlen($r['team_members']) > 50 ? '...' : '') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-light">
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
                                                                <a href="edit-awards.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/awards-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this award?');">
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