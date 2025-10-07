<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "includes/config.php";

// Get filename from URL
if (!isset($_GET['file'])) die("No file specified.");

// Use the file name from URL
$file = $_GET['file'];

// Server path to PDF
$file_path = "admin/action/audit_plan" . $file;

if (!file_exists($file_path)) {
    die("File not found. Tried path: " . htmlspecialchars(realpath($file_path)));
}

// Web path to PDF (for PDF.js)
$pdf_web_path = "/qai/admin/action/audit_plan" . $file; // absolute from web root
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Document</title>
    <style>
        body { margin: 0; }
        .top-bar { background:#007aff; color:white; padding:8px; font-size:14px; }
        .top-bar a { color:white; text-decoration:none; margin-right:15px; }
        .top-bar span.expiry { float:right; background:#333; padding:3px 6px; border-radius:4px; }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="javascript:history.back()">⬅ Back to Previous Page</a>
     <!--<span><?= htmlspecialchars($section) ?> <?= htmlspecialchars(pathinfo($file, PATHINFO_FILENAME)) ?></span>-->
    <!--<span class="expiry">Expires on <?= date('F d, Y', strtotime('+1 year')) ?></span>-->
</div>

<!-- PDF.js Viewer -->
<iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($pdf_web_path) ?>"
        width="100%" height="100%" style="border:none; position:fixed; top:40px; bottom:0; left:0; right:0;">
</iframe>


</body>
</html>
