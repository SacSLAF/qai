<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: maintenance-program.php");
    exit();
}

// Redirect to the form with ID parameter
header("Location: maintenance-program-form.php?id=" . (int)$_GET['id']);
exit();
?>