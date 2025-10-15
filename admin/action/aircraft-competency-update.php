<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../aircraft-competency.php');
    exit();
}

function toNull($v) { return isset($v) && $v !== '' ? $v : null; }

$id = isset($_POST['record_id']) ? intval($_POST['record_id']) : null;
if (!$id) {
    header('Location: ../aircraft-competency.php?error=invalid_id');
    exit();
}

$svc_no = trim($_POST['svc_no'] ?? '');
$rank = trim($_POST['rank'] ?? '');
$name = trim($_POST['name'] ?? '');

if ($svc_no === '' || $rank === '' || $name === '') {
    header('Location: ../edit-aircraft-competency.php?id=' . $id . '&error=required');
    exit();
}

// Map branch id to name
$branch = toNull($_POST['branch'] ?? '');
if (!empty($_POST['branch_id'])) {
    $bid = intval($_POST['branch_id']);
    $r = $db->query("SELECT name FROM ac_categories WHERE id = $bid");
    if ($r && $row = $r->fetch_assoc()) $branch = $row['name'];
}

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
$is_active = isset($_POST['is_active']) ? 1 : 0;

$sql = "UPDATE aircraft_competency SET
    svc_no = ?, `rank` = ?, name = ?, branch = ?, trade = ?, formation_id = ?, posted_in_date = ?, posted_out_date = ?,
    type_id = ?, competency_level = ?, training_start_date = ?, training_end_date = ?, formation_ref = ?, for_ref_date = ?,
    qai_ref = ?, qai_ref_date = ?, dt_ref = ?, dt_ref_date = ?, qao_ref = ?, qao_ref_date = ?, theory_marks = ?, practical_marks = ?,
    competency_issue_ref = ?, com_issue_date = ?, competency_renew_ref = ?, renew_date = ?, certificate_no = ?, cer_issued_date = ?,
    retired_date = ?, remarks = ?, is_active = ?
    WHERE record_id = ? OR id = ?";

$stmt = $db->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $db->error);
    header('Location: ../edit-aircraft-competency.php?id=' . $id . '&error=prepare');
    exit();
}

$stmt->bind_param(
    'sssssisisssssssssssssssssssssiii',
    $svc_no, $rank, $name, $branch, $trade, $formation_id, $posted_in_date, $posted_out_date,
    $type_id, $competency_level, $training_start_date, $training_end_date, $formation_ref, $for_ref_date,
    $qai_ref, $qai_ref_date, $dt_ref, $dt_ref_date, $qao_ref, $qao_ref_date, $theory_marks, $practical_marks,
    $competency_issue_ref, $com_issue_date, $competency_renew_ref, $renew_date, $certificate_no, $cer_issued_date,
    $retired_date, $remarks, $is_active,
    $id, $id
);

if ($stmt->execute()) {
    header('Location: ../aircraft-competency.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../edit-aircraft-competency.php?id=' . $id . '&error=execute');
    exit();
}
