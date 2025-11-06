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
    header('Location: ../active-qcc-form.php');
    exit();
}

// Collect fields
$sno = trim($_POST['sno'] ?? '');
$qcc_name = trim($_POST['qcc_name'] ?? '');
$slaf_establishment_id = (int)($_POST['slaf_establishment_id'] ?? 0);
$location = trim($_POST['location'] ?? '');
$team_members = trim($_POST['team_members'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 1); // Default to 1
$section_id = (int)($_POST['section_id'] ?? 5);   // Default to 5
$created_by = $_SESSION['admin_id'];

// Validate required fields
$required_fields = [
    'sno' => $sno,
    'qcc_name' => $qcc_name,
    'slaf_establishment_id' => $slaf_establishment_id,
    'location' => $location,
    'team_members' => $team_members
];

foreach ($required_fields as $field => $value) {
    if (empty($value)) {
        $_SESSION['error'] = "All required fields must be filled. Missing: $field";
        header('Location: ../active-qcc-form.php?error=1');
        exit();
    }
}

try {
    if (isset($_POST['id'])) {
        // UPDATE EXISTING RECORD
        $id = (int)$_POST['id'];
        
        $sql = "UPDATE active_qcc SET 
                sno = ?, qcc_name = ?, slaf_establishment_id = ?, location = ?, 
                team_members = ?, category_id = ?, section_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'ssissiii',
            $sno, $qcc_name, $slaf_establishment_id, $location,
            $team_members, $category_id, $section_id, $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database update failed: " . $stmt->error);
        }
        
        $stmt->close();
        error_log("Existing QCC record updated with ID: $id");

    } else {
        // INSERT NEW RECORD
        $sql = "INSERT INTO active_qcc (
            sno, qcc_name, slaf_establishment_id, location, team_members, 
            category_id, section_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'ssissiii',
            $sno, $qcc_name, $slaf_establishment_id, $location,
            $team_members, $category_id, $section_id, $created_by
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $last_id = $stmt->insert_id;
        $stmt->close();
        error_log("New QCC record inserted with ID: $last_id");
    }

    $_SESSION['success'] = "QCC registration saved successfully!";
    header('Location: ../active-qcc.php?success=1');
    exit();

} catch (Exception $e) {
    error_log("QCC Save Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: ../active-qcc-form.php?error=1');
    exit();
}
?>