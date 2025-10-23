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
    header('Location: ../latitude-form.php');
    exit();
}

// Helper to convert empty strings to NULL
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

// Collect fields
$active = $_POST['active'] ?? 'YES';
$type = toNull($_POST['type'] ?? 'Latitude');
$formation_id = toNull($_POST['formation_id'] ?? '');
$aircraft_type_id = toNull($_POST['aircraft_type_id'] ?? '');
$tail_no = toNull($_POST['tail_no'] ?? '');
$part_no = toNull($_POST['part_no'] ?? '');
$description = toNull($_POST['description'] ?? '');
$serial_no = toNull($_POST['serial_no'] ?? '');
$reason = toNull($_POST['reason'] ?? '');
$hrs = toNull($_POST['hrs'] ?? '');
$ldgs = toNull($_POST['ldgs'] ?? '');
$date = toNull($_POST['date'] ?? '');
$present_latitude = toNull($_POST['present_latitude'] ?? '');
$dgae_auth_ref = toNull($_POST['dgae_auth_ref'] ?? '');
$recommend = toNull($_POST['recommend'] ?? '');
$auth_date = toNull($_POST['auth_date'] ?? '');
$latitude_expiry = toNull($_POST['latitude_expiry'] ?? '');
$total_prev_latitude = toNull($_POST['total_prev_latitude'] ?? '');
$demand_ref = toNull($_POST['demand_ref'] ?? '');
$status = toNull($_POST['status'] ?? '');
$created_by = $_SESSION['admin_id'];

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    $sql = "UPDATE latitude SET 
            active = ?, type = ?, formation_id = ?, aircraft_type_id = ?, tail_no = ?, 
            part_no = ?, description = ?, serial_no = ?, reason = ?, hrs = ?, ldgs = ?, 
            date = ?, present_latitude = ?, dgae_auth_ref = ?,recommend = ?, auth_date = ?, 
            latitude_expiry = ?, total_prev_latitude = ?, demand_ref = ?, status = ?,
            updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../latitude-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiisssssiissssssssis',
        $active, $type, $formation_id, $aircraft_type_id, $tail_no,
        $part_no, $description, $serial_no, $reason, $hrs, $ldgs,
        $date, $present_latitude, $dgae_auth_ref, $auth_date,
        $latitude_expiry, $total_prev_latitude, $demand_ref, $status,
        $id,$recommend
    );
} else {
    // Insert new record
    $sql = "INSERT INTO latitude (
        active, type, formation_id, aircraft_type_id, tail_no, part_no, description,
        serial_no, reason, hrs, ldgs, date, present_latitude, dgae_auth_ref,recommend, auth_date,
        latitude_expiry, total_prev_latitude, demand_ref, status, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../latitude-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'ssiisssssiisssssssssi',
        $active, $type, $formation_id, $aircraft_type_id, $tail_no,
        $part_no, $description, $serial_no, $reason, $hrs, $ldgs,
        $date, $present_latitude, $dgae_auth_ref,$recommend, $auth_date,
        $latitude_expiry, $total_prev_latitude, $demand_ref, $status,
        $created_by
    );
}

if ($stmt->execute()) {
    header('Location: ../latitude.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../latitude-form.php?error=' . urlencode('DB execute error'));
    exit();
}