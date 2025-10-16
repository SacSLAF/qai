<?php
// action/vehicle-emission-test-delete.php
require_once '../../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ../vehicle-emission-test.php');
    exit();
}

$id = (int)$_POST['id'];

$stmt = $db->prepare("DELETE FROM vehicle_emission_test WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: ../vehicle-emission-test.php?success=1');
    } else {
        header('Location: ../vehicle-emission-test.php?error=' . urlencode('Delete failed'));
    }
    $stmt->close();
} else {
    header('Location: ../vehicle-emission-test.php?error=' . urlencode('DB error'));
}
exit();
?>