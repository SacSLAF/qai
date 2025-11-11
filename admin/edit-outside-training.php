<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: outside-training.php");
    exit();
}

// Redirect to the form with ID parameter
header("Location: outside-training-form.php?id=" . (int)$_GET['id']);
exit();