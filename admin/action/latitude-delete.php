<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../latitude.php');
    exit();
}

$id = (int)$_POST['id'];

$stmt = $db->prepare("DELETE FROM latitude WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: ../latitude.php?success=1');
    } else {
        header('Location: ../latitude.php?error=' . urlencode('Delete failed'));
    }
    $stmt->close();
} else {
    header('Location: ../latitude.php?error=' . urlencode('DB error'));
}
exit();