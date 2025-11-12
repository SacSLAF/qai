<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header('Location: ../training-records-form.php');
    exit();
}

// Collect and validate fields
$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$sno = isset($_POST['sno']) ? trim($_POST['sno']) : null;
$svc_no = isset($_POST['svc_no']) ? trim($_POST['svc_no']) : '';
$rank_id = isset($_POST['rank_id']) ? (int)$_POST['rank_id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$training_type = isset($_POST['training_type']) ? trim($_POST['training_type']) : '';
$course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
$ac_category_id = isset($_POST['ac_category_id']) && !empty($_POST['ac_category_id']) ? (int)$_POST['ac_category_id'] : null;
$trade = isset($_POST['trade']) && !empty($_POST['trade']) ? trim($_POST['trade']) : null;
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
$cert_number = isset($_POST['cert_number']) && !empty($_POST['cert_number']) ? trim($_POST['cert_number']) : null;
$created_by = $_SESSION['admin_id'];

// Debug logging (remove in production)
error_log("Form Data Received:");
error_log("SNo: " . $sno);
error_log("SVC No: " . $svc_no);
error_log("Course Name: " . $course_name);
error_log("Trade: " . $trade);
error_log("End Date: " . $end_date);

// Validate required fields
$required_fields = [
    'Service No' => $svc_no,
    'Rank' => $rank_id,
    'Name' => $name,
    'Training Type' => $training_type,
    'Course Name' => $course_name,
    'Start Date' => $start_date,
    'End Date' => $end_date
];

$missing_fields = [];
foreach ($required_fields as $field_name => $value) {
    if (empty($value)) {
        $missing_fields[] = $field_name;
    }
}

if (!empty($missing_fields)) {
    $_SESSION['error'] = "All required fields must be filled. Missing: " . implode(', ', $missing_fields);
    header('Location: ../training-records-form.php' . ($id ? '?id=' . $id : ''));
    exit();
}

// Validate dates
if ($start_date > $end_date) {
    $_SESSION['error'] = "End date cannot be before start date";
    header('Location: ../training-records-form.php' . ($id ? '?id=' . $id : ''));
    exit();
}

try {
    // Check if we're in edit mode
    $is_edit_mode = ($id !== null && $id > 0);

    if ($is_edit_mode) {
        // UPDATE EXISTING RECORD
        $sql = "UPDATE training_records SET 
                sno = ?, svc_no = ?, rank_id = ?, name = ?, training_type = ?, 
                course_name = ?, ac_category_id = ?, trade = ?, start_date = ?, 
                end_date = ?, cert_number = ?, created_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        // Correct parameter types: s=string, i=integer
        $stmt->bind_param(
            'ssisssissssii',
            $sno, $svc_no, $rank_id, $name, $training_type,
            $course_name, $ac_category_id, $trade, $start_date,
            $end_date, $cert_number, $created_by, $id
        );
        
    } else {
        // INSERT NEW RECORD
        $sql = "INSERT INTO training_records (
            sno, svc_no, rank_id, name, training_type, course_name, 
            ac_category_id, trade, start_date, end_date, cert_number, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'ssisssissssi',
            $sno, $svc_no, $rank_id, $name, $training_type,
            $course_name, $ac_category_id, $trade, $start_date,
            $end_date, $cert_number, $created_by
        );
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Database operation failed: " . $stmt->error);
    }
    
    $stmt->close();

    $_SESSION['success'] = "Training Record " . ($is_edit_mode ? 'updated' : 'saved') . " successfully!";
    header('Location: ../training-records.php?success=1');
    exit();

} catch (Exception $e) {
    error_log("Training Record Save Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while saving the training record. Please try again.";
    header('Location: ../training-records-form.php' . ($id ? '?id=' . $id : '') . '&error=1');
    exit();
}
?>