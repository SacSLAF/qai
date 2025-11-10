<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$establishments = [];
$placement_options = ['1st', '2nd', '3rd'];
$award_types = [
    'qcc' => 'Best Quality Control Circle Award',
    'environment' => 'Best Environment Management Project Award'
];

// Fetch SLAF establishments
$est_result = $db->query("SELECT id, name, code FROM slaf_establishments WHERE is_active = 1 ORDER BY name");
if ($est_result) {
    $establishments = $est_result->fetch_all(MYSQLI_ASSOC);
}

// Set default values
$default_section_id = 5;  // Default to productivity section
$default_category_id = 4; // Default category for Awards

// If editing, fetch existing record
$record = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
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
    <title><?= isset($record) ? 'Edit' : 'Add' ?> Award</title>
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
                            <h4 class="card-title"><?= isset($record) ? 'Edit' : 'Add' ?> Award</h4>
                            <a href="awards.php" class="btn btn-sm btn-outline-secondary">
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

                            <form action="action/awards-save.php" method="post">
                                <?php if (isset($record)): ?>
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                <?php endif; ?>

                                <!-- Hidden fields for default values -->
                                <input type="hidden" name="section_id" value="<?= $default_section_id ?>">
                                <input type="hidden" name="category_id" value="<?= $default_category_id ?>">
                                <input type="hidden" name="created_by" value="<?= $_SESSION['admin_id'] ?>">

                                <div class="row">
                                    <!-- Award Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-trophy me-2"></i>Award Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Serial Number *</label>
                                                    <input type="text" name="sno" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['sno']) : '' ?>" 
                                                           required placeholder="e.g., AW-001">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Year *</label>
                                                    <input type="number" name="year" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['year']) : date('Y') ?>" 
                                                           required min="2000" max="2030">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Award Type *</label>
                                                    <select name="award_type" class="form-select" id="awardType" required>
                                                        <option value="">Select Award Type</option>
                                                        <?php foreach ($award_types as $value => $label): ?>
                                                            <option value="<?= $value ?>" 
                                                                <?= isset($record) && $record['award_type'] == $value ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($label) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
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
                                            <div class="col-md-6" id="qccNameField">
                                                <div class="mb-3">
                                                    <label class="form-label">QCC Name *</label>
                                                    <input type="text" name="qcc_name" class="form-control" 
                                                           value="<?= isset($record) ? htmlspecialchars($record['qcc_name']) : '' ?>" 
                                                           placeholder="Enter QCC Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Placement *</label>
                                                    <select name="placement" class="form-select" required>
                                                        <option value="">Select Placement</option>
                                                        <?php foreach ($placement_options as $placement): ?>
                                                            <option value="<?= $placement ?>" 
                                                                <?= isset($record) && $record['placement'] == $placement ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($placement) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Team Members *</label>
                                                    <textarea name="team_members" class="form-control" rows="3" 
                                                              required placeholder="Enter team members (comma separated)"><?= isset($record) ? htmlspecialchars($record['team_members']) : '' ?></textarea>
                                                    <small class="form-text text-muted">Separate team members with commas</small>
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
                                                    <small class="form-text text-muted">Default section for awards</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <input type="text" class="form-control" value="Awards" readonly style="background-color: #f8f9fa;">
                                                    <small class="form-text text-muted">Default category for awards</small>
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
                                                <i class="fas fa-save"></i> <?= isset($record) ? 'Update' : 'Save' ?> Award
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

    <script>
        // Show/hide fields based on award type
        document.getElementById('awardType').addEventListener('change', function() {
            const awardType = this.value;
            const qccNameField = document.getElementById('qccNameField');
            
            if (awardType === 'qcc') {
                qccNameField.style.display = 'block';
                // Make QCC name required
                document.querySelector('input[name="qcc_name"]').required = true;
            } else {
                qccNameField.style.display = 'none';
                // Make QCC name not required for environment awards
                document.querySelector('input[name="qcc_name"]').required = false;
            }
        });

        // Trigger change event on page load to set initial state
        document.getElementById('awardType').dispatchEvent(new Event('change'));
    </script>
</body>
</html>