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

$stmt = $db->prepare("DELETE FROM aircraft_competency WHERE record_id = ? OR id = ?");
if (!$stmt) {
    error_log('Prepare failed: ' . $db->error);
    header('Location: ../aircraft-competency.php?error=prepare');
    exit();
}

$stmt->bind_param('ii', $id, $id);
if ($stmt->execute()) {
    header('Location: ../aircraft-competency.php?deleted=1');
    exit();
} else {
    error_log('Delete failed: ' . $stmt->error);
    header('Location: ../aircraft-competency.php?error=delete');
    exit();
}
