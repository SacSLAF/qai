<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$ranks = [];
$ac_categories = [];
$record = null;

try {
    // Fetch ranks - with multiple possible column name variations
    $ranks_query = "SELECT id, rank_name FROM ranks ORDER BY id ASC";
    $ranks_result = $db->query($ranks_query);
    
    if ($ranks_result && $ranks_result->num_rows > 0) {
        $ranks = $ranks_result->fetch_all(MYSQLI_ASSOC);
    } else {
        // Alternative query if rank_name doesn't exist
        $ranks_result = $db->query("SELECT id, name as rank_name FROM ranks ORDER BY id ASC");
        if ($ranks_result && $ranks_result->num_rows > 0) {
            $ranks = $ranks_result->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    // Fetch AC Categories - with multiple possible column name variations
    $ac_categories_query = "SELECT id, name FROM ac_categories ORDER BY name";
    $ac_categories_result = $db->query($ac_categories_query);
    
    if ($ac_categories_result && $ac_categories_result->num_rows > 0) {
        $ac_categories = $ac_categories_result->fetch_all(MYSQLI_ASSOC);
    } else {
        // Alternative query if table name is different
        $ac_categories_result = $db->query("SELECT id, category_name as name FROM aircraft_categories ORDER BY category_name");
        if ($ac_categories_result && $ac_categories_result->num_rows > 0) {
            $ac_categories = $ac_categories_result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // If editing, fetch existing record
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT * FROM training_records WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $record = $result->fetch_assoc();
            }
            $stmt->close();
        }
    }

} catch (Exception $e) {
    error_log("Error in training-records-form.php: " . $e->getMessage());
    // Don't show error to user, just log it
}

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Training Record</title>
</head>
<body>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Training Record</h4>
                            <a href="training-records.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <!-- Debug information (remove in production) -->
                            <div class="alert alert-info d-none">
                                <strong>Debug Info:</strong><br>
                                Ranks found: <?= count($ranks) ?><br>
                                Categories found: <?= count($ac_categories) ?>
                            </div>

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

                            <form action="action/training-records-save.php" method="post">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <!-- Personal Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Personal Details</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">S/No</label>
                                                    <input type="text" name="sno" class="form-control" 
                                                        value="<?= isset($record) && $record['sno'] ? htmlspecialchars($record['sno']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">SVC No *</label>
                                                    <input type="text" name="svc_no" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['svc_no']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Rank *</label>
                                                    <select name="rank_id" class="form-select" required>
                                                        <option value="">Select Rank</option>
                                                        <?php if (!empty($ranks)): ?>
                                                            <?php foreach ($ranks as $rank): ?>
                                                                <option value="<?= $rank['id'] ?>" 
                                                                    <?= isset($record) && $record['rank_id'] == $rank['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($rank['rank_name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="">No ranks available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (empty($ranks)): ?>
                                                        <small class="text-danger">No ranks found in database. Please add ranks first.</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Name *</label>
                                                    <input type="text" name="name" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['name']) : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Training Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Training Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Training Type *</label>
                                                    <select name="training_type" class="form-select" required>
                                                        <option value="">Select Training Type</option>
                                                        <option value="cpd-inhouse" <?= isset($record) && $record['training_type'] == 'cpd-inhouse' ? 'selected' : '' ?>>CPD In-House</option>
                                                        <option value="cpd-outside" <?= isset($record) && $record['training_type'] == 'cpd-outside' ? 'selected' : '' ?>>CPD Outside</option>
                                                        <option value="workshop-inhouse" <?= isset($record) && $record['training_type'] == 'workshop-inhouse' ? 'selected' : '' ?>>Workshop In-House</option>
                                                        <option value="workshop-outside" <?= isset($record) && $record['training_type'] == 'workshop-outside' ? 'selected' : '' ?>>Workshop Outside</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Course Name *</label>
                                                    <input type="text" name="course_name" class="form-control" 
                                                        value="<?= isset($record) ? htmlspecialchars($record['course_name']) : '' ?>" 
                                                        required placeholder="Enter full course name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Directorate</label>
                                                    <select name="ac_category_id" class="form-select">
                                                        <option value="">Select Category</option>
                                                        <?php if (!empty($ac_categories)): ?>
                                                            <?php foreach ($ac_categories as $category): ?>
                                                                <option value="<?= $category['id'] ?>" 
                                                                    <?= isset($record) && $record['ac_category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($category['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="">No categories available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (empty($ac_categories)): ?>
                                                        <small class="text-danger">No aircraft categories found in database.</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
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
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Cert. Number</label>
                                                    <input type="text" name="cert_number" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['cert_number']) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Start Date *</label>
                                                    <input type="date" name="start_date" class="form-control" 
                                                           value="<?= isset($record) && $record['start_date'] ? $record['start_date'] : '' ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">End Date *</label>
                                                    <input type="date" name="end_date" class="form-control" 
                                                        value="<?= isset($record) && $record['end_date'] ? $record['end_date'] : '' ?>" 
                                                        required>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Training Record
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

    <div class="footer">
        <div class="copyright">
            <p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">Directorate of Information Technology. Sri Lanka Air Force.</a> 2025</p>
        </div>
    </div>

    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/deznav-init.js"></script>
</body>
</html>