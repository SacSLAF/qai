<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$formations = [];
$types = [];

// Fetch formations
$formations_result = $db->query("SELECT formation_id, formation_name FROM formation ORDER BY formation_name");
if ($formations_result) {
    $formations = $formations_result->fetch_all(MYSQLI_ASSOC);
}

// Fetch types
$types_result = $db->query("SELECT type_id, type_name FROM type ORDER BY type_name");
if ($types_result) {
    $types = $types_result->fetch_all(MYSQLI_ASSOC);
}

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM latitude WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
}

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Latitude Record</title>
</head>
<body>
    <!-- Include your template files -->
    <?php include "template/preloader.php"; ?>
    <?php include "template/nav.php"; ?>
    <?php include "template/header.php"; ?>
    <?php include "template/desnav.php"; ?>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Latitude Record</h4>
                            <a href="latitude.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> Record saved successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?= htmlspecialchars($_GET['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="action/latitude-save.php" method="post">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <!-- Basic Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Details</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Active *</label>
                                                    <select name="active" class="form-select" required>
                                                        <option value="YES" <?= isset($record) && $record['active'] == 'YES' ? 'selected' : '' ?>>YES</option>
                                                        <option value="NO" <?= isset($record) && $record['active'] == 'NO' ? 'selected' : '' ?>>NO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!--<div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Type</label>
                                                    <input type="text" name="type" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['type']) : 'Latitude' ?>">
                                                </div>
                                            </div>-->
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation *</label>
                                                    <select name="formation_id" class="form-select" required>
                                                        <option value="">Select Formation</option>
                                                        <?php foreach ($formations as $formation): ?>
                                                            <option value="<?= $formation['formation_id'] ?>" 
                                                                <?= isset($record) && $record['formation_id'] == $formation['formation_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($formation['formation_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Aircraft Type *</label>
                                                    <select name="aircraft_type_id" class="form-select" required>
                                                        <option value="">Select Type</option>
                                                        <?php foreach ($types as $type): ?>
                                                            <option value="<?= $type['type_id'] ?>" 
                                                                <?= isset($record) && $record['aircraft_type_id'] == $type['type_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($type['type_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Aircraft Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-plane me-2"></i>Aircraft Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Tail Number</label>
                                                    <input type="text" name="tail_no" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['tail_no']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Part Number</label>
                                                    <input type="text" name="part_no" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['part_no']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Serial Number</label>
                                                    <input type="text" name="serial_no" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['serial_no']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="3"><?= isset($record) ? htmlspecialchars($record['description']) : '' ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Latitude Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-clock me-2"></i>Latitude Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" name="date" class="form-control" value="<?= isset($record) ? $record['date'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Hours</label>
                                                    <input type="number" step="0.01" name="hrs" class="form-control" value="<?= isset($record) ? $record['hrs'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">LDGS</label>
                                                    <input type="number" name="ldgs" class="form-control" value="<?= isset($record) ? $record['ldgs'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Present Latitude</label>
                                                    <input type="text" name="present_latitude" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['present_latitude']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Previous Latitude</label>
                                                    <input type="text" name="total_prev_latitude" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['total_prev_latitude']) : '' ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Authorization Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-file-signature me-2"></i>Authorization Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">DGAE Auth Reference</label>
                                                    <input type="text" name="dgae_auth_ref" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['dgae_auth_ref']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Auth Date</label>
                                                    <input type="date" name="auth_date" class="form-control" value="<?= isset($record) ? $record['auth_date'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Latitude Expiry</label>
                                                    <input type="date" name="latitude_expiry" class="form-control" value="<?= isset($record) ? $record['latitude_expiry'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Demand Reference</label>
                                                    <input type="text" name="demand_ref" class="form-control" value="<?= isset($record) ? htmlspecialchars($record['demand_ref']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="">Select Status</option>
                                                        <option value="Pending" <?= isset($record) && $record['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="Approved" <?= isset($record) && $record['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                        <option value="Rejected" <?= isset($record) && $record['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                        <option value="Expired" <?= isset($record) && $record['status'] == 'Expired' ? 'selected' : '' ?>>Expired</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <textarea name="reason" class="form-control" rows="3"><?= isset($record) ? htmlspecialchars($record['reason']) : '' ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-md-12">
                                        <div class="text-end">
                                            <button type="reset" class="btn btn-secondary me-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Record
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include your footer and scripts -->
    <div class="footer">
        <div class="copyright">
            <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Directorate of Information Technology. Sri Lanka Air Force.</a> 2025</p>
        </div>
    </div>

    <!-- Required scripts -->
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/deznav-init.js"></script>
</body>
</html>