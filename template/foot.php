<style> 
    footer {
      height:60px;
      background-color: #184274;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      overflow: hidden;
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
      font-size: 1rem;
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