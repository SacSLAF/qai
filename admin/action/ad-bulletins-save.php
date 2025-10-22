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
    header('Location: ../ad-bulletins-form.php');
    exit();
}

// Collect fields
$reference_no = trim($_POST['reference_no'] ?? '');
$bulletin_description = trim($_POST['bulletin_description'] ?? '');
$related_aircraft_id = (int)($_POST['related_aircraft_id'] ?? 0);
$formation_id = (int)($_POST['formation_id'] ?? 0);
$date_of_issue = $_POST['date_of_issue'] ?? '';

// Validate required fields
if (empty($reference_no) || empty($bulletin_description) || $related_aircraft_id <= 0 || $formation_id <= 0 || empty($date_of_issue)) {
    header('Location: ../ad-bulletins-form.php?error=' . urlencode('All fields are required'));
    exit();
}

// Check if reference number already exists (for new records)
if (!isset($_POST['id'])) {
    $check_stmt = $db->prepare("SELECT id FROM ad_bulletins WHERE reference_no = ?");
    $check_stmt->bind_param("s", $reference_no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        header('Location: ../ad-bulletins-form.php?error=' . urlencode('Reference number already exists'));
        exit();
    }
    $check_stmt->close();
}

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    
    // Check if reference number exists for other records
    $check_stmt = $db->prepare("SELECT id FROM ad_bulletins WHERE reference_no = ? AND id != ?");
    $check_stmt->bind_param("si", $reference_no, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        header('Location: ../ad-bulletins-form.php?error=' . urlencode('Reference number already exists'));
        exit();
    }
    $check_stmt->close();
    
    $sql = "UPDATE ad_bulletins SET 
            reference_no = ?, bulletin_description = ?, related_aircraft_id = ?, 
            formation_id = ?, date_of_issue = ?
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../ad-bulletins-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiisi',
        $reference_no, $bulletin_description, $related_aircraft_id,
        $formation_id, $date_of_issue, $id
    );
} else {
    // Insert new record
    $sql = "INSERT INTO ad_bulletins (
        reference_no, bulletin_description, related_aircraft_id, 
        formation_id, date_of_issue
    ) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../ad-bulletins-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiis',
        $reference_no, $bulletin_description, $related_aircraft_id,
        $formation_id, $date_of_issue
    );
}

if ($stmt->execute()) {
    header('Location: ../ad-bulletins.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../ad-bulletins-form.php?error=' . urlencode('DB execute error'));
    exit();
}
?>