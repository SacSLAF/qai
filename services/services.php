<?php
// service.php
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

    // Use uploaded PDFs for Audit Plan and VET Annual Plan if present
    $annual_plan_dir = "../admin/action/uploads/services/annual_plan/";
    $audit_plan_pdf = $annual_plan_dir . "audit_plan.pdf";
    $vet_annual_pdf = $annual_plan_dir . "vet_annual.pdf";
    $audit_plan_exists = file_exists($audit_plan_pdf);
    $vet_annual_exists = file_exists($vet_annual_pdf);
    $audit_plan_web = $audit_plan_exists ? "/qai/admin/action/uploads/services/annual_plan/audit_plan.pdf" : '';
    $vet_annual_web = $vet_annual_exists ? "/qai/admin/action/uploads/services/annual_plan/vet_annual.pdf" :

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
    $latitude_data = [];
    $le_error = '';

    // Check if database connection exists and is valid
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $qa_reports_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $ac_error = $qa_reports_error;
        $le_error = $qa_reports_error;
        $vehicle_emission_error = $qa_reports_error;
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

        $r_res = $db->query("SELECT id, rank_name FROM ranks");
        if ($r_res) {
            foreach ($r_res->fetch_all(MYSQLI_ASSOC) as $r) {
                $ranks_map[$r['id']] = $r['rank_name'];
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

        // Fetch Latitude data
        $stmt = $db->prepare("
            SELECT l.*, f.formation_name, t.type_name 
            FROM latitude l 
            LEFT JOIN formation f ON l.formation_id = f.formation_id 
            LEFT JOIN type t ON l.aircraft_type_id = t.type_id 
            ORDER BY l.created_at DESC
        ");

        if ($stmt) {
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $latitude_data = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            } else {
                $le_error = "Latitude query execution failed: " . $stmt->error;
            }
        } else {
            $le_error = "Error preparing Latitude query: " . $db->error;
        }

        // Fetch all vehicle emission test records
        $records = [];
        $res = $db->query("SELECT v.*, s.name as camp_name 
                        FROM vehicle_emission_test v 
                        LEFT JOIN slaf_establishments s ON v.camp_id = s.id 
                        ORDER BY v.fuel_type, v.test_date DESC, v.serial_no ASC");
        if ($res !== false) {
            $records = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log('Error fetching vehicle emission test records: ' . $db->error);
            $records = [];
        }
    }

    // Fetch Modification data
    $modification_data = [];
    $modification_error = '';
    $stmt_mod = $db->prepare("
        SELECT m.*, f.formation_name, t.type_name 
        FROM modification m 
        LEFT JOIN formation f ON m.formation_id = f.formation_id 
        LEFT JOIN type t ON m.type_id = t.type_id 
        ORDER BY m.created_at DESC
    ");

    if ($stmt_mod) {
        if ($stmt_mod->execute()) {
            $result_mod = $stmt_mod->get_result();
            $modification_data = $result_mod->fetch_all(MYSQLI_ASSOC);
            $stmt_mod->close();
        } else {
            $modification_error = "Modification query execution failed: " . $stmt_mod->error;
        }
    } else {
        $modification_error = "Error preparing Modification query: " . $db->error;
    }

    // Fetch R&D data
    $rnd_data = [];
    $rnd_error = '';
    $stmt_rnd = $db->prepare("
        SELECT r.*, f.formation_name, t.type_name 
        FROM rnd r 
        LEFT JOIN formation f ON r.formation_id = f.formation_id 
        LEFT JOIN type t ON r.type_id = t.type_id 
        ORDER BY r.created_at DESC
    ");

    if ($stmt_rnd) {
        if ($stmt_rnd->execute()) {
            $result_rnd = $stmt_rnd->get_result();
            $rnd_data = $result_rnd->fetch_all(MYSQLI_ASSOC);
            $stmt_rnd->close();
        } else {
            $rnd_error = "R&D query execution failed: " . $stmt_rnd->error;
        }
    } else {
        $rnd_error = "Error preparing R&D query: " . $db->error;
    }
    // Count vehicle emission records by fuel type for debugging
    $vet_count = is_array($vehicle_emission_data) ? count($vehicle_emission_data) : 0;
    $diesel_count = 0;
    $petrol_count = 0;
    if (is_array($vehicle_emission_data)) {
        foreach ($vehicle_emission_data as $rec) {
            $ft = isset($rec['fuel_type']) ? strtolower(trim($rec['fuel_type'])) : '';
            if ($ft === 'diesel') $diesel_count++;
            if ($ft === 'petrol' || $ft === 'gasoline') $petrol_count++;
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
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* .tab-content {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        } */

        /* .top-bar {
            background: #007aff;
            color: white;
            padding: 8px;
            font-size: 14px;
            margin: -20px -20px 20px -20px;
            border-radius: 5px 5px 0 0;
        } */

        /* .top-bar a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        } */

        .pdf-viewer-container {
            position: relative;
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
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
        
        /* Vehicle Emission Test specific styles */
        .fuel-type-content {
            display: none;
        }
        .fuel-type-content.active {
            display: block;
        }
        .fuel-type-option.active {
            background-color: #007bff;
            color: white;
        }
        .vehicle-test-table th {
            background-color: #839abdff;
            color: white;
        }
        .test-values {
            font-size: 0.85rem;
        }

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
        .colour-defult {
            font-size: small;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../template/header.php'; ?>

    <!-- Main Content -->
    <main class="container-fluid">

        <!-- <div class="main-container"> -->
        <!-- Navigation Tabs -->

        <div class="row">
            <div class="col-lg-1 col-xl-1 mb-4">
                <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                    <!-- QA Audits Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle active" role="button">QA Audits</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item active" data-bs-target="#audits_plan" role="tab">Audits Plan</a>
                            <a class="qa-dropdown-item" data-bs-target="#audit_check_list" role="tab">Audit Check Lists</a>
                            <a class="qa-dropdown-item" data-bs-target="#qa_report" role="tab">QA Reports</a>
                        </div>
                    </div>
                    <!-- Aircraft Competency Dropdown - Using ac_categories only -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Aircraft Competency</a>
                        <div class="qa-dropdown-menu">
                            <?php if (!empty($ac_cat_map)): ?>
                                <?php ksort($ac_cat_map); ?>
                                <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                                    <?php if ($category_id == 4) continue; ?>
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
                        <a class="nav-link qa-dropdown-toggle" role="button">Mod / R&D</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#modification" role="tab">Modifications</a>
                            <a class="qa-dropdown-item" data-bs-target="#rnd" role="tab">R&D</a>
                        </div>
                    </div>

                    <!-- Vehicle Emission Test Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">VET</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item" data-bs-target="#vehicle_annual_plans" role="tab">Annual Plans</a>
                            <a class="qa-dropdown-item" data-bs-target="#vehicle_test_reports_diesel" role="tab">Diesel</a>
                            <a class="qa-dropdown-item" data-bs-target="#vehicle_test_reports_petrol" role="tab">Petrol</a>
                        </div>
                    </div>
                </div>
                <!-- </div> -->
            </div>

            <!-- Tab Content -->
            <div class="col-lg-11 col-xl-11">
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- Welcome Screen (shown by default) -->
                    <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                        <div class="welcome-message">
                            <!-- <img src="../assets/img/qai-welcome.jpg" alt="Quality Assurance Inspectorate" class="welcome-image"> -->
                        </div>
                    </div>

                    <!-- QA Audits Content Panes -->
                    <div class="tab-pane fade" id="audits_plan" role="tabpanel">
                        <?php if (!empty($audit_plan_web)): ?>
                            <div class="top-bar">
                                <a href="<?= $audit_plan_web ?>" target="_blank" class="btn btn-sm btn-dark">Audit Plan</a>
                            </div>
                            <div class="pdf-viewer-container">
                                <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($audit_plan_web) ?>"
                                    width="100%" height="100%" style="border:none;"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p>No audit plan document is available at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="audit_check_list" role="tabpanel">
                        <h4 class="colour-defult">Audit Check Lists</h4>
                        <!-- <p>Standard operating procedures and checklists for quality audits.</p> -->
                        <div class="">
                            <div class="card">
                                <!-- <div class="card-header">Audit Check Lists</div> -->
                                <div class="card-body">
                                    <?php if (!empty($qa_check_lists_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($qa_check_lists_error) ?>
                                        </div>
                                    <?php elseif (!empty($qa_check_lists)): ?>
                                        <div class="table-responsive">
                                            <table class="table  table-hover document-table" id="qaCheckListTable">
                                                <thead style="font-size:x-small;">
                                                    <tr>
                                                        <th>Description</th>
                                                        <th>Checklist Number</th>
                                                        <th>View</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size:x-small;">
                                                    <?php foreach ($qa_check_lists as $qa_check_list): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($qa_check_list['description'] ?? 'No description') ?></td>
                                                            <td><strong><?= htmlspecialchars($qa_check_list['title']) ?></strong></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>"
                                                                        class="btn btn-primary btn-view-details btn-sm view-pdf-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#pdfModal"
                                                                        data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $qa_check_list['file_path']) ?>">
                                                                        View
                                                                    </a>
                                                                    <!-- <button class="btn btn-view-details btn-sm view-details-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#detailsModal"
                                                                        data-record-type="qa_check_list"
                                                                        data-record-id="<?= $qa_check_list['id'] ?>"
                                                                        data-record-title="<?= htmlspecialchars($qa_check_list['title']) ?>"
                                                                        data-record-description="<?= htmlspecialchars($qa_check_list['description'] ?? '') ?>"
                                                                        data-record-file-path="<?= htmlspecialchars($qa_check_list['file_path'] ?? '') ?>"
                                                                        data-record-uploaded-at="<?= htmlspecialchars($qa_check_list['uploaded_at'] ?? '') ?>">
                                                                        View Details
                                                                    </button> -->
                                                                </div>
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
                        <h4 class="colour-defult">QA Reports</h4>
                        <!-- <p>Quality assurance reports and analytics.</p> -->
                        <div class="">
                            <div class="card">
                                <!-- <div class="card-header">Recent Reports</div> -->
                                <div class="card-body">
                                    <?php if (!empty($qa_reports_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($qa_reports_error) ?>
                                        </div>
                                    <?php elseif (!empty($qa_reports)): ?>
                                        <div class="table-responsive">
                                            <table class="table dataTable table-hover document-table" id="qaReportsTable">
                                                <thead style="font-size:x-small;">
                                                    <tr>
                                                        <th>Location</th>
                                                        <th>Description</th>
                                                        <th>Date Carried out</th>
                                                        <th>View</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size:x-small;">
                                                    <?php foreach ($qa_reports as $report): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($report['title']) ?></strong></td>
                                                            <td><?= htmlspecialchars($report['description'] ?? 'No description') ?></td>
                                                            <td><?= date('M d, Y', strtotime($report['uploaded_at'])) ?></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>"
                                                                        class="btn btn-primary btn-view-details btn-sm view-pdf-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#pdfModal"
                                                                        data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $report['file_path']) ?>">
                                                                        View
                                                                    </a>
                                                                    <!-- <button class="btn btn-view-details btn-sm view-details-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#detailsModal"
                                                                        data-record-type="qa_report"
                                                                        data-record-id="<?= $report['id'] ?>"
                                                                        data-record-title="<?= htmlspecialchars($report['title']) ?>"
                                                                        data-record-description="<?= htmlspecialchars($report['description'] ?? '') ?>"
                                                                        data-record-file-path="<?= htmlspecialchars($report['file_path'] ?? '') ?>"
                                                                        data-record-uploaded-at="<?= htmlspecialchars($report['uploaded_at'] ?? '') ?>">
                                                                        View Details
                                                                    </button> -->
                                                                </div>
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
                        <?php foreach ($ac_cat_map as $category_id => $category_name):
                            $name_map = [
                                "AE" => "Aeronautical Engineering",
                                "GE" => "General Engineering",
                                "EE" => "Electronics Engineering"
                            ];
                            $display_name = $name_map[$category_name] ?? $category_name;
                        ?>
                            <div class="tab-pane fade" id="ac_cmpt_<?= $category_id ?>" role="tabpanel">
                                <h4 class="colour-defult">Aircraft Competency - <?= htmlspecialchars($display_name) ?></h4>

                                <div class="">
                                    <?php if (!empty($ac_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($ac_error) ?>
                                        </div>
                                    <?php elseif (isset($aircraft_competency_data[$category_id]) && !empty($aircraft_competency_data[$category_id]['records'])): ?>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="table-responsive-comp">
                                                    <table class="table table-hover mb-0 competencyTable" id="competencyTable_<?= $category_id ?>">
                                                        <thead style="font-size:x-small;">
                                                            <tr>
                                                                <th>SVC No</th>
                                                                <th>Rank</th>
                                                                <th>Name</th>
                                                                <th>Trade</th>
                                                                <th>Formation</th>
                                                                <th>Posted Date</th>
                                                                <th>Type</th>
                                                                <th>Competancy</th>
                                                                <!-- <th>Competecy Issue Ref</th> -->
                                                                <th>Issue Date</th>
                                                                <th>Renew Date</th>
                                                                <th>Certificate No</th>
                                                                <th>Issue Date</th>
                                                                <!-- <th>Formation</th> -->
                                                                <!-- <th>Posted In Date</th> -->
                                                                <!-- <th>Competecy Issue Ref</th> -->
                                                                <th>View</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody style="font-size:x-small;">
                                                            <?php foreach ($aircraft_competency_data[$category_id]['records'] as $index => $record): ?>
                                                                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-light-blue' ?>">
                                                                    <td><?= htmlspecialchars($record['svc_no'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($ranks_map[$record['rank']] ?? 'Unknown Rank') ?></td>
                                                                    <td><?= htmlspecialchars($record['name'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['trade'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($formations_map[$record['formation_id']] ?? $record['formation'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['posted_in_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($types_map[$record['type_id']] ?? $record['aircraft_type'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['competency_level'] ?? '') ?></td>
                                                                    <!-- <td><?= htmlspecialchars($record['com_issue_ref'] ?? '') ?></td> -->
                                                                    <td><?= htmlspecialchars($record['com_issue_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['renew_date'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['certificate_no'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($record['cer_issued_date'] ?? '') ?></td>
                                                                    <!-- <td><?= htmlspecialchars($record['posted_in_date'] ?? '') ?></td> -->
                                                                    <!-- <td><?= htmlspecialchars($record['competency_issue_ref'] ?? '') ?></td> -->
                                                                    <td>
                                                                        <button class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#detailsModal"
                                                                            data-record-type="aircraft_competency"
                                                                            data-record-id="<?= $record['record_id'] ?? '' ?>"
                                                                            data-record-svc-no="<?= htmlspecialchars($record['svc_no'] ?? '') ?>"
                                                                            data-record-rank="<?= htmlspecialchars($ranks_map[$record['rank']] ?? 'Unknown Rank') ?>"
                                                                            data-record-name="<?= htmlspecialchars($record['name'] ?? '') ?>"
                                                                            data-record-trade="<?= htmlspecialchars($record['trade'] ?? '') ?>"
                                                                            data-record-formation="<?= htmlspecialchars($formations_map[$record['formation_id']] ?? $record['formation'] ?? '') ?>"
                                                                            data-record-posted-in-date="<?= htmlspecialchars($record['posted_in_date'] ?? '') ?>"
                                                                            data-record-posted-out-date="<?= htmlspecialchars($record['posted_out_date'] ?? '') ?>"
                                                                            data-record-aircraft-type="<?= htmlspecialchars($types_map[$record['type_id']] ?? $record['aircraft_type'] ?? '') ?>"
                                                                            data-record-competency-level="<?= htmlspecialchars($record['competency_level'] ?? '') ?>"
                                                                            data-record-training-start-date="<?= htmlspecialchars($record['training_start_date'] ?? '') ?>"
                                                                            data-record-training-end-date="<?= htmlspecialchars($record['training_end_date'] ?? '') ?>"
                                                                            data-record-formation-ref="<?= htmlspecialchars($record['formation_ref'] ?? '') ?>"
                                                                            data-record-for-ref-date="<?= htmlspecialchars($record['for_ref_date'] ?? '') ?>"
                                                                            data-record-qai-ref="<?= htmlspecialchars($record['qai_ref'] ?? '') ?>"
                                                                            data-record-qai-ref-date="<?= htmlspecialchars($record['qai_ref_date'] ?? '') ?>"
                                                                            data-record-dt-ref="<?= htmlspecialchars($record['dt_ref'] ?? '') ?>"
                                                                            data-record-dt-ref-date="<?= htmlspecialchars($record['dt_ref_date'] ?? '') ?>"
                                                                            data-record-qao-ref="<?= htmlspecialchars($record['qao_ref'] ?? '') ?>"
                                                                            data-record-qao-ref-date="<?= htmlspecialchars($record['qao_ref_date'] ?? '') ?>"
                                                                            data-record-theory-marks="<?= htmlspecialchars($record['theory_marks'] ?? '') ?>"
                                                                            data-record-practical-marks="<?= htmlspecialchars($record['practical_marks'] ?? '') ?>"
                                                                            data-record-competency-issue-ref="<?= htmlspecialchars($record['competency_issue_ref'] ?? '') ?>"
                                                                            data-record-com-issue-date="<?= htmlspecialchars($record['com_issue_date'] ?? '') ?>"
                                                                            data-record-competency-renew-ref="<?= htmlspecialchars($record['competency_renew_ref'] ?? '') ?>"
                                                                            data-record-renew-date="<?= htmlspecialchars($record['renew_date'] ?? '') ?>"
                                                                            data-record-certificate-no="<?= htmlspecialchars($record['certificate_no'] ?? '') ?>"
                                                                            data-record-cer-issued-date="<?= htmlspecialchars($record['cer_issued_date'] ?? '') ?>"
                                                                            data-record-retired-date="<?= htmlspecialchars($record['retired_date'] ?? '') ?>"
                                                                            data-record-remarks="<?= htmlspecialchars($record['remarks'] ?? '') ?>">
                                                                            View
                                                                        </button>
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
                                            No aircraft competency records found for <?= htmlspecialchars($category_name) ?>.
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
                        <h4 class="colour-defult">Latitude Records</h4>
                        <div class="mt-4">
                            <?php if (!empty($le_error)): ?>
                                <div class="alert alert-danger">
                                    <strong>Database Error:</strong> <?= htmlspecialchars($le_error) ?>
                                </div>
                            <?php elseif (!empty($latitude_data)): ?>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table  table-hover mb-0" id="latitudeTable">
                                                <thead class="table-light" style="font-size:x-small;">
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Formation</th>
                                                        <th>Aircraft Type</th>
                                                        <th>Description</th>
                                                        <th>Present Latitude</th>
                                                        <th>Latitude Expire</th>
                                                        <!-- <th>Tail No</th> -->
                                                        <!-- <th>Part No</th> -->
                                                        <!-- <th>Serial No</th> -->
                                                        <th>View</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size:x-small;">
                                                    <?php foreach ($latitude_data as $record): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-<?= $record['active'] == 'YES' ? 'success' : 'danger' ?>">
                                                                    <?= htmlspecialchars($record['type']) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= htmlspecialchars($record['formation_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['description'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['present_latitude'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['latitude_expiry'] ?? 'N/A') ?></td>
                                                            <!-- <td><?= htmlspecialchars($record['tail_no'] ?? 'N/A') ?></td> -->
                                                            <!-- <td><?= htmlspecialchars($record['part_no'] ?? 'N/A') ?></td> -->
                                                            <!-- <td><?= htmlspecialchars($record['serial_no'] ?? 'N/A') ?></td> -->
                                                            <td>
                                                                <button class="btn btn-view-details btn-sm view-details-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#detailsModal"
                                                                    data-record-type="latitude"
                                                                    data-record-id="<?= $record['id'] ?? '' ?>"
                                                                    data-record-active="<?= htmlspecialchars($record['active'] ?? '') ?>"
                                                                    data-record-type-value="<?= htmlspecialchars($record['type'] ?? '') ?>"
                                                                    data-record-formation="<?= htmlspecialchars($record['formation_name'] ?? '') ?>"
                                                                    data-record-aircraft-type="<?= htmlspecialchars($record['type_name'] ?? '') ?>"
                                                                    data-record-tail-no="<?= htmlspecialchars($record['tail_no'] ?? '') ?>"
                                                                    data-record-part-no="<?= htmlspecialchars($record['part_no'] ?? '') ?>"
                                                                    data-record-description="<?= htmlspecialchars($record['description'] ?? '') ?>"
                                                                    data-record-recommendation="<?= htmlspecialchars($record['recommend'] ?? '') ?>"
                                                                    data-record-serial-no="<?= htmlspecialchars($record['serial_no'] ?? '') ?>"
                                                                    data-record-reason="<?= htmlspecialchars($record['reason'] ?? '') ?>"
                                                                    data-record-hrs="<?= htmlspecialchars($record['hrs'] ?? '') ?>"
                                                                    data-record-ldgs="<?= htmlspecialchars($record['ldgs'] ?? '') ?>"
                                                                    data-record-date="<?= htmlspecialchars($record['date'] ?? '') ?>"
                                                                    data-record-present-latitude="<?= htmlspecialchars($record['present_latitude'] ?? '') ?>"
                                                                    data-record-dgae-auth-ref="<?= htmlspecialchars($record['dgae_auth_ref'] ?? '') ?>"
                                                                    data-record-auth-date="<?= htmlspecialchars($record['auth_date'] ?? '') ?>"
                                                                    data-record-latitude-expiry="<?= htmlspecialchars($record['latitude_expiry'] ?? '') ?>"
                                                                    data-record-total-prev-latitude="<?= htmlspecialchars($record['total_prev_latitude'] ?? '') ?>"
                                                                    data-record-demand-ref="<?= htmlspecialchars($record['demand_ref'] ?? '') ?>"
                                                                    data-record-status="<?= htmlspecialchars($record['status'] ?? '') ?>">
                                                                    View
                                                                </button>
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
                                    No latitude records found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Modification / R&D Tab Panes -->
                    <!-- Modification Tab -->
                    <div class="tab-pane fade" id="modification" role="tabpanel">
                        <h4 class="colour-defult">Modification Records</h4>

                        <div class="mt-4">
                            <?php if (!empty($modification_error)): ?>
                                <div class="alert alert-danger">
                                    <strong>Database Error:</strong> <?= htmlspecialchars($modification_error) ?>
                                </div>
                            <?php elseif (!empty($modification_data)): ?>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table  table-hover mb-0" id="modificationTable">
                                                <thead class="table-light" style="font-size:x-small;">
                                                    <tr>
                                                        <th>Mod No</th>
                                                        <th>Directorate</th>
                                                        <th>Formation</th>
                                                        <th>Aircraft Type</th>
                                                        <th>Description</th>
                                                        <th>Recommended Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size:x-small;">
                                                    <?php foreach ($modification_data as $record): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($record['mod_no'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['directorate'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['formation_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['description'] ?? 'N/A') ?></td>
                                                            <td>
                                                                <?= $record['recommended_date'] ?
                                                                    date('M d, Y', strtotime($record['recommended_date'])) :
                                                                    'N/A'
                                                                ?>
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
                                    No modification records found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- R&D Tab -->
                    <div class="tab-pane fade" id="rnd" role="tabpanel">
                        <h4 class="colour-defult">Research & Development Records</h4>
                        <div class="mt-4">
                            <?php if (!empty($rnd_error)): ?>
                                <div class="alert alert-danger">
                                    <strong>Database Error:</strong> <?= htmlspecialchars($rnd_error) ?>
                                </div>
                            <?php elseif (!empty($rnd_data)): ?>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table  table-hover mb-0" id="rndTable">
                                                <thead class="table-light" style="font-size:x-small;">
                                                    <tr>
                                                        <th>R&D No</th>
                                                        <th>Directorate</th>
                                                        <th>Formation</th>
                                                        <th>Aircraft Type</th>
                                                        <th>Description</th>
                                                        <th>Issue Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size:x-small;">
                                                    <?php foreach ($rnd_data as $record): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($record['rnd_no'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['directorate'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['formation_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['type_name'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($record['description'] ?? 'N/A') ?></td>
                                                            <td>
                                                                <?= $record['issue_date'] ?
                                                                    date('M d, Y', strtotime($record['issue_date'])) :
                                                                    'N/A'
                                                                ?>
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
                                    No R&D records found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Vehicle Emission Test Tab Panes -->
                    <div class="tab-pane fade" id="vehicle_annual_plans" role="tabpanel">
                        <h3 class="colour-defult">Vehicle Emission Test - Annual Plan</h3>
                        <div class="mt-1">
                            <?php if (!empty($vet_annual_web)): ?>
                                <div class="top-bar">
                                    <a href="<?= $vet_annual_web ?>" target="_blank" class="btn btn-sm btn-dark">VET Annual Plan</a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($vet_annual_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No VET plan document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Vehicle Emission Test - Diesel Reports Tab -->
                    <div class="tab-pane fade" id="vehicle_test_reports_diesel" role="tabpanel">
                        <h4 class="colour-defult">Vehicle Emission Test - Diesel Vehicles</h4>

                        <div class="table-responsive">
                            <table id="dieselTable" class="table table-striped table-hover vehicle-test-table">
                                <thead>
                                    <tr>
                                        <th>S/No</th>
                                        <th>Camp</th>
                                        <th>Vehicle No</th>
                                        <th>Vehicle Type</th>
                                        <th>Model</th>
                                        <th>Date</th>
                                        <!-- <th>Test Values</th> -->
                                        <th>Status</th>
                                        <th>Next Due Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $diesel_records = array_filter($records, function ($r) {
                                        return isset($r['fuel_type']) && strtolower(trim($r['fuel_type'])) === 'diesel';
                                    });
                                    ?>
                                    <?php if (empty($diesel_records)): ?>
                                        <tr>
                                            <td>No data</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($diesel_records as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['serial_no'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($r['camp_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['vehicle_no'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['vehicle_type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['model'] ?? 'N/A') ?></td>
                                                <td><?= $r['test_date'] ? htmlspecialchars($r['test_date']) : 'N/A' ?></td>
                                                <!-- <td class="test-values">
                                                    <?php if (!empty($r['first_test'])): ?>
                                                        <small>1st: <?= $r['first_test'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['second_test'])): ?>
                                                        <small>2nd: <?= $r['second_test'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['third_test'])): ?>
                                                        <small>3rd: <?= $r['third_test'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['average'])): ?>
                                                        <small><strong>Avg: <?= $r['average'] ?></strong></small>
                                                    <?php endif; ?>
                                                </td> -->
                                                <td>
                                                    <span class="badge badge-<?=
                                                                                $r['status'] == 'Pass' ? 'success' : ($r['status'] == 'Fail' ? 'danger' : ($r['status'] == 'Not Suitable' ? 'warning' : 'secondary'))
                                                                                ?>">
                                                        <?= htmlspecialchars($r['status'] ?? 'N/A') ?>
                                                    </span>
                                                </td>
                                                <td><?= $r['next_due_date'] ? htmlspecialchars($r['next_due_date']) : 'N/A' ?></td>
                                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Vehicle Emission Test - Petrol Reports Tab -->
                    <div class="tab-pane fade" id="vehicle_test_reports_petrol" role="tabpanel">
                        <h4 class="colour-defult">Vehicle Emission Test - Petrol Vehicles</h4>

                        <div class="table-responsive">
                            <table id="petrolTable" class="table table-striped table-hover vehicle-test-table">
                                <thead>
                                    <tr>
                                        <th>S/No</th>
                                        <th>Camp</th>
                                        <th>Vehicle No</th>
                                        <th>Vehicle Type</th>
                                        <th>Model</th>
                                        <th>Date</th>
                                        <!-- <th>Test Values</th> -->
                                        <th>Status</th>
                                        <th>Next Due Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $petrol_records = array_filter($records, function ($r) {
                                        $fuel_type = isset($r['fuel_type']) ? strtolower(trim($r['fuel_type'])) : '';
                                        return $fuel_type === 'petrol' || $fuel_type === 'gasoline';
                                    });
                                    ?>
                                    <?php if (empty($petrol_records)): ?>
                                        <tr>
                                            <td>No data</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($petrol_records as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['serial_no'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($r['camp_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['vehicle_no'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['vehicle_type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($r['model'] ?? 'N/A') ?></td>
                                                <td><?= $r['test_date'] ? htmlspecialchars($r['test_date']) : 'N/A' ?></td>
                                                <!-- <td class="test-values">
                                                    <?php if (!empty($r['rpm_2500_hc'])): ?>
                                                        <small>2500 RPM HC: <?= $r['rpm_2500_hc'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['rpm_2500_co'])): ?>
                                                        <small>2500 RPM CO: <?= $r['rpm_2500_co'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['idle_hc'])): ?>
                                                        <small>Idle HC: <?= $r['idle_hc'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($r['idle_co'])): ?>
                                                        <small>Idle CO: <?= $r['idle_co'] ?></small>
                                                    <?php endif; ?>
                                                </td> -->
                                                <td>
                                                    <span class="badge badge-<?=
                                                                                $r['status'] == 'Pass' ? 'success' : ($r['status'] == 'Fail' ? 'danger' : ($r['status'] == 'Not Suitable' ? 'warning' : 'secondary'))
                                                                                ?>">
                                                        <?= htmlspecialchars($r['status'] ?? 'N/A') ?>
                                                    </span>
                                                </td>
                                                <td><?= $r['next_due_date'] ? htmlspecialchars($r['next_due_date']) : 'N/A' ?></td>
                                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- </div> -->
            </div>
            <!-- </div> -->
        </div>
    </main>

    <!-- PDF Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="height: 80vh;">
                    <iframe id="pdfFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalTitle">Record Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <!-- Details will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../template/foot.php'; ?>
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../assets/datatable/datatable.min.js"></script>
    <link rel="stylesheet" href="../assets/datatable/datatable.min.css">
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="../assets/js/swiper-bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log("Document ready - starting initialization");

            const dataTableConfig = {
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "Filter:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "Previous",
                        next: "Next",
                    },
                },
                autoWidth: false,
                responsive: true,
                destroy: true,
            };

            // Initialize always visible tables
            const alwaysVisibleTables = [
                "qaReportsTable",
                "qaCheckListTable",
                "latitudeTable",
                "modificationTable",
                "rndTable",
            ];

            alwaysVisibleTables.forEach((tableId) => {
                if ($("#" + tableId).length && !$.fn.DataTable.isDataTable("#" + tableId)) {
                    console.log("Initializing table:", tableId);
                    $("#" + tableId).DataTable(dataTableConfig);
                }
            });

            // ✅ Initialize all competency tables by class
            $(".competencyTable").each(function() {
                const tableId = $(this).attr("id");
                if (!$.fn.DataTable.isDataTable("#" + tableId)) {
                    console.log("Initializing competency table:", tableId);
                    $("#" + tableId).DataTable(dataTableConfig);
                }
            });
            $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function(e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            });

            $('a[data-bs-target="#vehicle_test_reports_diesel"]').on('shown.bs.tab', function() {
                setTimeout(() => {
                    initializeVehicleTable('diesel');
                }, 100);
            });

            $('a[data-bs-target="#vehicle_test_reports_petrol"]').on('shown.bs.tab', function() {
                setTimeout(() => {
                    initializeVehicleTable('petrol');
                }, 100);
            });
            // Function to initialize vehicle tables
            function initializeVehicleTable(fuelType) {
                const tableId = fuelType + 'Table';
                const tableElement = $('#' + tableId);

                // Check if table exists
                if (tableElement.length) {
                    console.log("Initializing table:", tableId);

                    // Destroy existing DataTable if it exists (cleanup)
                    if ($.fn.DataTable.isDataTable('#' + tableId)) {
                        $('#' + tableId).DataTable().destroy();
                        console.log("Destroyed existing DataTable for:", tableId);
                    }

                    // Check if table has actual data (not just the "no data" row)
                    const hasRealData = tableElement.find('tbody tr').length > 1 ||
                        (tableElement.find('tbody tr').length === 1 &&
                            tableElement.find('tbody tr td:first-child').text() !== 'No data');

                    if (hasRealData) {
                        // Initialize new DataTable
                        $('#' + tableId).DataTable({
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            language: {
                                search: "Filter:",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                paginate: {
                                    previous: "Previous",
                                    next: "Next",
                                },
                            },
                            autoWidth: false,
                            responsive: true,
                            destroy: true // Allow reinitialization
                        });
                        console.log("Successfully initialized DataTable for:", tableId);
                    } else {
                        console.log("No real data found for table:", tableId, "- skipping DataTable initialization");
                    }
                } else {
                    console.log("Table not found:", tableId);
                }
            }

            // Ensure diesel is shown by default and initialize its table
            setTimeout(() => {
                // $('.fuel-type-option[data-target="diesel"]').click();

                // Initialize diesel table if it exists
                if ($('#dieselTable').length && !$.fn.DataTable.isDataTable('#dieselTable')) {
                    console.log("Initializing diesel table on load");
                    $('#dieselTable').DataTable(dataTableConfig);
                }
                // Initialize diesel table if it exists
                if ($('#petrolTable').length && !$.fn.DataTable.isDataTable('#petrolTable')) {
                    console.log("Initializing petrol table on load");
                    $('#petrolTable').DataTable(dataTableConfig);
                }
            }, 500);

            // Modal functionality
            const pdfModal = document.getElementById("pdfModal");
            const pdfFrame = document.getElementById("pdfFrame");

            if (pdfModal) {
                pdfModal.addEventListener("show.bs.modal", function(event) {
                    const button = event.relatedTarget;
                    const pdfUrl = button.getAttribute("data-pdf-url");
                    pdfFrame.src = pdfUrl;
                });

                pdfModal.addEventListener("hidden.bs.modal", function() {
                    pdfFrame.src = "";
                });
            }

            // Details Modal functionality
            const detailsModal = document.getElementById("detailsModal");
            const detailsModalTitle = document.getElementById("detailsModalTitle");
            const detailsModalBody = document.getElementById("detailsModalBody");

            if (detailsModal) {
                detailsModal.addEventListener("show.bs.modal", function(event) {
                    const button = event.relatedTarget;
                    const recordType = button.getAttribute("data-record-type");

                    let title = "Record Details";
                    switch (recordType) {
                        case "qa_report":
                            title = "QA Report Details";
                            break;
                        case "qa_check_list":
                            title = "Audit Checklist Details";
                            break;
                        case "aircraft_competency":
                            title = "Aircraft Competency Details";
                            break;
                        case "latitude":
                            title = "Latitude Record Details";
                            break;
                        case "vehicle_emission":
                            title = "Vehicle Emission Test Details";
                            break;
                    }
                    detailsModalTitle.textContent = title;

                    let content = "";
                    switch (recordType) {
                        case "qa_report":
                        case "qa_check_list":
                            content = generateDocumentDetails(button);
                            break;
                        case "aircraft_competency":
                            content = generateAircraftCompetencyDetails(button);
                            break;
                        case "latitude":
                            content = generateLatitudeDetails(button);
                            break;
                        case "vehicle_emission":
                            content = generateVehicleEmissionDetails(button);
                            break;
                        default:
                            content = "<p>No details available.</p>";
                    }
                    detailsModalBody.innerHTML = content;
                });

                detailsModal.addEventListener("hidden.bs.modal", function() {
                    detailsModalBody.innerHTML = "";
                });
            }

            // Helper functions
            function formatValue(value) {
                if (
                    !value ||
                    value === "null" ||
                    value === "undefined" ||
                    value === "0000-00-00"
                ) {
                    return '<span class="empty-value">Not provided</span>';
                }
                return value;
            }

            function formatDate(dateString) {
                if (!dateString || dateString === "0000-00-00") {
                    return '<span class="empty-value">Not provided</span>';
                }
                try {
                    return new Date(dateString).toLocaleDateString();
                } catch (e) {
                    return dateString;
                }
            }

            function generateDocumentDetails(button) {
                const title = button.getAttribute("data-record-title");
                const description = button.getAttribute("data-record-description");
                const filePath = button.getAttribute("data-record-file-path");
                const uploadedAt = button.getAttribute("data-record-uploaded-at");

                return `
                <table class="details-modal-table">
                    <tr><th>Document Title:</th><td>${formatValue(
                      title
                    )}</td></tr>
                    <tr><th>Description:</th><td>${formatValue(
                      description
                    )}</td></tr>
                    <tr><th>File Path:</th><td>${formatValue(
                      filePath
                    )}</td></tr>
                    <tr><th>Uploaded Date:</th><td>${formatDate(
                      uploadedAt
                    )}</td></tr>
                </table>
            `;
            }

            function generateAircraftCompetencyDetails(button) {
                const svcNo = button.getAttribute("data-record-svc-no");
                const rank = button.getAttribute("data-record-rank");
                const name = button.getAttribute("data-record-name");
                const trade = button.getAttribute("data-record-trade");
                const formation = button.getAttribute("data-record-formation");
                const postedInDate = button.getAttribute("data-record-posted-in-date");
                const postedOutDate = button.getAttribute("data-record-posted-out-date");
                const aircraftType = button.getAttribute("data-record-aircraft-type");
                const competencyLevel = button.getAttribute("data-record-competency-level");
                const trainingStartDate = button.getAttribute(
                    "data-record-training-start-date"
                );
                const trainingEndDate = button.getAttribute(
                    "data-record-training-end-date"
                );
                const formationRef = button.getAttribute("data-record-formation-ref");
                const forRefDate = button.getAttribute("data-record-for-ref-date");
                const qaiRef = button.getAttribute("data-record-qai-ref");
                const qaiRefDate = button.getAttribute("data-record-qai-ref-date");
                const dtRef = button.getAttribute("data-record-dt-ref");
                const dtRefDate = button.getAttribute("data-record-dt-ref-date");
                const qaoRef = button.getAttribute("data-record-qao-ref");
                const qaoRefDate = button.getAttribute("data-record-qao-ref-date");
                const theoryMarks = button.getAttribute("data-record-theory-marks");
                const practicalMarks = button.getAttribute("data-record-practical-marks");
                const competencyIssueRef = button.getAttribute(
                    "data-record-competency-issue-ref"
                );
                const comIssueDate = button.getAttribute("data-record-com-issue-date");
                const competencyRenewRef = button.getAttribute(
                    "data-record-competency-renew-ref"
                );
                const renewDate = button.getAttribute("data-record-renew-date");
                const certificateNo = button.getAttribute("data-record-certificate-no");
                const cerIssuedDate = button.getAttribute("data-record-cer-issued-date");
                const retiredDate = button.getAttribute("data-record-retired-date");
                const remarks = button.getAttribute("data-record-remarks");

                return `
                <div class="section-divider">Personal Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>SVC Number:</th>
                        <td>${formatValue(svcNo)}</td>
                    </tr>
                    <tr>
                        <th>Rank:</th>
                        <td>${formatValue(rank)}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>${formatValue(name)}</td>
                    </tr>
                    <tr>
                        <th>Trade:</th>
                        <td>${formatValue(trade)}</td>
                    </tr>
                    <tr>
                        <th>Formation:</th>
                        <td>${formatValue(formation)}</td>
                    </tr>
                </table>

                <div class="section-divider">Posting Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Posted In Date:</th>
                        <td>${formatDate(postedInDate)}</td>
                    </tr>
                    <tr>
                        <th>Posted Out Date:</th>
                        <td>${formatDate(postedOutDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Aircraft & Competency Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Aircraft Type:</th>
                        <td>${formatValue(aircraftType)}</td>
                    </tr>
                    <tr>
                        <th>Competency Level:</th>
                        <td>${formatValue(competencyLevel)}</td>
                    </tr>
                    <tr>
                        <th>Training Start Date:</th>
                        <td>${formatDate(trainingStartDate)}</td>
                    </tr>
                    <tr>
                        <th>Training End Date:</th>
                        <td>${formatDate(trainingEndDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Reference Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Formation Reference:</th>
                        <td>${formatValue(formationRef)}</td>
                    </tr>
                    <tr>
                        <th>Formation Ref Date:</th>
                        <td>${formatDate(forRefDate)}</td>
                    </tr>
                    <tr>
                        <th>QAI Reference:</th>
                        <td>${formatValue(qaiRef)}</td>
                    </tr>
                    <tr>
                        <th>QAI Ref Date:</th>
                        <td>${formatDate(qaiRefDate)}</td>
                    </tr>
                    <tr>
                        <th>DT Reference:</th>
                        <td>${formatValue(dtRef)}</td>
                    </tr>
                    <tr>
                        <th>DT Ref Date:</th>
                        <td>${formatDate(dtRefDate)}</td>
                    </tr>
                    <tr>
                        <th>QAO Reference:</th>
                        <td>${formatValue(qaoRef)}</td>
                    </tr>
                    <tr>
                        <th>QAO Ref Date:</th>
                        <td>${formatDate(qaoRefDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Assessment Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Theory Marks:</th>
                        <td>${formatValue(theoryMarks)}</td>
                    </tr>
                    <tr>
                        <th>Practical Marks:</th>
                        <td>${formatValue(practicalMarks)}</td>
                    </tr>
                    <tr>
                        <th>Competency Issue Reference:</th>
                        <td>${formatValue(competencyIssueRef)}</td>
                    </tr>
                    <tr>
                        <th>Competency Issue Date:</th>
                        <td>${formatDate(comIssueDate)}</td>
                    </tr>
                    <tr>
                        <th>Competency Renew Reference:</th>
                        <td>${formatValue(competencyRenewRef)}</td>
                    </tr>
                    <tr>
                        <th>Renew Date:</th>
                        <td>${formatDate(renewDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Certificate Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Certificate Number:</th>
                        <td>${formatValue(certificateNo)}</td>
                    </tr>
                    <tr>
                        <th>Certificate Issued Date:</th>
                        <td>${formatDate(cerIssuedDate)}</td>
                    </tr>
                    <tr>
                        <th>Retired Date:</th>
                        <td>${formatDate(retiredDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Additional Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Remarks:</th>
                        <td>${formatValue(remarks)}</td>
                    </tr>
                </table>
            `;
            }

            function generateLatitudeDetails(button) {
                const active = button.getAttribute("data-record-active");
                const typeValue = button.getAttribute("data-record-type-value");
                const formation = button.getAttribute("data-record-formation");
                const aircraftType = button.getAttribute("data-record-aircraft-type");
                const tailNo = button.getAttribute("data-record-tail-no");
                const partNo = button.getAttribute("data-record-part-no");
                const description = button.getAttribute("data-record-description");
                const serialNo = button.getAttribute("data-record-serial-no");
                const reason = button.getAttribute("data-record-reason");
                const hrs = button.getAttribute("data-record-hrs");
                const ldgs = button.getAttribute("data-record-ldgs");
                const date = button.getAttribute("data-record-date");
                const presentLatitude = button.getAttribute("data-record-present-latitude");
                const dgaeAuthRef = button.getAttribute("data-record-dgae-auth-ref");
                const authDate = button.getAttribute("data-record-auth-date");
                const latitudeExpiry = button.getAttribute("data-record-latitude-expiry");
                const totalPrevLatitude = button.getAttribute(
                    "data-record-total-prev-latitude"
                );
                const demandRef = button.getAttribute("data-record-demand-ref");
                const status = button.getAttribute("data-record-status");

                const statusBadge =
                    status === "Approved" ?
                    "success" :
                    status === "Pending" ?
                    "warning" :
                    status === "Expired" ?
                    "danger" :
                    "secondary";

                const activeBadge = active === "YES" ? "success" : "danger";

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Active Status:</th>
                        <td><span class="badge badge-${activeBadge}">${active}</span></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td>${formatValue(typeValue)}</td>
                    </tr>
                    <tr>
                        <th>Formation:</th>
                        <td>${formatValue(formation)}</td>
                    </tr>
                    <tr>
                        <th>Aircraft Type:</th>
                        <td>${formatValue(aircraftType)}</td>
                    </tr>
                    <tr>
                        <th>Tail Number:</th>
                        <td>${formatValue(tailNo)}</td>
                    </tr>
                    <tr>
                        <th>Part Number:</th>
                        <td>${formatValue(partNo)}</td>
                    </tr>
                </table>

                <div class="section-divider">Technical Details</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Description:</th>
                        <td>${formatValue(description)}</td>
                    </tr>
                    <tr>
                        <th>Serial Number:</th>
                        <td>${formatValue(serialNo)}</td>
                    </tr>
                    <tr>
                        <th>Reason:</th>
                        <td>${formatValue(reason)}</td>
                    </tr>
                    <tr>
                        <th>Hours:</th>
                        <td>${formatValue(hrs)}</td>
                    </tr>
                    <tr>
                        <th>Landings:</th>
                        <td>${formatValue(ldgs)}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>${formatDate(date)}</td>
                    </tr>
                </table>

                <div class="section-divider">Latitude Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Present Latitude:</th>
                        <td><strong>${formatValue(
                          presentLatitude
                        )}</strong></td>
                    </tr>
                    <tr>
                        <th>Total Previous Latitude:</th>
                        <td>${formatValue(totalPrevLatitude)}</td>
                    </tr>
                    <tr>
                        <th>DGAE Authorization Reference:</th>
                        <td>${formatValue(dgaeAuthRef)}</td>
                    </tr>
                    <tr>
                        <th>Authorization Date:</th>
                        <td>${formatDate(authDate)}</td>
                    </tr>
                    <tr>
                        <th>Latitude Expiry Date:</th>
                        <td>${formatDate(latitudeExpiry)}</td>
                    </tr>
                </table>

                <div class="section-divider">Status Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Demand Reference:</th>
                        <td>${formatValue(demandRef)}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span class="badge badge-${statusBadge}">${formatValue(status)}</span></td>
                    </tr>
                </table>
            `;
            }

            function generateVehicleEmissionDetails(button) {
                const serialNo = button.getAttribute("data-record-serial-no");
                const camp = button.getAttribute("data-record-camp");
                const vehicleNo = button.getAttribute("data-record-vehicle-no");
                const vehicleType = button.getAttribute("data-record-vehicle-type");
                const model = button.getAttribute("data-record-model");
                const testDate = button.getAttribute("data-record-test-date");
                const firstTest = button.getAttribute("data-record-first-test");
                const secondTest = button.getAttribute("data-record-second-test");
                const thirdTest = button.getAttribute("data-record-third-test");
                const average = button.getAttribute("data-record-average");
                const rpm2500Hc = button.getAttribute("data-record-rpm-2500-hc");
                const rpm2500Co = button.getAttribute("data-record-rpm-2500-co");
                const idleHc = button.getAttribute("data-record-idle-hc");
                const idleCo = button.getAttribute("data-record-idle-co");
                const status = button.getAttribute("data-record-status");
                const nextDueDate = button.getAttribute("data-record-next-due-date");
                const remarks = button.getAttribute("data-record-remarks");

                const statusBadge =
                    status === "Pass" ?
                    "success" :
                    status === "Fail" ?
                    "danger" :
                    status === "Not Suitable" ?
                    "warning" :
                    status === "Serviceable Not Done" ?
                    "secondary" :
                    "secondary";

                // Determine fuel type based on test values
                const isDiesel = firstTest || secondTest || thirdTest || average;
                const isPetrol = rpm2500Hc || rpm2500Co || idleHc || idleCo;

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>S/No:</th>
                        <td>${formatValue(serialNo)}</td>
                    </tr>
                    <tr>
                        <th>Camp:</th>
                        <td>${formatValue(camp)}</td>
                    </tr>
                    <tr>
                        <th>Vehicle Number:</th>
                        <td><strong>${formatValue(vehicleNo)}</strong></td>
                    </tr>
                    <tr>
                        <th>Vehicle Type:</th>
                        <td>${formatValue(vehicleType)}</td>
                    </tr>
                    <tr>
                        <th>Model:</th>
                        <td>${formatValue(model)}</td>
                    </tr>
                    <tr>
                        <th>Test Date:</th>
                        <td>${formatDate(testDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Test Results</div>
                <table class="details-modal-table">
                    ${
                      isDiesel
                        ? `
                    <tr>
                        <th>1st Test Result:</th>
                        <td>${formatValue(firstTest)}</td>
                    </tr>
                    <tr>
                        <th>2nd Test Result:</th>
                        <td>${formatValue(secondTest)}</td>
                    </tr>
                    <tr>
                        <th>3rd Test Result:</th>
                        <td>${formatValue(thirdTest)}</td>
                    </tr>
                    <tr>
                        <th>Average:</th>
                        <td><strong>${formatValue(average)}</strong></td>
                    </tr>
                    `
                        : ""
                    }
                    ${
                      isPetrol
                        ? `
                    <tr>
                        <th>2500 RPM HC:</th>
                        <td>${formatValue(rpm2500Hc)}</td>
                    </tr>
                    <tr>
                        <th>2500 RPM CO:</th>
                        <td>${formatValue(rpm2500Co)}</td>
                    </tr>
                    <tr>
                        <th>Idle HC:</th>
                        <td>${formatValue(idleHc)}</td>
                    </tr>
                    <tr>
                        <th>Idle CO:</th>
                        <td>${formatValue(idleCo)}</td>
                    </tr>
                    `
                        : ""
                    }
                </table>

                <div class="section-divider">Status Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Status:</th>
                        <td><span class="badge badge-${statusBadge}">${formatValue(status)}</span></td>
                    </tr>
                    <tr>
                        <th>Next Due Date:</th>
                        <td>${formatDate(nextDueDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Additional Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Remarks:</th>
                        <td>${formatValue(remarks)}</td>
                    </tr>
                </table>
            `;
            }

            // Navigation and tab handling
            const welcomePane = document.querySelector("#welcome");
            if (welcomePane) {
                welcomePane.classList.add("show", "active");
            }

            document.querySelectorAll(".nav-link, .qa-dropdown-item").forEach((item) => {
                item.classList.remove("active");
            });

            document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                menu.classList.remove("show");
            });

            // Dropdown toggle handling
            const dropdownToggles = document.querySelectorAll(".qa-dropdown-toggle");
            dropdownToggles.forEach((toggle) => {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    if (!dropdownMenu) return;

                    const isCurrentlyOpen = dropdownMenu.classList.contains("show");

                    dropdownToggles.forEach((otherToggle) => {
                        const otherMenu = otherToggle.nextElementSibling;
                        if (otherMenu) otherMenu.classList.remove("show");
                    });

                    if (!isCurrentlyOpen) dropdownMenu.classList.add("show");
                });
            });

            // Main nav links
            const mainNavLinks = document.querySelectorAll(
                ".nav-link:not(.qa-dropdown-toggle)"
            );
            mainNavLinks.forEach((item) => {
                item.addEventListener("click", function(e) {
                    e.preventDefault();
                    document
                        .querySelectorAll(".nav-link, .qa-dropdown-item")
                        .forEach((tab) => {
                            tab.classList.remove("active");
                        });
                    document.querySelectorAll(".qa-dropdown-toggle").forEach((toggle) => {
                        toggle.classList.remove("active");
                    });
                    this.classList.add("active");

                    const targetId = this.getAttribute("data-bs-target");
                    const targetPane = document.querySelector(targetId);

                    document.querySelectorAll(".tab-pane").forEach((pane) => {
                        pane.classList.remove("show", "active");
                    });

                    if (targetPane) targetPane.classList.add("show", "active");
                    document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                        menu.classList.remove("show");
                    });
                });
            });

            // Dropdown items
            const dropdownItems = document.querySelectorAll(".qa-dropdown-item");
            dropdownItems.forEach((item) => {
                item.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    document
                        .querySelectorAll(".nav-link, .qa-dropdown-item")
                        .forEach((tab) => {
                            tab.classList.remove("active");
                        });
                    document.querySelectorAll(".qa-dropdown-toggle").forEach((toggle) => {
                        toggle.classList.remove("active");
                    });
                    this.classList.add("active");

                    if (this.classList.contains("qa-dropdown-item")) {
                        const parentDropdown = this.closest(".qa-dropdown");
                        if (parentDropdown) {
                            const dropdownToggle = parentDropdown.querySelector(
                                ".qa-dropdown-toggle"
                            );
                            if (dropdownToggle) {
                                dropdownToggle.classList.add("active");
                                const dropdownMenu = dropdownToggle.nextElementSibling;
                                if (dropdownMenu) dropdownMenu.classList.add("show");
                            }
                        }
                    }

                    const targetId = this.getAttribute("data-bs-target");
                    const targetPane = document.querySelector(targetId);

                    document.querySelectorAll(".tab-pane").forEach((pane) => {
                        pane.classList.remove("show", "active");
                    });

                    if (targetPane) targetPane.classList.add("show", "active");
                });
            });

            // Close dropdowns on outside click
            // document.addEventListener("click", function(e) {
            //     if (!e.target.closest(".qa-dropdown")) {
            //         document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
            //             menu.classList.remove("show");
            //         });
            //     }
            // });

            document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                menu.addEventListener("click", function(e) {
                    e.stopPropagation();
                });
            });

            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                        menu.classList.remove("show");
                    });
                }
            });

            console.log("Initialization complete");
        });
    </script>
</body>
</html>