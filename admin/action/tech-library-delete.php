<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../tech-library.php');
    exit();
}

$id = (int)$_POST['id'];

// First, get the file path to delete the physical file
$stmt = $db->prepare("SELECT file_path FROM tech_library WHERE id = ?");
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
$stmt = $db->prepare("DELETE FROM tech_library WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Document deleted successfully!";
        header('Location: ../tech-library.php?success=1');
    } else {
        $_SESSION['error'] = "Delete failed: " . $db->error;
        header('Location: ../tech-library.php?error=1');
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Database error: " . $db->error;
    header('Location: ../tech-library.php?error=1');
}
exit();
?>