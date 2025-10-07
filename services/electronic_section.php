<?php include '../template/head.php'; ?>
<head>
    <link rel="stylesheet" href="../assets/css/audit_card.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
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

        .card {
            height: 60px;
            transition: all 0.3s ease;
            border: 2px solid #0d6efd;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                <li class="breadcrumb-item "><a href="qa_audits.php">QA Audits</a></li>
                <li class="breadcrumb-item"><a href="ee.php">Electronic Engineering</a></li>
                <li class="breadcrumb-item active">Electronic Sections</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <h3 class="colour-defult">Electronic Section at Academy / Base / Station<i class="fas fa-book"></i>
               <!-- <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>-->
            </h3>
        </div>
        
        <div class="row text-center " style="justify-content: center;">
            <!-- Electronic Section at Academy / Base / Station -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="online_sub.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                           <!-- <i class="fas fa-globe fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Academy CBY</h5>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Flight Squadron Card -->
            <div class="col-md-3 col-sm-2 mb-4 ">
                <a href="ad_bulletins.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!-- <i class="fas fa-file-alt fs-1 text-primary mb-3"></i>-->
                           <h5 class="mb-0 text-dark fw-semibold">SLAF Base ANU</h5>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Air Defense Radar Squadron Card -->
            <div class="col-md-3 col-sm-2 mb-4">
                <a href="qai_news.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                           <!-- <i class="fas fa-newspaper fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Base HIN</h5>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Maintenance Programme Card -->
            <div class="col-md-3 col-sm-2 mb-4">
                <a href="maintenace.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-cogs fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Base VNA</h5>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100" >
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn CHO</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn WLA</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn KGL</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn KTK</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn PLV</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn PLY</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn IRM</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn BCL</h5>
                        </div>
                    </div>
                </a>
            </div>

                        <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn PGL</h5>
                        </div>
                    </div>
                </a>
            </div>

                        <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF Stn DIA</h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF PTS AMP</h5>
                        </div>
                    </div>
                </a>
            </div>

                        <!-- Technical Library Card -->
            <div class="col-md-3 col-sm-2 mb-4" style="height: 80px;">
                <a href="tech_lib.php" class="card-link text-decoration-none w-100">
                    <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                        <div class="card-body text-center p-4">
                            <!--<i class="fas fa-book-open fs-1 text-primary mb-3"></i>-->
                            <h5 class="mb-0 text-dark fw-semibold">SLAF PTS MIR</h5>
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
</body>
</html>