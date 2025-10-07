<style>
    /* Logo Section */
    .logo-section {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      height: 100px;
      background-color: white;
      gap: 2em;
      padding: 0;
      margin: 0;
      box-sizing: border-box;
        
    }

        .logo-link {
        display: block;
        height: 100%;
        width: 20%;
        transition: transform 0.3s ease;
        }

        .logo-link:hover {
        transform: scale(1.05);
        }

        .logo-left {
        height: 100%;
        width: 100%;
        object-fit: contain;
        display: block;
        }


    .logo-right {
      height: 100%;
      width: 80%;
      object-fit: contain;
    }

    /* Navigation */
    .main-nav {
      background-color: white;
      width: 100%;
      display: flex;
      justify-content: center;
      border-bottom: 3px solid #4b4747ff;
    }

    .main-nav ul {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      list-style: none;
      display: flex;
      gap: 7em;
      margin: 0;
      padding: 1em 0;
    }

    .main-nav a {
      color: #222;
      text-decoration: none;
      font-weight: 500;
      position: relative;
      padding-bottom: 4px;
      transition: color 0.3s ease;
    }

    .main-nav a:hover {
      color: var(--primary-color);
    }

    .main-nav a.active::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 3px;
      width: 100%;
      background-color: var(--primary-color);
    }
</style>    

    <header>
    <section class="logo-section">
    <a href="/qai/index.php" class="logo-link">
        <img src="/qai/assets/img/logo/QAI_NEW_LOG.png" alt="SLAF Logo" class="logo-left">
    </a>
    <img src="/qai/assets/img/jet-banner.png" alt="Jet Aircraft Banner" class="logo-right">
    </section>


      <nav class="main-nav">
        <ul>
          <li><a href="/qai/index.php">Web Portal</a></li>
          <li><a href="/qai/qai/inspectorate.php">Quality Assurance Inspectorate</a></li>
          <li><a href="/qai/services/services.php">Services</a></li>
          <li><a href="/qai/publication/publication.php">Technical Publications</a></li>
          <li><a href="/qai/training/training.php">Training</a></li>
          <li><a href="/qai/productivity/productivity.php">Productivity & OSH</a></li>
        </ul>
      </nav>
    </header>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a');

    navLinks.forEach(link => {
      if (link.getAttribute('href') === currentPath) {
        link.classList.add('active');
      }
    });
  });
</script>


