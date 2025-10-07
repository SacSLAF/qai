<?php include '../template/head.php'; 
require_once "../includes/config.php";
?>

<head>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .dropdown-menu {
            border: 1px solid #0d6efd;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.1);
        }
    </style>
</head>
<body>
    <?php include '../template/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5 pt-5">
        <div class="page-header mb-4">
            <h3 class="colour-defult">Services<i class="fa fa-tasks ms-2"></i>
                <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>
        
        <div class="row">
            <!-- Left Side Navigation -->
            <div class="col-lg-3 col-xl-2 mb-4">
                <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                    <!-- QA Audits with dropdown -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="qaAuditsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            QA Audits
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="qaAuditsDropdown">
                            <li><a class="dropdown-item" id="auditplan-tab" data-bs-toggle="pill" href="#auditplan" role="tab">Audit Plans</a></li>
                            <li><a class="dropdown-item" id="auditreport-tab" data-bs-toggle="pill" href="#auditreport" role="tab">Audit Reports</a></li>
                            <li><a class="dropdown-item" id="checklist-tab" data-bs-toggle="pill" href="#checklist" role="tab">Audit Checklist</a></li>
                        </ul>
                    </div>
                    
                    <a class="nav-link" id="competency-tab" data-bs-toggle="pill" href="#competency" role="tab">Aircraft Competency</a>
                    <a class="nav-link" id="latitudes-tab" data-bs-toggle="pill" href="#latitudes" role="tab">Latitudes & Extensions</a>
                    <a class="nav-link" id="modifications-tab" data-bs-toggle="pill" href="#modifications" role="tab">Modifications R&D Projects</a>
                    <a class="nav-link" id="emission-tab" data-bs-toggle="pill" href="#emission" role="tab">Vehicle Emission Test</a>
                </div>
            </div>

            <!-- Right Side Content -->
            <div class="col-lg-9 col-xl-10">
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- Tab: Audit Plan -->
                    <div class="tab-pane fade" id="auditplan" role="tabpanel">
                        <?php echo generateServiceCards('services-auditplan.php'); ?>
                    </div>
                    
                    <!-- Tab: Audit Reports -->
                    <div class="tab-pane fade" id="auditreport" role="tabpanel">
                        <?php echo generateServiceCards('services-auditreport.php'); ?>
                    </div>
                    
                    <!-- Tab: Audit Checklist -->
                    <div class="tab-pane fade" id="checklist" role="tabpanel">
                        <?php echo generateServiceCards('services-checklist.php'); ?>
                    </div>
                    
                    <!-- Tab: Aircraft Competency -->
                    <div class="tab-pane fade show active" id="competency" role="tabpanel">
                        <?php include 'ac.php'; ?>
                    </div>
                    
                    <!-- Tab: Latitudes & Extensions -->
                    <div class="tab-pane fade" id="latitudes" role="tabpanel">
                        <?php include 'latitudes.php'; ?>
                    </div>
                    
                    <!-- Tab: Modifications R&D Projects -->
                    <div class="tab-pane fade" id="modifications" role="tabpanel">
                        <?php include 'mod.php'; ?>
                    </div>
                    
                    <!-- Tab: Vehicle Emission Test -->
                    <div class="tab-pane fade" id="emission" role="tabpanel">
                        <?php include 'veh.php'; ?>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    </main>

    <?php
    function generateServiceCards($baseUrl) {
        $sections = [
            'Aeronautical Engineering' => ['img' => 'AE.png'],
            'Air Operations' => ['img' => 'AO.png'],
            'Construction Engineering' => ['img' => 'CE.png'],
            'Electronics Engineering' => ['img' => 'EE.png'],
            'General Engineering' => ['img' => 'GE.png'],
            'Ground Operations' => ['img' => 'GO.png'],
            'Productivity Management' => ['img' => 'PEM.png'],        
            'Training' => ['img' => 'training.png'],
        ];
        
        $html = '<div class="row text-center">';
        
        foreach ($sections as $section => $data) {
            $html .= '
            <div class="col-md-3 mb-4">
                <a href="'.$baseUrl.'?section='.urlencode($section).'" class="card-link text-decoration-none">
                    <div class="card hover-effect border border-2 border-primary bg-white" style="height:60px" >
                        <div class="card-body text-center p-4">
                            <h6 class="mb-0 text-dark fw-semibold">'.$section.'</h6>
                        </div>
                    </div>
                </a>
            </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    include '../template/foot.php';
    ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="..node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle URL hash for direct tab access
        document.addEventListener("DOMContentLoaded", function() {
            var hash = window.location.hash;
            if (hash) {
                var tabTrigger = document.querySelector('a[href="' + hash + '"]');
                if (tabTrigger) {
                    var tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }
            
            // Update URL hash when tabs are shown
            var tabEls = document.querySelectorAll('a[data-bs-toggle="pill"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (e) {
                    history.replaceState(null, null, e.target.getAttribute('href'));
                });
            });
            
            // Activate the first QA Audit dropdown option by default if in QA section
            if (window.location.hash === '#auditplan' || window.location.hash === '#auditreport' || window.location.hash === '#checklist') {
                document.querySelector('#qaAuditsDropdown').classList.add('active');
            }
        });
    </script>
</body>
</html>