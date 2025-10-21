<style>
  footer {
    height: 25px;
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
    bottom:0;
    left: 0;
    width: 100%;
    z-index: 1000;
  }

  /* Marquee */
  .news-marquee {
    width: 100%;
    /* background-image: linear-gradient(to right, #4f4e4e, #373636); */
    padding: 0;
    margin: 0;
  }

  .marquee-content {
    display: flex;
    gap: 10em;
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
      <span>Quality Assurance Inspectorate</span>
      <span>Services</span>
      <span>Technical Publication</span>
      <span>Training</span>
      <span>Productivity & OSH</span>
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
</script>