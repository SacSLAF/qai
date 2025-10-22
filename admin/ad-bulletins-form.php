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
    $stmt = $db->prepare("SELECT * FROM ad_bulletins WHERE id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> AD Bulletin</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> AD Bulletin</h4>
                            <a href="ad-bulletins.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> Bulletin saved successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?= htmlspecialchars($_GET['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="action/ad-bulletins-save.php" method="post">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <!-- Basic Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Bulletin Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Reference No *</label>
                                                    <input type="text" name="reference_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['reference_no']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date of Issue *</label>
                                                    <input type="date" name="date_of_issue" class="form-control" 
                                                           value="<?= isset($record) ? $record['date_of_issue'] : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
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
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Related Aircraft *</label>
                                                    <select name="related_aircraft_id" class="form-select" required>
                                                        <option value="">Select Aircraft Type</option>
                                                        <?php foreach ($types as $type): ?>
                                                            <option value="<?= $type['type_id'] ?>" 
                                                                <?= isset($record) && $record['related_aircraft_id'] == $type['type_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($type['type_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Bulletin Description *</label>
                                                    <textarea name="bulletin_description" class="form-control" rows="5" required><?= isset($record) ? htmlspecialchars($record['bulletin_description']) : '' ?></textarea>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Bulletin
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