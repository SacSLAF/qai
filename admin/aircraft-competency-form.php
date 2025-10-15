<?php
require_once '../includes/config.php';
// session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch data for dropdowns
$branches = [];
$formations = [];
$types = [];

// Fetch branches from ac_categories table
$branches_result = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
if ($branches_result) {
    $branches = $branches_result->fetch_all(MYSQLI_ASSOC);
}

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

include "template/head.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Aircraft Competency Record</title>
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
                            <h4 class="card-title">Add Aircraft Competency Record</h4>
                            <a href="aircraft-competency.php" class="btn btn-sm btn-outline-secondary">
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

                            <form action="action/aircraft-competency-save.php" method="post">
                                <div class="row">
                                    <!-- Personal Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Personal Details</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">SVC Number *</label>
                                                    <input type="text" name="svc_no" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Rank *</label>
                                                    <input type="text" name="rank" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Name *</label>
                                                    <input type="text" name="name" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Branch</label>
                                                    <select name="branch_id" class="form-select">
                                                        <option value="">Select Branch</option>
                                                        <?php foreach ($branches as $branch): ?>
                                                            <option value="<?= $branch['id'] ?>">
                                                                <?= htmlspecialchars($branch['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Trade</label>
                                                    <input type="text" name="trade" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Posting Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Posting Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation</label>
                                                    <select name="formation_id" class="form-select">
                                                        <option value="">Select Formation</option>
                                                        <?php foreach ($formations as $formation): ?>
                                                            <option value="<?= $formation['formation_id'] ?>">
                                                                <?= htmlspecialchars($formation['formation_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Posted In Date</label>
                                                    <input type="date" name="posted_in_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Posted Out Date</label>
                                                    <input type="date" name="posted_out_date" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Aircraft & Competency -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-plane me-2"></i>Aircraft & Competency Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Aircraft Type *</label>
                                                    <select name="type_id" class="form-select" required>
                                                        <option value="">Select Type</option>
                                                        <?php foreach ($types as $type): ?>
                                                            <option value="<?= $type['type_id'] ?>">
                                                                <?= htmlspecialchars($type['type_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Level *</label>
                                                    <select name="competency_level" class="form-select" required>
                                                        <option value="">Select Level</option>
                                                        <option value="First Line">First Line</option>
                                                        <option value="Second Line">Second Line</option>
                                                        <option value="Third Line">Third Line</option>
                                                        <option value="Specialist">Specialist</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Training & References -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Training & References</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Training Start Date</label>
                                                    <input type="date" name="training_start_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Training End Date</label>
                                                    <input type="date" name="training_end_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation Reference</label>
                                                    <input type="text" name="formation_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation Ref Date</label>
                                                    <input type="date" name="for_ref_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAI Reference</label>
                                                    <input type="text" name="qai_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAI Ref Date</label>
                                                    <input type="date" name="qai_ref_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">DT Reference</label>
                                                    <input type="text" name="dt_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">DT Ref Date</label>
                                                    <input type="date" name="dt_ref_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAO Reference</label>
                                                    <input type="text" name="qao_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAO Ref Date</label>
                                                    <input type="date" name="qao_ref_date" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Assessment -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-clipboard-list me-2"></i>Assessment</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Theory Marks</label>
                                                    <input type="text" name="theory_marks" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Practical Marks</label>
                                                    <input type="text" name="practical_marks" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Issue Ref</label>
                                                    <input type="text" name="competency_issue_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Issue Date</label>
                                                    <input type="date" name="com_issue_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Renew Ref</label>
                                                    <input type="text" name="competency_renew_ref" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Renew Date</label>
                                                    <input type="date" name="renew_date" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Certificate & Other -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-certificate me-2"></i>Certificate & Other</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Certificate Number</label>
                                                    <input type="text" name="certificate_no" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Certificate Issued Date</label>
                                                    <input type="date" name="cer_issued_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Retired Date</label>
                                                    <input type="date" name="retired_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Continue with other sections (Training, Reference, Assessment, Certificate, etc.) -->
                                    <!-- ... your existing form sections for other fields ... -->

                                    <!-- Submit Button -->
                                    <div class="col-md-12">
                                        <div class="text-end">
                                            <button type="reset" class="btn btn-secondary me-2">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Record
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