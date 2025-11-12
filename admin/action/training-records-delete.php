<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../training-records.php');
    exit();
}

$id = (int)$_POST['id'];

// Delete the database record
$stmt = $db->prepare("DELETE FROM training_records WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Training Record deleted successfully!";
        header('Location: ../training-records.php?success=1');
    } else {
        $_SESSION['error'] = "Failed to delete training record";
        header('Location: ../training-records.php?error=1');
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Database error";
    header('Location: ../training-records.php?error=1');
}
exit();
?>