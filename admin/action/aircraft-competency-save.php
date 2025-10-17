<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../aircraft-competency-form.php');
    exit();
}

// Helper functions for proper type handling
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

function toIntNull($v) {
    return isset($v) && $v !== '' ? (int)$v : null;
}

function toFloatNull($v) {
    return isset($v) && $v !== '' ? (float)$v : null;
}

// Handle branch - assuming you want to store the ID, not name
$branch = toIntNull($_POST['branch_id'] ?? $_POST['branch'] ?? '');

// Collect fields with proper type handling
$svc_no = toNull($_POST['svc_no'] ?? '');
$rank = toNull($_POST['rank'] ?? '');
$name = toNull($_POST['name'] ?? '');
$trade = toNull($_POST['trade'] ?? '');
// var_dump($trade);exit();
$formation_id = toIntNull($_POST['formation_id'] ?? '');
$posted_in_date = toNull($_POST['posted_in_date'] ?? '');
$posted_out_date = toNull($_POST['posted_out_date'] ?? '');
$type_id = toIntNull($_POST['type_id'] ?? '');
$competency_level = toNull($_POST['competency_level'] ?? '');
$training_start_date = toNull($_POST['training_start_date'] ?? '');
$training_end_date = toNull($_POST['training_end_date'] ?? '');
$formation_ref = toNull($_POST['formation_ref'] ?? '');
$for_ref_date = toNull($_POST['for_ref_date'] ?? '');
$qai_ref = toNull($_POST['qai_ref'] ?? '');
$qai_ref_date = toNull($_POST['qai_ref_date'] ?? '');
$dt_ref = toNull($_POST['dt_ref'] ?? '');
$dt_ref_date = toNull($_POST['dt_ref_date'] ?? '');
$qao_ref = toNull($_POST['qao_ref'] ?? '');
$qao_ref_date = toNull($_POST['qao_ref_date'] ?? '');
$theory_marks = toFloatNull($_POST['theory_marks'] ?? '');
$practical_marks = toFloatNull($_POST['practical_marks'] ?? '');
$competency_issue_ref = toNull($_POST['competency_issue_ref'] ?? '');
$com_issue_date = toNull($_POST['com_issue_date'] ?? '');
$competency_renew_ref = toNull($_POST['competency_renew_ref'] ?? '');
$renew_date = toNull($_POST['renew_date'] ?? '');
$certificate_no = toNull($_POST['certificate_no'] ?? '');
$cer_issued_date = toNull($_POST['cer_issued_date'] ?? '');
$retired_date = toNull($_POST['retired_date'] ?? '');
$remarks = toNull($_POST['remarks'] ?? '');

// Debug: Check what values you're getting
// error_log("Branch: " . $branch . ", Type: " . gettype($branch));
// error_log("Trade: " . $trade . ", Type: " . gettype($trade));
// error_log("Formation ID: " . $formation_id . ", Type: " . gettype($formation_id));

$sql = "INSERT INTO aircraft_competency (
    `svc_no`, `rank`, `name`, `branch`, `trade`, `formation_id`, `posted_in_date`, `posted_out_date`,
    `type_id`, `competency_level`, `training_start_date`, `training_end_date`, `formation_ref`, `for_ref_date`,
    `qai_ref`, `qai_ref_date`, `dt_ref`, `dt_ref_date`, `qao_ref`, `qao_ref_date`, `theory_marks`, `practical_marks`,
    `competency_issue_ref`, `com_issue_date`, `competency_renew_ref`, `renew_date`, `certificate_no`, `cer_issued_date`,
    `retired_date`, `remarks` 
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $db->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $db->error);
    header('Location: ../aircraft-competency-form.php?error=' . urlencode('DB prepare error: ' . $db->error));
    exit();
}

// Use 'i' for integers and 'd' for decimals, 's' for strings
$stmt->bind_param(
    'ssssssssisssssssssssddisssssss', // Changed to match actual types
    $svc_no, $rank, $name, $branch, $trade, $formation_id, $posted_in_date, $posted_out_date,
    $type_id, $competency_level, $training_start_date, $training_end_date, $formation_ref, $for_ref_date,
    $qai_ref, $qai_ref_date, $dt_ref, $dt_ref_date, $qao_ref, $qao_ref_date, $theory_marks, $practical_marks,
    $competency_issue_ref, $com_issue_date, $competency_renew_ref, $renew_date, $certificate_no, $cer_issued_date,
    $retired_date, $remarks
);

if ($stmt->execute()) {
    header('Location: ../aircraft-competency.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../aircraft-competency-form.php?error=' . urlencode('DB execute error: ' . $stmt->error));
    exit();
}