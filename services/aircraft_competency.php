<?php include '../template/head.php'; ?>
<head>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .breadcrumb {
            margin-top: 20px;
            background-color: #e2edfbff;
            padding: 8px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .breadcrumb a {
            color: #0d6efd;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        :root {
            --primary-color: #1a4f72;
            --secondary-color: #ffcc00;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
            line-height: 1.6;
        }
        
        .colour-defult {
            color: var(--primary-color) !important;
        }
        
        .page-header {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .nav-pills .nav-link {
            color: #495057;
            border-radius: 0;
            padding: 12px 20px;
            margin-bottom: 8px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .nav-pills .nav-link.active, 
        .nav-pills .nav-link:hover {
            background-color: rgba(26, 79, 114, 0.1);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
        
        .tab-content {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%; /* Ensure cards take full height of their container */
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 50px; /* Set a minimum height for consistency */
        }
        
        /* Ensure all cards are the same height */
        .card-link {
            height: 100%;
            display: block;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .nav-pills {
                flex-direction: row !important;
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 10px;
            }
            
            .nav-pills .nav-item {
                display: inline-block;
                float: none;
            }
            
            .nav-pills .nav-link {
                border-left: none;
                border-bottom: 3px solid transparent;
                margin-bottom: 0;
                margin-right: 5px;
            }
            
            .nav-pills .nav-link.active, 
            .nav-pills .nav-link:hover {
                border-left: none;
                border-bottom: 3px solid var(--primary-color);
            }
        }
        
        @media (max-width: 768px) {
            .page-header h3 {
                font-size: 1.5rem;
            }
            
            .card-body {
                min-height: 80px; /* Slightly smaller on mobile */
            }
        }
    </style>
</head>
<body>
    <?php include '../template/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5 pt-5">

    <!-- Breadcrumb Navigation -->
                
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="services.php">Services</a></li>
                <li class="breadcrumb-item active" aria-current="page">Aircraft Competency</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <h3 class="colour-defult">Aircraft Competency<i class="fa fa-tasks"></i>
                <!--<div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>-->
            </h3>
        </div>
        
                <div class="row text-center " style="justify-content: center;">
                    <!-- Electronic Section at Academy / Base / Station -->
                    <div class="col-md-3 col-sm-2 mb-4" style="height: 100px;">
                        <a href="ac-ae-aircraft_type.php" class="card-link text-decoration-none w-100">
                            <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                                <div class="card-body text-center p-4">
                                <!-- <i class="fas fa-globe fs-1 text-primary mb-3"></i>-->
                                    <h5 class="mb-0 text-dark fw-semibold">Aeronautical Engineering</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Maintenance Programme Card -->
                    <div class="col-md-3 col-sm-2 mb-4">
                        <a href="ac-ee.php" class="card-link text-decoration-none w-100">
                            <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                                <div class="card-body text-center p-4">
                                    <!--<i class="fas fa-cogs fs-1 text-primary mb-3"></i>-->
                                    <h5 class="mb-0 text-dark fw-semibold">Electronics Engineering</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Technical Library Card -->
                    <div class="col-md-3 col-sm-2 mb-4">
                        <a href="ac-ge-aircraft_type.php" class="card-link text-decoration-none w-100" >
                            <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                                <div class="card-body text-center p-4">
                                    <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                                    <h5 class="mb-0 text-dark fw-semibold">General Engineering</h5>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
                <hr>
    </main>

    <?php
    
    include '../template/footer.php';
    include '../template/foot.php';
    ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    
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
        });
    </script>
</body> 
</html>