<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/config.php";
// Check if a PDF file is requested
$show_pdf = false;
$pdf_file = '';
$pdf_web_path = '';

// Default file to show in Audits Plan tab
$default_file = 'doc_1.pdf';

//if (isset($_GET['file'])) {
$file = 'doc_1.pdf';
//} else {
// If no file specified, use the default
// $file = $default_file;
//}

// Server path to PDF - corrected relative path
$file_path = "../admin/action/uploads/services/audit_plan/" . $file;

// Get absolute path for file existence check
$absolute_path = realpath($file_path);

if ($absolute_path && file_exists($absolute_path)) {
    $show_pdf = true;
    // Web path to PDF (for PDF.js)
    $pdf_web_path = "/qai/admin/action/uploads/services/audit_plan/" . $file;
} else {
    $show_pdf = false;
    $error = "File not found. Tried path: " . htmlspecialchars($file_path);
    if ($absolute_path === false) {
        $error .= " (Path does not exist)";
    }
}

include '../template/head.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Quality Assurance Inspectorate</title>
    <!-- Bootstrap CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="../assets/css/swiper-bundle.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .tab-content {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .top-bar {
            background: #007aff;
            color: white;
            padding: 8px;
            font-size: 14px;
            margin: -20px -20px 20px -20px;
            border-radius: 5px 5px 0 0;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }

        .top-bar span.expiry {
            float: right;
            background: #333;
            padding: 3px 6px;
            border-radius: 4px;
        }

        .pdf-viewer-container {
            position: relative;
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
        }

        .qa-dropdown {
            position: relative;
            display: block;
        }

        .qa-dropdown-menu {
            position: relative;
            width: 100%;
            border: none;
            box-shadow: none;
            margin-top: 0;
            padding-left: 15px;
            display: none;
        }

        .qa-dropdown-menu.show {
            display: block;
        }

        .qa-dropdown-item {
            display: block;
            padding: 8px 15px;
            color: #495057;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 3px;
            font-size: 0.95rem;
        }

        .qa-dropdown-item:hover,
        .qa-dropdown-item.active {
            background-color: #e9ecef;
            color: #1a4f72;
        }

        .qa-dropdown-toggle::after {
            float: right;
            margin-top: 8px;

            font-size: 0.8em;
        }


        .main-container {
            gap: 15px;
        }

        @media (min-width: 992px) {
            .main-container {
                display: grid;
                grid-template-columns: 200px 1fr;
            }

            .nav-column {
                padding-right: 0;
            }

            .content-column {
                padding-left: 0;
            }
        }

        .pdf-controls {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../template/header.php'; ?>

    <!-- Main Content -->
    <main class="container-fluid my-3 pt-3">
        <div class="main-container">
            <!-- Navigation Tabs -->
            <div class="nav-column">
                <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">

                    <a class="nav-link" data-bs-target="#ac" role="tab">Approved Training Syllabus</a>

                    <!-- CPD Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Continuous Professional Development</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#cpd_atf" role="tab">Annual Training Forecast</a>
                            <a class="qa-dropdown-item" data-bs-target="#cpd_ccpd" role="tab">Completed CPD</a>
                        </div>
                    </div>

                    <!-- PIPD Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Productivity Improvement Professional Development</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#pipd_atf" role="tab">Annual Training Forecast</a>
                            <a class="qa-dropdown-item" data-bs-target="#pipd_ccpd" role="tab">Completed CPD</a>
                        </div>
                    </div>

                    <!-- OT Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Outside Training</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#ot_atf" role="tab">Annual Training Forecast</a>
                            <a class="qa-dropdown-item" data-bs-target="#ot_cotp" role="tab">Completed Outside Training Programme</a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tab Content -->
            <div class="content-column">
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- Welcome Screen (shown by default) -->
                    <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                        <div class="welcome-message">
                            <img src="../assets/images/qai-welcome.jpg" alt="Quality Assurance Inspectorate" class="welcome-image">
                            <h4>Welcome to Command Quality Assurance Inspectorate</h4>
                            <p>Please select an option from the navigation menu to view the content.</p>
                        </div>
                    </div>
                    <!-- Other tab panes -->
                    <div class="tab-pane fade" id="ac" role="tabpanel">
                        <h4 class="colour-defult">Approved Training Syllabus</h4>
                        <?php include 'ats.php'; ?>
                    </div>

                    <!-- CPD -->
                    <div class="tab-pane fade" id="cpd_atf" role="tabpanel">
                        <?php if ($show_pdf): ?>
                            <div class="top-bar">
                                <a href="?file=<?= $default_file ?>" class="btn btn-sm btn-dark">Annual Training Forecast</a>
                                <!--<span class="expiry">Expires on <?= date('F d, Y', strtotime('+1 year')) ?></span>-->
                            </div>

                            <!-- PDF.js Viewer -->
                            <div class="pdf-viewer-container">
                                <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($pdf_web_path) ?>"
                                    width="100%" height="100%" style="border:none;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <div class="alert alert-info">
                                <p>No audit plan document is available at the moment.</p>
                                <a href="?file=doc_1.pdf" class="btn btn-primary">Test with doc_1.pdf</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="cpd_ccpd" role="tabpanel">
                        <h4 class="colour-defult">Completed CPD</h4>
                    </div>

                    <div class="tab-pane fade" id="pipd_atf" role="tabpanel">
                        <h4 class="colour-defult">PIPD - Annual Training Forecast</h4>
                    </div>

                    <div class="tab-pane fade" id="pipd_ccpd" role="tabpanel">
                        <h4 class="colour-defult">PIPD - Completed CPD</h4>
                    </div>

                    <div class="tab-pane fade" id="ot_atf" role="tabpanel">
                        <h4 class="colour-defult">OT - Annual Training Forecast</h4>
                    </div>

                    <div class="tab-pane fade" id="ot_cotp" role="tabpanel">
                        <h4 class="colour-defult">OT - Completed Outside Training Programme</h4>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../template/foot.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="../assets/js/swiper-bundle.min.js"></script>

    <script>
        // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {
            // Set initial active tab and content
            const initialTab = document.querySelector('[data-bs-target="#audits_plan"]');
            const initialPane = document.querySelector('#audits_plan');

            if (initialTab && initialPane) {
                initialTab.classList.add('active');
                initialPane.classList.add('show', 'active');

                // Activate the parent dropdown toggle
                const parentDropdown = initialTab.closest('.qa-dropdown');
                if (parentDropdown) {
                    const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                    if (dropdownToggle) {
                        dropdownToggle.classList.add('active');
                        // Keep the dropdown menu open initially
                        const dropdownMenu = dropdownToggle.nextElementSibling;
                        if (dropdownMenu) {
                            dropdownMenu.classList.add('show');
                        }
                    }
                }
            }

            // Handle dropdown toggle for ALL dropdowns
            const dropdownToggles = document.querySelectorAll('.qa-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    if (!dropdownMenu) return;

                    // Toggle current dropdown - close if open, open if closed
                    const isCurrentlyOpen = dropdownMenu.classList.contains('show');

                    // Close all dropdowns first
                    dropdownToggles.forEach(otherToggle => {
                        const otherMenu = otherToggle.nextElementSibling;
                        if (otherMenu) {
                            otherMenu.classList.remove('show');
                        }
                    });

                    // If it wasn't open, open it now
                    if (!isCurrentlyOpen) {
                        dropdownMenu.classList.add('show');
                    }
                });
            });

            // Handle tab selection for main nav links (non-QA Audits)
            const mainNavLinks = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)');
            mainNavLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all main nav links
                    document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown items
                    document.querySelectorAll('.qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show the target tab content
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // Close all dropdowns when selecting main nav items
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });

            // Handle tab selection for dropdown items (QA Audits sub-items)
            const dropdownItems = document.querySelectorAll('.qa-dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event from bubbling to document

                    // Remove active class from all main nav links
                    document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown items
                    document.querySelectorAll('.qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles except the parent
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked dropdown item
                    this.classList.add('active');

                    // Activate the parent dropdown toggle and keep menu open
                    const parentDropdown = this.closest('.qa-dropdown');
                    if (parentDropdown) {
                        const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                        if (dropdownToggle) {
                            dropdownToggle.classList.add('active');
                            // Keep the dropdown menu open
                            const dropdownMenu = dropdownToggle.nextElementSibling;
                            if (dropdownMenu) {
                                dropdownMenu.classList.add('show');
                            }
                        }
                    }

                    // Show the target tab content
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // DON'T close the dropdown menu - keep it open
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.qa-dropdown')) {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });

            // Prevent dropdown from closing when clicking inside dropdown menu
            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // Handle escape key to close dropdowns
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });
    </script>
</body>

</html>