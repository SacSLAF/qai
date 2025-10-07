<?php include '../template/head.php'; ?>

<body>
    <?php
    include '../template/header.php';
    require_once "../includes/config.php";
    ?>
    <!-- Main Content -->
    <main class="container my-5 pt-5">
        <div class="page-header mb-4">
            <h3 class="colour-defult">Contacts <i class="fa fa-contact"></i>
                <div class="float-end">
                    <a href="../index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <!-- Nav Tabs -->
                <ul class="nav flex-column nav-pills" id="inspectorateTabs" role="tablist" aria-orientation="vertical">
                    <li class="nav-item">
                        <a class="nav-link active" id="contact-tab" data-bs-toggle="pill" href="#contact" role="tab">Contact details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="form-feedback-tab" data-bs-toggle="pill" href="#form-feedback" role="tab">Feedback</a>
                    </li>

                </ul>
            </div>
            <div class="col-lg-10">
                <!-- Tab Content -->
                <div class="tab-content" id="inspectorateTabsContent">
                    <!-- Tab 1 -->
                    <div class="tab-pane fade show active" id="contact" role="tabpanel">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>DQA</strong> <br>
                                            Ext: 11100 <br>
                                            Mobile: 077-2229057
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                         <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(Flying)</span></strong> <br>
                                            Ext: 11119 <br>
                                            <!-- Mobile: 077-2229057 -->
                                        </p>
                                       
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(Flying)</span></strong> <br>
                                            Ext: 11119 <br>
                                            <!-- Mobile: 077-2229057 -->
                                        </p>
                                      
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>MASTER CONTROLLERS<br><span class="text-sm">(Electronic Engineering)</span></strong> <br>
                                            Ext: 11116 <br>
                                            <!-- Mobile: 077-2229142 -->
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                         <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(Aeronautical Engineering)</span></strong> <br>
                                            Ext: 11115 <br>
                                            Mobile: 077-2229145
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(General Engineering)</span></strong> <br>
                                            Ext: 11117 <br>
                                            Mobile: 077-2229165
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(Aeronautical Engineering)</span></strong> <br>
                                            Ext: 11115 <br>
                                            Mobile: 077-2229057
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                       <p class="mt-2 mb-0">
                                            <strong>SOQA<br><span class="text-sm">(Electronic Engineering)</span></strong> <br>
                                            Ext: 11116 <br>
                                            Mobile: 077-2229142
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                           <strong>MASTER CONTROLLERS<br><span class="text-sm">(AE/ATC/LOG)</span></strong> <br>
                                            Ext: 11153 <br>
                                            <!-- Mobile: 077-2229057 -->
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                         <p class="mt-2 mb-0">
                                           <strong>MASTER CONTROLLERS<br><span class="text-sm">(GE/EE/EOD/Fire)</span></strong> <br>
                                            Ext: 11154 <br>
                                            <!-- Mobile: 077-2229057 -->
                                        </p>
                                       
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>WO I/C<br><span class="text-sm">(CQAI)</span></strong> <br>
                                            Ext: 11151 <br>
                                            <!-- Mobile: 077-2229057 -->
                                        </p>
                                      
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <p class="mt-2 mb-0">
                                            <strong>LIBRARY AND ORDERLY ROOM<br></strong> 
                                            Ext: 11156 & 11157 <br>
                                            <!-- Mobile: 077-2229142 -->
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="form-feedback" role="tabpanel">
                        <!-- <div class="alert alert-info">
                            <h5><strong>Feedback</strong></h5>
                        </div> -->
                        <div class="container my-5">
                                <div class="card shadow-sm mx-auto" style="max-width: 600px;">
                                    <!-- <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Send a Message</h5>
                                    </div> -->
                                    <div class="card-body">
                                        <form action="#" method="post">
                                            <div class="mb-3">
                                                <label for="serviceNo" class="form-label">Service No (optional)</label>
                                                <input type="text" class="form-control" id="serviceNo" name="serviceNo" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rank" class="form-label">Rank (optional)</label>
                                                <input type="text" class="form-control" id="rank" name="rank" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name (optional)</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="message" class="form-label">Message</label>
                                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-20">Submit</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
        <hr>
    </main>
    <?php
    include 'template/footer.php';
    include 'template/foot.php';
    ?>

</body>

</html>