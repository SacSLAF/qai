<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../awards.php');
    exit();
}

$id = (int)$_POST['id'];

// Delete the database record
$stmt = $db->prepare("DELETE FROM awards WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Award deleted successfully!";
        header('Location: ../awards.php?success=1');
    } else {
        $_SESSION['error'] = "Failed to delete award";
        header('Location: ../awards.php?error=1');
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Database error";
    header('Location: ../awards.php?error=1');
}
exit();