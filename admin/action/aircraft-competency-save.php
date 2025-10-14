<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../aircraft-competency-form.php');
    exit();
}

// Helper to convert empty strings to NULL
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

// Map branch_id -> ac_categories.name if provided
$branch_name = null;
if (!empty($_POST['branch_id'])) {
    $branch_id = (int)$_POST['branch_id'];
    $r = $db->query("SELECT name FROM ac_categories WHERE id = $branch_id");
    if ($r && $row = $r->fetch_assoc()) $branch_name = $row['name'];
}

// Collect fields
$svc_no = toNull($_POST['svc_no'] ?? '');
$rank = toNull($_POST['rank'] ?? '');
$name = toNull($_POST['name'] ?? '');
$branch = $branch_name ?? toNull($_POST['branch'] ?? '');
$trade = toNull($_POST['trade'] ?? '');
$formation_id = toNull($_POST['formation_id'] ?? '');
$posted_in_date = toNull($_POST['posted_in_date'] ?? '');
$posted_out_date = toNull($_POST['posted_out_date'] ?? '');
$type_id = toNull($_POST['type_id'] ?? '');
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
$theory_marks = toNull($_POST['theory_marks'] ?? '');
$practical_marks = toNull($_POST['practical_marks'] ?? '');
$competency_issue_ref = toNull($_POST['competency_issue_ref'] ?? '');
$com_issue_date = toNull($_POST['com_issue_date'] ?? '');
$competency_renew_ref = toNull($_POST['competency_renew_ref'] ?? '');
$renew_date = toNull($_POST['renew_date'] ?? '');
$certificate_no = toNull($_POST['certificate_no'] ?? '');
$cer_issued_date = toNull($_POST['cer_issued_date'] ?? '');
$retired_date = toNull($_POST['retired_date'] ?? '');
$remarks = toNull($_POST['remarks'] ?? '');

$sql = "INSERT INTO aircraft_competency (
    svc_no, rank, name, branch, trade, formation_id, posted_in_date, posted_out_date,
    type_id, competency_level, training_start_date, training_end_date, formation_ref, for_ref_date,
    qai_ref, qai_ref_date, dt_ref, dt_ref_date, qao_ref, qao_ref_date, theory_marks, practical_marks,
    competency_issue_ref, com_issue_date, competency_renew_ref, renew_date, certificate_no, cer_issued_date,
    retired_date, remarks 
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $db->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $db->error);
    header('Location: ../aircraft-competency-form.php?error=' . urlencode('DB prepare error'));
    exit();
}

$stmt->bind_param(
    'sssssisisssssssssssssssssssssi',
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
    header('Location: ../aircraft-competency-form.php?error=' . urlencode('DB execute error'));
    exit();
}
