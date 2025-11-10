<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../training-recird-cpd.php');
    exit();
}

$id = (int)$_POST['id'];

// First, get the file path to delete the physical file
$stmt = $db->prepare("SELECT file_path FROM training_record_cpd WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
    
    // Delete the physical file
    if ($record && !empty($record['file_path']) && file_exists('../../' . $record['file_path'])) {
        unlink('../../' . $record['file_path']);
    }
}

// Now delete the database record
$stmt = $db->prepare("DELETE FROM training_record_cpd WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Training Record CPD deleted successfully!";
        header('Location: ../training-record-cpd.php?success=1');
    } else {
        $_SESSION['error'] = "Failed to delete training record CPD";
        header('Location: ../training-record-cpd.php?error=1');
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Database error";
    header('Location: ../training-record-cpd.php?error=1');
}
exit();