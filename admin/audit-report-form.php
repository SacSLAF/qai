<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$establishments = [];
$productivity_categories = [];

// Fetch SLAF establishments
$est_result = $db->query("SELECT id, name, code FROM slaf_establishments WHERE is_active = 1 ORDER BY name");
if ($est_result) {
    $establishments = $est_result->fetch_all(MYSQLI_ASSOC);
}

// Fetch Productivity Categories (excluding Awards - category_id 4)
$cat_result = $db->query("SELECT id, name FROM productivity_categories WHERE id != 4 ORDER BY name");
if ($cat_result) {
    $productivity_categories = $cat_result->fetch_all(MYSQLI_ASSOC);
}

// Set default values
$default_section_id = 5;  // Default to productivity section

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM audit_report WHERE id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Audit Report</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Audit Report</h4>
                            <a href="audit-report.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> <?= $_SESSION['success'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php elseif (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?= $_SESSION['error'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <form action="action/audit-report-save.php" method="post" enctype="multipart/form-data">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                    <input type="hidden" name="existing_file" value="<?= $record['file_path'] ?>">
                                <?php endif; ?>

                                <!-- Hidden field for default section -->
                                <input type="hidden" name="section_id" value="<?= $default_section_id ?>">

                                <div class="row">
                                    <!-- Report Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-file-alt me-2"></i>Report Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Serial Number *</label>
                                                    <input type="text" name="sno" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['sno']) : '' ?>" 
                                                           required placeholder="e.g., AR-001">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SLAF Establishment *</label>
                                                    <select name="slaf_establishment_id" class="form-select" required>
                                                        <option value="">Select Establishment</option>
                                                        <?php foreach ($establishments as $est): ?>
                                                            <option value="<?= $est['id'] ?>" 
                                                                <?= isset($record) && $record['slaf_establishment_id'] == $est['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($est['name']) ?> 
                                                                (<?= htmlspecialchars($est['code']) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Conducted Date *</label>
                                                    <input type="date" name="conducted_date" class="form-control" 
                                                           value="<?= isset($record) ? $record['conducted_date'] : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Productivity Category *</label>
                                                    <select name="productivity_category_id" class="form-select" required>
                                                        <option value="">Select Category</option>
                                                        <?php foreach ($productivity_categories as $cat): ?>
                                                            <option value="<?= $cat['id'] ?>" 
                                                                <?= isset($record) && $record['productivity_category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cat['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Report File *</label>
                                                    <input type="file" name="report_file" class="form-control" 
                                                           accept=".pdf,.doc,.docx,.xls,.xlsx" 
                                                           <?= !isset($record) ? 'required' : '' ?>>
                                                    <?php if (isset($record) && !empty($record['file_path'])): ?>
                                                        <small class="form-text text-muted">
                                                            Current file: <a href="<?= htmlspecialchars($record['file_path']) ?>" target="_blank">View</a>
                                                        </small>
                                                    <?php endif; ?>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Display Default Values (Read-only) -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Section</label>
                                                    <input type="text" class="form-control" value="Productivity" readonly style="background-color: #f8f9fa;">
                                                    <small class="form-text text-muted">Default section for audit reports</small>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Report
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