<?php include 'template/head.php'; ?>

<body>

    <?php
    include 'template/header.php';
    ?>
    <!-- Main Content -->
    <main class="container my-5 pt-5 min-vh-60">
        <div class="page-header mb-4">
            <h3 class="colour-defult"> Publications <i class="fa fa-book"></i>
                <div class="float-end">
                    <a href="index.php" class="btn btn-info me-2"><i class="fa fa-home"></i> Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </h3>
        </div>

        <div class="row justify-content-center">
            <?php
            $publications = [
                ['title' => 'Publication Update', 'icon' => 'fa-book', 'link' => '#'],
                ['title' => 'Master Copies', 'icon' => 'fa-book-open', 'link' => '#'],
                ['title' => 'Publication Index', 'icon' => 'fa-newspaper', 'link' => '#'],
                 ['title' => 'Worksheets', 'icon' => 'fa-book', 'link' => '#'],
                ['title' => 'Service Shedule', 'icon' => 'fa-book-open', 'link' => '#'],
                ['title' => 'Sefty Notes', 'icon' => 'fa-newspaper', 'link' => '#'],
                ['title' => 'AD & Bulletings', 'icon' => 'fa-book', 'link' => '#'],
                
            ];
            foreach ($publications as $pub): ?>
                <div class="col-md-2 col-6 mb-4">
                    <a href="<?= htmlspecialchars($pub['link']) ?>" class="text-decoration-none">
                        <div class="card text-center shadow-sm h-100">
                            <div class="card-body">
                                <i class="fa <?= htmlspecialchars($pub['icon']) ?> fa-3x mb-3 text-info"></i>
                                <h6 class="card-title"><?= htmlspecialchars($pub['title']) ?></h6>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <hr>
    </main>
    <?php
    include 'template/footer.php';
    include 'template/foot.php';
    ?>

</body>

</html>