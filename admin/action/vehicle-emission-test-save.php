<?php
// action/vehicle-emission-test-save.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vehicle-emission-test-form.php');
    exit();
}

// Helper to convert empty strings to NULL
function toNull($v) {
    return isset($v) && $v !== '' ? $v : null;
}

// Collect fields
$serial_no = toNull($_POST['serial_no'] ?? '');
$camp_id = toNull($_POST['camp_id'] ?? '');
$vehicle_no = toNull($_POST['vehicle_no'] ?? '');
$vehicle_type = toNull($_POST['vehicle_type'] ?? '');
$model = toNull($_POST['model'] ?? '');
$test_date = toNull($_POST['test_date'] ?? '');
$first_test = toNull($_POST['first_test'] ?? '');
$second_test = toNull($_POST['second_test'] ?? '');
$third_test = toNull($_POST['third_test'] ?? '');
$average = toNull($_POST['average'] ?? '');
$status = toNull($_POST['status'] ?? '');
$next_due_date = toNull($_POST['next_due_date'] ?? '');
$remarks = toNull($_POST['remarks'] ?? '');
$created_by = $_SESSION['admin_id'];

// Validate required fields
if (empty($camp_id) || empty($vehicle_no) || empty($vehicle_type) || empty($test_date) || empty($status)) {
    header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('Please fill all required fields'));
    exit();
}

if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Update existing record
    $id = (int)$_POST['id'];
    $sql = "UPDATE vehicle_emission_test SET 
            serial_no = ?, camp_id = ?, vehicle_no = ?, vehicle_type = ?, model = ?, 
            test_date = ?, first_test = ?, second_test = ?, third_test = ?, average = ?, 
            status = ?, next_due_date = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'isssssddddsssi',
        $serial_no, $camp_id, $vehicle_no, $vehicle_type, $model,
        $test_date, $first_test, $second_test, $third_test, $average,
        $status, $next_due_date, $remarks, $id
    );
} else {
    // Insert new record
    $sql = "INSERT INTO vehicle_emission_test (
        serial_no, camp_id, vehicle_no, vehicle_type, model, test_date, 
        first_test, second_test, third_test, average, status, next_due_date, 
        remarks, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $db->error);
        header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('DB prepare error'));
        exit();
    }

    $stmt->bind_param(
        'isssssddddsssi',
        $serial_no, $camp_id, $vehicle_no, $vehicle_type, $model,
        $test_date, $first_test, $second_test, $third_test, $average,
        $status, $next_due_date, $remarks, $created_by
    );
}

if ($stmt->execute()) {
    header('Location: ../vehicle-emission-test.php?success=1');
    exit();
} else {
    error_log('Execute failed: ' . $stmt->error);
    header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('DB execute error'));
    exit();
}
?>