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
    header('Location: ../rnd-form.php');
    exit();
}

// Helper to convert empty strings to NULL
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

// Collect fields
$rnd_no = toNull($_POST['rnd_no'] ?? '');
$directorate = toNull($_POST['directorate'] ?? '');
$formation_id = toNull($_POST['formation_id'] ?? '');
$type_id = toNull($_POST['type_id'] ?? '');
$description = toNull($_POST['description'] ?? '');
$issue_date = toNull($_POST['issue_date'] ?? '');
$created_by = $_SESSION['admin_id'];

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    $sql = "UPDATE rnd SET 
            rnd_no = ?, directorate = ?, formation_id = ?, type_id = ?, 
            description = ?, issue_date = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../rnd-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiissi',
        $rnd_no, $directorate, $formation_id, $type_id,
        $description, $issue_date, $id
    );
} else {
    // Insert new record
    $sql = "INSERT INTO rnd (
        rnd_no, directorate, formation_id, type_id, description, 
        issue_date, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../rnd-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiissi',
        $rnd_no, $directorate, $formation_id, $type_id,
        $description, $issue_date, $created_by
    );
}

if ($stmt->execute()) {
    header('Location: ../rnd.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../rnd-form.php?error=' . urlencode('DB execute error'));
    exit();
}