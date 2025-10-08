<?php include '../template/head.php'; ?>
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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
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
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .shadow-card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .staff-card {
            height: 100%;
        }

        .staff-card img {
            border-radius: 5px;
            object-fit: cover;
        }

        .achievement-card {
            height: 100%;
        }

        .achievement-card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
        }

        .history-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .history-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .history-table tr:hover {
            background-color: rgba(26, 79, 114, 0.05);
        }

        .alert-info {
            background-color: rgba(26, 79, 114, 0.1);
            border-color: rgba(26, 79, 114, 0.2);
            color: var(--primary-color);
        }

        .policy-statement {
            font-style: italic;
            text-align: center;
            padding: 30px;
            background-color: rgba(26, 79, 114, 0.05);
            border-radius: 8px;
        }

        #orgImage {
            cursor: zoom-in;
            transition: transform 0.3s;
        }

        #orgImage:hover {
            transform: scale(1.02);
        }

        #swiperOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .mySwiper {
            width: 90%;
            height: 90%;
        }

        #closeSwiper {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            z-index: 10000;
        }

        .td {
            color: #1a4f72;
        }

        /* Responsive adjustments */

        @media (max-width: 768px) {
            .page-header h3 {
                font-size: 1.5rem;
            }

            .policy-statement {
                padding: 20px;
            }

            .history-table {
                font-size: 0.9rem;
            }

            .history-table th,
            .history-table td {
                padding: 8px 10px;
            }
        }
    </style>
    <style>
        body {
            font-family: Verdana, sans-serif;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        /* .row > .column {
        padding: 0 8px;
      }

      .row:after {
        content: "";
        display: table;
        clear: both;
      }

      .column {
        float: left;
        width: 25%;
      } */

        /* The Modal (background) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: black;
        }

        /* Modal Content */
        .modal-content {
            position: relative;
            background-color: #fefefe;
            margin: auto;
            padding: 0;
            width: 90%;
            max-width: 330px;
        }

        /* The Close Button */
        .close {
            color: white;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 35px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #999;
            text-decoration: none;
            cursor: pointer;
        }

        .mySlides {
            display: none;
        }

        .cursor {
            cursor: pointer;
        }

        /* Next & previous buttons */
        .prev,
        .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -50px;
            color: white;
            font-weight: bold;
            font-size: 20px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Position the "next button" to the right */
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        /* On hover, add a black background color with a little bit see-through */
        .prev:hover,
        .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        /* Number text (1/3 etc) */
        .numbertext {
            color: #f2f2f2;
            font-size: 12px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
        }

        img {
            margin-bottom: -4px;
        }

        .caption-container {
            text-align: center;
            background-color: black;
            padding: 2px 16px;
            color: white;
        }

        .demo {
            opacity: 0.6;
        }

        .active,
        .demo:hover {
            opacity: 1;
        }

        img.hover-shadow {
            transition: 0.3s;
        }

        .hover-shadow:hover {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2),
                0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
    </style>
</head>

<body>
    <!-- Header -->


    <?php include '../template/header.php'; ?>

    <!-- Main Content -->
    <main class="container-fluid my-3 pt-3">
        <!-- <div class="page-header mb-4">
            <h3 class="colour-defult">The Inspectorate <i class="fa fa-bullhorn"></i>
                <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>-->

        <div class="row">
            <!-- Navigation Tabs -->
            <div class="col-lg-3 col-xl-2 mb-4">
                <div class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist">
                    <!-- <a class="nav-link" href="#" data-bs-target="#welcome" role="tab">Welcome</a> -->
                    <a class="nav-link" href="#" data-bs-target="#org" role="tab">About us</a>
                    <a class="nav-link" href="#" data-bs-target="#functions" role="tab">Policy</a>
                    <a class="nav-link" href="#" data-bs-target="#structure" role="tab">Structure</a>
                    <a class="nav-link" href="#" data-bs-target="#responsibilities" role="tab">Staff</a>
                    <a class="nav-link" href="#" data-bs-target="#history" role="tab">History</a>
                    <a class="nav-link" href="#" data-bs-target="#achievements" role="tab">Achievements</a>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="col-lg-9 col-xl-10">
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- Welcome Screen (shown by default) -->
                    <div class="tab-pane fade show active" id="welcome" role="tabpanel">
                        <div class="welcome-message">
                            <img src="../assets/images/qai-welcome.jpg" alt="Quality Assurance Inspectorate" class="welcome-image">
                            <h4>Welcome to Command Quality Assurance Inspectorate</h4>
                            <p>Please select an option from the navigation menu to view the content.</p>
                        </div>
                    </div>
                    <!-- About Us -->
                    <div class="tab-pane fade" id="org" role="tabpanel" style="color:#1a4f72;">
                        <p>The Sri Lanka Air Force (SLAF), in its commitment to fulfil the aspirations of the Nation by achieving excellence in the field of aviation and in all associated functions, is continually striving to enhance customer satisfaction by exceeding stake holders' expectations whilst endeavouring to be efficient, reliable and socially responsible.</p>
                        <p>Towards this end, the Quality Assurance Inspectorate (QAI) was established to perform Quality Assurance Functions within the SLAF. The QAI functions under supervision of the Chief of Staff of the Sri Lanka Air Force and is headed by the Director Quality Assurance (DQA) who is primarily tasked to provide advice on the Quality Assurance Services (QAS) matters such as Policy, Organization and Administration.</p>

                        <h5 class="mt-4 mb-3">Main Functions</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item" style="color:#1a4f72;">Administration of the Quality Assurance Services (QAS) in the SLAF</li>
                            <li class="list-group-item" style="color:#1a4f72;">Provide an organization to assess the quality of material, processors and workmanship for the SLAF</li>
                            <li class="list-group-item" style="color:#1a4f72;">Ensure that the service operates efficiently and economically to assist the completion of the task in accordance with the policy directives</li>
                            <li class="list-group-item" style="color:#1a4f72;">Provision of adequate facilities for the quality assurance activities and ensure that inspection facilities under QAS control are properly authorized, operated and maintained</li>
                            <li class="list-group-item" style="color:#1a4f72;">Ensure that SLAF personnel employed on quality assurance duties are trained</li>
                            <li class="list-group-item" style="color:#1a4f72;">Conduct awareness training on quality to all SLAF personnel within the organization scope and with assistance of outside organization</li>
                            <li class="list-group-item" style="color:#1a4f72;">Control of technical publication management system</li>
                        </ul>
                    </div>

                    <!-- Policy -->
                    <div class="tab-pane fade" id="functions" role="tabpanel">
                        <div class="policy-statement">
                            <p class="lead">"The Sri Lanka Air Force is committed to fulfil the aspirations of the Nation by achieving excellence in the field of aviation and in all associated functions through enhanced customer satisfaction and by exceeding stake holders' expectations, whilst endeavouring to be an efficient, reliable and socially responsible Air Force"</p>
                        </div>
                    </div>

                    <!-- Structure -->
                    <div class="tab-pane fade" id="structure" role="tabpanel">
                        <div class="text-center">
                            <img id="orgImage" class="img-fluid rounded shadow" src="../assets/img/about/org.jpg" alt="Organization Chart">
                            <p class="text-muted mt-2">Click on the image to zoom</p>
                        </div>

                        <!-- Fullscreen Swiper -->
                        <div id="swiperOverlay">
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <div class="swiper-zoom-container">
                                            <img src="../assets/img/about/org.jpg" alt="Organization Chart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="closeSwiper">&times;</div>
                        </div>
                    </div>

                    <!-- Staff -->
                    <div class="tab-pane fade" id="responsibilities" role="tabpanel">
                        <!-- Director -->
                        <div class="mb-5">
                            <div class="alert alert-info" style="height: 40px;">
                                <h6 class="text-center mb-0">DIRECTOR QUALITY ASSURANCE</h6>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-md-6 col-lg-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <img width="200" height="250" src="../assets/img/staff/cdr.jpg" alt="DIRECTOR QUALITY ASSURANCE" class="img-fluid mb-3">
                                            <p class="mb-1">Air Cdre MF Jansen</p>
                                            <p class="mb-0" style="font-size:11px"><strong>Contact:</strong> 077-2229145 / 11100 </p>
                                            <p class="mb-1" style="font-size:11px">dqa@airforce.lk</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Assurance Staff -->
                        <div class="mb-5">
                            <div class="alert alert-info" style="height: 40px;">
                                <h6 class="text-center mb-0">QUALITY ASSURANCE STAFF OFFICERS</h6>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">SOQA(AE)</h6>
                                            <img width="200" height="250" src="../assets/img/staff/of1.jpg" alt="SOQA(AERO ENG-FW/RW)" class="img-fluid mb-3">
                                            <p class="mb-1">Sqn Ldr WPAC Dayaratne</p>
                                            <p class="mb-1" style="font-size:11px"><strong>Contact:</strong>077-2229145 / 11115</p>
                                            <p class="mb-1" style="font-size:11px">soqa.ae@airforce.lk</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">SOQA(EE)</h6>
                                            <img width="200" height="250" src="../assets/img/staff/of4.jpg" alt="SOQA(E/E&T ENG)" class="img-fluid mb-3">
                                            <p class="mb-1">Sqn Ldr UJC Kumara</p>
                                            <p class="mb-1" style="font-size:11px"><strong>Contact:</strong> 077-2229142 / 11116</p>
                                            <p class="mb-1" style="font-size:11px">soqa.ee@airforce.lk</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">SOQA(GE)</h6>
                                            <img width="200" height="250" src="../assets/img/staff/officer3.jpg" alt="SOQA(GE)" class="img-fluid mb-3">
                                            <p class="mb-1">Sqn Ldr THDM Hettige</p>
                                            <p class="mb-1" style="font-size:11px"><strong>Contact:</strong> 077-2229165 / 11117</p>
                                            <p class="mb-1" style="font-size:11px">soqa.ge@airforce.lk</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title">SOQA(TRG & PUG)</h6>
                                            <img width="200" height="250" src="../assets/img/staff/of2.jpg" alt="SOQA(TRG & PUG)" class="img-fluid mb-3">
                                            <p class="mb-1">Sqn Ldr MMDC MORAYAS</p>
                                            <p class="mb-1" style="font-size:11px"><strong>Contact:</strong> 0778829671 / 11119</p>
                                            <p class="mb-1" style="font-size:11px">soqa.trg@airforce.lk<br>soqa.osh@airforce.lk</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Assurance WO I/C -->
                        <div>
                            <div class="alert alert-info" style="height: 40px;">
                                <h6 class="text-center mb-0">QUALITY ASSURANCE WO I/C</h6>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-md-6 col-lg-3">
                                    <div class="card staff-card text-center">
                                        <div class="card-body">
                                            <img width="200" height="250" src="../assets/img/staff/of3.jpg" alt="QUALITY ASSURANCE WO I/C" class="img-fluid mb-3">
                                            <p class="mb-1">MWO RGD ARIYARATHNA</p>
                                            <p class="mb-1" style="font-size:11px"><strong>Contact:</strong> 0707909422 / 11151</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History -->
                    <div class="tab-pane fade" id="history" role="tabpanel" style="color:#1a4f72;">
                        <p>The Quality Assurance Inspectorate (QAI) was first established at SLAF Base Rma as the Command Quality Assurance Inspectorate (CQAI) in year 1988 under the command of Wg Cdr GY De Silva on the aim of leading/guiding SLAF on Quality and Productivity.</p>
                        <p>Subsequently the Inspectorate was shifted to the Air Force Headquarters to facilitate its effective operations throughout the full spectrum of SLAF operations and since then the QAI has been commanded by 22 Command Quality Assurance Officers (CQAOs).</p>

                        <h5 class="text-center my-4">COMMAND QUALITY ASSURANCE OFFICERS</h5>

                        <div class="table-responsive">
                            <table class="table table-hover history-table">
                                <thead>
                                    <tr>
                                        <th width="40%">Name</th>
                                        <th width="30%" class="text-center">From</th>
                                        <th width="30%" class="text-center">To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="color:#1a4f72;">Wg Cdr GY De Silva</td>
                                        <td style="color:#1a4f72;" class="text-center">09.03.1988</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.1990</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Sqn Ldr PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.1991</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.1991</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Sqn Ldr SAH Satharasinghe</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.1992</td>
                                        <td style="color:#1a4f72;" class="text-center">14.03.1993</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Sqn Ldr PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">15.03.1993</td>
                                        <td style="color:#1a4f72;" class="text-center">08.07.1996</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Wg Cdr PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">09.07.1996</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.1997</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Sqn Ldr DS Edirisinghe</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.1998</td>
                                        <td style="color:#1a4f72;" class="text-center">01.03.1998</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Wg Cdr KDU Chandrathilala</td>
                                        <td style="color:#1a4f72;" class="text-center">02.03.1998</td>
                                        <td style="color:#1a4f72;" class="text-center">01.12.2001</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt EPB Liyanage</td>
                                        <td style="color:#1a4f72;" class="text-center">02.12.2001</td>
                                        <td style="color:#1a4f72;" class="text-center">14.01.2003</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt EGJP De Silva</td>
                                        <td style="color:#1a4f72;" class="text-center">15.01.2003</td>
                                        <td style="color:#1a4f72;" class="text-center">31.09.2004</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Wg Cdr TKS Peiris</td>
                                        <td style="color:#1a4f72;" class="text-center">01.10.2004</td>
                                        <td style="color:#1a4f72;" class="text-center">10.07.2005</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Wg Cdr WPAK Wijesinghe</td>
                                        <td style="color:#1a4f72;" class="text-center">11.07.2005</td>
                                        <td style="color:#1a4f72;" class="text-center">09.08.2005</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt EPB Liyanage</td>
                                        <td style="color:#1a4f72;" class="text-center">10.08.2005</td>
                                        <td style="color:#1a4f72;" class="text-center">10.02.2006</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt BLW Balasuriya</td>
                                        <td style="color:#1a4f72;" class="text-center">11.02.2006</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.2006</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre BLW Balasuriya</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.2007</td>
                                        <td style="color:#1a4f72;" class="text-center">02.04.2007</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">03.04.2007</td>
                                        <td style="color:#1a4f72;" class="text-center">10.09.2008</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">11.09.2008</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.2009</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">AVM PDJ Kumarasiri</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.2010</td>
                                        <td style="color:#1a4f72;" class="text-center">24.07.2011</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt AH Wijesiri</td>
                                        <td style="color:#1a4f72;" class="text-center">25.07.2011</td>
                                        <td style="color:#1a4f72;" class="text-center">26.08.2012</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt AWE Wijesuriya</td>
                                        <td style="color:#1a4f72;" class="text-center">27.08.2012</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.2012</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre AWE Wijesuriya</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.2013</td>
                                        <td style="color:#1a4f72;" class="text-center">12.08.2013</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Cpt AH Wijesiri</td>
                                        <td style="color:#1a4f72;" class="text-center">13.08.2013</td>
                                        <td style="color:#1a4f72;" class="text-center">31.12.2013</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre AH Wijesiri</td>
                                        <td style="color:#1a4f72;" class="text-center">01.01.2014</td>
                                        <td style="color:#1a4f72;" class="text-center">18.01.2015</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre MD Rathnayake</td>
                                        <td style="color:#1a4f72;" class="text-center">19.01.2015</td>
                                        <td style="color:#1a4f72;" class="text-center">25.07.2016</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre RHKP Ranasinghe</td>
                                        <td style="color:#1a4f72;" class="text-center">26.07.2016</td>
                                        <td style="color:#1a4f72;" class="text-center">10.01.2017</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre TADR Senanayake</td>
                                        <td style="color:#1a4f72;" class="text-center">11.01.2017</td>
                                        <td style="color:#1a4f72;" class="text-center">30.06.2019</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre LMSK Leelaratne</td>
                                        <td style="color:#1a4f72;" class="text-center">01.07.2019</td>
                                        <td style="color:#1a4f72;" class="text-center">20.03.2022</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Gp Capt LMCB Nissanka</td>
                                        <td style="color:#1a4f72;" class="text-center">21.03.2022</td>
                                        <td style="color:#1a4f72;" class="text-center">18.09.2022</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre MPA Mahawattage</td>
                                        <td style="color:#1a4f72;" class="text-center">19.09.2022</td>
                                        <td style="color:#1a4f72;" class="text-center">04.07.2023</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td style="color:#1a4f72;" colspan="3" class="text-center fw-bold py-2">DIRECTOR QUALITY ASSURANCE</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre SPS Martino</td>
                                        <td style="color:#1a4f72;" class="text-center">05.07.2023</td>
                                        <td style="color:#1a4f72;" class="text-center">08.08.2024</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre CJ Hettiarachchi</td>
                                        <td style="color:#1a4f72;" class="text-center">08.08.2024</td>
                                        <td style="color:#1a4f72;" class="text-center">27.05.2025</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#1a4f72;">Air Cdre MF Jansen</td>
                                        <td style="color:#1a4f72;" class="text-center">27.07.2025</td>
                                        <td style="color:#1a4f72;" class="text-center">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <!-- Achievements -->
                    <div class="tab-pane fade" id="achievements" role="tabpanel">
                        <div class="row justify-content-center">

                            <!-- Card 1 -->
                            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2018.jpeg"
                                    data-title="2018 WINNER"
                                    data-description="National Productivity Award 2018">

                                    <img src="../assets/img/achievements/2018.jpeg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2018 WINNER"
                                        style="height:350px; width: 100%"
                                        onclick="openModal();currentSlide(1)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">National Productivity Award 2018</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2014.jpg"
                                    data-title="2014 WINNER"
                                    data-description="Inter Departmental category Organized by national productivity secretariat">

                                    <img src="../assets/img/achievements/2014.jpg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2014 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(2)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">National Productivity Award 2014</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2008.jpeg"
                                    data-title="2008 WINNER"
                                    data-description="Provincial Productivity Award - First Place">

                                    <img src="../assets/img/achievements/2008.jpeg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2008 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(3)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">Provincial Productivity Award 2008</p>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2003.jpeg"
                                    data-title="2003 WINNER"
                                    data-description="National Productivity Award">

                                    <img src="../assets/img/achievements/2003.jpeg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2003 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(4)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">National Productivity Award 2003</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/1996.jpg"
                                    data-title="1996 WINNER"
                                    data-description="Excellence in Quality">

                                    <img src="../assets/img/achievements/1996.jpg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="1996 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(5)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">National Quality Award 1996</p>
                                    </div>
                                </div>
                            </div>


                            <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2024.jpg"
                                    data-title="2024 WINNER"
                                    data-description="ICQCC Awards - Gold Award">

                                    <img src="../assets/img/achievements/2024.jpg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2024 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(6)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">ICQCC Award 2024</p>
                                    </div>
                                </div>
                            </div> -->

                            <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                                <div class="card achievement-card" style="height: 390px;"
                                    data-image="../assets/img/achievements/2023.jpeg"
                                    data-title="2023 WINNER"
                                    data-description="National Convention on Quality & Productivity 2023">

                                    <img src="../assets/img/achievements/2023.jpeg"
                                        class="card-img-top achievement-zoom cursor"
                                        alt="2023 WINNER"
                                        style="height:350px"
                                        onclick="openModal();currentSlide(7)" />

                                    <div class="card-body text-center">
                                        <p class="card-text" style="font-size:11px">National Quality & Productivity 2023</p>
                                    </div>
                                </div>
                            </div> -->

                        </div>
                    </div>





                    <!-- ✅ One Global Modal -->
                    <div id="myModal" class="modal">
                        <span class="close cursor" onclick="closeModal()">&times;</span>
                        <div class="modal-content">

                            <div class="mySlides">
                                <div class="numbertext">1 /7</div>
                                <img src="../assets/img/achievements/2018.jpeg" style="width:100%" />
                            </div>

                            <div class="mySlides">
                                <div class="numbertext">2 /7</div>
                                <img src="../assets/img/achievements/2014.jpg" style="width:100%" />
                            </div>

                            <div class="mySlides">
                                <div class="numbertext">3 / 7</div>
                                <img src="../assets/img/achievements/2008.jpeg" style="width:100%" />
                            </div>

                            <div class="mySlides">
                                <div class="numbertext">4 / 7</div>
                                <img src="../assets/img/achievements/2003.jpeg" style="width:100%" />
                            </div>


                            <div class="mySlides">
                                <div class="numbertext">5 / 7</div>
                                <img src="../assets/img/achievements/1996.jpg" style="width:100%" />
                            </div>


                            <div class="mySlides">
                                <div class="numbertext">6 / 7</div>
                                <img src="../assets/img/achievements/2024.jpg" style="width:100%" />
                            </div>

                            <div class="mySlides">
                                <div class="numbertext">7 / 7</div>
                                <img src="../assets/img/achievements/2023.jpeg" style="width:100%" />
                            </div>




                            <!-- Prev/Next controls -->
                            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                            <a class="next" onclick="plusSlides(1)">&#10095;</a>
                        </div>
                    </div>


                    <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card achievement-card" data-image="../assets/img/achievements/2008.jpeg" data-title="2008 WINNER" data-description="Provincial Productivity Award - First Place">
                            <img src="../assets/img/achievements/2008.jpeg" class="card-img-top achievement-zoom" alt="2008 WINNER" style="height:350px">
                            <div class="card-body text-center">
                                <h5 class="card-title">2008 WINNER</h5>
                                <p class="card-text"><strong>Provincial Productivity Award - First Place</strong></p>
                            </div>
                        </div> 
                    </div> -->
                    <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card achievement-card" data-image="../assets/img/achievements/2003.jpeg" data-title="2003 WINNER" data-description="National Productivity Award">
                            <img src="../assets/img/achievements/2003.jpeg" class="card-img-top achievement-zoom" alt="2003 WINNER" style="height:350px">
                            <div class="card-body text-center">
                                <h5 class="card-title">2003 WINNER</h5>
                                <p class="card-text"><strong>National Productivity Award</strong></p>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card achievement-card" data-image="../assets/img/achievements/1996.jpg" data-title="1996 WINNER" data-description="Excellence in Quality">
                            <img src="../assets/img/achievements/1996.jpg" class="card-img-top achievement-zoom" alt="1996 WINNER" style="height:350px">
                            <div class="card-body text-center">
                                <h5 class="card-title">1996 WINNER</h5>
                                <p class="card-text"><strong>Excellence in Quality</strong></p>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card achievement-card" data-image="../assets/img/achievements/2024.jpg" data-title="2024 Gold Award" data-description="ICQCC Awards - Gold Award">
                            <img src="../assets/img/achievements/2024.jpg" class="card-img-top achievement-zoom" alt="2008 WINNER" style="height:350px">
                            <div class="card-body text-center">
                                <h5 class="card-title">2024 Gold Award</h5>
                                <p class="card-text"><strong>ICQCC Awards - Gold Award</strong></p>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card achievement-card" data-image="../assets/img/achievements/2023.jpeg" data-title="2023 Gold Award" data-description="National Convention on Quality & Productivity 2023">
                            <img src="../assets/img/achievements/2023.jpeg" class="card-img-top achievement-zoom" alt="1996 Merit Award" style="height:350px">
                            <div class="card-body text-center">
                                <h5 class="card-title">2023 Gold Award</h5>
                                <p class="card-text"><strong>National Convention on Quality & Productivity 2023</strong></p>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>

        </div>
        </div>
        </div>
    </main>


    <!-- Footer -->
    <?php
    include '../template/foot.php';
    ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="../assets/js/swiper-bundle.min.js"></script>

    <script>
        // Handle tab selection
        document.addEventListener("DOMContentLoaded", function() {
            // Set initial active tab to welcome screen
            const welcomePane = document.querySelector('#welcome');
            if (welcomePane) {
                welcomePane.classList.add('show', 'active');
            }

            // Remove any active classes from navigation items initially
            document.querySelectorAll('.nav-link').forEach(item => {
                item.classList.remove('active');
            });

            // Handle tab selection for main nav links
            const mainNavLinks = document.querySelectorAll('.nav-link');
            mainNavLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-link').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show the target tab content and hide welcome screen
                    const targetId = this.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(targetId);

                    // Hide all tab panes including welcome
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the selected tab pane
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });

            // Handle URL hash for direct tab access
            var hash = window.location.hash;
            if (hash) {
                var tabTrigger = document.querySelector('a[href="' + hash + '"]');
                if (tabTrigger) {
                    // Remove active class from all nav items first
                    document.querySelectorAll('.nav-link').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Add active to the target tab
                    tabTrigger.classList.add('active');

                    // Hide all tab panes including welcome
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Show the target tab pane
                    const targetPane = document.querySelector(hash);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                }
            }

            // Update URL hash when tabs are shown
            var tabEls = document.querySelectorAll('.nav-link');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('data-bs-target');
                    history.replaceState(null, null, targetId);
                });
            });
        });

        // Swiper functionality for image zoom
        let swiperInstance = null;
        const overlay = document.getElementById('swiperOverlay');
        const closeBtn = document.getElementById('closeSwiper');

        function openSwiper() {
            overlay.style.display = 'flex';
            swiperInstance = new Swiper(".mySwiper", {
                zoom: true,
                loop: false
            });
        }

        function closeSwiperOverlay() {
            overlay.style.display = 'none';
            if (swiperInstance) {
                swiperInstance.destroy(true, true);
                swiperInstance = null;
            }
        }

        // Open on image click
        document.getElementById('orgImage').addEventListener('click', openSwiper);

        // Close on close button
        closeBtn.addEventListener('click', closeSwiperOverlay);

        // Close when clicking outside image
        overlay.addEventListener('click', function(e) {
            if (!e.target.closest('.swiper-zoom-container')) {
                closeSwiperOverlay();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                closeSwiperOverlay();
            }
        });

        // Modal functions for achievements
        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        var slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides((slideIndex += n));
        }

        function currentSlide(n) {
            showSlides((slideIndex = n));
        }

        function showSlides(n) {
            var i;
            var slides = document.getElementsByClassName("mySlides");
            if (n > slides.length) {
                slideIndex = 1;
            }
            if (n < 1) {
                slideIndex = slides.length;
            }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex - 1].style.display = "block";
        }
    </script>
</body>

</html>