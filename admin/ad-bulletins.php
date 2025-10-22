<?php
require_once '../includes/config.php';

// Simple auth guard
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all AD Bulletin records
$records = [];
$res = $db->query("SELECT ab.*, f.formation_name, t.type_name 
                   FROM ad_bulletins ab 
                   LEFT JOIN formation f ON ab.formation_id = f.formation_id 
                   LEFT JOIN type t ON ab.related_aircraft_id = t.type_id 
                   ORDER BY ab.date_of_issue DESC");
if ($res !== false) {
    $records = $res->fetch_all(MYSQLI_ASSOC);
} else {
    error_log('Error fetching AD Bulletin records: ' . $db->error);
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
                                <h4 class="card-title">AD Bulletins</h4>
                                <a href="ad-bulletins-form.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add New Bulletin
                                </a>
                            </div>

                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-primary mx-5">
                                    Bulletin saved successfully!
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger mx-5">
                                    Error occurred while processing your request.
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="adBulletinsTable" class="display min-w850 table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Reference No</th>
                                                <th>Bulletin Description</th>
                                                <th>Related Aircraft</th>
                                                <th>Formation</th>
                                                <th>Date of Issue</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($records)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No records found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($records as $r): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($r['reference_no']) ?></td>
                                                        <td><?= htmlspecialchars(substr($r['bulletin_description'], 0, 100)) . (strlen($r['bulletin_description']) > 100 ? '...' : '') ?></td>
                                                        <td><?= htmlspecialchars($r['type_name'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($r['formation_name'] ?? 'N/A') ?></td>
                                                        <td><?= $r['date_of_issue'] ? htmlspecialchars($r['date_of_issue']) : 'N/A' ?></td>
                                                        <td>
                                                            <div class="d-flex">
                                                                <a href="edit-ad-bulletins.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </a>

                                                                <form action="action/ad-bulletins-delete.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this bulletin?');">
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