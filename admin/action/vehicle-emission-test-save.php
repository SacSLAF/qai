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

// Helper to validate and format date
function formatDate($dateString) {
    if (empty($dateString)) {
        return null;
    }
    
    // Check if the date is valid and can be parsed
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return null;
    }
    
    return date('Y-m-d', $timestamp);
}

// Collect fields
$serial_no = toNull($_POST['serial_no'] ?? '');
$camp_id = toNull($_POST['camp_id'] ?? '');
$vehicle_no = toNull($_POST['vehicle_no'] ?? '');
$vehicle_type = toNull($_POST['vehicle_type'] ?? '');
$fuel_type = $_POST['fuel_type'] ?? 'Diesel';
$model = toNull($_POST['model'] ?? '');
$test_date = formatDate($_POST['test_date'] ?? '');
$next_due_date = formatDate($_POST['next_due_date'] ?? '');

// Diesel test parameters
$first_test = toNull($_POST['first_test'] ?? '');
$second_test = toNull($_POST['second_test'] ?? '');
$third_test = toNull($_POST['third_test'] ?? '');
$average = toNull($_POST['average'] ?? '');

// Petrol test parameters
$rpm_2500_hc = toNull($_POST['rpm_2500_hc'] ?? '');
$rpm_2500_co = toNull($_POST['rpm_2500_co'] ?? '');
$idle_hc = toNull($_POST['idle_hc'] ?? '');
$idle_co = toNull($_POST['idle_co'] ?? '');

$status = toNull($_POST['status'] ?? '');
$next_due_date = toNull($_POST['next_due_date'] ?? '');
$remarks = toNull($_POST['remarks'] ?? '');
$created_by = $_SESSION['admin_id'];

// Validate required fields
if (empty($camp_id) || empty($vehicle_no) || empty($vehicle_type) || empty($test_date) || empty($status)) {
    header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('Please fill all required fields'));
    exit();
}

// Additional validation for test_date
if ($test_date === null) {
    header('Location: ../vehicle-emission-test-form.php?error=' . urlencode('Invalid test date format'));
    exit();
}

try {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing record
        $id = (int)$_POST['id'];
        $sql = "UPDATE vehicle_emission_test SET 
                serial_no = ?, camp_id = ?, vehicle_no = ?, vehicle_type = ?, fuel_type = ?, model = ?, 
                test_date = ?, first_test = ?, second_test = ?, third_test = ?, average = ?,
                rpm_2500_hc = ?, rpm_2500_co = ?, idle_hc = ?, idle_co = ?,
                status = ?, next_due_date = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('DB prepare error: ' . $db->error);
        }

        $stmt->bind_param(
            'issssssssssssssssi',
            $serial_no, $camp_id, $vehicle_no, $vehicle_type, $fuel_type, $model,
            $test_date, $first_test, $second_test, $third_test, $average,
            $rpm_2500_hc, $rpm_2500_co, $idle_hc, $idle_co,
            $status, $next_due_date, $remarks, $id
        );
    } else {
        // Insert new record
        $sql = "INSERT INTO vehicle_emission_test (
            `serial_no`, `camp_id`, `vehicle_no`, `vehicle_type`, `fuel_type`, `model`, `test_date`, 
            `first_test`, `second_test`, `third_test`, `average`,
            `rpm_2500_hc`, `rpm_2500_co`, `idle_hc`, `idle_co`,
            `status`, `next_due_date`, `remarks`, `created_by`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('DB prepare error: ' . $db->error);
        }

        $stmt->bind_param(
            'issssssssssssssssii',
            $serial_no, $camp_id, $vehicle_no, $vehicle_type, $fuel_type, $model,
            $test_date, $first_test, $second_test, $third_test, $average,
            $rpm_2500_hc, $rpm_2500_co, $idle_hc, $idle_co,
            $status, $next_due_date, $remarks, $created_by
        );
    }

    if ($stmt->execute()) {
        header('Location: ../vehicle-emission-test.php?success=1');
        exit();
    } else {
        throw new Exception('DB execute error: ' . $stmt->error);
    }
} catch (Exception $e) {
    error_log('Error in vehicle-emission-test-save.php: ' . $e->getMessage());
    header('Location: ../vehicle-emission-test-form.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>