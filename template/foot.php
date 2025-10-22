<style>
  footer {
    height: 50px;
    background-color: #184274;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    overflow: hidden;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
  }

  /* Marquee */
  .news-marquee {
    width: 100%;
    background-image: linear-gradient(to bottom, #c2c2c2ff, #494949ff);
    padding: 0;
    margin: 0;
    height: 25px;
  }

  .marquee-content {
    display: flex;
    gap: 1em;
    white-space: nowrap;
    animation: scroll-left 20s linear infinite;
    font-weight: 500;
    font-size: small;
  }

  @keyframes scroll-left {
    0% {
      transform: translateX(100%);
    }

    100% {
      transform: translateX(-100%);
    }
  }
</style>

<footer>
  <section class="news-marquee">
    <div class="marquee-content">
      <span>Quality Assurance Inspectorate &nbsp - </span>
      <span>Services &nbsp- </span>
      <span>Technical Publication &nbsp - </span>
      <span>Training &nbsp - </span>
      <span>Productivity and OSH</span>
    </div>
  </section>
</footer>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const marquee = document.querySelector('.marquee-content');
    marquee.addEventListener('mouseenter', function() {
      this.style.animationPlayState = 'paused';
    });
    marquee.addEventListener('mouseleave', function() {
      this.style.animationPlayState = 'running';
    });
  });

  // Function to set underline width to text width
  function setExactUnderlineWidth() {
    const navLinks = document.querySelectorAll('.nav-pills .nav-link');

    navLinks.forEach(link => {
      // Create a temporary span to measure text width
      const span = document.createElement('span');
      span.textContent = link.textContent;
      span.style.visibility = 'hidden';
      span.style.position = 'absolute';
      span.style.whiteSpace = 'nowrap';
      span.style.fontSize = window.getComputedStyle(link).fontSize;
      span.style.fontWeight = window.getComputedStyle(link).fontWeight;
      span.style.fontFamily = window.getComputedStyle(link).fontFamily;

      document.body.appendChild(span);
      const textWidth = span.offsetWidth;
      document.body.removeChild(span);

      // Set CSS variable with the exact width
      link.style.setProperty('--text-width', `${textWidth}px`);
    });
  }

  // Initialize on load and when window resizes
  document.addEventListener('DOMContentLoaded', setExactUnderlineWidth);
  window.addEventListener('resize', setExactUnderlineWidth);
</script>