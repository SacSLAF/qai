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
    header('Location: ../awards-form.php');
    exit();
}

// Collect fields
$sno = isset($_POST['sno']) ? trim($_POST['sno']) : '';
$year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
$award_type = isset($_POST['award_type']) ? trim($_POST['award_type']) : '';
$slaf_establishment_id = isset($_POST['slaf_establishment_id']) ? (int)$_POST['slaf_establishment_id'] : 0;
$qcc_name = isset($_POST['qcc_name']) ? trim($_POST['qcc_name']) : null;
$placement = isset($_POST['placement']) ? trim($_POST['placement']) : '';
$team_members = isset($_POST['team_members']) ? trim($_POST['team_members']) : '';
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 4;
$section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 5;
$created_by = $_SESSION['admin_id'];

// Validate required fields
$required_fields = [
    'Serial Number' => $sno,
    'Year' => $year,
    'Award Type' => $award_type,
    'SLAF Establishment' => $slaf_establishment_id,
    'Placement' => $placement,
    'Team Members' => $team_members
];

$missing_fields = [];
foreach ($required_fields as $field_name => $value) {
    if (empty($value)) {
        $missing_fields[] = $field_name;
    }
}

// Validate award type specific fields
if ($award_type === 'qcc' && empty($qcc_name)) {
    $missing_fields[] = 'QCC Name';
}

if (!empty($missing_fields)) {
    $_SESSION['error'] = "All required fields must be filled. Missing: " . implode(', ', $missing_fields);
    header('Location: ../awards-form.php?error=1');
    exit();
}

// Validate year
if ($year < 2000 || $year > 2030) {
    $_SESSION['error'] = "Invalid year. Must be between 2000 and 2030";
    header('Location: ../awards-form.php?error=2');
    exit();
}

try {
    // Begin transaction
    $db->begin_transaction();

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // UPDATE EXISTING RECORD
        $id = (int)$_POST['id'];
        
        $sql = "UPDATE awards SET 
                sno = ?, year = ?, award_type = ?, slaf_establishment_id = ?, 
                qcc_name = ?, placement = ?, team_members = ?, 
                category_id = ?, section_id = ?, created_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'sississiiii',
            $sno, $year, $award_type, $slaf_establishment_id,
            $qcc_name, $placement, $team_members,
            $category_id, $section_id, $created_by, $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database update failed: " . $stmt->error);
        }
        
        $stmt->close();

    } else {
        // INSERT NEW RECORD
        $sql = "INSERT INTO awards (
            sno, year, award_type, slaf_establishment_id, qcc_name, 
            placement, team_members, category_id, section_id, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database preparation failed: " . $db->error);
        }

        $stmt->bind_param(
            'sississiii',
            $sno, $year, $award_type, $slaf_establishment_id,
            $qcc_name, $placement, $team_members,
            $category_id, $section_id, $created_by
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Database insert failed: " . $stmt->error);
        }
        
        $stmt->close();
    }

    // Commit transaction
    $db->commit();

    $_SESSION['success'] = "Award " . (isset($_POST['id']) ? 'updated' : 'saved') . " successfully!";
    header('Location: ../awards.php?success=1');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: ../awards-form.php?error=3');
    exit();
}