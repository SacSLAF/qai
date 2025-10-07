<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "includes/config.php";

// Get filename from URL
if (!isset($_GET['file'])) die("No file specified.");

$file = $_GET['file'];
echo "<!-- Requested file: " . htmlspecialchars($file) . " -->\n";

// Correct file path - files are stored in admin/action/uploads/services/
$file_path = "admin/action/" . $file;
echo "<!-- File path: " . htmlspecialchars($file_path) . " -->\n";

// Check if file exists
if (!file_exists($file_path)) {
    echo "<!-- File does not exist at: " . htmlspecialchars(realpath($file_path)) . " -->\n";
    
    // Try alternative paths for debugging
    $alternative_paths = [
        "admin/action/uploads/services/" . basename($file),
        "../admin/action/" . $file,
        "uploads/services/" . basename($file)
    ];
    
    foreach ($alternative_paths as $alt_path) {
        echo "<!-- Trying: " . htmlspecialchars($alt_path) . " -> " . (file_exists($alt_path) ? 'EXISTS' : 'NOT FOUND') . " -->\n";
    }
    
    die("File not found. Tried path: " . htmlspecialchars($file_path));
}

// Web path to PDF (for PDF.js) - use relative path from assets/pdfjs/web/
$pdf_web_path = "../../../" . $file_path;
echo "<!-- PDF web path: " . htmlspecialchars($pdf_web_path) . " -->\n";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Document - <?= htmlspecialchars(basename($file)) ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .top-bar {
            background: #007aff;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 4px;
        }

        .top-bar a:hover {
            background: rgba(255,255,255,0.3);
        }

        .file-info {
            display: inline-block;
            margin-left: 15px;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <a href="javascript:history.back()">⬅ Back</a>
        <span class="file-info">Viewing: <?= htmlspecialchars(basename($file)) ?></span>
    </div>

    <!-- PDF.js Viewer -->
    <iframe 
        src="assets/pdfjs/web/viewer.html?file=<?= urlencode($pdf_web_path) ?>" 
        width="100%" 
        height="100%" 
        style="border:none; position:fixed; top:45px; bottom:0; left:0; right:0;">
    </iframe>

</body>
</html>