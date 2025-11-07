<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$categories = [];
$rev_status_options = ['Current', 'Revised', 'Obsolete'];

// Fetch Productivity Categories
$cat_result = $db->query("SELECT id, name FROM productivity_categories ORDER BY name");
if ($cat_result) {
    $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
}

// Set default values
$default_section_id = 5;  // Default to productivity section
$default_category_id = 3; // Default category for OSH Manual

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM osh_manual WHERE id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> OSH Manual</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> OSH Manual</h4>
                            <a href="osh-manual.php" class="btn btn-sm btn-outline-secondary">
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

                            <form action="action/osh-manual-save.php" method="post" enctype="multipart/form-data">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                    <input type="hidden" name="existing_file" value="<?= $record['file_path'] ?>">
                                <?php endif; ?>

                                <!-- Hidden fields for default values -->
                                <input type="hidden" name="section_id" value="<?= $default_section_id ?>">
                                <input type="hidden" name="category_id" value="<?= $default_category_id ?>">
                                <input type="hidden" name="created_by" value="<?= $_SESSION['admin_id'] ?>">

                                <div class="row">
                                    <!-- Manual Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-book me-2"></i>Manual Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Serial Number *</label>
                                                    <input type="text" name="sno" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['sno']) : '' ?>" 
                                                           required placeholder="e.g., OSH-001">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Manual Number *</label>
                                                    <input type="text" name="manual_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['manual_no']) : '' ?>" 
                                                           required placeholder="e.g., MAN-2024-001">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description *</label>
                                                    <textarea name="description" class="form-control" rows="3" 
                                                              required placeholder="Enter manual description"><?= isset($record) ? htmlspecialchars($record['description']) : '' ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Revision Status *</label>
                                                    <input type="text" name="rev_status" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['rev_status']) : '' ?>" 
                                                           required>

                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Manual File *</label>
                                                    <input type="file" name="manual_file" class="form-control" 
                                                           accept=".pdf,.doc,.docx" 
                                                           <?= !isset($record) ? 'required' : '' ?>>
                                                    <?php if (isset($record) && !empty($record['file_path'])): ?>
                                                        <small class="form-text text-muted">
                                                            Current file: <a href="<?= htmlspecialchars($record['file_path']) ?>" target="_blank">View</a>
                                                        </small>
                                                    <?php endif; ?>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PDF, DOC, DOCX (Max: 10MB)
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
                                                    <small class="form-text text-muted">Default section for OSH manuals</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <input type="text" class="form-control" value="OSH Manual" readonly style="background-color: #f8f9fa;">
                                                    <small class="form-text text-muted">Default category for OSH manuals</small>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Manual
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