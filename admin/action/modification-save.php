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
    header('Location: ../modification-form.php');
    exit();
}

// Helper to convert empty strings to NULL
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

// Collect fields
$mod_no = toNull($_POST['mod_no'] ?? '');
$directorate = toNull($_POST['directorate'] ?? '');
$formation_id = toNull($_POST['formation_id'] ?? '');
$type_id = toNull($_POST['type_id'] ?? '');
$description = toNull($_POST['description'] ?? '');
$recommended_date = toNull($_POST['recommended_date'] ?? '');
$created_by = $_SESSION['admin_id'];

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    $sql = "UPDATE modification SET 
            mod_no = ?, directorate = ?, formation_id = ?, type_id = ?, 
            description = ?, recommended_date = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../modification-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiissi',
        $mod_no, $directorate, $formation_id, $type_id,
        $description, $recommended_date, $id
    );
} else {
    // Insert new record
    $sql = "INSERT INTO modification (
        mod_no, directorate, formation_id, type_id, description, 
        recommended_date, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../modification-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiissi',
        $mod_no, $directorate, $formation_id, $type_id,
        $description, $recommended_date, $created_by
    );
}

if ($stmt->execute()) {
    header('Location: ../modification.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../modification-form.php?error=' . urlencode('DB execute error'));
    exit();
}