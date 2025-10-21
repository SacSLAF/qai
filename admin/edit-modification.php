<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: modification.php");
    exit();
}

header("Location: modification-form.php?id=" . (int)$_GET['id']);
exit();
?>