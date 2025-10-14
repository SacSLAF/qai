<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['current_status'])) {
    header('Location: ../aircraft-competency.php');
    exit();
}

$id = intval($_POST['id']);
$current = intval($_POST['current_status']);
$new = $current === 1 ? 0 : 1;

$stmt = $db->prepare("UPDATE aircraft_competency SET is_active = ? WHERE record_id = ? OR id = ?");
if (!$stmt) {
    error_log('Prepare failed: ' . $db->error);
    header('Location: ../aircraft-competency.php?error=prepare');
    exit();
}

$stmt->bind_param('iii', $new, $id, $id);
if ($stmt->execute()) {
    header('Location: ../aircraft-competency.php?success=1');
    exit();
} else {
    error_log('Toggle failed: ' . $stmt->error);
    header('Location: ../aircraft-competency.php?error=toggle');
    exit();
}
