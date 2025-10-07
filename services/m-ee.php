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
                <li class="breadcrumb-item "><a href="modification.php">Modifications - R&D</a></li>
                <li class="breadcrumb-item active">Electronic Engineering</a></li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <h3 class="colour-defult">Electronic Engineering<i class="fas fa-book"></i>
               <!-- <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>-->
            </h3>
        </div>

            <div class="row text-center " style="justify-content: center;">
                    <!-- Electronic Section at Academy / Base / Station -->
                    <div class="col-md-3 col-sm-2 mb-4" style="height: 100px;">
                        <a href="m-ae.php" class="card-link text-decoration-none w-100">
                            <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                                <div class="card-body text-center p-4">
                                <!-- <i class="fas fa-globe fs-1 text-primary mb-3"></i>-->
                                    <h5 class="mb-0 text-dark fw-semibold">Avionics</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Maintenance Programme Card -->
                    <div class="col-md-3 col-sm-2 mb-4">
                        <a href="m-ge.php" class="card-link text-decoration-none w-100">
                            <div class="card hover-effect border border-2 border-primary bg-white h-100" style=" width: 250px;">
                                <div class="card-body text-center p-4">
                                    <!--<i class="fas fa-cogs fs-1 text-primary mb-3"></i>-->
                                    <h5 class="mb-0 text-dark fw-semibold">Radar System</h5>
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