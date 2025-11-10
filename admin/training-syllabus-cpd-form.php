<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$categories = [];

// Fetch AC Categories
$cat_result = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
if ($cat_result) {
    $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
}

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT tsc.*, ac.name as category_name 
                         FROM training_syllabus_cpd tsc 
                         LEFT JOIN ac_categories ac ON tsc.ac_categories_id = ac.id 
                         WHERE tsc.id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Training Syllabus CPD</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Training Syllabus CPD</h4>
                            <a href="training-syllabus-cpd.php" class="btn btn-sm btn-outline-secondary">
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

                            <form action="action/training-syllabus-cpd-save.php" method="post" enctype="multipart/form-data">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                    <input type="hidden" name="existing_file" value="<?= $record['file_path'] ?>">
                                <?php endif; ?>

                                <input type="hidden" name="created_by" value="<?= $_SESSION['admin_id'] ?>">

                                <div class="row">
                                    <!-- CPD Syllabus Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-book me-2"></i>CPD Syllabus Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Syllabus Number *</label>
                                                    <input type="text" name="syllabus_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['syllabus_no']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Trade</label>
                                                    <select name="trade" class="form-select">
                                                        <option value="">Select Trade</option>
                                                        <option value="Airframe" <?= isset($record) && $record['trade'] == 'Airframe' ? 'selected' : '' ?>>Airframe</option>
                                                        <option value="Aero ENG" <?= isset($record) && $record['trade'] == 'Aero ENG' ? 'selected' : '' ?>>Aero ENG</option>
                                                        <option value="Aero E&I" <?= isset($record) && $record['trade'] == 'Aero E&I' ? 'selected' : '' ?>>Aero E&I</option>
                                                        <option value="Safety Eqpt" <?= isset($record) && $record['trade'] == 'Safety Eqpt' ? 'selected' : '' ?>>Safety Eqpt</option>
                                                        <option value="Air Radio" <?= isset($record) && $record['trade'] == 'Air Radio' ? 'selected' : '' ?>>Air Radio</option>
                                                        <option value="AGSE" <?= isset($record) && $record['trade'] == 'AGSE' ? 'selected' : '' ?>>AGSE</option>
                                                        <option value="Armament" <?= isset($record) && $record['trade'] == 'Armament' ? 'selected' : '' ?>>Armament</option>
                                                        <option value="Airframe & Power Plant" <?= isset($record) && $record['trade'] == 'Airframe & Power Plant' ? 'selected' : '' ?>>Airframe & Power Plant</option>
                                                        <option value="None Tech" <?= isset($record) && $record['trade'] == 'None Tech' ? 'selected' : '' ?>>None Tech</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Course Description *</label>
                                                    <textarea name="course_description" class="form-control" rows="4" 
                                                              required placeholder="Enter course description"><?= isset($record) ? htmlspecialchars($record['course_description']) : '' ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Issue</label>
                                                    <input type="text" name="issue" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['issue']) : '' ?>" 
                                                           >
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Revision</label>
                                                    <input type="text" name="revision" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['revision']) : '' ?>" 
                                                           >
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Revised Date</label>
                                                    <input type="date" name="revised_date" class="form-control" 
                                                           value="<?= isset($record) && $record['revised_date'] ? htmlspecialchars($record['revised_date']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Category *</label>
                                                    <select name="ac_categories_id" class="form-select" required>
                                                        <option value="">Select Category</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?= $cat['id'] ?>" 
                                                                <?= isset($record) && $record['ac_categories_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cat['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Syllabus File *</label>
                                                    <input type="file" name="syllabus_file" class="form-control" 
                                                           accept=".pdf" 
                                                           <?= !isset($record) ? 'required' : '' ?>>
                                                    <?php if (isset($record) && !empty($record['file_path'])): ?>
                                                        <small class="form-text text-muted">
                                                            Current file: <a href="<?= htmlspecialchars($record['file_path']) ?>" target="_blank">View</a>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> CPD Syllabus
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