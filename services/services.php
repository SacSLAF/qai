<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

try {
    require_once "../includes/config.php";

    // Initialize variables to avoid undefined variable errors
    $show_pdf = false;
    $pdf_file = '';
    $pdf_web_path = '';
    $error = '';
    $file = 'doc_1.pdf';

    // Check if a PDF file is requested
    $file_path = "../admin/action/uploads/services/audit_plan/" . $file;
    $absolute_path = realpath($file_path);

    if ($absolute_path && file_exists($absolute_path)) {
        $show_pdf = true;
        $pdf_web_path = "/qai/admin/action/uploads/services/audit_plan/" . $file;
        $pdf_web_path_vet_plan = "/qai/admin/action/uploads/services/vet_plan/" . $file;
    } else {
        $show_pdf = false;
        $error = "File not found. Tried path: " . htmlspecialchars($file_path);
        if ($absolute_path === false) {
            $error .= " (Path does not exist)";
        }
    }

    // Initialize arrays and error variables
    $qa_reports = [];
    $qa_check_lists = [];
    $qa_reports_error = '';
    $qa_check_lists_error = '';
    $formations_map = [];
    $types_map = [];
    $ac_cat_map = [];
    $aircraft_competency_data = [];
    $ac_error = '';
    $latitude_extension_data = [];
    $le_error = '';

    // Check if database connection exists and is valid
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $qa_reports_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $ac_error = $qa_reports_error;
        $le_error = $qa_reports_error;
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
            } else {
                $qa_check_lists_error = "QA Check List query execution failed: " . $stmt2->error;
            }
        } else {
            $qa_check_lists_error = "Error preparing QA Check List query: " . $db->error;
        }

        // Load lookup maps (formations, types, ac_categories)
        $f_res = $db->query("SELECT formation_id, formation_name FROM formation");
        if ($f_res) {
            foreach ($f_res->fetch_all(MYSQLI_ASSOC) as $f) {
                $formations_map[$f['formation_id']] = $f['formation_name'];
            }
        }

        $t_res = $db->query("SELECT type_id, type_name FROM type");
        if ($t_res) {
            foreach ($t_res->fetch_all(MYSQLI_ASSOC) as $t) {
                $types_map[$t['type_id']] = $t['type_name'];
            }
        }

        // Load ac_categories for aircraft competency categories
        $ac_res = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
        if ($ac_res) {
            foreach ($ac_res->fetch_all(MYSQLI_ASSOC) as $c) {
                $ac_cat_map[$c['id']] = $c['name'];
            }
        }

        // Fetch aircraft competency records and organize by ac_category
        $stmt3 = $db->prepare("
            SELECT ac.* 
            FROM aircraft_competency ac 
            ORDER BY ac.branch, ac.type_id, ac.name
        ");

        if ($stmt3) {
            if ($stmt3->execute()) {
                $result3 = $stmt3->get_result();
                $all_records = $result3->fetch_all(MYSQLI_ASSOC);

                // Organize by ac_category (branch field in aircraft_competency table)
                foreach ($all_records as $record) {
                    $category_id = $record['branch'] ?? 0;
                    $category_name = $ac_cat_map[$category_id] ?? 'Uncategorized';

                    if (!isset($aircraft_competency_data[$category_id])) {
                        $aircraft_competency_data[$category_id] = [
                            'category_name' => $category_name,
                            'records' => []
                        ];
                    }
                    $aircraft_competency_data[$category_id]['records'][] = $record;
                }
                $stmt3->close();
            } else {
                $ac_error = "Aircraft Competency query execution failed: " . $stmt3->error;
            }
        } else {
            $ac_error = "Error preparing Aircraft Competency query: " . $db->error;
        }

        // Fetch Latitude & Extensions data
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

    // Include head template after all PHP processing
    include '../template/head.php';

    // Clear any previous output
    ob_end_clean();
} catch (Exception $e) {
    // Handle any exceptions
    ob_end_clean();
    die("Error: " . $e->getMessage());
}
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../node_modules/@fortawesome/fontawesome-free/css/all.min.css">

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

        .welcome-message {
            text-align: center;
            padding: 40px 20px;
        }

        .welcome-image {
            max-width: 300px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .table-responsive {
            margin-top: 15px;
        }

        .document-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .no-documents {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 12px;
            color: #6c757d;
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

                    <!-- Aircraft Competency Dropdown - Using ac_categories only -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Aircraft Competency</a>
                        <div class="qa-dropdown-menu">
                            <?php if (!empty($ac_cat_map)): ?>
                                <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                                    <a class="qa-dropdown-item" data-bs-target="#ac_cmpt_<?= $category_id ?>" role="tab">
                                        <?= htmlspecialchars($category_name) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <a class="qa-dropdown-item text-muted">No categories found</a>
                            <?php endif; ?>
                        </div>
                    </div>

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
                    <!-- Welcome Screen (shown by default) -->
                    <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                        <div class="welcome-message">
                            <img src="../assets/images/qai-welcome.jpg" alt="Quality Assurance Inspectorate" class="welcome-image">
                            <h4>Welcome to Command Quality Assurance Inspectorate</h4>
                            <p>Please select an option from the navigation menu to view the content.</p>
                        </div>
                    </div>

                    <!-- QA Audits Content Panes -->
                    <div class="tab-pane fade" id="audits_plan" role="tabpanel">
                        <?php if ($show_pdf): ?>
                            <div class="top-bar">
                                <a href="?file=<?= $file ?>" class="btn btn-sm btn-dark">Audit Plan Document</a>
                            </div>

                            <!-- PDF.js Viewer -->
                            <div class="pdf-viewer-container">
                                <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($pdf_web_path) ?>"
                                    width="100%" height="100%" style="border:none;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <div class="alert alert-info">
                                <p>No audit plan document is available at the moment.</p>
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
                                    <?php if (!empty($qa_check_lists_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($qa_check_lists_error) ?>
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
                                                            <!-- <td>
                                                                <?php if (!empty($qa_check_list['file_path'])): ?>
                                                                    <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>"
                                                                        target="_blank"
                                                                        class="btn btn-primary btn-sm view-pdf-btn">
                                                                        View PDF
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No file</span>
                                                                <?php endif; ?>
                                                            </td> -->
                                                            <td>
                                                                <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>"
                                                                    class="btn btn-primary btn-sm view-pdf-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#pdfModal"
                                                                    data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>">
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
                                                            <!-- <td>
                                                                <?php if (!empty($report['file_path'])): ?>
                                                                    <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                        target="_blank"
                                                                        class="btn btn-primary btn-sm view-pdf-btn">
                                                                        View PDF
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No file</span>
                                                                <?php endif; ?>
                                                            </td> -->
                                                            <td>
                                                                <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                    class="btn btn-primary btn-sm view-pdf-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#pdfModal"
                                                                    data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>">
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
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aircraft Competency Tab Panes - Using ac_categories only -->
                    <?php if (!empty($ac_cat_map)): ?>
                        <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                            <div class="tab-pane fade" id="ac_cmpt_<?= $category_id ?>" role="tabpanel">
                                <h4 class="colour-defult">Aircraft Competency - <?= htmlspecialchars($category_name) ?></h4>

                                <div class="mt-4">
                                    <?php if (!empty($ac_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($ac_error) ?>
                                        </div>
                                    <?php elseif (isset($aircraft_competency_data[$category_id]) && !empty($aircraft_competency_data[$category_id]['records'])): ?>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-hover mb-0">
                                                        <thead>
                                                            <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-light-blue' ?>">
                                                                <th>SVC No</th>
                                                                <th>Rank</th>
                                                                <th>Name</th>
                                                                <th>Trade</th>
                                                                <th>Formation</th>
                                                                <th>Posted In Date</th>
                                                                <th>Aircraft Type</th>
                                                                <!-- <th>Type</th> -->
                                                                <th>Competency Level</th>
                                                                <th>Competency Issue Ref</th>
                                                                <!-- <th>Com Issue Date</th>
                                                                <th>Competency Renew Ref</th>
                                                                <th>Renew Date</th>
                                                                <th>Certificate No</th>
                                                                <th>Cer Issued Date</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($aircraft_competency_data[$category_id]['records'] as $record): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($record['svc_no'] ?? '') ?></strong></td>
                                                                    <td><?= htmlspecialchars($record['rank'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['name'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['trade'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($formations_map[$record['formation_id']] ?? $record['formation'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['posted_in_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($types_map[$record['type_id']] ?? $record['aircraft_type'] ?? '') ?></td>
                                                                    <!-- <td><?= htmlspecialchars($record['type_id'] ?? '') ?></td> -->
                                                                    <td><?= htmlspecialchars($record['competency_level'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['competency_issue_ref'] ?? '') ?></td>
                                                                    <!-- <td><?= htmlspecialchars($record['com_issue_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['competency_renew_ref'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['renew_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['certificate_no'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['cer_issued_date'] ?? '') ?></td> -->
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <style>
                                                .bg-light-blue {
                                                    background-color: #f0f8ff !important;
                                                }

                                                .bg-white {
                                                    background-color: #ffffff !important;
                                                }

                                                .table tbody tr:hover {
                                                    background-color: #e3f2fd !important;
                                                }

                                                .table th {
                                                    background-color: #839abdff;
                                                    color: white;
                                                    font-weight: 600;
                                                    border: none;
                                                }

                                                .table td {
                                                    border-bottom: 1px solid #dee2e6;
                                                    padding: 12px 8px;
                                                    vertical-align: middle;
                                                }
                                            </style>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No aircraft competency records found for <?= htmlspecialchars($category_name) ?>.
                                            <?php
                                            // Debug information
                                            echo "<br><small>Category ID: $category_id | ";
                                            echo "Total records in data: " . count($aircraft_competency_data) . " | ";
                                            echo "Records for this category: " . (isset($aircraft_competency_data[$category_id]) ? count($aircraft_competency_data[$category_id]['records']) : '0');
                                            echo "</small>";
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No aircraft categories found in the system.
                        </div>
                    <?php endif; ?>

                    <!-- Latitude & Extensions Tab -->
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

                    <!-- Modification / R&D Tab Panes -->
                    <div class="tab-pane fade" id="modification" role="tabpanel">
                        <h4 class="colour-defult">Modification</h4>
                        <p>Documentation and records related to aircraft and equipment modifications.</p>
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Modification records and documentation will be displayed here.
                            </div>
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tools me-2"></i>
                                        Modification Records
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Modification data will be loaded here...</p>
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
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-flask me-2"></i>
                                        R&D Projects
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">R&D project data will be loaded here...</p>
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
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Annual Test Plans
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Annual emission test plans will be loaded here...</p>
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
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file-contract me-2"></i>
                                        Test Reports
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Emission test reports will be loaded here...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Modal Structure -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="height: 80vh;">
                    <iframe id="pdfFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include '../template/foot.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="../assets/js/swiper-bundle.min.js"></script>

    <script>
        // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {
            const pdfModal = document.getElementById('pdfModal');
            const pdfFrame = document.getElementById('pdfFrame');

            pdfModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const pdfUrl = button.getAttribute('data-pdf-url');
                pdfFrame.src = pdfUrl;
            });

            pdfModal.addEventListener('hidden.bs.modal', function() {
                pdfFrame.src = ""; // Clear iframe to stop PDF from running
            });
            // Set initial active tab to welcome screen
            const welcomePane = document.querySelector('#welcome');
            if (welcomePane) {
                welcomePane.classList.add('show', 'active');
            }

            // Remove any active classes from navigation items initially
            document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(item => {
                item.classList.remove('active');
            });

            // Close all dropdown menus initially
            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });

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

                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show the target tab content and hide welcome screen
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes including welcome
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

                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Remove active class from all dropdown toggles
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });

                    // Add active class to clicked dropdown item
                    this.classList.add('active');

                    // If this is a dropdown item, activate the parent dropdown toggle and keep menu open
                    if (this.classList.contains('qa-dropdown-item')) {
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
                    }

                    // Show the target tab content and hide welcome screen
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes including welcome
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