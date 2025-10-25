<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../maintenance-program.php');
    exit();
}

$id = (int)$_POST['id'];

// First, get the file path to delete the physical file
$stmt = $db->prepare("SELECT file_path FROM maintenance_documents WHERE id = ?");
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
$stmt = $db->prepare("DELETE FROM maintenance_documents WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: ../maintenance-program.php?success=1');
    } else {
        header('Location: ../maintenance-program.php?error=' . urlencode('Delete failed'));
    }
    $stmt->close();
} else {
    header('Location: ../maintenance-program.php?error=' . urlencode('DB error'));
}
exit();
?>