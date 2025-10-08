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
    $pdf_web_path = "/qai/admin/action/uploads/services/audit_plan/" . $file;
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

// Fetch Aircraft Competency data categorized by branch
$aircraft_competency_data = [];
$ac_error = '';

// Define branch mappings
$branch_mappings = [
    1 => 'Aeronautical Engineering',
    2 => 'Air Operations',
    3 => 'Construction Engineering',
    4 => 'Electronic Engineering',
    5 => 'General Engineering',
    6 => 'Ground Operations',
    7 => 'Productivity Management',
    8 => 'Training'
];

if (!isset($db) || !$db || $db->connect_error) {
    // die("DB error: " . ($db->connect_error ?? 'Unknown error'));
    $ac_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
} else {
    // Fetch all aircraft competency records
    $stmt3 = $db->prepare("
        SELECT ac.*, b.name as branch_name 
        FROM aircraft_competency ac 
        LEFT JOIN branches b ON ac.branch_id = b.id 
        WHERE ac.is_active = 1 
        ORDER BY b.name, ac.aircraft_type, ac.name
    ");

    if ($stmt3) {
        if ($stmt3->execute()) {
            $result3 = $stmt3->get_result();
            $all_records = $result3->fetch_all(MYSQLI_ASSOC);

            // Organize by branch
            foreach ($all_records as $record) {
                $branch_id = $record['branch_id'] ?? 0;
                $branch_name = $record['branch_name'] ?? 'Uncategorized';

                if (!isset($aircraft_competency_data[$branch_id])) {
                    $aircraft_competency_data[$branch_id] = [
                        'branch_name' => $branch_name,
                        'records' => []
                    ];
                }
                $aircraft_competency_data[$branch_id]['records'][] = $record;
            }
            $stmt3->close();
        } else {
            $ac_error = "Aircraft Competency query execution failed: " . $stmt3->error;
        }
    } else {
        $ac_error = "Error preparing Aircraft Competency query: " . $db->error;
    }
}

// Fetch Latitude & Extensions data
$latitude_extension_data = [];
$le_error = '';

if (!isset($db) || !$db || $db->connect_error) {
    $le_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
} else {
    // Fetch all latitude extension records
    $stmt = $db->prepare("
        SELECT le.*, b.name as branch_name 
        FROM latitude_extension le 
        LEFT JOIN branches b ON le.branch_id = b.id 
        WHERE le.is_active = 1 
        ORDER BY le.uploaded_at DESC, le.related_aircraft, le.title
    ");

    if ($stmt) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $latitude_extension_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $le_error = "Latitude & Extensions query execution failed: " . $stmt->error;
        }
    } else {
        $le_error = "Error preparing Latitude & Extensions query: " . $db->error;
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
    <main class="container-fluid my-3 pt-3">
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
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_1" role="tab">AE</a>
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_5" role="tab">GE</a>
                            <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_4" role="tab">EE</a>
                        </div>
                    </div>
                    <!-- <a class="nav-link" data-bs-target="#ac" role="tab">Aircraft Competency</a> -->
                    <a class="nav-link" data-bs-target="#latitude" role="tab">Latitude & Extensions</a>
                    <!-- Modification / R&D Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Modifications / R&D</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#modification" role="tab">Modifications</a>
                            <a class="qa-dropdown-item" data-bs-target="#rnd" role="tab">R&D</a>
                        </div>
                    </div>

                    <!-- Vehicle Emission Test Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Vehicle Emission Test</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#vehicle_annual_plans" role="tab">Annual Plans</a>
                            <a class="qa-dropdown-item" data-bs-target="#vehicle_test_reports" role="tab">Test Reports</a>
                        </div>
                    </div>
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

                    <!-- Aircraft Competency Dropdown -->
                    <!-- <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Aircraft Competencyi</a>
                        <div class="qa-dropdown-menu">
                            <?php foreach ($branch_mappings as $branch_id => $branch_name): ?>
                                <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_<?= $branch_id ?>" role="tab">
                                    <?= htmlspecialchars($branch_name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div> -->

                    <!-- Aircraft Competency Tab Panes -->
                    <?php foreach ($branch_mappings as $branch_id => $branch_name): ?>
                        <div class="tab-pane fade" id="ac_cmpt_<?= $branch_id ?>" role="tabpanel">
                            <h4 class="colour-defult">Aircraft Competency - <?= htmlspecialchars($branch_name) ?></h4>
                            <p>This section contains Aircraft Competency information and certification records for <?= htmlspecialchars($branch_name) ?>.</p>

                            <div class="mt-4">
                                <?php if (!empty($ac_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($ac_error) ?>
                                    </div>
                                <?php elseif (isset($aircraft_competency_data[$branch_id]) && !empty($aircraft_competency_data[$branch_id]['records'])): ?>
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-plane me-2"></i>
                                                <?= htmlspecialchars($branch_name) ?> - Competency Records
                                                <span class="badge bg-light text-dark ms-2">
                                                    <?= count($aircraft_competency_data[$branch_id]['records']) ?> records
                                                </span>
                                            </h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>SVC No</th>
                                                            <th>Rank</th>
                                                            <th>Name</th>
                                                            <th>Aircraft Type</th>
                                                            <th>Last Competency</th>
                                                            <th>Renewal Date</th>
                                                            <th>Currency</th>
                                                            <th>Squadron</th>
                                                            <th>Document</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($aircraft_competency_data[$branch_id]['records'] as $record): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($record['svc_no']) ?></strong></td>
                                                                <td><?= htmlspecialchars($record['rank']) ?></td>
                                                                <td><?= htmlspecialchars($record['name']) ?></td>
                                                                <td>
                                                                    <span class="badge bg-info"><?= htmlspecialchars($record['aircraft_type']) ?></span>
                                                                </td>
                                                                <td><?= htmlspecialchars($record['last_level_of_competency']) ?></td>
                                                                <td>
                                                                    <?php
                                                                    $renewal_date = new DateTime($record['renewal_date']);
                                                                    $today = new DateTime();
                                                                    $days_until_renewal = $today->diff($renewal_date)->days;
                                                                    $is_expired = $renewal_date < $today;
                                                                    ?>
                                                                    <span class="badge <?= $is_expired ? 'bg-danger' : ($days_until_renewal <= 30 ? 'bg-warning' : 'bg-success') ?>">
                                                                        <?= date('M d, Y', strtotime($record['renewal_date'])) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge <?= $record['currency'] === 'Current' ? 'bg-success' : 'bg-secondary' ?>">
                                                                        <?= htmlspecialchars($record['currency']) ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= htmlspecialchars($record['squadron']) ?></td>
                                                                <td>
                                                                    <?php if (!empty($record['file_path']) && file_exists("../admin/action/" . $record['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-outline-primary">
                                                                            <i class="fas fa-file-pdf"></i> View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No document</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No aircraft competency records found for <?= htmlspecialchars($branch_name) ?>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- <div class="tab-pane fade" id="latitude" role="tabpanel">
                        <h4 class="colour-defult">Latitude & Extensions</h4>
                        <p>Information regarding latitude approvals and extension requests.</p>
                    </div> -->
                    <div class="tab-pane fade" id="latitude" role="tabpanel">
                        <h4 class="colour-defult">Latitude & Extensions</h4>
                        <p class="text-muted">Information regarding latitude approvals and extension requests.</p>

                        <div class="mt-4">
                            <?php if (!empty($le_error)): ?>
                                <div class="alert alert-danger">
                                    <strong>Database Error:</strong> <?= htmlspecialchars($le_error) ?>
                                </div>
                            <?php elseif (!empty($latitude_extension_data)): ?>
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-expand-alt me-2"></i>
                                            Latitude & Extension Records
                                            <span class="badge bg-light text-dark ms-2">
                                                <?= count($latitude_extension_data) ?> records
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Latitude Description</th>
                                                        <th>Related Aircraft</th>
                                                        <th>Latitude Period</th>
                                                        <th>Branch</th>
                                                        <th>Upload Date</th>
                                                        <th>Document</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($latitude_extension_data as $record): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars($record['title']) ?></strong>
                                                            </td>
                                                            <td>
                                                                <?= !empty($record['description']) ? htmlspecialchars($record['description']) : '<span class="text-muted">No description</span>' ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($record['latitude_description']) ?></td>
                                                            <td>
                                                                <span class="badge bg-primary"><?= htmlspecialchars($record['related_aircraft']) ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($record['latitude_period']) ?></span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= !empty($record['branch_name']) ? htmlspecialchars($record['branch_name']) : 'N/A' ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= date('M d, Y', strtotime($record['uploaded_at'])) ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($record['file_path']) && file_exists("../admin/action/" . $record['file_path'])): ?>
                                                                    <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>"
                                                                        target="_blank"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-file-pdf"></i> View
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No document</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No latitude and extension records found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Individual Branch Tabs -->
                    <?php foreach ($branch_mappings as $branch_id => $branch_name): ?>
                        <div class="tab-pane fade" id="latitude_<?= $branch_id ?>" role="tabpanel">
                            <h4 class="colour-defult">Latitude & Extensions - <?= htmlspecialchars($branch_name) ?></h4>
                            <p>Latitude approvals and extension requests for <?= htmlspecialchars($branch_name) ?>.</p>

                            <div class="mt-4">
                                <?php if (!empty($le_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($le_error) ?>
                                    </div>
                                <?php elseif (isset($latitude_extension_data[$branch_id]) && !empty($latitude_extension_data[$branch_id]['records'])): ?>
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-expand-alt me-2"></i>
                                                <?= htmlspecialchars($branch_name) ?> - Latitude Records
                                                <span class="badge bg-light text-dark ms-2">
                                                    <?= count($latitude_extension_data[$branch_id]['records']) ?> records
                                                </span>
                                            </h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Description</th>
                                                            <th>Latitude Description</th>
                                                            <th>Related Aircraft</th>
                                                            <th>Latitude Period</th>
                                                            <th>Upload Date</th>
                                                            <th>Document</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($latitude_extension_data[$branch_id]['records'] as $record): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($record['title']) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <?= !empty($record['description']) ? htmlspecialchars($record['description']) : '<span class="text-muted">No description</span>' ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($record['latitude_description']) ?></td>
                                                                <td>
                                                                    <span class="badge bg-primary"><?= htmlspecialchars($record['related_aircraft']) ?></span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($record['latitude_period']) ?></span>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        <?= date('M d, Y', strtotime($record['uploaded_at'])) ?>
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <?php if (!empty($record['file_path']) && file_exists("../admin/action/" . $record['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-outline-primary">
                                                                            <i class="fas fa-file-pdf"></i> View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No document</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No latitude and extension records found for <?= htmlspecialchars($branch_name) ?>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Modification / R&D Tab Panes -->
                    <div class="tab-pane fade" id="modification" role="tabpanel">
                        <h4 class="colour-defult">Modification</h4>
                        <p>Documentation and records related to aircraft and equipment modifications.</p>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Modification records and documentation will be displayed here.
                            </div>

                            <!-- Add your modification content here -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tools me-2"></i>
                                        Modification Records
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Modification data will be loaded here...</p>
                                    <!-- Table or cards for modification records -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="rnd" role="tabpanel">
                        <h4 class="colour-defult">Research & Development</h4>
                        <p>Research projects, development initiatives, and innovation records.</p>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                R&D projects and research documentation will be displayed here.
                            </div>

                            <!-- Add your R&D content here -->
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-flask me-2"></i>
                                        R&D Projects
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">R&D project data will be loaded here...</p>
                                    <!-- Table or cards for R&D projects -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Emission Test Tab Panes -->
                    <div class="tab-pane fade" id="vehicle_annual_plans" role="tabpanel">
                        <h4 class="colour-defult">Vehicle Emission Test - Annual Plans</h4>
                        <p>Annual testing schedules, plans, and compliance documentation for vehicle emission tests.</p>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Annual emission test plans and schedules will be displayed here.
                            </div>

                            <!-- Add your annual plans content here -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Annual Test Plans
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Annual emission test plans will be loaded here...</p>
                                    <!-- Table or cards for annual plans -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="vehicle_test_reports" role="tabpanel">
                        <h4 class="colour-defult">Vehicle Emission Test - Test Reports</h4>
                        <p>Detailed test reports, results, and compliance certificates for vehicle emission testing.</p>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Vehicle emission test reports and results will be displayed here.
                            </div>

                            <!-- Add your test reports content here -->
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file-contract me-2"></i>
                                        Test Reports
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Emission test reports will be loaded here...</p>
                                    <!-- Table or cards for test reports -->
                                </div>
                            </div>
                        </div>
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