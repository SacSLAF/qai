<?php
// publication.php
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
    $file = 'CPD Training.pdf';

    // Server path to PDF - corrected relative path
    $file_path = "../admin/action/uploads/publication/tech/" . $file;

    // Get absolute path for file existence check
    $absolute_path = realpath($file_path);

    if ($absolute_path && file_exists($absolute_path)) {
        $show_pdf = true;
        // Web path to PDF (for PDF.js)
        $pdf_web_path = "/qai/admin/action/uploads/publication/tech/" . $file;
    } else {
        $show_pdf = false;
        $error = "File not found. Tried path: " . htmlspecialchars($file_path);
        if ($absolute_path === false) {
            $error .= " (Path does not exist)";
        }
    }

    // Fetch data for AD Bulletins
    $ad_bulletins = [];
    $ad_bulletins_error = '';
    $formations_map = [];
    $types_map = [];

    // Fetch data for QAI Newsletters
    $qai_newsletters = [];
    $qai_newsletters_error = '';

    // Fetch Maintenance Program data
    $maintenance_schedules = [];
    $maintenance_worksheets = [];
    $maintenance_error = '';

    // Fetch Technical Library data
    $tech_library = [];
    $tech_library_error = '';

    // Check database connection
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $ad_bulletins_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $qai_newsletters_error = $ad_bulletins_error;
        $maintenance_error = $ad_bulletins_error;
        $tech_library_error = $ad_bulletins_error;
    } else {
        // Load lookup maps (formations, types)
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

        // Fetch AD Bulletins records
        $stmt_ad = $db->prepare("
            SELECT ab.*, f.formation_name, t.type_name 
            FROM ad_bulletins ab 
            LEFT JOIN formation f ON ab.formation_id = f.formation_id 
            LEFT JOIN type t ON ab.related_aircraft_id = t.type_id 
            ORDER BY ab.date_of_issue DESC
        ");

        if ($stmt_ad) {
            if ($stmt_ad->execute()) {
                $result_ad = $stmt_ad->get_result();
                $ad_bulletins = $result_ad->fetch_all(MYSQLI_ASSOC);
                $stmt_ad->close();
            } else {
                $ad_bulletins_error = "AD Bulletins query execution failed: " . $stmt_ad->error;
            }
        } else {
            $ad_bulletins_error = "Error preparing AD Bulletins query: " . $db->error;
        }

        // Fetch QAI Newsletters records
        $stmt_qai = $db->prepare("
            SELECT qn.* 
            FROM qai_newsletters qn 
            ORDER BY qn.issue_date DESC, qn.sno DESC
        ");

        if ($stmt_qai) {
            if ($stmt_qai->execute()) {
                $result_qai = $stmt_qai->get_result();
                $qai_newsletters = $result_qai->fetch_all(MYSQLI_ASSOC);
                $stmt_qai->close();
            } else {
                $qai_newsletters_error = "QAI Newsletters query execution failed: " . $stmt_qai->error;
            }
        } else {
            $qai_newsletters_error = "Error preparing QAI Newsletters query: " . $db->error;
        }

        // Fetch Maintenance Program data
        // Fetch schedules (document_type = 'schedule')
        $stmt_schedules = $db->prepare("
            SELECT md.*, f.formation_name, t.type_name, b.name as branch_name 
            FROM maintenance_documents md 
            LEFT JOIN formation f ON md.formation_id = f.formation_id 
            LEFT JOIN type t ON md.type_id = t.type_id 
            LEFT JOIN branches b ON md.branch_id = b.id 
            WHERE md.document_type = 'schedule' 
            ORDER BY md.document_number ASC
        ");

        if ($stmt_schedules) {
            if ($stmt_schedules->execute()) {
                $result_schedules = $stmt_schedules->get_result();
                $maintenance_schedules = $result_schedules->fetch_all(MYSQLI_ASSOC);
            } else {
                $maintenance_error = "Schedules query execution failed: " . $stmt_schedules->error;
            }
            $stmt_schedules->close();
        } else {
            $maintenance_error = "Error preparing Schedules query: " . $db->error;
        }

        // Fetch worksheets (document_type = 'worksheet')
        $stmt_worksheets = $db->prepare("
            SELECT md.*, f.formation_name, t.type_name, b.name as branch_name 
            FROM maintenance_documents md 
            LEFT JOIN formation f ON md.formation_id = f.formation_id 
            LEFT JOIN type t ON md.type_id = t.type_id 
            LEFT JOIN branches b ON md.branch_id = b.id 
            WHERE md.document_type = 'worksheet' 
            ORDER BY md.document_number ASC
        ");

        if ($stmt_worksheets) {
            if ($stmt_worksheets->execute()) {
                $result_worksheets = $stmt_worksheets->get_result();
                $maintenance_worksheets = $result_worksheets->fetch_all(MYSQLI_ASSOC);
            } else {
                $maintenance_error = "Worksheets query execution failed: " . $stmt_worksheets->error;
            }
            $stmt_worksheets->close();
        } else {
            $maintenance_error = "Error preparing Worksheets query: " . $db->error;
        }

        // Fetch Technical Library records
        $stmt_tech = $db->prepare("SELECT * FROM tech_library ORDER BY sno ASC");
        if ($stmt_tech) {
            if ($stmt_tech->execute()) {
                $result_tech = $stmt_tech->get_result();
                $tech_library = $result_tech->fetch_all(MYSQLI_ASSOC);
                $stmt_tech->close();
            } else {
                $tech_library_error = "Technical Library query execution failed: " . $stmt_tech->error;
            }
        } else {
            $tech_library_error = "Error preparing Technical Library query: " . $db->error;
        }
    }

    // Organize maintenance data by branch for easier filtering
    $schedules_by_branch = [];
    $worksheets_by_branch = [];

    $branch_map = [
        1 => 'Aeronautical Engineering',
        4 => 'Electronic Engineering',
        5 => 'General Engineering'
    ];

    foreach ($maintenance_schedules as $schedule) {
        $branch_id = $schedule['branch_id'];
        if (!isset($schedules_by_branch[$branch_id])) {
            $schedules_by_branch[$branch_id] = [];
        }
        $schedules_by_branch[$branch_id][] = $schedule;
    }

    foreach ($maintenance_worksheets as $worksheet) {
        $branch_id = $worksheet['branch_id'];
        if (!isset($worksheets_by_branch[$branch_id])) {
            $worksheets_by_branch[$branch_id] = [];
        }
        $worksheets_by_branch[$branch_id][] = $worksheet;
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

        /* Dropdown styles */
        .qa-dropdown {
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
            font-size: 0.875rem;
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
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../template/header.php'; ?>

    <!-- Main Content -->
    <main class="container-fluid">
        <div class="row">
            <div class="col-lg-1 col-xl-1 mb-4">
                <div class="nav-column">
                    <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                        <a class="nav-link" data-bs-target="#ac" role="tab">Online Subscription</a>
                        <a class="nav-link" data-bs-target="#ad" role="tab">ADs & Bulletins</a>
                        <a class="nav-link" data-bs-target="#qai_news" role="tab">QAI Safety Newsletters</a>
                        <!-- Maintenance Program Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle" role="button">Maintenance Program</a>
                            <div class="qa-dropdown-menu">
                                <!-- Servicing Schedule Submenu -->
                                <div class="qa-dropdown">
                                    <a class="qa-dropdown-item qa-dropdown-toggle" role="button" style="font-size:x-small;">Servicing Schedule</a>
                                    <div class="qa-dropdown-menu">
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#schedule_1" role="tab">Aeronautical Engineering</a>
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#schedule_5" role="tab">General Engineering</a>
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#schedule_4" role="tab">Electronic Engineering</a>
                                    </div>
                                </div>
                                
                                <!-- Worksheet Submenu -->
                                <div class="qa-dropdown">
                                    <a class="qa-dropdown-item qa-dropdown-toggle" role="button">Worksheet</a>
                                    <div class="qa-dropdown-menu">
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#worksheet_1" role="tab">Aeronautical Engineering</a>
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#worksheet_5" role="tab">General Engineering</a>
                                        <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#worksheet_4" role="tab">Electronic Engineering</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a class="nav-link" data-bs-target="#vehicle" role="tab">Technical Library</a>
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

                        <!-- Online Subscription Tab -->
                        <div class="tab-pane fade" id="ac" role="tabpanel">
                            <h4 class="colour-defult">Online Subscription</h4>
                            <div>
                                <?php include 'online_sub.php' ?>
                            </div>
                        </div>

                        <!-- ADs & Bulletins Tab -->
                        <div class="tab-pane fade" id="ad" role="tabpanel">
                            <h4 class="colour-defult">ADs & Bulletins</h4>
                            <div class="mt-4">
                                <?php if (!empty($ad_bulletins_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($ad_bulletins_error) ?>
                                    </div>
                                <?php elseif (!empty($ad_bulletins)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0 " id="adBulletinsTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>Reference No</th>
                                                            <th>Bulletin Description</th>
                                                            <th>Related Aircraft</th>
                                                            <th>Formation</th>
                                                            <th>Date of Issue</th>
                                                            <th>View</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($ad_bulletins as $bulletin): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($bulletin['reference_no']) ?></strong></td>
                                                                <td><?= htmlspecialchars($bulletin['bulletin_description']) ?></td>
                                                                <td><?= htmlspecialchars($bulletin['type_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($bulletin['formation_name'] ?? 'N/A') ?></td>
                                                                <td><?= $bulletin['date_of_issue'] ? date('M d, Y', strtotime($bulletin['date_of_issue'])) : 'N/A' ?></td>
                                                                <td>
                                                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#detailsModal"
                                                                        data-record-type="ad_bulletin"
                                                                        data-record-id="<?= $bulletin['id'] ?? '' ?>"
                                                                        data-record-reference-no="<?= htmlspecialchars($bulletin['reference_no'] ?? '') ?>"
                                                                        data-record-bulletin-description="<?= htmlspecialchars($bulletin['bulletin_description'] ?? '') ?>"
                                                                        data-record-related-aircraft="<?= htmlspecialchars($bulletin['type_name'] ?? '') ?>"
                                                                        data-record-formation="<?= htmlspecialchars($bulletin['formation_name'] ?? '') ?>"
                                                                        data-record-date-of-issue="<?= htmlspecialchars($bulletin['date_of_issue'] ?? '') ?>">
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
                                        No AD Bulletins found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- QAI Safety Newsletters Tab -->
                        <div class="tab-pane fade" id="qai_news" role="tabpanel">
                            <h4 class="colour-defult">QAI Safety Newsletters</h4>
                            <div class="mt-4">
                                <?php if (!empty($qai_newsletters_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($qai_newsletters_error) ?>
                                    </div>
                                <?php elseif (!empty($qai_newsletters)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table  table-hover mb-0 " id="qaiNewslettersTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>QSN No</th>
                                                            <th>Description</th>
                                                            <th>Issue Date</th>
                                                            <th>View</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($qai_newsletters as $newsletter): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($newsletter['sno']) ?></strong></td>
                                                                <td><?= htmlspecialchars($newsletter['description']) ?></td>
                                                                <td><?= $newsletter['issue_date'] ? date('M d, Y', strtotime($newsletter['issue_date'])) : 'N/A' ?></td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        <?php if (!empty($newsletter['file_path'])): ?>
                                                                            <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $newsletter['file_path']) ?>"
                                                                                class="btn btn-view-details btn-sm view-details-btn"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#pdfModal"
                                                                                data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $newsletter['file_path']) ?>">
                                                                                <i class="fas fa-file-pdf me-1"></i>PDF
                                                                            </a>
                                                                        <?php endif; ?>
                                                                        <button class="btn btn-view-details btn-sm view-details-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#detailsModal"
                                                                            data-record-type="qai_newsletter"
                                                                            data-record-id="<?= $newsletter['id'] ?? '' ?>"
                                                                            data-record-qsn-no="<?= htmlspecialchars($newsletter['sno'] ?? '') ?>"
                                                                            data-record-description="<?= htmlspecialchars($newsletter['description'] ?? '') ?>"
                                                                            data-record-issue-date="<?= htmlspecialchars($newsletter['issue_date'] ?? '') ?>">
                                                                            View
                                                                        </button>
                                                                    </div>
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
                                        No QAI Safety Newsletters found.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Servicing Schedule Tab Panes for each branch -->
                        <?php foreach ($branch_map as $branch_id => $branch_name): ?>
                        <div class="tab-pane fade" id="schedule_<?= $branch_id ?>" role="tabpanel">
                            <h4 class="colour-defult">Servicing Schedule - <?= htmlspecialchars($branch_name) ?></h4>
                            <div class="mt-4">
                                <?php if (!empty($maintenance_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($maintenance_error) ?>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    $schedules_to_display = $schedules_by_branch[$branch_id] ?? [];
                                    ?>
                                    <?php if (empty($schedules_to_display)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No servicing schedules found for <?= htmlspecialchars($branch_name) ?>.
                                        </div>
                                    <?php else: ?>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="servicingScheduleTable" style="font-size:x-small;">
                                                        <thead>
                                                            <tr>
                                                                <th>Document No</th>
                                                                <th>Description</th>
                                                                <th>Formation</th>
                                                                <th>Aircraft Type</th>
                                                                <th>Issue</th>
                                                                <th>Revision</th>
                                                                <th>Revision Date</th>
                                                                <th>File</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($schedules_to_display as $doc): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($doc['document_number']) ?></strong></td>
                                                                    <td><?= htmlspecialchars($doc['description']) ?></td>
                                                                    <td><?= htmlspecialchars($doc['formation_name'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($doc['type_name'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($doc['issue']) ?></td>
                                                                    <td><?= htmlspecialchars($doc['revision'] ?? 'N/A') ?></td>
                                                                    <td><?= ($doc['revision_date'] && $doc['revision_date'] != '0000-00-00' ? date('M d, Y', strtotime($doc['revision_date'])) : 'N/A') ?></td>
                                                                    <td>
                                                                        <?php
                                                                        if (!empty($doc['file_path'])): ?>
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
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Worksheet Tab Panes for each branch -->
                        <?php foreach ($branch_map as $branch_id => $branch_name): ?>
                        <div class="tab-pane fade" id="worksheet_<?= $branch_id ?>" role="tabpanel">
                            <h4 class="colour-defult">Worksheet - <?= htmlspecialchars($branch_name) ?></h4>
                            <div class="mt-4">
                                <?php if (!empty($maintenance_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($maintenance_error) ?>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    $worksheets_to_display = $worksheets_by_branch[$branch_id] ?? [];
                                    ?>
                                    <?php if (empty($worksheets_to_display)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No worksheets found for <?= htmlspecialchars($branch_name) ?>.
                                        </div>
                                    <?php else: ?>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0" id="worksheetTable" style="font-size:x-small;">
                                                        <thead>
                                                            <tr>
                                                                <th>Document No</th>
                                                                <th>Description</th>
                                                                <th>Formation</th>
                                                                <th>Aircraft Type</th>
                                                                <th>Issue</th>
                                                                <th>Revision</th>
                                                                <th>Revision Date</th>
                                                                <th>File</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($worksheets_to_display as $doc): ?>
                                                                <tr>
                                                                    <td><strong><?= htmlspecialchars($doc['document_number']) ?></strong></td>
                                                                    <td><?= htmlspecialchars($doc['description']) ?></td>
                                                                    <td><?= htmlspecialchars($doc['formation_name'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($doc['type_name'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($doc['issue']) ?></td>
                                                                    <td><?= htmlspecialchars($doc['revision'] ?? 'N/A') ?></td>
                                                                    <td><?= ($doc['revision_date'] && $doc['revision_date'] != '0000-00-00' ? date('M d, Y', strtotime($doc['revision_date'])) : 'N/A') ?></td>
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
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Technical Library Tab -->
                        <div class="tab-pane fade" id="vehicle" role="tabpanel">
                            <h4 class="colour-defult">Technical Library</h4>
                            <div class="mt-4">
                                <?php if (!empty($tech_library_error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Database Error:</strong> <?= htmlspecialchars($tech_library_error) ?>
                                    </div>
                                <?php elseif (!empty($tech_library)): ?>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table  table-hover mb-0" id="techLibraryTable" style="font-size:x-small;">
                                                    <thead>
                                                        <tr>
                                                            <th>S.No</th>
                                                            <th>Publication Index</th>
                                                            <th>File</th>
                                                            <th>View</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($tech_library as $item): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($item['sno']) ?></strong></td>
                                                                <td><?= htmlspecialchars($item['publication_index']) ?></td>
                                                                <td>
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $item['file_path']) ?>"
                                                                           class="btn btn-view-details btn-sm view-details-btn"
                                                                           data-bs-toggle="modal"
                                                                           data-bs-target="#pdfModal"
                                                                           data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $item['file_path']) ?>">
                                                                            <i class="fas fa-file-pdf me-1"></i>PDF
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No file</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#detailsModal"
                                                                        data-record-type="tech_library"
                                                                        data-record-id="<?= $item['id'] ?? '' ?>"
                                                                        data-record-sno="<?= htmlspecialchars($item['sno'] ?? '') ?>"
                                                                        data-record-publication-index="<?= htmlspecialchars($item['publication_index'] ?? '') ?>"
                                                                        data-record-file-path="<?= htmlspecialchars($item['file_path'] ?? '') ?>">
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
                                        No Technical Library documents found.
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

            // Initialize AD Bulletins table
            if ($('#adBulletinsTable').length) {
                $('#adBulletinsTable').DataTable(dataTableConfig);
            }

            // Initialize QAI Newsletters table
            if ($('#qaiNewslettersTable').length) {
                $('#qaiNewslettersTable').DataTable(dataTableConfig);
            }

            if ($('#servicingScheduleTable').length) {
                $('#servicingScheduleTable').DataTable(dataTableConfig);
            }

            if ($('#worksheetTable').length) {
                $('#worksheetTable').DataTable(dataTableConfig);
            }

            // Initialize Technical Library table
            if ($('#techLibraryTable').length) {
                $('#techLibraryTable').DataTable(dataTableConfig);
            }

            // Initialize DataTables for maintenance tables when their tabs are shown
            $('a[data-bs-target^="#schedule_"], a[data-bs-target^="#worksheet_"]').on('click', function() {
                const target = $(this).data('bs-target');
                setTimeout(() => {
                    $(target + ' table').DataTable(dataTableConfig);
                }, 100);
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

            // Details Modal functionality
            const detailsModal = document.getElementById('detailsModal');
            const detailsModalTitle = document.getElementById('detailsModalTitle');
            const detailsModalBody = document.getElementById('detailsModalBody');

            if (detailsModal) {
                detailsModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const recordType = button.getAttribute('data-record-type');

                    let title = 'Record Details';
                    switch (recordType) {
                        case 'ad_bulletin':
                            title = 'AD Bulletin Details';
                            break;
                        case 'qai_newsletter':
                            title = 'QAI Safety Newsletter Details';
                            break;
                        case 'tech_library':
                            title = 'Technical Library Document Details';
                            break;
                        default:
                            title = 'Record Details';
                    }
                    detailsModalTitle.textContent = title;

                    let content = '';
                    switch (recordType) {
                        case 'ad_bulletin':
                            content = generateADBulletinDetails(button);
                            break;
                        case 'qai_newsletter':
                            content = generateQAINewsletterDetails(button);
                            break;
                        case 'tech_library':
                            content = generateTechLibraryDetails(button);
                            break;
                        default:
                            content = '<p>No details available.</p>';
                    }
                    detailsModalBody.innerHTML = content;
                });

                detailsModal.addEventListener('hidden.bs.modal', function() {
                    detailsModalBody.innerHTML = '';
                });
            }

            // Helper functions
            function formatValue(value) {
                if (!value || value === 'null' || value === 'undefined' || value === '0000-00-00') {
                    return '<span class="empty-value">Not provided</span>';
                }
                return value;
            }

            function formatDate(dateString) {
                if (!dateString || dateString === '0000-00-00') {
                    return '<span class="empty-value">Not provided</span>';
                }
                try {
                    return new Date(dateString).toLocaleDateString();
                } catch (e) {
                    return dateString;
                }
            }

            function generateADBulletinDetails(button) {
                const referenceNo = button.getAttribute('data-record-reference-no');
                const bulletinDescription = button.getAttribute('data-record-bulletin-description');
                const relatedAircraft = button.getAttribute('data-record-related-aircraft');
                const formation = button.getAttribute('data-record-formation');
                const dateOfIssue = button.getAttribute('data-record-date-of-issue');

                return `
                    <div class="section-divider">Basic Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Reference Number:</th>
                            <td><strong>${formatValue(referenceNo)}</strong></td>
                        </tr>
                        <tr>
                            <th>Date of Issue:</th>
                            <td>${formatDate(dateOfIssue)}</td>
                        </tr>
                        <tr>
                            <th>Related Aircraft:</th>
                            <td>${formatValue(relatedAircraft)}</td>
                        </tr>
                        <tr>
                            <th>Formation:</th>
                            <td>${formatValue(formation)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Bulletin Description</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Description:</th>
                            <td style="white-space: pre-wrap;">${formatValue(bulletinDescription)}</td>
                        </tr>
                    </table>
                `;
            }

            function generateQAINewsletterDetails(button) {
                const qsnNo = button.getAttribute('data-record-qsn-no');
                const description = button.getAttribute('data-record-description');
                const issueDate = button.getAttribute('data-record-issue-date');

                return `
                    <div class="section-divider">Basic Information</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>QSN Number:</th>
                            <td><strong>${formatValue(qsnNo)}</strong></td>
                        </tr>
                        <tr>
                            <th>Issue Date:</th>
                            <td>${formatDate(issueDate)}</td>
                        </tr>
                    </table>

                    <div class="section-divider">Newsletter Description</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>Description:</th>
                            <td style="white-space: pre-wrap;">${formatValue(description)}</td>
                        </tr>
                    </table>
                `;
            }

            function generateTechLibraryDetails(button) {
                const sno = button.getAttribute('data-record-sno');
                const publicationIndex = button.getAttribute('data-record-publication-index');
                const filePath = button.getAttribute('data-record-file-path');

                return `
                    <div class="section-divider">Technical Library Document</div>
                    <table class="details-modal-table">
                        <tr>
                            <th>S.No:</th>
                            <td><strong>${formatValue(sno)}</strong></td>
                        </tr>
                        <tr>
                            <th>Publication Index:</th>
                            <td style="white-space: pre-wrap;">${formatValue(publicationIndex)}</td>
                        </tr>
                        <tr>
                            <th>File:</th>
                            <td>
                                ${filePath && filePath !== 'null' && filePath !== 'undefined' ? 
                                    `<a href="/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + filePath)}" 
                                      class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i>View Document
                                     </a>` : 
                                    '<span class="empty-value">No file available</span>'
                                }
                            </td>
                        </tr>
                    </table>
                `;
            }

            // Enhanced Navigation and dropdown handling for nested structure
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

            // Enhanced dropdown toggle handling for nested dropdowns
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

            console.log('Publication page initialization complete');
        });
    </script>
</body>
</html>