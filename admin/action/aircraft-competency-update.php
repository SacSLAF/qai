<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../aircraft-competency.php');
    exit();
}

// Get and validate record ID
$record_id = intval($_POST['record_id']);
if ($record_id <= 0) {
    header('Location: ../aircraft-competency.php?error=invalid_id');
    exit();
}

// Get form data
$svc_no = trim($_POST['svc_no']);
$rank_id = intval($_POST['rank_id']);
$name = trim($_POST['name']);
$branch = !empty($_POST['branch']) ? intval($_POST['branch']) : NULL;
$trade = trim($_POST['trade']);
$formation_id = !empty($_POST['formation_id']) ? intval($_POST['formation_id']) : NULL;
$posted_in_date = !empty($_POST['posted_in_date']) ? $_POST['posted_in_date'] : NULL;
$posted_out_date = !empty($_POST['posted_out_date']) ? $_POST['posted_out_date'] : NULL;
$type_id = intval($_POST['type_id']);
$competency_level = trim($_POST['competency_level']);
$training_start_date = !empty($_POST['training_start_date']) ? $_POST['training_start_date'] : NULL;
$training_end_date = !empty($_POST['training_end_date']) ? $_POST['training_end_date'] : NULL;
$formation_ref = !empty($_POST['formation_ref']) ? trim($_POST['formation_ref']) : NULL;
$for_ref_date = !empty($_POST['for_ref_date']) ? $_POST['for_ref_date'] : NULL;
$qai_ref = !empty($_POST['qai_ref']) ? trim($_POST['qai_ref']) : NULL;
$qai_ref_date = !empty($_POST['qai_ref_date']) ? $_POST['qai_ref_date'] : NULL;
$dt_ref = !empty($_POST['dt_ref']) ? trim($_POST['dt_ref']) : NULL;
$dt_ref_date = !empty($_POST['dt_ref_date']) ? $_POST['dt_ref_date'] : NULL;
$qao_ref = !empty($_POST['qao_ref']) ? trim($_POST['qao_ref']) : NULL;
$qao_ref_date = !empty($_POST['qao_ref_date']) ? $_POST['qao_ref_date'] : NULL;
$theory_marks = !empty($_POST['theory_marks']) ? floatval($_POST['theory_marks']) : NULL;
$practical_marks = !empty($_POST['practical_marks']) ? floatval($_POST['practical_marks']) : NULL;
$competency_issue_ref = !empty($_POST['competency_issue_ref']) ? trim($_POST['competency_issue_ref']) : NULL;
$com_issue_date = !empty($_POST['com_issue_date']) ? $_POST['com_issue_date'] : NULL;
$competency_renew_ref = !empty($_POST['competency_renew_ref']) ? trim($_POST['competency_renew_ref']) : NULL;
$renew_date = !empty($_POST['renew_date']) ? $_POST['renew_date'] : NULL;
$certificate_no = !empty($_POST['certificate_no']) ? trim($_POST['certificate_no']) : NULL;
$cer_issued_date = !empty($_POST['cer_issued_date']) ? $_POST['cer_issued_date'] : NULL;
$retired_date = !empty($_POST['retired_date']) ? $_POST['retired_date'] : NULL;
$remarks = !empty($_POST['remarks']) ? trim($_POST['remarks']) : NULL;

// Validate required fields
if (empty($svc_no) || empty($rank_id) || empty($name) || empty($trade) || empty($type_id) || empty($competency_level)) {
    header("Location: ../edit-aircraft-competency.php?id=$record_id&error=missing_required_fields");
    exit();
}

try {
    // Prepare update query
    $sql = "UPDATE aircraft_competency SET 
            `svc_no` = ?,
            `rank` = ?,
            `name` = ?,
            `branch` = ?,
            `trade` = ?,
            `formation_id` = ?,
            `posted_in_date` = ?,
            `posted_out_date` = ?,
            `type_id` = ?,
            `competency_level` = ?,
            `training_start_date` = ?,
            `training_end_date` = ?,
            `formation_ref` = ?,
            `for_ref_date` = ?,
            `qai_ref` = ?,
            `qai_ref_date` = ?,
            `dt_ref` = ?,
            `dt_ref_date` = ?,
            `qao_ref` = ?,
            `qao_ref_date` = ?,
            `theory_marks` = ?,
            `practical_marks` = ?,
            `competency_issue_ref` = ?,
            `com_issue_date` = ?,
            `competency_renew_ref` = ?,
            `renew_date` = ?,
            `certificate_no` = ?,
            `cer_issued_date` = ?,
            `retired_date` = ?,
            `remarks` = ?
            WHERE record_id = ?";

    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $db->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sisssississsssssssssddssssssssi",
        $svc_no,
        $rank_id,
        $name,
        $branch,
        $trade,
        $formation_id,
        $posted_in_date,
        $posted_out_date,
        $type_id,
        $competency_level,
        $training_start_date,
        $training_end_date,
        $formation_ref,
        $for_ref_date,
        $qai_ref,
        $qai_ref_date,
        $dt_ref,
        $dt_ref_date,
        $qao_ref,
        $qao_ref_date,
        $theory_marks,
        $practical_marks,
        $competency_issue_ref,
        $com_issue_date,
        $competency_renew_ref,
        $renew_date,
        $certificate_no,
        $cer_issued_date,
        $retired_date,
        $remarks,
        $record_id
    );

    // Execute update
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: ../edit-aircraft-competency.php?id=$record_id&success=1");
        } else {
            header("Location: ../edit-aircraft-competency.php?id=$record_id&error=no_changes_made");
        }
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Aircraft competency update error: " . $e->getMessage());
    header("Location: ../edit-aircraft-competency.php?id=$record_id&error=database_error");
}

$db->close();
exit();