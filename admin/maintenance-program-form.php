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
$branches = [];

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

// Fetch branches
$branches_result = $db->query("SELECT id, name FROM branches ORDER BY name");
if ($branches_result) {
    $branches = $branches_result->fetch_all(MYSQLI_ASSOC);
}

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM maintenance_documents WHERE id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Maintenance Document</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Maintenance Document</h4>
                            <a href="maintenance-program.php" class="btn btn-sm btn-outline-secondary">
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

                            <form action="action/maintenance-program-save.php" method="post" enctype="multipart/form-data">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                    <input type="hidden" name="existing_file" value="<?= $record['file_path'] ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <!-- Document Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-file-alt me-2"></i>Document Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Document Type *</label>
                                                    <select name="document_type" class="form-select" required>
                                                        <option value="">Select Type</option>
                                                        <option value="worksheet" <?= isset($record) && $record['document_type'] == 'worksheet' ? 'selected' : '' ?>>Worksheet</option>
                                                        <option value="schedule" <?= isset($record) && $record['document_type'] == 'schedule' ? 'selected' : '' ?>>Schedule</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Document Number *</label>
                                                    <input type="text" name="document_number" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['document_number']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description *</label>
                                                    <textarea name="description" class="form-control" rows="3" required><?= isset($record) ? htmlspecialchars($record['description']) : '' ?></textarea>
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
                                                <label class="form-label">Trade</label>
                                                    <select name="trade" class="form-select" required>
                                                        <option value="" selected disabled>Select Trade</option>
                                                        <option value="Airframe">Airframe</option>
                                                        <option value="Aero ENG">Aero ENG</option>
                                                        <option value="Aero E&I">Aero E&I</option>
                                                        <option value="Safety Eqpt">Safety Eqpt</option>
                                                        <option value="Air Radio">Air Radio</option>
                                                        <option value="AGSE">AGSE</option>
                                                        <option value="Armament">Armament</option>
                                                        <option value="Airframe & Power Plant">Airframe & Power Plant</option>
                                                        <option value="None Tech">None Tech</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Aircraft Type *</label>
                                                    <select name="type_id" class="form-select" required>
                                                        <option value="">Select Aircraft Type</option>
                                                        <?php foreach ($types as $type): ?>
                                                            <option value="<?= $type['type_id'] ?>" 
                                                                <?= isset($record) && $record['type_id'] == $type['type_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($type['type_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Issue *</label>
                                                    <input type="text" name="issue" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['issue']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Revision</label>
                                                    <input type="text" name="revision" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['revision']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Revision Date</label>
                                                    <input type="date" name="revision_date" class="form-control" 
                                                           value="<?= isset($record) && $record['revision_date'] ? $record['revision_date'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Branch *</label>
                                                    <select name="branch_id" class="form-select" required>
                                                        <option value="">Select Branch</option>
                                                            <?php 
                                                            // Define the allowed branch IDs
                                                            $allowed_branch_ids = [1, 4, 5];

                                                            foreach ($branches as $branch): 
                                                                // Check if the branch ID is in the allowed list
                                                                if (in_array($branch['id'], $allowed_branch_ids)): 
                                                            ?>
                                                                <option value="<?= $branch['id'] ?>" 
                                                                    <?= isset($record) && $record['branch_id'] == $branch['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($branch['name']) ?>
                                                                </option>
                                                            <?php 
                                                                endif;
                                                            endforeach; 
                                                            ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Document File *</label>
                                                    <input type="file" name="document_file" class="form-control" 
                                                           accept=".pdf,application/pdf" 
                                                           <?= !isset($record) ? 'required' : '' ?>>
                                                    <?php if (isset($record) && !empty($record['file_path'])): ?>
                                                        <small class="form-text text-muted">
                                                            Current file: <a href="<?= htmlspecialchars($record['file_path']) ?>">View</a>
                                                        </small>
                                                    <?php endif; ?>
                                                    <small class="form-text text-muted">
                                                        Allowed formats: PDF Only (Max: 10MB)
                                                    </small>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Document
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