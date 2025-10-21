<?php
    include 'template/head.php';
?>
<style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100vh;
      width: 100vw;
      overflow: hidden;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .page-wrapper {
      display: flex;
      flex-direction: column;
      height: 100vh;
      width: 100vw;
    }
    main{
      margin-top: -4rem;
    }
</style>
<body>
  <div class="page-wrapper">

    <?php include 'template/header.php'; ?>

    <main>
      <div class="slider-wrapper">
        <div class="swiper-slide">
          <img src="assets/img/slider/bk-01.jpg" alt="Quality Inspection">
        </div>
      </div>
    </main>

    <?php include 'template/foot.php'; ?>
  </div>
</body>
</html>
