<?php
// productivity.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

try {
    require_once "../includes/config.php";
    
    // Initialize variables to avoid undefined variable errors
    $show_pdf = false;
    $pdf_file = '';
    $pdf_web_path = '';
    $error = '';

    // Fetch data for Active QCC
    $active_qcc = [];
    $active_qcc_error = '';

    // Use uploaded PDFs for Audit Plan and Audit Checklist if present
    $productivity_dir = "../admin/action/uploads/productivity/productivity/";
    $productivity_audit_plan_pdf = $productivity_dir . "audit_plan.pdf";
    $productivity_audit_check_pdf = $productivity_dir . "audit_checklist.pdf";

    $oshy_dir = "../admin/action/uploads/productivity/occupational_safety/";
    $osh_audit_plan_pdf = $oshy_dir . "audit_plan.pdf";
    $osh_audit_check_pdf = $oshy_dir . "audit_checklist.pdf";

    $env_dir = "../admin/action/uploads/productivity/environmental/";
    $env_audit_plan_pdf = $env_dir . "audit_plan.pdf";
    $env_audit_check_pdf = $env_dir . "audit_checklist.pdf";

    $productivity_audit_plan_exists = file_exists($productivity_audit_plan_pdf);
    $productivity_audit_check_exists = file_exists($productivity_audit_check_pdf);

    $osh_audit_plan_exists = file_exists($osh_audit_plan_pdf);
    $osh_audit_check_exists = file_exists($osh_audit_check_pdf);

    $env_audit_plan_exists = file_exists($env_audit_plan_pdf);
    $env_audit_check_exists = file_exists($env_audit_check_pdf);

    // Fixed the variable assignments - removed colons and corrected variable names
    $productivity_audit_plan_web = $productivity_audit_plan_exists ? "/qai/admin/action/uploads/productivity/productivity/audit_plan.pdf" : '';
    $productivity_audit_check_web = $productivity_audit_check_exists ? "/qai/admin/action/uploads/productivity/productivity/audit_checklist.pdf" : '';

    $osh_audit_plan_web = $osh_audit_plan_exists ? "/qai/admin/action/uploads/productivity/occupational_safety/audit_plan.pdf" : '';
    $osh_audit_check_web = $osh_audit_check_exists ? "/qai/admin/action/uploads/productivity/occupational_safety/audit_checklist.pdf" : '';

    $env_audit_plan_web = $env_audit_plan_exists ? "/qai/admin/action/uploads/productivity/environmental/audit_plan.pdf" : '';
    $env_audit_check_web = $env_audit_check_exists ? "/qai/admin/action/uploads/productivity/environmental/audit_checklist.pdf" : '';

    // Fetch data for Productivity Audit Plan (Category ID = 2)
    $productivity_audit_plan = [];
    $productivity_audit_plan_error = '';

    // Fetch data for Productivity Audit Checklist (Category ID = 3)
    $productivity_audit_checklist = [];
    $productivity_audit_checklist_error = '';

    // Fetch data for Productivity Audit Report (Category ID = 1)
    $productivity_audit_report = [];
    $productivity_audit_report_error = '';

    // Fetch data for OSH Audit Report (Category ID = 2)
    $osh_audit_report = [];
    $osh_audit_report_error = '';

    // Fetch data for Environment Audit Report (Category ID = 3)
    $env_audit_report = [];
    $env_audit_report_error = '';

    // Fetch other OSH data
    $osh_audit_plan = [];
    $osh_audit_checklist = [];
    $osh_manual = [];
    $osh_error = '';

    // Fetch other Environment data
    $env_audit_plan = [];
    $env_audit_checklist = [];
    $env_error = '';

    // Fetch data for Awards
    $awards_best_qcc = [];
    $awards_best_env = [];
    $awards_error = '';

    // Check database connection
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $active_qcc_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $productivity_audit_plan_error = $active_qcc_error;
        $productivity_audit_checklist_error = $active_qcc_error;
        $productivity_audit_report_error = $active_qcc_error;
        $osh_audit_report_error = $active_qcc_error;
        $env_audit_report_error = $active_qcc_error;
        $osh_error = $active_qcc_error;
        $env_error = $active_qcc_error;
        $awards_error = $active_qcc_error;
    } else {
        // Fetch Active QCC records
        $stmt_qcc = $db->prepare("
            SELECT aq.*, se.name as establishment_name, se.code as establishment_code, 
                   pc.name as category_name, s.name as section_name, a.username as created_by_name
            FROM active_qcc aq 
            LEFT JOIN slaf_establishments se ON aq.slaf_establishment_id = se.id 
            LEFT JOIN productivity_categories pc ON aq.category_id = pc.id 
            LEFT JOIN sections s ON aq.section_id = s.id 
            LEFT JOIN admins a ON aq.created_by = a.id 
            ORDER BY aq.sno ASC
        ");

        if ($stmt_qcc) {
            if ($stmt_qcc->execute()) {
                $result_qcc = $stmt_qcc->get_result();
                $active_qcc = $result_qcc->fetch_all(MYSQLI_ASSOC);
                $stmt_qcc->close();
            } else {
                $active_qcc_error = "Active QCC query execution failed: " . $stmt_qcc->error;
            }
        } else {
            $active_qcc_error = "Error preparing Active QCC query: " . $db->error;
        }

        // Fetch Productivity Audit Report records (Category ID = 1)
        $stmt_prod_report = $db->prepare("
            SELECT ar.*, se.name as establishment_name, se.code as establishment_code,
                   pc.name as category_name, s.name as section_name, a.username as created_by_name
            FROM audit_report ar 
            LEFT JOIN slaf_establishments se ON ar.slaf_establishment_id = se.id 
            LEFT JOIN productivity_categories pc ON ar.productivity_category_id = pc.id 
            LEFT JOIN sections s ON ar.section_id = s.id 
            LEFT JOIN admins a ON ar.uploaded_by = a.id 
            WHERE ar.productivity_category_id = 1
            ORDER BY ar.conducted_date DESC, ar.sno ASC
        ");

        if ($stmt_prod_report) {
            if ($stmt_prod_report->execute()) {
                $result_prod_report = $stmt_prod_report->get_result();
                $productivity_audit_report = $result_prod_report->fetch_all(MYSQLI_ASSOC);
                $stmt_prod_report->close();
            } else {
                $productivity_audit_report_error = "Productivity Audit Report query execution failed: " . $stmt_prod_report->error;
            }
        } else {
            $productivity_audit_report_error = "Error preparing Productivity Audit Report query: " . $db->error;
        }

        // Fetch OSH Audit Report records (Category ID = 2)
        $stmt_osh_report = $db->prepare("
            SELECT ar.*, se.name as establishment_name, se.code as establishment_code,
                   pc.name as category_name, s.name as section_name, a.username as created_by_name
            FROM audit_report ar 
            LEFT JOIN slaf_establishments se ON ar.slaf_establishment_id = se.id 
            LEFT JOIN productivity_categories pc ON ar.productivity_category_id = pc.id 
            LEFT JOIN sections s ON ar.section_id = s.id 
            LEFT JOIN admins a ON ar.uploaded_by = a.id 
            WHERE ar.productivity_category_id = 2
            ORDER BY ar.conducted_date DESC, ar.sno ASC
        ");

        if ($stmt_osh_report) {
            if ($stmt_osh_report->execute()) {
                $result_osh_report = $stmt_osh_report->get_result();
                $osh_audit_report = $result_osh_report->fetch_all(MYSQLI_ASSOC);
                $stmt_osh_report->close();
            } else {
                $osh_audit_report_error = "OSH Audit Report query execution failed: " . $stmt_osh_report->error;
            }
        } else {
            $osh_audit_report_error = "Error preparing OSH Audit Report query: " . $db->error;
        }

        // Fetch Environment Audit Report records (Category ID = 3)
        $stmt_env_report = $db->prepare("
            SELECT ar.*, se.name as establishment_name, se.code as establishment_code,
                   pc.name as category_name, s.name as section_name, a.username as created_by_name
            FROM audit_report ar 
            LEFT JOIN slaf_establishments se ON ar.slaf_establishment_id = se.id 
            LEFT JOIN productivity_categories pc ON ar.productivity_category_id = pc.id 
            LEFT JOIN sections s ON ar.section_id = s.id 
            LEFT JOIN admins a ON ar.uploaded_by = a.id 
            WHERE ar.productivity_category_id = 3
            ORDER BY ar.conducted_date DESC, ar.sno ASC
        ");

        if ($stmt_env_report) {
            if ($stmt_env_report->execute()) {
                $result_env_report = $stmt_env_report->get_result();
                $env_audit_report = $result_env_report->fetch_all(MYSQLI_ASSOC);
                $stmt_env_report->close();
            } else {
                $env_audit_report_error = "Environment Audit Report query execution failed: " . $stmt_env_report->error;
            }
        } else {
            $env_audit_report_error = "Error preparing Environment Audit Report query: " . $db->error;
        }

        // Fetch OSH Manual records (from osh_manual table)
            $stmt_osh_manual = $db->prepare("
                SELECT om.*, pc.name as category_name, s.name as section_name, a.username as created_by_name
                FROM osh_manual om 
                LEFT JOIN productivity_categories pc ON om.category_id = pc.id 
                LEFT JOIN sections s ON om.section_id = s.id 
                LEFT JOIN admins a ON om.created_by = a.id 
                ORDER BY om.sno ASC, om.created_at DESC
            ");

        if ($stmt_osh_manual) {
            if ($stmt_osh_manual->execute()) {
                $result_osh_manual = $stmt_osh_manual->get_result();
                $osh_manual = $result_osh_manual->fetch_all(MYSQLI_ASSOC);
                $stmt_osh_manual->close();
            } else {
                $osh_error = "OSH Manual query execution failed: " . $stmt_osh_manual->error;
            }
        } else {
            $osh_error = "Error preparing OSH Manual query: " . $db->error;
        }

        // Fetch Awards - Best QCC records
        $stmt_awards_qcc = $db->prepare("
            SELECT pd.*, pc.name as category_name, a.username as created_by_name
            FROM productivity_documents pd 
            LEFT JOIN productivity_categories pc ON pd.productivity_category_id = pc.id 
            LEFT JOIN admins a ON pd.uploaded_by = a.id 
            WHERE pd.productivity_category_id = 4 AND pd.is_active = 1
            ORDER BY pd.title ASC
        ");

        if ($stmt_awards_qcc) {
            if ($stmt_awards_qcc->execute()) {
                $result_awards_qcc = $stmt_awards_qcc->get_result();
                $awards_best_qcc = $result_awards_qcc->fetch_all(MYSQLI_ASSOC);
                $stmt_awards_qcc->close();
            } else {
                $awards_error = "Awards Best QCC query execution failed: " . $stmt_awards_qcc->error;
            }
        } else {
            $awards_error = "Error preparing Awards Best QCC query: " . $db->error;
        }

        // Fetch Awards - Best Environment Management Project records
        $stmt_awards_env = $db->prepare("
            SELECT pd.*, pc.name as category_name, a.username as created_by_name
            FROM productivity_documents pd 
            LEFT JOIN productivity_categories pc ON pd.productivity_category_id = pc.id 
            LEFT JOIN admins a ON pd.uploaded_by = a.id 
            WHERE pd.productivity_category_id = 4 AND pd.is_active = 1
            ORDER BY pd.title ASC
        ");

        if ($stmt_awards_env) {
            if ($stmt_awards_env->execute()) {
                $result_awards_env = $stmt_awards_env->get_result();
                $awards_best_env = $result_awards_env->fetch_all(MYSQLI_ASSOC);
                $stmt_awards_env->close();
            } else {
                $awards_error = "Awards Best Environment query execution failed: " . $stmt_awards_env->error;
            }
        } else {
            $awards_error = "Error preparing Awards Best Environment query: " . $db->error;
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
    <title>Command Quality Assurance Inspectorate - Productivity</title>
    <!-- Bootstrap CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="../assets/css/swiper-bundle.min.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../assets/datatable/datatable.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .colour-defult {
            font-size: small;
        }
        
        .text-truncate-multiline {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 200px;
        }

        .form-select-sm {
            font-size: 0.875rem;
        }

        .branch-content {
            display: none;
        }
        .branch-content.active {
            display: block;
        }

        .details-modal-table {
            width: 100%;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .details-modal-table th {
            background-color: #f8f9fa;
            width: 30%;
            padding: 8px 12px;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }
        .details-modal-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
            word-break: break-word;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .modal-xl-custom {
            max-width: 1200px;
        }

        .section-divider {
            border-top: 2px solid #007bff;
            margin: 15px 0;
            padding-top: 10px;
            font-weight: bold;
            color: #007bff;
        }
        .empty-value {
            color: #6c757d;
            font-style: italic;
        }

        .welcome-image {
            width: 100%;
        }

       /* .qa-dropdown {
            position: relative;
        }

        .qa-dropdown-menu {
            position: static;
            display: none;
            padding: 0;
            margin: 0;
            background-color: transparent;
            border: none;
            box-shadow: none;
        }

        .qa-dropdown-menu.show {
            display: block;
        }

        .qa-dropdown-item {
            display: block;
            width: 100%;
            padding: 0.5rem 1.5rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            text-decoration: none;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            font-size:x-small;
        }

        .qa-dropdown-item:hover,
        .qa-dropdown-item:focus {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }

        .qa-dropdown-toggle::after {
            float: right;
            margin-left: 0.255em;
            vertical-align: 0.255em;
        }

        .nav-column .nav-link {
            border-radius: 0;
            padding: 0.75rem 1rem;
        }

        .nav-column .qa-dropdown-item {
            font-size:x-small;
            padding-left: 2rem;
            color: #212529;
        }

        .nav-column .qa-dropdown-item.qa-dropdown-subitem {
            font-size:x-small;
            padding-left: 3rem;
            color: #212529;
        }*/

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .pdf-viewer-container {
            height: calc(100vh - 200px);
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../template/header.php'; ?>

    <!-- Main Content -->
    <main class="container-fluid">
        <div class="row">
            <div class="col-lg-1 col-xl-1">
                    <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                        <!-- Productivity Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle active" role="button">Productivity</a>
                            <div class="qa-dropdown-menu">
                                <a class="qa-dropdown-item active" data-bs-target="#qcc_active" role="tab">Active QCC 2025</a>
                                <a class="qa-dropdown-item" data-bs-target="#prod_audit_plan" role="tab">Productivity Audit Plan</a>
                                <a class="qa-dropdown-item" data-bs-target="#prod_audit_checklist" role="tab">Productivity Audit Check List</a>
                                <a class="qa-dropdown-item" data-bs-target="#prod_audit_report" role="tab">Productivity Audit Report</a>
                            </div>
                        </div>
                        
                        <!-- OSH Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle" role="button">Occupational Safety <br>& Health</a>
                            <div class="qa-dropdown-menu">
                                <a class="qa-dropdown-item" data-bs-target="#osh_plan" role="tab">Audit Plan</a>
                                <a class="qa-dropdown-item" data-bs-target="#osh_checklist" role="tab">Audit Check List</a>
                                <a class="qa-dropdown-item" data-bs-target="#osh_report" role="tab">Audit Reports</a>
                                <a class="qa-dropdown-item" data-bs-target="#osh_man" role="tab">OSH Manual</a>
                            </div>
                        </div>

                        <!-- Environment Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle" role="button">Environmental Mgt</a>
                            <div class="qa-dropdown-menu">
                                <a class="qa-dropdown-item" data-bs-target="#env_plan" role="tab">Audit Plan</a>
                                <a class="qa-dropdown-item" data-bs-target="#env_checklist" role="tab">Audit Check List</a>
                                <a class="qa-dropdown-item" data-bs-target="#env_report" role="tab">Audit Report</a>
                            </div>
                        </div>

                        <!-- Awards Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle" role="button">Awards</a>
                            <div class="qa-dropdown-menu">
                                <a class="qa-dropdown-item" data-bs-target="#awards_qcc" role="tab">Best QCC</a>
                                <a class="qa-dropdown-item" data-bs-target="#awards_env" role="tab">Best Environment Management Project</a>
                            </div>
                        </div>
                    </div>

            </div>

            <!-- Tab Content -->
            <div class="col-lg-11 col-xl-11">
                <div class="content-column">
                    <div class="tab-content" id="inspectorateTabsContent">
                        <!-- Welcome Screen (shown by default) -->
                        <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                            <div class="welcome-message">
                                <!-- <img src="../assets/img/qai-welcome.jpg" alt="Quality Assurance Inspectorate" class="welcome-image"> -->
                            </div>
                        </div>

                        <!-- Active QCC Tab -->
                        <div class="tab-pane fade" id="qcc_active" role="tabpanel">
                            <h4 class="colour-defult">Active QCC 2025</h4>
                            <div class="mt-4">
                                <?php if (!empty($active_qcc_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($active_qcc_error) ?>
                                    </div>
                                <?php elseif (!empty($active_qcc)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="activeQccTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S/No</th>
                                                            <th>QCC Name</th>
                                                            <th>SLAF Establishment</th>
                                                            <th>Location</th>
                                                            <th>Team Members</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($active_qcc as $qcc): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($qcc['sno']) ?></strong></td>
                                                                <td><?= htmlspecialchars($qcc['qcc_name']) ?></td>
                                                                <td>
                                                                    <?= htmlspecialchars($qcc['establishment_name']) ?>
                                                                    <?php if (!empty($qcc['establishment_code'])): ?>
                                                                        <br><small class="text-muted">(<?= htmlspecialchars($qcc['establishment_code']) ?>)</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($qcc['location']) ?></td>
                                                                <td>
                                                                    <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($qcc['team_members']) ?>">
                                                                        <?= htmlspecialchars(substr($qcc['team_members'], 0, 30)) . (strlen($qcc['team_members']) > 30 ? '...' : '') ?>
                                                                    </span>
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
                                        No Active QCC records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Productivity Audit Plan Tab -->
                        <div class="tab-pane fade" id="prod_audit_plan" role="tabpanel">
                            <?php if (!empty($productivity_audit_plan_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $productivity_audit_plan_web ?>" target="_blank" class="btn btn-sm btn-dark">Audit Plan</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($productivity_audit_plan_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No audit plan document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Productivity Audit Checklist Tab -->
                        <div class="tab-pane fade" id="prod_audit_checklist" role="tabpanel">
                            <?php if (!empty($productivity_audit_check_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $productivity_audit_check_web ?>" target="_blank" class="btn btn-sm btn-dark">Audit Checklist</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($productivity_audit_check_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No audit checklist document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Productivity Audit Report Tab -->
                        <div class="tab-pane fade" id="prod_audit_report" role="tabpanel">
                            <h4 class="colour-defult">Productivity Audit Report</h4>
                            <div class="mt-4">
                                <?php if (!empty($productivity_audit_report_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($productivity_audit_report_error) ?>
                                    </div>
                                <?php elseif (!empty($productivity_audit_report)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="prodAuditReportTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S/No</th>
                                                            <th>Establishment</th>
                                                            <th>Conducted Date</th>
                                                            <th>Category</th>
                                                            <th>Section</th>
                                                            <th>Created By</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($productivity_audit_report as $report): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($report['sno']) ?></strong></td>
                                                                <td>
                                                                    <?= htmlspecialchars($report['establishment_name']) ?>
                                                                    <?php if (!empty($report['establishment_code'])): ?>
                                                                        <br><small class="text-muted">(<?= htmlspecialchars($report['establishment_code']) ?>)</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= date('Y-m-d', strtotime($report['conducted_date'])) ?></td>
                                                                <td><?= htmlspecialchars($report['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['section_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['created_by_name'] ?? 'System') ?></td>
                                                                <td>
                                                                    <?php if (!empty($report['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No Productivity Audit Report records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- OSH Tab Panes -->
                        <div class="tab-pane fade" id="osh_plan" role="tabpanel">
                            <?php if (!empty($osh_audit_plan_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $osh_audit_plan_web ?>" target="_blank" class="btn btn-sm btn-dark">OSH Audit Plan</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($osh_audit_plan_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No OSH audit plan document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane fade" id="osh_checklist" role="tabpanel">
                            <?php if (!empty($osh_audit_check_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $osh_audit_check_web ?>" target="_blank" class="btn btn-sm btn-dark">OSH Audit Checklist</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($osh_audit_check_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No OSH audit checklist document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- OSH Manual Tab -->
                        <div class="tab-pane fade" id="osh_man" role="tabpanel">
                            <h4 class="colour-defult">OSH Manual</h4>
                            <div class="mt-4">
                                <?php if (!empty($osh_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($osh_error) ?>
                                    </div>
                                <?php elseif (!empty($osh_manual)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="oshManualTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S/No</th>
                                                            <th>Description</th>
                                                            <th>Manual No</th>
                                                            <th>Revision Status</th>
                                                            <th>Category</th>
                                                            <th>Section</th>
                                                            <th>Created By</th>
                                                            <th>Created Date</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($osh_manual as $manual): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($manual['sno']) ?></strong></td>
                                                                <td><?= htmlspecialchars($manual['description']) ?></td>
                                                                <td><?= htmlspecialchars($manual['manual_no']) ?></td>
                                                                <td>
                                                                    <span class="badge badge-<?= 
                                                                        $manual['rev_status'] === 'Current' ? 'success' : 
                                                                        ($manual['rev_status'] === 'Revised' ? 'warning' : 'secondary')
                                                                    ?>">
                                                                        <?= htmlspecialchars($manual['rev_status']) ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= htmlspecialchars($manual['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($manual['section_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($manual['created_by_name'] ?? 'System') ?></td>
                                                                <td><?= date('Y-m-d', strtotime($manual['created_at'])) ?></td>
                                                                <td>
                                                                    <?php if (!empty($manual['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $manual['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $manual['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No OSH Manual records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="osh_report" role="tabpanel">
                            <h4 class="colour-defult">OSH Audit Report</h4>
                            <div class="mt-4">
                                <?php if (!empty($osh_audit_report_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($osh_audit_report_error) ?>
                                    </div>
                                <?php elseif (!empty($osh_audit_report)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="oshReportTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S/No</th>
                                                            <th>Establishment</th>
                                                            <th>Conducted Date</th>
                                                            <th>Category</th>
                                                            <th>Section</th>
                                                            <th>Created By</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($osh_audit_report as $report): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($report['sno']) ?></strong></td>
                                                                <td>
                                                                    <?= htmlspecialchars($report['establishment_name']) ?>
                                                                    <?php if (!empty($report['establishment_code'])): ?>
                                                                        <br><small class="text-muted">(<?= htmlspecialchars($report['establishment_code']) ?>)</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= date('Y-m-d', strtotime($report['conducted_date'])) ?></td>
                                                                <td><?= htmlspecialchars($report['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['section_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['created_by_name'] ?? 'System') ?></td>
                                                                <td>
                                                                    <?php if (!empty($report['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No OSH Audit Report records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Environment Tab Panes -->
                        <div class="tab-pane fade" id="env_plan" role="tabpanel">
                            <?php if (!empty($env_audit_plan_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $env_audit_plan_web ?>" target="_blank" class="btn btn-sm btn-dark">Environment Audit Plan</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($env_audit_plan_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No environment audit plan document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane fade" id="env_checklist" role="tabpanel">
                            <?php if (!empty($env_audit_check_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $env_audit_check_web ?>" target="_blank" class="btn btn-sm btn-dark">Environment Audit Checklist</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($env_audit_check_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No environment audit checklist document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane fade" id="env_report" role="tabpanel">
                            <h4 class="colour-defult">Environment - Audit Report</h4>
                            <div class="mt-4">
                                <?php if (!empty($env_audit_report_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($env_audit_report_error) ?>
                                    </div>
                                <?php elseif (!empty($env_audit_report)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="envReportTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S/No</th>
                                                            <th>Establishment</th>
                                                            <th>Conducted Date</th>
                                                            <th>Category</th>
                                                            <th>Section</th>
                                                            <th>Created By</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($env_audit_report as $report): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($report['sno']) ?></strong></td>
                                                                <td>
                                                                    <?= htmlspecialchars($report['establishment_name']) ?>
                                                                    <?php if (!empty($report['establishment_code'])): ?>
                                                                        <br><small class="text-muted">(<?= htmlspecialchars($report['establishment_code']) ?>)</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= date('Y-m-d', strtotime($report['conducted_date'])) ?></td>
                                                                <td><?= htmlspecialchars($report['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['section_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($report['created_by_name'] ?? 'System') ?></td>
                                                                <td>
                                                                    <?php if (!empty($report['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No Environment Audit Report records found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Awards Tab Panes -->
                        <div class="tab-pane fade" id="awards_qcc" role="tabpanel">
                            <h4 class="colour-defult">Awards - Best QCC</h4>
                            <div class="mt-4">
                                <?php if (!empty($awards_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($awards_error) ?>
                                    </div>
                                <?php elseif (!empty($awards_best_qcc)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="awardsQccTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Description</th>
                                                            <th>Category</th>
                                                            <th>Created By</th>
                                                            <th>Upload Date</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($awards_best_qcc as $doc): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($doc['title']) ?></strong></td>
                                                                <td><?= htmlspecialchars($doc['description']) ?></td>
                                                                <td><?= htmlspecialchars($doc['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($doc['created_by_name'] ?? 'System') ?></td>
                                                                <td><?= date('Y-m-d', strtotime($doc['uploaded_at'])) ?></td>
                                                                <td>
                                                                    <?php if (!empty($doc['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $doc['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $doc['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No Best QCC Awards documents found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="awards_env" role="tabpanel">
                            <h4 class="colour-defult">Awards - Best Environment Management Project</h4>
                            <div class="mt-4">
                                <?php if (!empty($awards_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($awards_error) ?>
                                    </div>
                                <?php elseif (!empty($awards_best_env)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="awardsEnvTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Description</th>
                                                            <th>Category</th>
                                                            <th>Created By</th>
                                                            <th>Upload Date</th>
                                                            <th>File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($awards_best_env as $doc): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($doc['title']) ?></strong></td>
                                                                <td><?= htmlspecialchars($doc['description']) ?></td>
                                                                <td><?= htmlspecialchars($doc['category_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($doc['created_by_name'] ?? 'System') ?></td>
                                                                <td><?= date('Y-m-d', strtotime($doc['uploaded_at'])) ?></td>
                                                                <td>
                                                                    <?php if (!empty($doc['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $doc['file_path']) ?>"
                                                                            class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $doc['file_path']) ?>">View
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
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
                                        No Best Environment Management Project Awards documents found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- PDF Modal -->
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

    <!-- jQuery -->
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="../assets/datatable/datatable.min.js"></script>
    <!-- Swiper JS -->
    <script src="../assets/js/swiper-bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Document ready - initializing DataTables');

            // DataTable configuration
            const dataTableConfig = {
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "order": [
                    [0, "asc"]
                ],
                "language": {
                    "search": "Filter:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "previous": "Previous",
                        "next": "Next"
                    }
                },
                "autoWidth": false,
                "responsive": true,
                "destroy": true
            };

            // Initialize all tables
            const tableIds = [
                'activeQccTable', 'prodAuditReportTable',
                'oshManualTable', 'oshReportTable',
                'envReportTable',
                'awardsQccTable', 'awardsEnvTable'
            ];

            tableIds.forEach(tableId => {
                if ($('#' + tableId).length) {
                    $('#' + tableId).DataTable(dataTableConfig);
                }
            });

            // PDF Modal functionality
            const pdfModal = document.getElementById('pdfModal');
            const pdfFrame = document.getElementById('pdfFrame');

            if (pdfModal) {
                pdfModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const pdfUrl = button.getAttribute('data-pdf-url');
                    pdfFrame.src = pdfUrl;
                });

                pdfModal.addEventListener('hidden.bs.modal', function() {
                    pdfFrame.src = "";
                });
            }

            // Enhanced Navigation and dropdown handling
            const welcomePane = document.querySelector('#welcome');
            if (welcomePane) {
                welcomePane.classList.add('show', 'active');
            }

            document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(item => {
                item.classList.remove('active');
            });

            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });

            // Enhanced dropdown toggle handling
            const dropdownToggles = document.querySelectorAll('.qa-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    if (!dropdownMenu) return;

                    const isCurrentlyOpen = dropdownMenu.classList.contains('show');

                    // Close all dropdowns at the same level
                    const parentItem = this.parentElement;
                    if (parentItem) {
                        const siblings = parentItem.parentElement?.children;
                        if (siblings) {
                            Array.from(siblings).forEach(sibling => {
                                if (sibling !== parentItem) {
                                    const siblingMenu = sibling.querySelector('.qa-dropdown-menu');
                                    if (siblingMenu) {
                                        siblingMenu.classList.remove('show');
                                    }
                                    const siblingToggle = sibling.querySelector('.qa-dropdown-toggle');
                                    if (siblingToggle) {
                                        siblingToggle.classList.remove('active');
                                    }
                                }
                            });
                        }
                    }

                    if (!isCurrentlyOpen) {
                        dropdownMenu.classList.add('show');
                        this.classList.add('active');
                    } else {
                        dropdownMenu.classList.remove('show');
                        this.classList.remove('active');
                    }
                });
            });

            // Main nav links and dropdown items
            const allNavItems = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle), .qa-dropdown-item:not(.qa-dropdown-toggle)');
            allNavItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (this.classList.contains('qa-dropdown-toggle')) {
                        return; // Let the dropdown toggle handler deal with it
                    }

                    e.preventDefault();
                    // Remove active class from all items
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Add active class to clicked item and its parents
                    let currentItem = this;
                    while (currentItem) {
                        currentItem.classList.add('active');
                        if (currentItem.classList.contains('qa-dropdown-item')) {
                            const parentDropdown = currentItem.closest('.qa-dropdown');
                            if (parentDropdown) {
                                const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                                if (dropdownToggle) {
                                    dropdownToggle.classList.add('active');
                                }
                            }
                        }
                        currentItem = currentItem.parentElement?.closest('.qa-dropdown-item') ||
                            currentItem.parentElement?.closest('.nav-link');
                    }

                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // Close all dropdown menus
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });

            // Close dropdowns on outside click
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-column')) {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                }
            });

            document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                }
            });

            console.log('Productivity page initialization complete');
        });
    </script>
</body>
</html>