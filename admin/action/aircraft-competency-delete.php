<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../aircraft-competency.php');
    exit();
}

$id = intval($_POST['id']);

// Debug log
error_log("Attempting to delete aircraft competency record with record_id: " . $id);

try {
    // Use record_id as the primary key (based on your table structure)
    $stmt = $db->prepare("DELETE FROM aircraft_competency WHERE record_id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }

    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        error_log("Successfully deleted aircraft competency record with record_id: " . $id);
        header('Location: ../aircraft-competency.php?deleted=1');
        exit();
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
} catch (Exception $e) {
    error_log('Error in aircraft-competency-delete.php: ' . $e->getMessage());
    header('Location: ../aircraft-competency.php?error=delete');
    exit();
}