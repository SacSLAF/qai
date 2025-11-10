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

    // Fetch Training Syllabus CPD data - This will now be used in Training Record -> CPD
    $training_cpd_data = [];
    $cpd_error = '';

    // Check if database connection exists and is valid
    if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
        $ts_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
        $cpd_error = $ts_error;
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

        // Fetch Training Syllabus CPD records - This data will be shown in Training Record -> CPD
        $stmt_cpd = $db->prepare("
            SELECT tsc.*, ac.name as category_name
            FROM training_record_cpd tsc 
            LEFT JOIN ac_categories ac ON tsc.ac_categories_id = ac.id 
            ORDER BY tsc.ac_categories_id, tsc.sno ASC
        ");

        if ($stmt_cpd) {
            if ($stmt_cpd->execute()) {
                $result_cpd = $stmt_cpd->get_result();
                $all_cpd_records = $result_cpd->fetch_all(MYSQLI_ASSOC);

                // Organize by ac_category
                foreach ($all_cpd_records as $record) {
                    $category_id = $record['ac_categories_id'] ?? 0;
                    $category_name = $ac_cat_map[$category_id] ?? 'Uncategorized';

                    if (!isset($training_cpd_data[$category_id])) {
                        $training_cpd_data[$category_id] = [
                            'category_name' => $category_name,
                            'records' => []
                        ];
                    }
                    $training_cpd_data[$category_id]['records'][] = $record;
                }
                $stmt_cpd->close();
            } else {
                $cpd_error = "Training Syllabus CPD query execution failed: " . $stmt_cpd->error;
            }
        } else {
            $cpd_error = "Error preparing Training Syllabus CPD query: " . $db->error;
        }
    }

    // Convert PHP data to JSON for JavaScript use
    $training_cpd_data_json = json_encode($training_cpd_data);

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
        #cpdCategoryFilter {
            max-width: 300px;
        }

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
                            <a class="qa-dropdown-item" data-bs-target="#record_cpd" role="tab">CPD</a>
                            <a class="qa-dropdown-item" data-bs-target="#record_ot" role="tab">Outside Training</a>
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
                                    <a href="<?= $forecast_2026_web ?>" target="_blank" class="btn btn-sm btn-dark">
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
                                    <a href="<?= $prospect_2026_web ?>" target="_blank" class="btn btn-sm btn-dark">
                                     <i class="fas fa-download me-1"></i> Download Prospect 2026
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
                                                                                    class="btn btn-view-pdf btn-sm view-pdf-btn"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#pdfModal"
                                                                                    data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $record['file_path']) ?>">
                                                                                    View PDF
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <button class="btn btn-sm btn-secondary" disabled title="No file available">
                                                                                    View PDF
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

                    <div class="tab-pane fade" id="ts-cpd" role="tabpanel">
                        <h4 class="colour-defult">Approved Training Syllabus - CPD</h4>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Outside training records will be displayed here.
                        </div>
                    </div>

                    <!-- Training Record CPD Content Pane -->
                    <div class="tab-pane fade" id="record_cpd" role="tabpanel">
                        <h4 class="colour-defult">CPD Training Records</h4>
                        
                        <!-- Category Filter Dropdown -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="recordCpdCategoryFilter" class="form-label">Select Your Directorate:</label>
                                <select class="form-select form-select-sm" id="recordCpdCategoryFilter">
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

                        <div class="mt-4" id="recordCpdTableContainer">
                            <!-- CPD table will be displayed here based on category selection -->
                            <div>
                                <!--<i class="fas fa-info-circle me-2"></i>
                                Please select a category to view CPD training records.-->
                            </div>
                        </div>
                    </div>

                    <!-- Training Record Outside Training Content Pane -->
                    <div class="tab-pane fade" id="record_ot" role="tabpanel">
                        <h4 class="colour-defult">Outside Training Records</h4>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Outside training records will be displayed here.
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
        // Store CPD data from PHP - This will be used in Training Record -> CPD
        const trainingCpdData = <?= $training_cpd_data_json ?>;

        $(document).ready(function() {
            console.log("Training page - starting initialization");

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
                order: [[0, 'asc']], // Default sort by Syllabus No
                stateSave: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 }, // Syllabus No
                    { responsivePriority: 2, targets: 1 }, // Description
                    { responsivePriority: 3, targets: -1 }, // Actions
                    { orderable: false, targets: -1 } // Disable sorting for Actions column
                ]
            };

            // DataTable configuration for CPD tables
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
                order: [[0, 'asc']], // Default sort by S/NO
                stateSave: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 }, // S/NO
                    { responsivePriority: 2, targets: 2 }, // Description
                    { responsivePriority: 3, targets: -1 }, // Actions
                    { orderable: false, targets: -1 } // Disable sorting for Actions column
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

            // Training Record CPD Category filter functionality
            function setupRecordCpdCategoryFilter() {
                const categoryFilter = document.getElementById('recordCpdCategoryFilter');
                const tableContainer = document.getElementById('recordCpdTableContainer');
                
                if (categoryFilter) {
                    categoryFilter.addEventListener('change', function() {
                        const selectedCategoryId = this.value;
                        
                        if (!selectedCategoryId) {
                            // Show placeholder message
                            tableContainer.innerHTML = `
                                <div>
                                    <!--<i class="fas fa-info-circle me-2"></i>
                                    Please select a category to view CPD training records.-->
                                </div>
                            `;
                            return;
                        }
                        
                        // Show loading state
                        tableContainer.innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading CPD records...</p>
                            </div>
                        `;
                        
                        // Load the table for selected category after a short delay
                        setTimeout(() => {
                            loadRecordCpdTable(selectedCategoryId);
                        }, 300);
                    });
                }
            }

            // Load Training Record CPD table for selected category
            function loadRecordCpdTable(categoryId) {
                const tableContainer = document.getElementById('recordCpdTableContainer');
                const categoryName = document.querySelector(`#recordCpdCategoryFilter option[value="${categoryId}"]`).textContent;
                
                // Check if we have data for this category
                if (trainingCpdData[categoryId] && trainingCpdData[categoryId].records && trainingCpdData[categoryId].records.length > 0) {
                    const records = trainingCpdData[categoryId].records;
                    
                    // Generate table HTML
                    let tableHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 cpdTable" id="recordCpdTable_${categoryId}" style="width:100%">
                                        <thead style="font-size:x-small;">
                                            <tr>
                                                <th>S/NO</th>
                                                <th>Trade</th>
                                                <th>Description</th>
                                                <th>Duration</th>
                                                <th>Category</th>
                                                <th>File</th>
                                                <th>View</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:x-small;">
                    `;
                    
                    // Add table rows
                    records.forEach((record, index) => {
                        tableHtml += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-light-blue'}">
                                <td><strong>${escapeHtml(record.sno)}</strong></td>
                                <td>${escapeHtml(record.trade || 'N/A')}</td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="${escapeHtml(record.description)}">
                                        ${escapeHtml(record.description.substring(0, 80))}${record.description.length > 80 ? '...' : ''}
                                    </span>
                                </td>
                                <td>${escapeHtml(record.duration || 'N/A')}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        ${escapeHtml(record.category_name || 'N/A')}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                        `;
                        
                        if (record.file_path) {
                            const pdfUrl = `/qai/assets/pdfjs/web/viewer.html?file=${encodeURIComponent('/qai/admin/action/' + record.file_path)}`;
                            tableHtml += `
                                <a href="${pdfUrl}"
                                    class="btn btn-view-pdf btn-sm view-pdf-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pdfModal"
                                    data-pdf-url="${pdfUrl}">
                                    View PDF
                                </a>
                            `;
                        } else {
                            tableHtml += `
                                <button class="btn btn-sm btn-secondary" disabled title="No file available">
                                    View PDF
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
                                        data-record-type="training_cpd"
                                        data-record-id="${record.id || ''}"
                                        data-record-sno="${escapeHtml(record.sno || '')}"
                                        data-record-trade="${escapeHtml(record.trade || '')}"
                                        data-record-description="${escapeHtml(record.description || '')}"
                                        data-record-duration="${escapeHtml(record.duration || '')}"
                                        data-record-category="${escapeHtml(record.category_name || '')}"
                                        data-record-file-path="${escapeHtml(record.file_path || '')}">
                                        View Details
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
                    
                    // Initialize DataTable
                    setTimeout(() => {
                        const tableId = `recordCpdTable_${categoryId}`;
                        if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                            $('#' + tableId).DataTable(cpdDataTableConfig);
                        }
                        
                        // Initialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 100);
                    
                } else {
                    // No records found for this category
                    tableContainer.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No CPD records found for <strong>${categoryName}</strong>.
                        </div>
                    `;
                }
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

            // Initialize tables on page load for visible tabs
            setTimeout(() => {
                initializeSyllabusTables();
            }, 500);

            // Reinitialize tables when tabs are shown
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).data('bs-target');
                console.log('Tab shown:', target);
                
                // Small delay to ensure tab content is fully rendered
                setTimeout(() => {
                    initializeSyllabusTables();
                    
                    // Initialize Training Record CPD category filter if we're in the CPD tab
                    if (target === '#record_cpd') {
                        setupRecordCpdCategoryFilter();
                    }
                    
                    // Adjust DataTables after tab change
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                }, 300);
            });

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
                        case "training_cpd":
                            title = "CPD Training Syllabus Details";
                            break;
                    }
                    detailsModalTitle.textContent = title;

                    let content = "";
                    switch (recordType) {
                        case "training_syllabus":
                            content = generateTrainingSyllabusDetails(button);
                            break;
                        case "training_cpd":
                            content = generateTrainingCpdDetails(button);
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

                // Create file link if available
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

            function generateTrainingCpdDetails(button) {
                const sno = button.getAttribute("data-record-sno");
                const trade = button.getAttribute("data-record-trade");
                const description = button.getAttribute("data-record-description");
                const duration = button.getAttribute("data-record-duration");
                const category = button.getAttribute("data-record-category");
                const filePath = button.getAttribute("data-record-file-path");

                // Create file link if available
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

            // Navigation and tab handling
            const welcomePane = document.querySelector("#welcome");
            if (welcomePane) {
                welcomePane.classList.add("show", "active");
            }

            // Set initial active states
            document.querySelectorAll(".nav-link, .qa-dropdown-item").forEach((item) => {
                item.classList.remove("active");
            });

            // Set Training Forecast as active by default
            const forecastToggle = document.querySelector('.qa-dropdown-toggle[role="button"]');
            const forecastItem = document.querySelector('.qa-dropdown-item[data-bs-target="#forecast"]');
            
            if (forecastToggle && forecastItem) {
                forecastToggle.classList.add('active');
                forecastItem.classList.add('active');
                
                // Show the dropdown menu for active state
                const dropdownMenu = forecastToggle.nextElementSibling;
                if (dropdownMenu) {
                    dropdownMenu.classList.add('show');
                }
            }

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

                    // Close all other dropdowns
                    dropdownToggles.forEach((otherToggle) => {
                        if (otherToggle !== toggle) {
                            const otherMenu = otherToggle.nextElementSibling;
                            if (otherMenu) otherMenu.classList.remove("show");
                        }
                    });

                    // Toggle current dropdown
                    if (!isCurrentlyOpen) {
                        dropdownMenu.classList.add("show");
                    } else {
                        dropdownMenu.classList.remove("show");
                    }
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
                    
                    // Reinitialize DataTables for the new tab
                    setTimeout(() => {
                        initializeSyllabusTables();
                        if (targetId === '#record_cpd') {
                            setupRecordCpdCategoryFilter();
                            
                            // Reset category filter when switching to CPD tab
                            const categoryFilter = document.getElementById('recordCpdCategoryFilter');
                            if (categoryFilter) {
                                categoryFilter.value = '';
                                const tableContainer = document.getElementById('recordCpdTableContainer');
                                tableContainer.innerHTML = `
                                    <div>
                                        <!--<i class="fas fa-info-circle me-2"></i>
                                        Please select a category to view CPD training records.-->
                                    </div>
                                `;
                            }
                        }
                    }, 300);
                });
            });

            // Close dropdowns on outside click
            document.addEventListener("click", function(e) {
                if (!e.target.closest(".qa-dropdown")) {
                    document.querySelectorAll(".qa-dropdown-menu").forEach((menu) => {
                        menu.classList.remove("show");
                    });
                }
            });

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

            // Handle window resize for DataTables responsiveness
            $(window).on('resize', function() {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
            });

            console.log("Training page initialization complete");
        });
    </script>
</body>
</html>