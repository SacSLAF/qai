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
    header('Location: ../qai-newsletters-form.php');
    exit();
}

// Collect fields
$qsn_no = trim($_POST['qsn_no'] ?? '');
$description = trim($_POST['description'] ?? '');
$issue_date = $_POST['issue_date'] ?? '';

// Validate required fields
if (empty($qsn_no) || empty($description) || empty($issue_date)) {
    header('Location: ../qai-newsletters-form.php?error=' . urlencode('All fields are required'));
    exit();
}

// Check if QSN number already exists (for new records)
if (!isset($_POST['id'])) {
    $check_stmt = $db->prepare("SELECT id FROM qai_newsletters WHERE sno = ?");
    $check_stmt->bind_param("s", $qsn_no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        header('Location: ../qai-newsletters-form.php?error=' . urlencode('QSN number already exists'));
        exit();
    }
    $check_stmt->close();
}

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    
    // Check if QSN number exists for other records
    $check_stmt = $db->prepare("SELECT id FROM qai_newsletters WHERE sno = ? AND id != ?");
    $check_stmt->bind_param("si", $qsn_no, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        header('Location: ../qai-newsletters-form.php?error=' . urlencode('QSN number already exists'));
        exit();
    }
    $check_stmt->close();
    
    $sql = "UPDATE qai_newsletters SET 
            sno = ?, description = ?, issue_date = ?
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../qai-newsletters-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'sssi',
        $qsn_no, $description, $issue_date, $id
    );
} else {
    // Insert new record
    $sql = "INSERT INTO qai_newsletters (
        sno, description, issue_date
    ) VALUES (?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../qai-newsletters-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'sss',
        $qsn_no, $description, $issue_date
    );
}

if ($stmt->execute()) {
    header('Location: ../qai-newsletters.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../qai-newsletters-form.php?error=' . urlencode('DB execute error'));
    exit();
}
?>