<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: aircraft-competency.php');
    exit();
}

$id = intval($_GET['id']);

$res = $db->query("SELECT * FROM aircraft_competency WHERE `record_id` = $id LIMIT 1");
if (!$res || $res->num_rows === 0) {
    header('Location: aircraft-competency.php?error=notfound');
    exit();
}

$record = $res->fetch_assoc();

// Load lookups
$branches = [];
$b_res = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
if ($b_res) $branches = $b_res->fetch_all(MYSQLI_ASSOC);

$formations = [];
$f_res = $db->query("SELECT formation_id, formation_name FROM formation ORDER BY formation_name");
if ($f_res) $formations = $f_res->fetch_all(MYSQLI_ASSOC);

$types = [];
$t_res = $db->query("SELECT type_id, type_name FROM type ORDER BY type_name");
if ($t_res) $types = $t_res->fetch_all(MYSQLI_ASSOC);

$ranks = [];
$r_res = $db->query("SELECT id, rank_name FROM ranks ORDER BY id");
if ($r_res) $ranks = $r_res->fetch_all(MYSQLI_ASSOC);

include 'template/head.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aircraft Competency Record</title>
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
                            <h4 class="card-title">Edit Aircraft Competency Record</h4>
                            <a href="aircraft-competency.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> Record updated successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?= htmlspecialchars($_GET['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="action/aircraft-competency-update.php" method="post">
                                <input type="hidden" name="record_id" value="<?= htmlspecialchars($record['record_id']) ?>">

                                <div class="row">
                                    <!-- Personal Details -->
                                    <div class="col-md-12 mb-4">
                                        <h5 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Personal Details</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">SVC Number *</label>
                                                    <input type="text" name="svc_no" class="form-control" value="<?= htmlspecialchars($record['svc_no']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Rank *</label>
                                                    <select name="rank_id" class="form-select" required>
                                                        <option value="" disabled>Select Rank</option>
                                                        <?php foreach ($ranks as $rank_item): ?>
                                                            <option value="<?= $rank_item['id'] ?>" 
                                                                <?= ($record['rank'] == $rank_item['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($rank_item['rank_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Name *</label>
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($record['name']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Branch</label>
                                                    <select name="branch" class="form-select">
                                                        <option value="">Select Branch</option>
                                                        <?php foreach ($branches as $branch): ?>
                                                            <option value="<?= $branch['id'] ?>" 
                                                                <?= ($record['branch'] == $branch['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($branch['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Trade</label>
                                                    <select name="trade" class="form-select" required>
                                                        <option value="" disabled>Select Trade</option>
                                                        <?php
                                                        $trades = [
                                                            "Airframe",
                                                            "Aero ENG",
                                                            "Aero E&I",
                                                            "Safety Eqpt",
                                                            "Air Radio",
                                                            "AGSE",
                                                            "Armament",
                                                            "Airframe & Power Plant",
                                                            "None Tech"
                                                        ];
                                                        foreach ($trades as $trade): ?>
                                                            <option value="<?= $trade ?>" <?= ($record['trade'] == $trade) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($trade) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
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
                                                            <option value="<?= $formation['formation_id'] ?>" 
                                                                <?= ($record['formation_id'] == $formation['formation_id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($formation['formation_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Posted In Date</label>
                                                    <input type="date" name="posted_in_date" class="form-control" value="<?= htmlspecialchars($record['posted_in_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Posted Out Date</label>
                                                    <input type="date" name="posted_out_date" class="form-control" value="<?= htmlspecialchars($record['posted_out_date']) ?>">
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
                                                            <option value="<?= $type['type_id'] ?>" 
                                                                <?= ($record['type_id'] == $type['type_id']) ? 'selected' : '' ?>>
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
                                                        <option value="" disabled>Select Level</option>
                                                        <?php
                                                        $levels = [
                                                            "First Line",
                                                            "Second Line",
                                                            "Third Line",
                                                            "Flight Line",
                                                            "Bay Level",
                                                            "Shop Level",
                                                            "Field Level",
                                                            "Depot Level",
                                                            "B1 A&P Flight Line",
                                                            "B1 A&P First Line",
                                                            "Sign and Work",
                                                            "Fiber Level 1",
                                                            "Fiber Level 2",
                                                            "Other"
                                                        ];
                                                        foreach ($levels as $level): ?>
                                                            <option value="<?= $level ?>" <?= ($record['competency_level'] == $level) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($level) ?>
                                                            </option>
                                                        <?php endforeach; ?>
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
                                                    <input type="date" name="training_start_date" class="form-control" value="<?= htmlspecialchars($record['training_start_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Training End Date</label>
                                                    <input type="date" name="training_end_date" class="form-control" value="<?= htmlspecialchars($record['training_end_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation Reference</label>
                                                    <input type="text" name="formation_ref" class="form-control" value="<?= htmlspecialchars($record['formation_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Formation Ref Date</label>
                                                    <input type="date" name="for_ref_date" class="form-control" value="<?= htmlspecialchars($record['for_ref_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAI Reference</label>
                                                    <input type="text" name="qai_ref" class="form-control" value="<?= htmlspecialchars($record['qai_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAI Ref Date</label>
                                                    <input type="date" name="qai_ref_date" class="form-control" value="<?= htmlspecialchars($record['qai_ref_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">DT Reference</label>
                                                    <input type="text" name="dt_ref" class="form-control" value="<?= htmlspecialchars($record['dt_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">DT Ref Date</label>
                                                    <input type="date" name="dt_ref_date" class="form-control" value="<?= htmlspecialchars($record['dt_ref_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAO Reference</label>
                                                    <input type="text" name="qao_ref" class="form-control" value="<?= htmlspecialchars($record['qao_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">QAO Ref Date</label>
                                                    <input type="date" name="qao_ref_date" class="form-control" value="<?= htmlspecialchars($record['qao_ref_date']) ?>">
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
                                                    <input type="number" step="0.01" name="theory_marks" class="form-control" value="<?= htmlspecialchars($record['theory_marks']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Practical Marks</label>
                                                    <input type="number" step="0.01" name="practical_marks" class="form-control" value="<?= htmlspecialchars($record['practical_marks']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Issue Ref</label>
                                                    <input type="text" name="competency_issue_ref" class="form-control" value="<?= htmlspecialchars($record['competency_issue_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Issue Date</label>
                                                    <input type="date" name="com_issue_date" class="form-control" value="<?= htmlspecialchars($record['com_issue_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Competency Renew Ref</label>
                                                    <input type="text" name="competency_renew_ref" class="form-control" value="<?= htmlspecialchars($record['competency_renew_ref']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Renew Date</label>
                                                    <input type="date" name="renew_date" class="form-control" value="<?= htmlspecialchars($record['renew_date']) ?>">
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
                                                    <input type="text" name="certificate_no" class="form-control" value="<?= htmlspecialchars($record['certificate_no']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Certificate Issued Date</label>
                                                    <input type="date" name="cer_issued_date" class="form-control" value="<?= htmlspecialchars($record['cer_issued_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Retired Date</label>
                                                    <input type="date" name="retired_date" class="form-control" value="<?= htmlspecialchars($record['retired_date']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($record['remarks']) ?></textarea>
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
                                                <i class="fas fa-save"></i> Update Record
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