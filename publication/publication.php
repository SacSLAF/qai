<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/config.php";

// Check if a PDF file is requested
$show_pdf = false;
$pdf_file = '';
$pdf_web_path = '';
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

// Check database connection
if (!isset($db) || !$db || (property_exists($db, 'connect_error') && $db->connect_error)) {
    $ad_bulletins_error = "Database connection failed: " . ($db->connect_error ?? 'Unknown error');
    $qai_newsletters_error = $ad_bulletins_error;
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
        ORDER BY qn.issue_date DESC, qn.qsn_no DESC
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../assets/datatable/datatable.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* .tab-content {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        } */
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
                <div class="nav-column">
                    <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">

                    <a class="nav-link" data-bs-target="#ac" role="tab">Online Subscription</a>
                    <a class="nav-link" data-bs-target="#ad" role="tab">ADs & Bulletins</a>
                    <a class="nav-link" data-bs-target="#qai_news" role="tab">QAI Safety Newsletters</a>

                        <!-- Maintenance Program Dropdown -->
                        <div class="qa-dropdown">
                            <a class="nav-link qa-dropdown-toggle" role="button">Maintenance Program</a>
                            <div class="qa-dropdown-menu">
                                <a class="qa-dropdown-item " data-bs-target="#audits_plan" role="tab">Servicing Schedule</a>
                                <a class="qa-dropdown-item" data-bs-target="#audit_check_list" role="tab">Worksheet</a>
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
                                            <table class="table table-striped table-hover mb-0" id="adBulletinsTable">
                                                <thead>
                                                    <tr>
                                                        <th>Reference No</th>
                                                        <th>Bulletin Description</th>
                                                        <th>Related Aircraft</th>
                                                        <th>Formation</th>
                                                        <th>Date of Issue</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ad_bulletins as $bulletin): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($bulletin['reference_no']) ?></strong></td>
                                                            <td class="text-truncate-multiline" title="<?= htmlspecialchars($bulletin['bulletin_description']) ?>">
                                                                <?= htmlspecialchars($bulletin['bulletin_description']) ?>
                                                            </td>
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
                                                                    View Details
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
                                            <table class="table table-striped table-hover mb-0" id="qaiNewslettersTable">
                                                <thead>
                                                    <tr>
                                                        <th>QSN No</th>
                                                        <th>Description</th>
                                                        <th>Issue Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($qai_newsletters as $newsletter): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($newsletter['qsn_no']) ?></strong></td>
                                                            <td class="text-truncate-multiline" title="<?= htmlspecialchars($newsletter['description']) ?>">
                                                                <?= htmlspecialchars($newsletter['description']) ?>
                                                            </td>
                                                            <td><?= $newsletter['issue_date'] ? date('M d, Y', strtotime($newsletter['issue_date'])) : 'N/A' ?></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <?php if (!empty($newsletter['file_path'])): ?>
                                                                        <a href="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $newsletter['file_path']) ?>"
                                                                            class="btn btn-view-pdf btn-sm view-pdf-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#pdfModal"
                                                                            data-pdf-url="/qai/assets/pdfjs/web/viewer.html?file=<?= urlencode('/qai/admin/action/' . $newsletter['file_path']) ?>">
                                                                            <i class="fas fa-file-pdf me-1"></i>View PDF
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <button class="btn btn-view-details btn-sm view-details-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#detailsModal"
                                                                        data-record-type="qai_newsletter"
                                                                        data-record-id="<?= $newsletter['id'] ?? '' ?>"
                                                                        data-record-qsn-no="<?= htmlspecialchars($newsletter['qsn_no'] ?? '') ?>"
                                                                        data-record-description="<?= htmlspecialchars($newsletter['description'] ?? '') ?>"
                                                                        data-record-issue-date="<?= htmlspecialchars($newsletter['issue_date'] ?? '') ?>">
                                                                        View Details
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

                    <!-- Maintenance tab panes -->
                    <div class="tab-pane fade" id="audits_plan" role="tabpanel">
                        <h4 class="colour-defult">Servicing Schedule</h4>
                    </div>

                    <div class="tab-pane fade" id="audit_check_list" role="tabpanel">
                        <h4 class="colour-defult">Worksheet</h4>
                    </div>

                    <!-- Technical Library Tab -->
                    <div class="tab-pane fade show" id="vehicle" role="tabpanel">
                        <?php if ($show_pdf): ?>
                            <div class="top-bar">
                                <a href="?file=<?= $default_file ?>" class="btn btn-sm btn-dark">Technical Library Document</a>
                            </div>
                        <?php endif; ?>
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
                                                <table class="table table-striped table-hover mb-0" id="adBulletinsTable">
                                                    <thead style="font-size:x-small;">
                                                        <tr>
                                                            <th>Reference No</th>
                                                            <th>Bulletin Description</th>
                                                            <th>Related Aircraft</th>
                                                            <th>Formation</th>
                                                            <th>Date of Issue</th>
                                                            <!-- <th>Actions</th>-->
                                                        </tr>
                                                    </thead>
                                                    <tbody style="font-size:x-small;">
                                                        <?php foreach ($ad_bulletins as $bulletin): ?>
                                                            <tr>
                                                                <td><strong><?= htmlspecialchars($bulletin['reference_no']) ?></strong></td>
                                                                <td class="text-truncate-multiline" title="<?= htmlspecialchars($bulletin['bulletin_description']) ?>">
                                                                    <?= htmlspecialchars($bulletin['bulletin_description']) ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($bulletin['type_name'] ?? 'N/A') ?></td>
                                                                <td><?= htmlspecialchars($bulletin['formation_name'] ?? 'N/A') ?></td>
                                                                <td><?= $bulletin['date_of_issue'] ? date('M d, Y', strtotime($bulletin['date_of_issue'])) : 'N/A' ?></td>
                                                                <!--<td>
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
                                                                    View Details
                                                                </button>
                                                            </td>-->
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
                        <div class="tab-pane fade" id="latitude" role="tabpanel">
                            <h4 class="colour-defult">QAI Safety Newsletters</h4>
                            <div>
                                <?php include 'qai_news.php' ?>
                            </div>
                        </div>

                        <!-- Maintenance tab panes -->
                        <div class="tab-pane fade" id="audits_plan" role="tabpanel">
                            <h4 class="colour-defult">Servicing Schedule</h4>
                        </div>

                        <div class="tab-pane fade" id="audit_check_list" role="tabpanel">
                            <h4 class="colour-defult">Worksheet</h4>
                        </div>

                        <!-- Technical Library Tab -->
                        <div class="tab-pane fade show" id="vehicle" role="tabpanel">
                            <?php if ($show_pdf): ?>
                                <div class="top-bar">
                                    <a href="?file=<?= $default_file ?>" class="btn btn-sm btn-dark">Technical Library Document</a>
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
                                    <p>No technical library document is available at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- </div> -->
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
                    <h5 class="modal-title" id="detailsModalTitle">AD Bulletin Details</h5>
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
                        case 'ad_bulletin': title = 'AD Bulletin Details'; break;
                        case 'qai_newsletter': title = 'QAI Safety Newsletter Details'; break;
                        default: title = 'Record Details';
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
                const filePath = button.getAttribute('data-record-file-path');
                const createdAt = button.getAttribute('data-record-created-at');
                const updatedAt = button.getAttribute('data-record-updated-at');

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
                    </table>
                `;
            }

            // Navigation and tab handling
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

            // Dropdown toggle handling
            const dropdownToggles = document.querySelectorAll('.qa-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    if (!dropdownMenu) return;

                    const isCurrentlyOpen = dropdownMenu.classList.contains('show');

                    dropdownToggles.forEach(otherToggle => {
                        const otherMenu = otherToggle.nextElementSibling;
                        if (otherMenu) otherMenu.classList.remove('show');
                    });

                    if (!isCurrentlyOpen) dropdownMenu.classList.add('show');
                });
            });

            // Main nav links
            const mainNavLinks = document.querySelectorAll('.nav-link:not(.qa-dropdown-toggle)');
            mainNavLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                    this.classList.add('active');

                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    if (targetPane) targetPane.classList.add('show', 'active');
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                });
            });

            // Dropdown items
            const dropdownItems = document.querySelectorAll('.qa-dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    document.querySelectorAll('.nav-link, .qa-dropdown-item').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    document.querySelectorAll('.qa-dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                    this.classList.add('active');

                    if (this.classList.contains('qa-dropdown-item')) {
                        const parentDropdown = this.closest('.qa-dropdown');
                        if (parentDropdown) {
                            const dropdownToggle = parentDropdown.querySelector('.qa-dropdown-toggle');
                            if (dropdownToggle) {
                                dropdownToggle.classList.add('active');
                                const dropdownMenu = dropdownToggle.nextElementSibling;
                                if (dropdownMenu) dropdownMenu.classList.add('show');
                            }
                        }
                    }

                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    if (targetPane) targetPane.classList.add('show', 'active');
                });
            });

            // Close dropdowns on outside click
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.qa-dropdown')) {
                    document.querySelectorAll('.qa-dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
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
                }
            });

            console.log('Publication page initialization complete');
        });

    </script>
</body>
</html>