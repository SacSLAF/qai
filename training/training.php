<?php
// training.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

try {
    require_once "../includes/config.php";
    
    // Initialize variables
    $training_syllabus_data = [];
    $ts_error = '';
    $formations_map = [];
    $types_map = [];
    $ac_cat_map = [];

    // Check for Training Forecast PDF files
    $training_forecast_dir = '../admin/action/uploads/training/forecast/';
    $forecast_2026_pdf = $training_forecast_dir . 'Forecast-2026.pdf';
    $prospect_2026_pdf = $training_forecast_dir . 'Prospect-2026.pdf';

    $forecast_2026_exists = file_exists($forecast_2026_pdf);
    $prospect_2026_exists = file_exists($prospect_2026_pdf);

    // Create web paths for PDF viewer
    $forecast_2026_web = $forecast_2026_exists ? "/qai/admin/action/uploads/training/forecast/Forecast-2026.pdf" : '';
    $prospect_2026_web = $prospect_2026_exists ? "/qai/admin/action/uploads/training/forecast/Prospect-2026.pdf" : '';

    // Fetch data from both CPD tables
    $training_syllabus_cpd_data = [];
    $training_record_cpd_data = [];
    $syllabus_cpd_error = '';
    $record_cpd_error = '';

    // Fetch Outside Training records
    $outside_training_data = [];
    $outside_training_error = '';

    // Check if database connection exists and is valid
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $ts_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $syllabus_cpd_error = $ts_error;
        $record_cpd_error = $ts_error;
        $outside_training_error = $ts_error;
    } else {
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

        // Load ac_categories for training syllabus categories
        $ac_res = $db->query("SELECT id, name FROM ac_categories ORDER BY name");
        if ($ac_res) {
            foreach ($ac_res->fetch_all(MYSQLI_ASSOC) as $c) {
                $ac_cat_map[$c['id']] = $c['name'];
            }
        }

        // Fetch training syllabus records and organize by ac_category
        $stmt = $db->prepare("
            SELECT ts.* 
            FROM training_syllabus ts 
            ORDER BY ts.ac_categories_id, ts.syllabus_no
        ");

        if ($stmt) {
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $all_records = $result->fetch_all(MYSQLI_ASSOC);

                // Organize by ac_category
                foreach ($all_records as $record) {
                    $category_id = $record['ac_categories_id'] ?? 0;
                    $category_name = $ac_cat_map[$category_id] ?? 'Uncategorized';

                    if (!isset($training_syllabus_data[$category_id])) {
                        $training_syllabus_data[$category_id] = [
                            'category_name' => $category_name,
                            'records' => []
                        ];
                    }
                    $training_syllabus_data[$category_id]['records'][] = $record;
                }
                $stmt->close();
            } else {
                $ts_error = "Training Syllabus query execution failed: " . $stmt->error;
            }
        } else {
            $ts_error = "Error preparing Training Syllabus query: " . $db->error;
        }

        // Fetch Training Syllabus CPD records
        $stmt_syllabus_cpd = $db->prepare("
            SELECT tsc.*, ac.name as category_name
            FROM training_syllabus_cpd tsc 
            LEFT JOIN ac_categories ac ON tsc.ac_categories_id = ac.id 
            ORDER BY tsc.ac_categories_id, tsc.syllabus_no ASC
        ");

        if ($stmt_syllabus_cpd) {
            if ($stmt_syllabus_cpd->execute()) {
                $result_syllabus_cpd = $stmt_syllabus_cpd->get_result();
                $all_syllabus_cpd_records = $result_syllabus_cpd->fetch_all(MYSQLI_ASSOC);

                // Organize by ac_category
                foreach ($all_syllabus_cpd_records as $record) {
                    $category_id = $record['ac_categories_id'] ?? 0;
                    $category_name = $ac_cat_map[$category_id] ?? 'Uncategorized';

                    if (!isset($training_syllabus_cpd_data[$category_id])) {
                        $training_syllabus_cpd_data[$category_id] = [
                            'category_name' => $category_name,
                            'records' => []
                        ];
                    }
                    $training_syllabus_cpd_data[$category_id]['records'][] = $record;
                }
                $stmt_syllabus_cpd->close();
            } else {
                $syllabus_cpd_error = "Training Syllabus CPD query execution failed: " . $stmt_syllabus_cpd->error;
            }
        } else {
            $syllabus_cpd_error = "Error preparing Training Syllabus CPD query: " . $db->error;
        }

        // Training Record
        $training_records_data = [];
        $tr_error = '';

        // Fetch training records data
        if (!empty($db) && !$db->connect_error) {
            $stmt = $db->prepare("
                SELECT tr.*, r.rank_name, ac.name as category_name
                FROM training_records tr 
                LEFT JOIN ranks r ON tr.rank_id = r.id 
                LEFT JOIN ac_categories ac ON tr.ac_category_id = ac.id 
                ORDER BY tr.training_type, tr.ac_category_id, tr.start_date DESC
            ");

            if ($stmt) {
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $all_records = $result->fetch_all(MYSQLI_ASSOC);

                    // Organize by training_type only (no category grouping)
                    foreach ($all_records as $record) {
                        $training_type = $record['training_type'] ?? 'other';
                        
                        if (!isset($training_records_data[$training_type])) {
                            $training_records_data[$training_type] = [];
                        }
                        
                        $training_records_data[$training_type][] = $record;
                    }
                    $stmt->close();
                }
            }
        }


    }

    // Convert PHP data to JSON for JavaScript use
    $training_syllabus_cpd_data_json = json_encode($training_syllabus_cpd_data);
    $training_record_cpd_data_json = json_encode($training_record_cpd_data);
    $outside_training_data_json = json_encode($outside_training_data);
    $training_records_data_json = json_encode($training_records_data);

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
    <title>Command Quality Assurance Inspectorate - Training</title>
    <!-- Bootstrap CSS -->
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="../assets/css/swiper-bundle.min.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .pdf-viewer-container {
            height: calc(100vh - 200px);
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
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
        
        .bg-light-blue {
            background-color: #f0f8ff !important;
        }

        .bg-white {
            background-color: #ffffff !important;
        }

        .table td {
            border-bottom: 1px solid #dee2e6;
            padding: 12px 8px;
            vertical-align: middle;
        }
        .colour-defult {
            font-size: small;
        }

        /* Training Syllabus specific styles */
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
            padding: 5px 10px;
            color: #495057;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 3px;
            font-size: x-small;
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
        
        /* DataTables customization */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: x-small;
            margin: 10px 0;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            font-size: x-small;
            padding: 4px 8px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            font-size: x-small;
            padding: 4px 8px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: x-small;
            padding: 4px 8px;
        }

        /* CPD Category sections */
        .category-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .cpd-category h5 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            color: #1a4f72;
        }

        /* Filter styling */
        #scheduleCpdCategoryFilter,
        #recordCpdCategoryFilter {
            max-width: 300px;
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

        /* Nested dropdown styles */
        .qa-dropdown-subitem {
            padding-left: 25px !important;
            font-size: 0.75rem !important;
        }

        .qa-dropdown .qa-dropdown-menu .qa-dropdown-menu {
            margin-left: 10px;
            border-left: 2px solid #dee2e6;
        }

        .qa-dropdown-subitem:hover {
            background-color: #f8f9fa !important;
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
                <div class="nav flex-column nav-pills" id="trainingTabs" role="tablist">
                    <!-- Training Forecast Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle active" role="button">Training Forecast</a>
                        <div class="qa-dropdown-menu">
                            <a class="qa-dropdown-item active" data-bs-target="#forecast" role="tab">Forecast 2026</a>
                            <a class="qa-dropdown-item" data-bs-target="#prospect" role="tab">Prospect 2026</a>
                        </div>
                    </div>

                    <!-- Approved Training Syllabus Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Approved Training Syllabus</a>
                        <div class="qa-dropdown-menu">
                            <?php if (!empty($ac_cat_map)): ?>
                                <?php ksort($ac_cat_map); ?>
                                <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                                    <a class="qa-dropdown-item" data-bs-target="#syllabus_<?= $category_id ?>" role="tab">
                                        <?= htmlspecialchars($category_name) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <a class="qa-dropdown-item text-muted">No categories found</a>
                            <?php endif; ?>
                            <a class="qa-dropdown-item" data-bs-target="#ts-cpd" role="tab">CPD</a>
                        </div>
                    </div>

                    <!-- Training Record Dropdown -->
                    <div class="qa-dropdown">
                        <a class="nav-link qa-dropdown-toggle" role="button">Training Record</a>
                        <div class="qa-dropdown-menu">
                            <div class="qa-dropdown">
                                <a class="qa-dropdown-item qa-dropdown-toggle" role="button">CPD</a>
                                <div class="qa-dropdown-menu">
                                    <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#cpd_in_house" role="tab">In House</a>
                                    <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#cpd_outside" role="tab">Outside</a>
                                </div>
                            </div>
                            <div class="qa-dropdown">
                                <a class="qa-dropdown-item qa-dropdown-toggle" role="button">Workshop</a>
                                <div class="qa-dropdown-menu">
                                    <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#w_in_house" role="tab">In House</a>
                                    <a class="qa-dropdown-item qa-dropdown-subitem" data-bs-target="#w_outside" role="tab">Outside</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="col-lg-11 col-xl-11">
                <div class="tab-content" id="trainingTabsContent">
                    <!-- Welcome Screen (shown by default) -->
                    <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                        <div class="welcome-message">
                            <!-- Welcome content can go here -->
                        </div>
                    </div>

                    <!-- Training Forecast Content Panes -->
                    <div class="tab-pane fade" id="forecast" role="tabpanel">
                        <h4 class="colour-defult">Training Forecast 2026</h4>
                        <div class="mt-4">
                            <?php if (!empty($forecast_2026_web)): ?>
                                <div class="top-bar mb-3">
                                    <a href="<?= $forecast_2026_web ?>" class="btn btn-sm btn-dark">
                                        Forecast 2026
                                    </a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($forecast_2026_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-3 fa-2x"></i>
                                        <div>
                                            <h5 class="alert-heading">No Forecast Document Available</h5>
                                            <p class="mb-0">The Training Forecast 2026 PDF has not been uploaded yet.</p>
                                            <p class="mb-0"><small>Please check back later or contact the administrator.</small></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="prospect" role="tabpanel">
                        <h4 class="colour-defult">Training Prospect 2026</h4>
                        <div class="mt-4">
                            <?php if (!empty($prospect_2026_web)): ?>
                                <div class="top-bar mb-3">
                                    <a href="<?= $prospect_2026_web ?>" class="btn btn-sm btn-dark">
                                     Prospect 2026
                                    </a>
                                </div>
                                <div class="pdf-viewer-container">
                                    <iframe src="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode($prospect_2026_web) ?>"
                                        width="100%" height="100%" style="border:none;"></iframe>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-3 fa-2x"></i>
                                        <div>
                                            <h5 class="alert-heading">No Prospect Document Available</h5>
                                            <p class="mb-0">The Training Prospect 2026 PDF has not been uploaded yet.</p>
                                            <p class="mb-0"><small>Please check back later or contact the administrator.</small></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Approved Training Syllabus Tab Panes -->
                    <?php if (!empty($ac_cat_map)): ?>
                        <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                            <div class="tab-pane fade" id="syllabus_<?= $category_id ?>" role="tabpanel">
                                <h4 class="colour-defult">Approved Training Syllabus - <?= htmlspecialchars($category_name) ?></h4>

                                <div class="">
                                    <?php if (!empty($ts_error)): ?>
                                        <div class="alert alert-danger">
                                            <strong>Database Error:</strong> <?= htmlspecialchars($ts_error) ?>
                                        </div>
                                    <?php elseif (isset($training_syllabus_data[$category_id]) && !empty($training_syllabus_data[$category_id]['records'])): ?>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-hover mb-0 syllabusTable" id="syllabusTable_<?= $category_id ?>" style="width:100%">
                                                        <thead style="font-size:x-small;">
                                                            <tr>
                                                                <th>Syllabus No</th>
                                                                <th>Description</th>
                                                                <th>Formation</th>
                                                                <th>Aircraft Type</th>
                                                                <th>Trade</th>
                                                                <th>Syllabus Type</th>
                                                                <th>Issue</th>
                                                                <th>Revision</th>
                                                                <th>Revision Date</th>
                                                                <th>File</th>
                                                                <th>View</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody style="font-size:x-small;">
                                                            <?php foreach ($training_syllabus_data[$category_id]['records'] as $index => $record): ?>
                                                                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-light-blue' ?>">
                                                                    <td><strong><?= htmlspecialchars($record['syllabus_no']) ?></strong></td>
                                                                    <td><?= htmlspecialchars(substr($record['description'], 0, 80)) . (strlen($record['description']) > 80 ? '...' : '') ?></td>
                                                                    <td><?= htmlspecialchars($formations_map[$record['formation_id']] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($types_map[$record['type_id']] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($record['trade'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($record['syllabus_type'] ?? 'N/A') ?></td>
                                                                    <td><?= htmlspecialchars($record['issue']) ?></td>
                                                                    <td><?= htmlspecialchars($record['revision'] ?? '-') ?></td>
                                                                    <td><?= $record['revision_date'] ? htmlspecialchars($record['revision_date']) : '-' ?></td>
                                                                    <td>
                                                                        <div class="btn-group" role="group">
                                                                            <?php if (!empty($record['file_path'])): ?>
                                                                                <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>"
                                                                                    class="btn btn-view-details btn-sm view-details-btn"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#pdfModal"
                                                                                    data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>">
                                                                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <button class="btn btn-sm btn-secondary" disabled title="No file available">
                                                                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                                                                </button>
                                                                            <?php endif; ?>
                                                                            </div>

                                                                    </td>
                                                                    <td>
                                                                            <button class="btn btn-view-details btn-sm view-details-btn"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#detailsModal"
                                                                                data-record-type="training_syllabus"
                                                                                data-record-id="<?= $record['id'] ?? '' ?>"
                                                                                data-record-syllabus-no="<?= htmlspecialchars($record['syllabus_no'] ?? '') ?>"
                                                                                data-record-description="<?= htmlspecialchars($record['description'] ?? '') ?>"
                                                                                data-record-formation="<?= htmlspecialchars($formations_map[$record['formation_id']] ?? '') ?>"
                                                                                data-record-aircraft-type="<?= htmlspecialchars($types_map[$record['type_id']] ?? '') ?>"
                                                                                data-record-trade="<?= htmlspecialchars($record['trade'] ?? '') ?>"
                                                                                data-record-syllabus-type="<?= htmlspecialchars($record['syllabus_type'] ?? '') ?>"
                                                                                data-record-issue="<?= htmlspecialchars($record['issue'] ?? '') ?>"
                                                                                data-record-revision="<?= htmlspecialchars($record['revision'] ?? '') ?>"
                                                                                data-record-revision-date="<?= htmlspecialchars($record['revision_date'] ?? '') ?>"
                                                                                data-record-file-path="<?= htmlspecialchars($record['file_path'] ?? '') ?>">
                                                                                View
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
                                            No training syllabus records found for <?= htmlspecialchars($category_name) ?>.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No training syllabus categories found in the system.
                        </div>
                    <?php endif; ?>

                    <!-- Training Schedule CPD Section -->
                    <div class="tab-pane fade" id="ts-cpd" role="tabpanel">
                        <h4 class="colour-defult">Approved Training Syllabus - CPD</h4>
                        
                        <!-- Category Filter Dropdown -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="scheduleCpdCategoryFilter" class="form-label">Select Your Directorate:</label>
                                <select class="form-select form-select-sm" id="scheduleCpdCategoryFilter">
                                    <option value="">Select a category</option>
                                    <?php if (!empty($ac_cat_map)): ?>
                                        <?php ksort($ac_cat_map); ?>
                                        <?php foreach ($ac_cat_map as $category_id => $category_name): ?>
                                            <option value="<?= $category_id ?>"><?= htmlspecialchars($category_name) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4" id="scheduleCpdTableContainer">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please select a directorate to view CPD training syllabus records.
                            </div>
                        </div>
                    </div>

                    <!-- Training Record CPD In House -->
                    <div class="tab-pane fade" id="cpd_in_house" role="tabpanel">
                        <h4 class="colour-defult">CPD Training Records - In House</h4>
                        
                        <div class="mt-4" id="cpdInhouseTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading CPD In House training records...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Training Record CPD Outside -->
                    <div class="tab-pane fade" id="cpd_outside" role="tabpanel">
                        <h4 class="colour-defult">CPD Training Records - Outside</h4>
                        
                        <div class="mt-4" id="cpdOutsideTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading CPD Outside training records...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Workshop In House -->
                    <div class="tab-pane fade" id="w_in_house" role="tabpanel">
                        <h4 class="colour-defult">Workshop Training Records - In House</h4>
                        
                        <div class="mt-4" id="workshopInhouseTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading Workshop In House training records...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Workshop Outside -->
                    <div class="tab-pane fade" id="w_outside" role="tabpanel">
                        <h4 class="colour-defult">Workshop Training Records - Outside</h4>
                        
                        <div class="mt-4" id="workshopOutsideTableContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading Workshop Outside training records...</p>
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
                    <h5 class="modal-title">Training Syllabus Document</h5>
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
                    <h5 class="modal-title" id="detailsModalTitle">Training Syllabus Details</h5>
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
        // Store all data from PHP
        const trainingSyllabusCpdData = <?= $training_syllabus_cpd_data_json ?>;
        const trainingRecordCpdData = <?= $training_record_cpd_data_json ?>;
        const outsideTrainingData = <?= $outside_training_data_json ?>;
        const trainingRecordsData = <?= $training_records_data_json ?>;

        $(document).ready(function() {
            console.log("Training page - starting initialization");
            console.log("Training Records Data:", trainingRecordsData);

            const dataTableConfig = {
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "Filter:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                },
                autoWidth: false,
                responsive: true,
                destroy: true,
                ordering: true,
                order: [[0, 'asc']],
                stateSave: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: -1 },
                    { orderable: false, targets: -1 }
                ]
            };

            const cpdDataTableConfig = {
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "Filter:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                },
                autoWidth: false,
                responsive: true,
                destroy: true,
                ordering: true,
                order: [[0, 'asc']],
                stateSave: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 2 },
                    { responsivePriority: 3, targets: -1 },
                    { orderable: false, targets: -1 }
                ]
            };

            const trainingRecordDataTableConfig = {
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "Filter:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                },
                autoWidth: false,
                responsive: true,
                destroy: true,
                ordering: true,
                order: [[0, 'asc']],
                stateSave: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 3 },
                    { responsivePriority: 3, targets: -1 },
                    { orderable: false, targets: -1 }
                ]
            };

            // Initialize all syllabus tables
            function initializeSyllabusTables() {
                $('.syllabusTable').each(function() {
                    const tableId = $(this).attr('id');
                    if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                        console.log('Initializing DataTable for:', tableId);
                        $('#' + tableId).DataTable(dataTableConfig);
                    }
                });
            }

            // Training Schedule CPD Category filter functionality
            function setupScheduleCpdCategoryFilter() {
                const categoryFilter = document.getElementById('scheduleCpdCategoryFilter');
                const tableContainer = document.getElementById('scheduleCpdTableContainer');
                
                if (categoryFilter) {
                    categoryFilter.addEventListener('change', function() {
                        const selectedCategoryId = this.value;
                        
                        if (!selectedCategoryId) {
                            tableContainer.innerHTML = `
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please select a directorate to view CPD training syllabus records.
                                </div>
                            `;
                            return;
                        }
                        
                        tableContainer.innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading CPD syllabus records...</p>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            loadScheduleCpdTable(selectedCategoryId);
                        }, 300);
                    });
                }
            }

            // Load Training Schedule CPD table for selected category
            function loadScheduleCpdTable(categoryId) {
                const tableContainer = document.getElementById('scheduleCpdTableContainer');
                const categoryName = document.querySelector(`#scheduleCpdCategoryFilter option[value="${categoryId}"]`).textContent;
                
                if (trainingSyllabusCpdData[categoryId] && trainingSyllabusCpdData[categoryId].records && trainingSyllabusCpdData[categoryId].records.length > 0) {
                    const records = trainingSyllabusCpdData[categoryId].records;
                    
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 cpdTable" id="scheduleCpdTable_${categoryId}" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>Syllabus No</th>
                                                <th>Trade</th>
                                                <th>Course Description</th>
                                                <th>Issue</th>
                                                <th>Revision</th>
                                                <th>Revised Date</th>
                                                <th>File</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td><strong>${escapeHtml(record.syllabus_no)}</strong></td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.course_description)}">
                                        ${escapeHtml(record.course_description.substring(0, 80))}${record.course_description.length > 80 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.issue || 'N/A')}</td>
                                <td>${escapeHtml(record.revision || 'N/A')}</td>
                                <td>${record.revised_date ? escapeHtml(record.revised_date) : 'N/A'}</td>
                                <td>
                                    <div class="btn-group" role="group">
                        `;
                        
                        if (record.file_path) {
                            const pdfUrl = `/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + record.file_path)}`;
                            tableHtml += `
                                <a href="${pdfUrl}"
                                    class="btn btn-view-details btn-sm view-details-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pdfModal"
                                    data-pdf-url="${pdfUrl}">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </a>
                            `;
                        } else {
                            tableHtml += `
                                <button class="btn btn-sm btn-secondary" disabled title="No file available">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                            `;
                        }
                        
                        tableHtml += `
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-record-type="training_syllabus_cpd"
                                        data-record-id="${record.id || ''}"
                                        data-record-syllabus-no="${escapeHtml(record.syllabus_no || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-course-description="${escapeHtml(record.course_description || '')}"
                                        data-record-issue="${escapeHtml(record.issue || '')}"
                                        data-record-revision="${escapeHtml(record.revision || '')}"
                                        data-record-revised-date="${escapeHtml(record.revised_date || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-file-path="${escapeHtml(record.file_path || '')}">
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    tableContainer.innerHTML = tableHtml;
                    
                    setTimeout(() => {
                        const tableId = `scheduleCpdTable_${categoryId}`;
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            $('#' + tableId).DataTable(cpdDataTableConfig);
                        }
                        
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No CPD syllabus records found for <strong>${categoryName}</strong>.
                        </div>
                    `;
                }
            }

            // Load CPD In House table (all data - no category filter)
            function loadCpdInhouseTable() {
                console.log('Loading CPD In House table...');
                const tableContainer = document.getElementById('cpdInhouseTableContainer');
                
                if (!tableContainer) {
                    console.error('CPD In House table container not found');
                    return;
                }
                
                if (trainingRecordsData['cpd-inhouse'] && trainingRecordsData['cpd-inhouse'].length > 0) {
                    console.log('Found', trainingRecordsData['cpd-inhouse'].length, 'CPD In House records');
                    const records = trainingRecordsData['cpd-inhouse'];
                    
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 trainingRecordTable" id="cpdInhouseTable" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Service No</th>
                                                <th>Rank</th>
                                                <th>Name</th>
                                                <th>Course Name</th>
                                                <th>Trade</th>
                                                <th>Directorate</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td>${escapeHtml(record.sno || 'N/A')}</td>
                                <td><strong>${escapeHtml(record.svc_no || 'N/A')}</strong></td>
                                <td>${escapeHtml(record.rank_name || 'N/A')}</td>
                                <td>${escapeHtml(record.name)}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.course_name)}">
                                        ${escapeHtml(record.course_name.substring(0, 50))}${record.course_name.length > 50 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>${escapeHtml(record.category_name || 'N/A')}</td>
                                <td>${record.start_date ? escapeHtml(record.start_date) : 'N/A'}</td>
                                <td>${record.end_date ? escapeHtml(record.end_date) : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-record-type="training_record"
                                        data-record-id="${record.id || ''}"
                                        data-record-sno="${escapeHtml(record.sno || '')}"
                                        data-record-svc-no="${escapeHtml(record.svc_no || '')}"
                                        data-record-rank="${escapeHtml(record.rank_name || '')}"
                                        data-record-name="${escapeHtml(record.name || '')}"
                                        data-record-course-name="${escapeHtml(record.course_name || '')}"
                                        data-record-training-type="${escapeHtml(record.training_type || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-start-date="${escapeHtml(record.start_date || '')}"
                                        data-record-end-date="${escapeHtml(record.end_date || '')}"
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    tableContainer.innerHTML = tableHtml;
                    
                    setTimeout(() => {
                        const tableId = 'cpdInhouseTable';
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            console.log('Initializing DataTable for CPD In House');
                            $('#' + tableId).DataTable(trainingRecordDataTableConfig);
                        }
                        
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    console.log('No CPD In House records found');
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No CPD In House training records found.
                        </div>
                    `;
                }
            }

            // Load CPD Outside table (all data - no category filter)
            function loadCpdOutsideTable() {
                console.log('Loading CPD Outside table...');
                const tableContainer = document.getElementById('cpdOutsideTableContainer');
                
                if (!tableContainer) {
                    console.error('CPD Outside table container not found');
                    return;
                }
                
                if (trainingRecordsData['cpd-outside'] && trainingRecordsData['cpd-outside'].length > 0) {
                    console.log('Found', trainingRecordsData['cpd-outside'].length, 'CPD Outside records');
                    const records = trainingRecordsData['cpd-outside'];
                    
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 trainingRecordTable" id="cpdOutsideTable" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Service No</th>
                                                <th>Rank</th>
                                                <th>Name</th>
                                                <th>Course Name</th>
                                                <th>Trade</th>
                                                <th>Directorate</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td>${escapeHtml(record.sno || 'N/A')}</td>
                                <td><strong>${escapeHtml(record.svc_no || 'N/A')}</strong></td>
                                <td>${escapeHtml(record.rank_name || 'N/A')}</td>
                                <td>${escapeHtml(record.name)}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.course_name)}">
                                        ${escapeHtml(record.course_name.substring(0, 50))}${record.course_name.length > 50 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>${escapeHtml(record.category_name || 'N/A')}</td>
                                <td>${record.start_date ? escapeHtml(record.start_date) : 'N/A'}</td>
                                <td>${record.end_date ? escapeHtml(record.end_date) : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-record-type="training_record"
                                        data-record-id="${record.id || ''}"
                                        data-record-sno="${escapeHtml(record.sno || '')}"
                                        data-record-svc-no="${escapeHtml(record.svc_no || '')}"
                                        data-record-rank="${escapeHtml(record.rank_name || '')}"
                                        data-record-name="${escapeHtml(record.name || '')}"
                                        data-record-course-name="${escapeHtml(record.course_name || '')}"
                                        data-record-training-type="${escapeHtml(record.training_type || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-start-date="${escapeHtml(record.start_date || '')}"
                                        data-record-end-date="${escapeHtml(record.end_date || '')}"
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    tableContainer.innerHTML = tableHtml;
                    
                    setTimeout(() => {
                        const tableId = 'cpdOutsideTable';
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            console.log('Initializing DataTable for CPD Outside');
                            $('#' + tableId).DataTable(trainingRecordDataTableConfig);
                        }
                        
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    console.log('No CPD Outside records found');
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No CPD Outside training records found.
                        </div>
                    `;
                }
            }

            // Load Workshop In House table (all data - no category filter)
            function loadWorkshopInhouseTable() {
                console.log('Loading Workshop In House table...');
                const tableContainer = document.getElementById('workshopInhouseTableContainer');
                
                if (!tableContainer) {
                    console.error('Workshop In House table container not found');
                    return;
                }
                
                if (trainingRecordsData['workshop-inhouse'] && trainingRecordsData['workshop-inhouse'].length > 0) {
                    console.log('Found', trainingRecordsData['workshop-inhouse'].length, 'Workshop In House records');
                    const records = trainingRecordsData['workshop-inhouse'];
                    
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 trainingRecordTable" id="workshopInhouseTable" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Service No</th>
                                                <th>Rank</th>
                                                <th>Name</th>
                                                <th>Course Name</th>
                                                <th>Trade</th>
                                                <th>Directorate</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td>${escapeHtml(record.sno || 'N/A')}</td>
                                <td><strong>${escapeHtml(record.svc_no || 'N/A')}</strong></td>
                                <td>${escapeHtml(record.rank_name || 'N/A')}</td>
                                <td>${escapeHtml(record.name)}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.course_name)}">
                                        ${escapeHtml(record.course_name.substring(0, 50))}${record.course_name.length > 50 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>${escapeHtml(record.category_name || 'N/A')}</td>
                                <td>${record.start_date ? escapeHtml(record.start_date) : 'N/A'}</td>
                                <td>${record.end_date ? escapeHtml(record.end_date) : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-record-type="training_record"
                                        data-record-id="${record.id || ''}"
                                        data-record-sno="${escapeHtml(record.sno || '')}"
                                        data-record-svc-no="${escapeHtml(record.svc_no || '')}"
                                        data-record-rank="${escapeHtml(record.rank_name || '')}"
                                        data-record-name="${escapeHtml(record.name || '')}"
                                        data-record-course-name="${escapeHtml(record.course_name || '')}"
                                        data-record-training-type="${escapeHtml(record.training_type || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-start-date="${escapeHtml(record.start_date || '')}"
                                        data-record-end-date="${escapeHtml(record.end_date || '')}"
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    tableContainer.innerHTML = tableHtml;
                    
                    setTimeout(() => {
                        const tableId = 'workshopInhouseTable';
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            console.log('Initializing DataTable for Workshop In House');
                            $('#' + tableId).DataTable(trainingRecordDataTableConfig);
                        }
                        
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    console.log('No Workshop In House records found');
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No Workshop In House training records found.
                        </div>
                    `;
                }
            }

            // Load Workshop Outside table (all data - no category filter)
            function loadWorkshopOutsideTable() {
                console.log('Loading Workshop Outside table...');
                const tableContainer = document.getElementById('workshopOutsideTableContainer');
                
                if (!tableContainer) {
                    console.error('Workshop Outside table container not found');
                    return;
                }
                
                if (trainingRecordsData['workshop-outside'] && trainingRecordsData['workshop-outside'].length > 0) {
                    console.log('Found', trainingRecordsData['workshop-outside'].length, 'Workshop Outside records');
                    const records = trainingRecordsData['workshop-outside'];
                    
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 trainingRecordTable" id="workshopOutsideTable" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Service No</th>
                                                <th>Rank</th>
                                                <th>Name</th>
                                                <th>Course Name</th>
                                                <th>Trade</th>
                                                <th>Directorate</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td>${escapeHtml(record.sno || 'N/A')}</td>
                                <td><strong>${escapeHtml(record.svc_no || 'N/A')}</strong></td>
                                <td>${escapeHtml(record.rank_name || 'N/A')}</td>
                                <td>${escapeHtml(record.name)}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.course_name)}">
                                        ${escapeHtml(record.course_name.substring(0, 50))}${record.course_name.length > 50 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>${escapeHtml(record.category_name || 'N/A')}</td>
                                <td>${record.start_date ? escapeHtml(record.start_date) : 'N/A'}</td>
                                <td>${record.end_date ? escapeHtml(record.end_date) : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-record-type="training_record"
                                        data-record-id="${record.id || ''}"
                                        data-record-sno="${escapeHtml(record.sno || '')}"
                                        data-record-svc-no="${escapeHtml(record.svc_no || '')}"
                                        data-record-rank="${escapeHtml(record.rank_name || '')}"
                                        data-record-name="${escapeHtml(record.name || '')}"
                                        data-record-course-name="${escapeHtml(record.course_name || '')}"
                                        data-record-training-type="${escapeHtml(record.training_type || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-start-date="${escapeHtml(record.start_date || '')}"
                                        data-record-end-date="${escapeHtml(record.end_date || '')}"
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    tableContainer.innerHTML = tableHtml;
                    
                    setTimeout(() => {
                        const tableId = 'workshopOutsideTable';
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            console.log('Initializing DataTable for Workshop Outside');
                            $('#' + tableId).DataTable(trainingRecordDataTableConfig);
                        }
                        
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    console.log('No Workshop Outside records found');
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No Workshop Outside training records found.
                        </div>
                    `;
                }
            }

            // Initialize training record tables
            function initializeTrainingRecordTables() {
                $('.trainingRecordTable').each(function() {
                    const tableId = $(this).attr('id');
                    if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                        console.log('Initializing DataTable for:', tableId);
                        $('#' + tableId).DataTable(trainingRecordDataTableConfig);
                    }
                });
            }

            // Enhanced nested dropdown functionality
            function setupNestedDropdowns() {
                // Handle main dropdown toggles
                const dropdownToggles = document.querySelectorAll(".qa-dropdown-toggle");
                
                dropdownToggles.forEach((toggle) => {
                    toggle.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const dropdownMenu = this.nextElementSibling;
                        if (!dropdownMenu) return;

                        const isCurrentlyOpen = dropdownMenu.classList.contains("show");

                        // Close all other dropdowns at the same level
                        const parentContainer = this.closest('.qa-dropdown');
                        if (parentContainer) {
                            const siblingDropdowns = parentContainer.parentElement.querySelectorAll('.qa-dropdown');
                            siblingDropdowns.forEach((sibling) => {
                                if (sibling !== parentContainer) {
                                    const siblingMenu = sibling.querySelector('.qa-dropdown-menu');
                                    if (siblingMenu) siblingMenu.classList.remove("show");
                                }
                            });
                        }

                        if (!isCurrentlyOpen) {
                            dropdownMenu.classList.add("show");
                        } else {
                            dropdownMenu.classList.remove("show");
                        }
                    });
                });

                // Handle nested dropdown items
                const dropdownItems = document.querySelectorAll(".qa-dropdown-item:not(.qa-dropdown-toggle)");
                dropdownItems.forEach((item) => {
                    item.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Remove active class from all items
                        document.querySelectorAll(".nav-link, .qa-dropdown-item").forEach((tab) => {
                            tab.classList.remove("active");
                        });
                        
                        // Add active class to clicked item
                        this.classList.add("active");

                        // Activate parent dropdown toggles
                        let currentElement = this;
                        while (currentElement) {
                            if (currentElement.classList.contains('qa-dropdown-item')) {
                                const parentDropdown = currentElement.closest(".qa-dropdown");
                                if (parentDropdown) {
                                    const dropdownToggle = parentDropdown.querySelector(".qa-dropdown-toggle");
                                    if (dropdownToggle) {
                                        dropdownToggle.classList.add("active");
                                        const dropdownMenu = dropdownToggle.nextElementSibling;
                                        if (dropdownMenu) dropdownMenu.classList.add("show");
                                    }
                                }
                            }
                            currentElement = currentElement.parentElement;
                        }

                        const targetId = this.getAttribute("data-bs-target");
                        const targetPane = document.querySelector(targetId);

                        // Hide all tab panes
                        document.querySelectorAll(".tab-pane").forEach((pane) => {
                            pane.classList.remove("show", "active");
                        });

                        // Show target tab pane
                        if (targetPane) {
                            targetPane.classList.add("show", "active");
                        }
                        
                        // Initialize relevant functionality based on the tab
                        setTimeout(() => {
                            initializeSyllabusTables();
                            
                            if (targetId === '#ts-cpd') {
                                setupScheduleCpdCategoryFilter();
                            }
                            
                            // Load training record tables when their tabs are activated
                            if (targetId === '#cpd_in_house') {
                                loadCpdInhouseTable();
                            } else if (targetId === '#cpd_outside') {
                                loadCpdOutsideTable();
                            } else if (targetId === '#w_in_house') {
                                loadWorkshopInhouseTable();
                            } else if (targetId === '#w_outside') {
                                loadWorkshopOutsideTable();
                            }
                        }, 300);
                    });
                });

                // Close dropdowns when clicking outside
                document.addEventListener("click", function(e) {
                    if (!e.target.closest(".qa-dropdown")) {
                        document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                            menu.classList.remove("show");
                        });
                    }
                });

                // Close dropdowns with Escape key
                document.addEventListener("keydown", function(e) {
                    if (e.key === "Escape") {
                        document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                            menu.classList.remove("show");
                        });
                    }
                });
            }

            // Helper function to escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            function formatValue(value) {
                if (
                    !value ||
                    value === "null" ||
                    value === "undefined" ||
                    value === "0000-00-00" ||
                    value === "N/A"
                ) {
                    return '<span class="empty-value">Not provided</span>';
                }
                return value;
            }

            function formatDate(dateString) {
                if (!dateString || dateString === "0000-00-00" || dateString === "-") {
                    return '<span class="empty-value">Not provided</span>';
                }
                try {
                    return new Date(dateString).toLocaleDateString('en-GB');
                } catch (e) {
                    return dateString;
                }
            }

            function getTrainingTypeDisplayName(trainingType) {
                const typeMap = {
                    'cpd-inhouse': 'CPD In House',
                    'cpd-outside': 'CPD Outside',
                    'workshop-inhouse': 'Workshop In House',
                    'workshop-outside': 'Workshop Outside'
                };
                return typeMap[trainingType] || trainingType;
            }

            // Modal functionality
            const pdfModal = document.getElementById("pdfModal");
            const pdfFrame = document.getElementById("pdfFrame");

            if (pdfModal) {
                pdfModal.addEventListener("show.bs.modal", function(event) {
                    const button = event.relatedTarget;
                    const pdfUrl = button.getAttribute("data-pdf-url");
                    const syllabusNo = button.closest('tr').querySelector('td:first-child').textContent;
                    
                    document.querySelector('#pdfModal .modal-title').textContent = 'Training Syllabus - ' + syllabusNo;
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
                        case "training_syllabus":
                            title = "Training Syllabus Details";
                            break;
                        case "training_syllabus_cpd":
                            title = "CPD Training Syllabus Details";
                            break;
                        case "training_record_cpd":
                            title = "CPD Training Record Details";
                            break;
                        case "training_record":
                            title = "Training Record Details";
                            break;
                        case "outside_training":
                            title = "Outside Training Details";
                            break;
                    }
                    detailsModalTitle.textContent = title;

                    let content = "";
                    switch (recordType) {
                        case "training_syllabus":
                            content = generateTrainingSyllabusDetails(button);
                            break;
                        case "training_syllabus_cpd":
                            content = generateTrainingSyllabusCpdDetails(button);
                            break;
                        case "training_record_cpd":
                            content = generateTrainingRecordCpdDetails(button);
                            break;
                        case "training_record":
                            content = generateTrainingRecordDetails(button);
                            break;
                        case "outside_training":
                            content = generateOutsideTrainingDetails(button);
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

            function generateTrainingSyllabusDetails(button) {
                const syllabusNo = button.getAttribute("data-record-syllabus-no");
                const description = button.getAttribute("data-record-description");
                const formation = button.getAttribute("data-record-formation");
                const aircraftType = button.getAttribute("data-record-aircraft-type");
                const trade = button.getAttribute("data-record-trade");
                const syllabusType = button.getAttribute("data-record-syllabus-type");
                const issue = button.getAttribute("data-record-issue");
                const revision = button.getAttribute("data-record-revision");
                const revisionDate = button.getAttribute("data-record-revision-date");
                const filePath = button.getAttribute("data-record-file-path");

                let fileLink = '<span class="empty-value">No file available</span>';
                if (filePath && filePath !== 'N/A') {
                    fileLink = `<a href="/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + filePath)}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> View Document
                            </a>`;
                }

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Syllabus Number:</th>
                        <td><strong>${formatValue(syllabusNo)}</strong></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>${formatValue(description)}</td>
                    </tr>
                    <tr>
                        <th>Formation:</th>
                        <td>${formatValue(formation)}</td>
                    </tr>
                    <tr>
                        <th>Aircraft Type:</th>
                        <td>${formatValue(aircraftType)}</td>
                    </tr>
                </table>

                <div class="section-divider">Training Details</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Trade:</th>
                        <td>${formatValue(trade)}</td>
                    </tr>
                    <tr>
                        <th>Syllabus Type:</th>
                        <td>${formatValue(syllabusType)}</td>
                    </tr>
                    <tr>
                        <th>Issue:</th>
                        <td>${formatValue(issue)}</td>
                    </tr>
                </table>

                <div class="section-divider">Revision Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Revision:</th>
                        <td>${formatValue(revision)}</td>
                    </tr>
                    <tr>
                        <th>Revision Date:</th>
                        <td>${formatDate(revisionDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Document</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Document:</th>
                        <td>${fileLink}</td>
                    </tr>
                </table>
            `;
            }

            function generateTrainingSyllabusCpdDetails(button) {
                const syllabusNo = button.getAttribute("data-record-syllabus-no");
                const trade = button.getAttribute("data-record-trade");
                const courseDescription = button.getAttribute("data-record-course-description");
                const issue = button.getAttribute("data-record-issue");
                const revision = button.getAttribute("data-record-revision");
                const revisedDate = button.getAttribute("data-record-revised-date");
                const category = button.getAttribute("data-record-category");
                const filePath = button.getAttribute("data-record-file-path");

                let fileLink = '<span class="empty-value">No file available</span>';
                if (filePath && filePath !== 'N/A') {
                    fileLink = `<a href="/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + filePath)}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> View Document
                            </a>`;
                }

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Syllabus Number:</th>
                        <td><strong>${formatValue(syllabusNo)}</strong></td>
                    </tr>
                    <tr>
                        <th>Trade:</th>
                        <td>${formatValue(trade)}</td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td>${formatValue(category)}</td>
                    </tr>
                </table>

                <div class="section-divider">Course Details</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Course Description:</th>
                        <td>${formatValue(courseDescription)}</td>
                    </tr>
                    <tr>
                        <th>Issue:</th>
                        <td>${formatValue(issue)}</td>
                    </tr>
                    <tr>
                        <th>Revision:</th>
                        <td>${formatValue(revision)}</td>
                    </tr>
                    <tr>
                        <th>Revised Date:</th>
                        <td>${formatDate(revisedDate)}</td>
                    </tr>
                </table>

                <div class="section-divider">Document</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Document:</th>
                        <td>${fileLink}</td>
                    </tr>
                </table>
                `;
            }

            function generateTrainingRecordCpdDetails(button) {
                const sno = button.getAttribute("data-record-sno");
                const trade = button.getAttribute("data-record-trade");
                const description = button.getAttribute("data-record-description");
                const duration = button.getAttribute("data-record-duration");
                const category = button.getAttribute("data-record-category");
                const filePath = button.getAttribute("data-record-file-path");

                let fileLink = '<span class="empty-value">No file available</span>';
                if (filePath && filePath !== 'N/A') {
                    fileLink = `<a href="/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + filePath)}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> View Document
                            </a>`;
                }

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Serial Number:</th>
                        <td><strong>${formatValue(sno)}</strong></td>
                    </tr>
                    <tr>
                        <th>Trade:</th>
                        <td>${formatValue(trade)}</td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td>${formatValue(category)}</td>
                    </tr>
                </table>

                <div class="section-divider">Training Details</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Duration:</th>
                        <td>${formatValue(duration)}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>${formatValue(description)}</td>
                    </tr>
                </table>

                <div class="section-divider">Document</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Document:</th>
                        <td>${fileLink}</td>
                    </tr>
                </table>
                `;
            }

            function generateTrainingRecordDetails(button) {
                const sno = button.getAttribute("data-record-sno");
                const svcNo = button.getAttribute("data-record-svc-no");
                const rank = button.getAttribute("data-record-rank");
                const name = button.getAttribute("data-record-name");
                const courseName = button.getAttribute("data-record-course-name");
                const trainingType = button.getAttribute("data-record-training-type");
                const category = button.getAttribute("data-record-category");
                const trade = button.getAttribute("data-record-trade");
                const startDate = button.getAttribute("data-record-start-date");
                const endDate = button.getAttribute("data-record-end-date");

                return `
                <div class="section-divider">Personal Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Serial Number:</th>
                        <td><strong>${formatValue(sno)}</strong></td>
                    </tr>
                    <tr>
                        <th>Service Number:</th>
                        <td><strong>${formatValue(svcNo)}</strong></td>
                    </tr>
                    <tr>
                        <th>Rank:</th>
                        <td>${formatValue(rank)}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>${formatValue(name)}</td>
                    </tr>
                </table>

                <div class="section-divider">Training Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Training Type:</th>
                        <td>${formatValue(getTrainingTypeDisplayName(trainingType))}</td>
                    </tr>
                    <tr>
                        <th>Directorate:</th>
                        <td>${formatValue(category)}</td>
                    </tr>
                    <tr>
                        <th>Course Name:</th>
                        <td>${formatValue(courseName)}</td>
                    </tr>
                    <tr>
                        <th>Trade:</th>
                        <td>${formatValue(trade)}</td>
                    </tr>
                </table>

                <div class="section-divider">Duration</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Start Date:</th>
                        <td>${formatDate(startDate)}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>${formatDate(endDate)}</td>
                    </tr>
                </table>
                `;
            }

            function generateOutsideTrainingDetails(button) {
                const sno = button.getAttribute("data-record-sno");
                const description = button.getAttribute("data-record-description");
                const conductedInstitute = button.getAttribute("data-record-conducted-institute");
                const duration = button.getAttribute("data-record-duration");
                const filePath = button.getAttribute("data-record-file-path");

                let fileLink = '<span class="empty-value">No file available</span>';
                if (filePath && filePath !== 'N/A') {
                    fileLink = `<a href="/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + filePath)}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-file-pdf"></i> View Document
                            </a>`;
                }

                return `
                <div class="section-divider">Basic Information</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Serial Number:</th>
                        <td><strong>${formatValue(sno)}</strong></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>${formatValue(description)}</td>
                    </tr>
                </table>

                <div class="section-divider">Training Details</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Conducted Institute:</th>
                        <td>${formatValue(conductedInstitute)}</td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>${formatValue(duration)}</td>
                    </tr>
                </table>

                <div class="section-divider">Document</div>
                <table class="details-modal-table">
                    <tr>
                        <th>Document:</th>
                        <td>${fileLink}</td>
                    </tr>
                </table>
                `;
            }

            // Initialize everything
            setTimeout(() => {
                initializeSyllabusTables();
                setupNestedDropdowns();
                
                // Initialize CPD tables if needed
                if (!$.fn.DataTable.isDataTable('#cpdOutsideTable')) {
                    $('#cpdOutsideTable').DataTable(cpdDataTableConfig);
                }
            }, 500);

            // Reinitialize tables when tabs are shown
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                console.log('Tab shown:', target);
                
                setTimeout(() => {
                    initializeSyllabusTables();
                    
                    if (target === '#ts-cpd') {
                        setupScheduleCpdCategoryFilter();
                    }
                    
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                }, 300);
            });

            $(window).on('resize', function() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
            });

            console.log("Training page initialization complete");
        });
    </script>
</body>
</html>