<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../qai-newsletters.php');
    exit();
}

$id = (int)$_POST['id'];

$stmt = $db->prepare("DELETE FROM qai_newsletters WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: ../qai-newsletters.php?success=1');
    } else {
        header('Location: ../qai-newsletters.php?error=' . urlencode('Delete failed'));
    }
    $stmt->close();
} else {
    header('Location: ../qai-newsletters.php?error=' . urlencode('DB error'));
}
exit();
?>