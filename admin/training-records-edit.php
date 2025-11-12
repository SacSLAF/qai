<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: training-records.php");
    exit();
}

// Redirect to the form with ID parameter
header("Location: training-records-form.php?id=" . (int)$_GET['id']);
exit();
?>