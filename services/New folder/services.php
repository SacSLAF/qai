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
//$default_file = 'doc_1.pdf';

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
    $pdf_web_path = "/qai-intra/admin/action/uploads/services/audit_plan/" . $file;
} else {
    $show_pdf = false;
    $error = "File not found. Tried path: " . htmlspecialchars($file_path);
    if ($absolute_path === false) {
        $error .= " (Path does not exist)";
    }
}

// Fetch PDF documents where qa_category_id = 2
$qa_reports = [];
$qa_check_lists = [];
$qa_reports_error = '';
$qa_check_lists_error = '';

// Check if database connection exists and is valid
if (!isset($db) || !$db || $db->connect_error) {
    $qa_reports_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
} else {
    // Fetch QA Reports (qa_category_id = 2)
    $stmt1 = $db->prepare("
        SELECT id, title, description, file_path, uploaded_at
        FROM service_documents 
        WHERE qa_category_id = 2 AND is_active = 1
        ORDER BY uploaded_at DESC
    ");

    if ($stmt1) {
        if ($stmt1->execute()) {
            $result = $stmt1->get_result();
            $qa_reports = $result->fetch_all(MYSQLI_ASSOC);
            $stmt1->close();
            echo "<!-- QA Reports query successful, found " . count($qa_reports) . " records -->";
        } else {
            $qa_reports_error = "QA Reports query execution failed: " . $stmt1->error;
        }
    } else {
        $qa_reports_error = "Error preparing QA Reports query: " . $db->error;
    }

    // Fetch QA Check List (qa_category_id = 1)
    $stmt2 = $db->prepare("
        SELECT id, title, description, file_path, uploaded_at
        FROM service_documents 
        WHERE qa_category_id = 1 AND is_active = 1
        ORDER BY uploaded_at DESC
    ");

    if ($stmt2) {
        if ($stmt2->execute()) {
            $result = $stmt2->get_result();
            $qa_check_lists = $result->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
            echo "<!-- QA Check List query successful, found " . count($qa_check_lists) . " records -->";
        } else {
            // Append error if QA Reports query was successful
            if (empty($qa_check_lists_error)) {
                $qa_check_lists_error = "QA Check List query execution failed: " . $stmt2->error;
            }
        }
    } else {
        // Append error if QA Reports query was successful
        if (empty($qa_check_lists_error)) {
            $qa_check_lists_error = "Error preparing QA Check List query: " . $db->error;
        }
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
    <main class="container my-3 pt-3">
        <div class="main-container">
            <!-- Navigation Tabs -->
            <div class="nav-column">
                <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                    <!-- QA Audits Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle active" role="button">QA Audits</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item active" data-bs-target="#audits_plan" role="tab">Audits Plan</a>
                            <a class="qa-dropdown-item" data-bs-target="#audit_check_list" role="tab">Audit Check List</a>
                            <a class="qa-dropdown-item" data-bs-target="#qa_report" role="tab">QA Report</a>
                        </div>
                    </div>
                    <!-- Aircraft Competency Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Aircraft Competency</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_ae" role="tab">AE</a>
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_ge" role="tab">GE</a>
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_ee" role="tab">EE</a>
                        </div>
                    </div>
                    <!-- <a class="nav-link" data-bs-target="#ac" role="tab">Aircraft Competency</a> -->
                    <a class="nav-link" data-bs-target="#latitude" role="tab">Latitude & Extensions</a>
                    <a class="nav-link" data-bs-target="#modification" role="tab">Modification / R&D</a>
                    <a class="nav-link" data-bs-target="#vehicle" role="tab">Vehicle Emission Test</a>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="content-column">
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- QA Audits Content Panes -->
                    <div class="tab-pane fade show active" id="audits_plan" role="tabpanel">
                        <?php if ($show_pdf): ?>
                            <div class="top-bar">
                                <a href="?file=<?= $default_file ?>" class="btn btn-sm btn-dark">Audit Plan Document</a>
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

                    <div class="tab-pane fade" id="audit_check_list" role="tabpanel">
                        <h4 class="colour-defult">Audit Check List</h4>
                        <p>Standard operating procedures and checklists for quality audits.</p>
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-header">Audit Check Lists</div>
                                <div class="card-body">
                                    <?php if (!empty($qa_reports_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($qa_reports_error) ?>
                                        </div>
                                    <?php elseif (!empty($qa_check_lists)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover document-table">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Date Uploaded</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($qa_check_lists as $qa_check_list): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($qa_check_list['title']) ?></strong></td>
                                                            <td><?= htmlspecialchars($qa_check_list['description'] ?? 'No description') ?></td>
                                                            <td><?= date('M d, Y', strtotime($qa_check_list['uploaded_at'])) ?></td>
                                                            <td>
                                                                <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>"
                                                                    target="_blank"
                                                                    class="btn btn-primary btn-sm view-pdf-btn">
                                                                    View PDF
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-documents">
                                            <p>No QA check lists available at the moment.</p>
                                            <?php if (empty($qa_reports_error)): ?>
                                                <p class="text-muted"><small>No documents found for QA category 1.</small></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="qa_report" role="tabpanel">
                        <h4 class="colour-defult">QA Report</h4>
                        <p>Quality assurance reports and analytics.</p>
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- <div class="card">
                                        <div class="card-header">Recent Reports</div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">Q3 2023 Quality Metrics</li>
                                                <li class="list-group-item">Safety Incident Report - August 2023</li>
                                                <li class="list-group-item">Compliance Review - July 2023</li>
                                            </ul>
                                        </div>
                                    </div> -->
                                    <div class="card">
                                        <div class="card-header">Recent Reports</div>
                                        <div class="card-body">
                                            <?php if (!empty($qa_reports_error)): ?>
                                                <div class="alert alert-danger">
                                                    <strong>Database Error:</strong> <?= htmlspecialchars($qa_reports_error) ?>
                                                </div>
                                            <?php elseif (!empty($qa_reports)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-hover document-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Title</th>
                                                                <th>Description</th>
                                                                <th>Date Uploaded</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($qa_reports as $report): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($report['title']) ?></strong></td>
                                                                    <td><?= htmlspecialchars($report['description'] ?? 'No description') ?></td>
                                                                    <td><?= date('M d, Y', strtotime($report['uploaded_at'])) ?></td>
                                                                    <td>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                            target="_blank"
                                                                            class="btn btn-primary btn-sm view-pdf-btn">
                                                                            View PDF
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="no-documents">
                                                    <p>No QA reports available at the moment.</p>
                                                    <?php if (empty($qa_reports_error)): ?>
                                                        <p class="text-muted"><small>No documents found for QA category 2.</small></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tab panes -->
                    <!-- <div class="tab-pane fade" id="ac" role="tabpanel">
                        <h4 class="colour-defult">Aircraft Competency</h4>
                        <p>This section contains Aircraft Competency information and certification records.</p>
                        <div class="alert alert-info mt-3">
                            <strong>Note:</strong> All aircraft certifications are up to date as of September 2023.
                        </div>
                    </div> -->
                    <div class="tab-pane fade" id="ac_cmpt_ae" role="tabpanel">
                        <h4 class="colour-defult">Audit Check List</h4>
                        <p>Standard operating procedures and checklists for quality audits.</p>
                        <div class="mt-4">
                            <div class="card">
                                AE
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ac_cmpt_ge" role="tabpanel">
                        <h4 class="colour-defult">Audit Check List</h4>
                        <p>Standard operating procedures and checklists for quality audits.</p>
                        <div class="mt-4">
                            <div class="card">
                                GE
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ac_cmpt_ee" role="tabpanel">
                        <h4 class="colour-defult">Audit Check List</h4>
                        <p>Standard operating procedures and checklists for quality audits.</p>
                        <div class="mt-4">
                            <div class="card">
                                EE
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="latitude" role="tabpanel">
                        <h4 class="colour-defult">Latitude & Extensions</h4>
                        <p>Information regarding latitude approvals and extension requests.</p>
                    </div>

                    <div class="tab-pane fade" id="modification" role="tabpanel">
                        <h4 class="colour-defult">Modification / R&D</h4>
                        <p>Research, development, and modification tracking.</p>
                    </div>

                    <div class="tab-pane fade" id="vehicle" role="tabpanel">
                        <h4 class="colour-defult">Vehicle Emission Test</h4>
                        <p>Vehicle emission testing records and compliance data.</p>
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
            <?php if ($show_pdf): ?>
                // If we're showing a PDF, make sure the audits plan tab is active
                document.querySelector('[data-bs-target="#audits_plan"]').classList.add('active');
                document.querySelector('.qa-dropdown-toggle').classList.add('active');
            <?php else: ?>
                // Set initial active tab
                const initialTab = document.querySelector('[data-bs-target="#audits_plan"]');
                if (initialTab) {
                    initialTab.classList.add('active');
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
            <?php endif; ?>

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

            // Handle tab selection for main nav links (non-dropdown items)
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

            // Handle tab selection for dropdown items (sub-menu items)
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

                    // Remove active class from all dropdown toggles
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

                    // DON'T close the dropdown menu - keep it open for sub-menu items
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