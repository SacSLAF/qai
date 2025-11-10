<!DOCTYPE html>
<html lang="en">

<?php
include "template/head.php";
require_once '../includes/config.php';

// Define upload directory for training forecast
$upload_dir = '../admin/action/uploads/training/forecast/';

// Define file mappings for training forecast
$file_mappings = [
    'forecast_2026_pdf' => 'Forecast-2026.pdf',
    'prospect_2026_pdf' => 'Prospect-2026.pdf'
];

$messages = [];

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process uploads for training forecast
    foreach ($file_mappings as $field => $filename) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            // Validate file type
            $file_type = $_FILES[$field]['type'];
            if ($file_type !== 'application/pdf') {
                $messages[] = "Error: $filename must be a PDF file.";
                continue;
            }
            
            // Validate file size (5MB)
            if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
                $messages[] = "Error: $filename exceeds 5MB size limit.";
                continue;
            }
            
            $tmp_name = $_FILES[$field]['tmp_name'];
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($tmp_name, $dest)) {
                $messages[] = "Success! Uploaded $filename successfully.";
            } else {
                $messages[] = "Failed to upload $filename.";
            }
        } elseif (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle other upload errors
            $error_code = $_FILES[$field]['error'];
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
            ];
            $messages[] = "Error uploading $filename: " . ($error_messages[$error_code] ?? "Unknown error.");
        }
    }
    
    // Show info message if no files were uploaded
    if (empty($messages)) {
        $messages[] = "No files were uploaded. Please select at least one PDF file to upload.";
    }
}

// Check if files exist and get their info
$file_info = [];
foreach ($file_mappings as $field => $filename) {
    $file_path = $upload_dir . $filename;
    $file_info[$field] = [
        'exists' => file_exists($file_path),
        'path' => $file_path,
        'size' => file_exists($file_path) ? filesize($file_path) : 0,
        'modified' => file_exists($file_path) ? date('F d, Y H:i', filemtime($file_path)) : null
    ];
}
?>

<body>

    <?php
    include "template/preloader.php";
    ?>

    <div id="main-wrapper">

        <?php
        include "template/nav.php";
        include "template/header.php";
        ?>

        <?php
        include "template/desnav.php";
        ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Upload Training Forecast Documents</h4>
                            </div>
                            <div class="card-body">
                                <?php foreach ($messages as $msg): ?>
                                    <?php if (strpos($msg, 'Error:') === 0): ?>
                                        <div class="alert alert-danger solid alert-dismissible fade show">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                            <strong>Error!</strong> <?= htmlspecialchars(substr($msg, 6)) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                                            </button>
                                        </div>
                                    <?php elseif (strpos($msg, 'Success!') === 0): ?>
                                        <div class="alert alert-success solid alert-dismissible fade show">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                            <strong>Success!</strong> <?= htmlspecialchars(substr($msg, 9)) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-primary mx-5 alert-dismissible fade show">
                                            <strong>Info!</strong> <?= htmlspecialchars($msg) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <form method="post" enctype="multipart/form-data" id="uploadForm">
                                    <!-- Training Forecast Section -->
                                    <div class="category-section mb-5">
                                        <h5 class="mb-3 text-primary">Training Forecast 2026</h5>
                                        <div class="row">
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label for="forecast_2026_pdf" class="form-label">Forecast 2026 PDF</label>
                                                    <div class="input-group">
                                                        <div class="form-file">
                                                            <input type="file" class="form-file-input form-control" name="forecast_2026_pdf" id="forecast_2026_pdf" accept=".pdf,application/pdf">
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Optional. Max file size: 5MB. Allowed types: PDF only
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6">
                                                <div class="form-group">
                                                    <label for="prospect_2026_pdf" class="form-label">Prospect 2026 PDF</label>
                                                    <div class="input-group">
                                                        <div class="form-file">
                                                            <input type="file" class="form-file-input form-control" name="prospect_2026_pdf" id="prospect_2026_pdf" accept=".pdf,application/pdf">
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Optional. Max file size: 5MB. Allowed types: PDF only
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i> Upload Selected PDFs
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Current Files Section -->
                                <div class="mt-5">
                                    <h5 class="mb-3">Current Uploaded Files</h5>
                                    
                                    <!-- Training Forecast Files -->
                                    <div class="category-files mb-4">
                                        <h6 class="mb-3 text-primary">Training Forecast 2026</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card card-file <?= $file_info['forecast_2026_pdf']['exists'] ? 'border-success' : 'border-warning' ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <i class="fas fa-file-pdf text-danger" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">Forecast 2026</h6>
                                                                <p class="mb-0 text-muted">
                                                                    <?php if ($file_info['forecast_2026_pdf']['exists']): ?>
                                                                        Last updated: <?= $file_info['forecast_2026_pdf']['modified'] ?><br>
                                                                        <small>File size: <?= round($file_info['forecast_2026_pdf']['size'] / 1024 / 1024, 2) ?> MB</small>
                                                                    <?php else: ?>
                                                                        <span class="text-warning">No file uploaded</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                            <div class="ms-3">
                                                                <?php if ($file_info['forecast_2026_pdf']['exists']): ?>
                                                                    <a href="<?= $file_info['forecast_2026_pdf']['path'] ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-eye me-1"></i> View
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                                        <i class="fas fa-eye me-1"></i> View
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card card-file <?= $file_info['prospect_2026_pdf']['exists'] ? 'border-success' : 'border-warning' ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <i class="fas fa-file-pdf text-danger" style="font-size: 2rem;"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">Prospect 2026</h6>
                                                                <p class="mb-0 text-muted">
                                                                    <?php if ($file_info['prospect_2026_pdf']['exists']): ?>
                                                                        Last updated: <?= $file_info['prospect_2026_pdf']['modified'] ?><br>
                                                                        <small>File size: <?= round($file_info['prospect_2026_pdf']['size'] / 1024 / 1024, 2) ?> MB</small>
                                                                    <?php else: ?>
                                                                        <span class="text-warning">No file uploaded</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                            <div class="ms-3">
                                                                <?php if ($file_info['prospect_2026_pdf']['exists']): ?>
                                                                    <a href="<?= $file_info['prospect_2026_pdf']['path'] ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-eye me-1"></i> View
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                                        <i class="fas fa-eye me-1"></i> View
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include "template/footer.php";
        ?>
    </div>

    <?php
    include "template/foot.php";
    ?>

    <!-- Additional styling for file cards -->
    <style>
        .card-file {
            border-left: 4px solid #1E67AF;
            transition: all 0.3s;
        }
        .card-file.border-success {
            border-left-color: #28a745;
        }
        .card-file.border-warning {
            border-left-color: #ffc107;
        }
        .card-file:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .required-label:after {
            content: " *";
            color: red;
        }
        .nav-pills .nav-link:not(.active):disabled {
            color: #6c757d;
            background-color: transparent;
        }
        .nav-pills .nav-link:disabled:hover {
            cursor: not-allowed;
        }
        .category-section {
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .category-files h6 {
            font-weight: 600;
        }
    </style>

    <script>
        // File validation
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Check file type
                        if (file.type !== 'application/pdf') {
                            alert('Error: Please select a PDF file.');
                            this.value = '';
                            return;
                        }
                        
                        // Check file size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Error: File size exceeds 5MB limit.');
                            this.value = '';
                            return;
                        }
                    }
                });
            });
            
            // Form submission validation - at least one file should be selected
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                const fileInputs = document.querySelectorAll('input[type="file"]');
                let hasFile = false;
                
                fileInputs.forEach(input => {
                    if (input.files.length > 0) {
                        hasFile = true;
                    }
                });
                
                if (!hasFile) {
                    alert('Please select at least one PDF file to upload.');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        });
    </script>

</body>

</html>