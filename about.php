<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tentang — Gals Collection</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>

      /* ============================================================
         RESET / BASE
      ============================================================ */
      body.about-page {
         background: #f5f3ef;
         font-family: Georgia, 'Times New Roman', serif;
         overflow-x: hidden;
      }

      /* ============================================================
         ANIMATION UTILITIES
      ============================================================ */
      .fade-up {
         opacity: 0;
         transform: translateY(4rem);
         transition: opacity .85s cubic-bezier(.22,1,.36,1),
                     transform .85s cubic-bezier(.22,1,.36,1);
      }

      .fade-up.visible {
         opacity: 1;
         transform: translateY(0);
      }

      .fade-in {
         opacity: 0;
         transition: opacity 1s ease;
      }

      .fade-in.visible {
         opacity: 1;
      }

      .zoom-in {
         opacity: 0;
         transform: scale(.92);
         transition: opacity .9s cubic-bezier(.22,1,.36,1),
                     transform .9s cubic-bezier(.22,1,.36,1);
      }

      .zoom-in.visible {
         opacity: 1;
         transform: scale(1);
      }

      /* stagger delays */
      .delay-1 { transition-delay: .1s !important; }
      .delay-2 { transition-delay: .22s !important; }
      .delay-3 { transition-delay: .36s !important; }
      .delay-4 { transition-delay: .50s !important; }
      .delay-5 { transition-delay: .65s !important; }

      /* ============================================================
         HERO SECTION — "DISCOVER OUR STORY"
      ============================================================ */
      .about-hero {
         display: grid;
         grid-template-columns: 1fr 1fr;
         min-height: 88vh;
         background: #f5f3ef;
         overflow: hidden;
      }

      /* Left: text */
      .about-hero-text {
         display: flex;
         flex-direction: column;
         justify-content: center;
         padding: 8rem 6rem 8rem 7%;
      }

      .about-hero-eyebrow {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .28rem;
         text-transform: uppercase;
         color: #999;
         margin-bottom: 2.8rem;
      }

      .about-hero-h1 {
         font-size: clamp(4rem, 5.5vw, 7rem);
         font-weight: 400;
         line-height: 1.05;
         text-transform: uppercase;
         letter-spacing: -.02em;
         color: #111;
         margin: 0 0 3rem;
      }

      .about-hero-h1 em {
         font-style: italic;
         font-weight: 300;
      }

      .about-hero-sub {
         font-family: Georgia, serif;
         font-size: 1.7rem;
         line-height: 1.85;
         color: #666;
         max-width: 46rem;
         margin-bottom: 3.6rem;
      }

      .about-hero-btn {
         display: inline-block;
         padding: 1.4rem 3.2rem;
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.35rem;
         font-weight: 700;
         letter-spacing: .14rem;
         text-transform: uppercase;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff;
         border: none;
         border-radius: .5rem;
         cursor: pointer;
         text-decoration: none;
         align-self: flex-start;
         transition: opacity .25s ease, transform .2s ease;
      }

      .about-hero-btn:hover {
         opacity: .82;
         transform: translateY(-2px);
      }

      /* Right: image collage */
      .about-hero-collage {
         position: relative;
         overflow: hidden;
      }

      .about-hero-collage .col-img-big {
         position: absolute;
         left: 5%;
         top: 8%;
         width: 58%;
         aspect-ratio: 3 / 4;
         border-radius: 1rem;
         overflow: hidden;
         box-shadow: 0 2rem 5rem rgba(0,0,0,.14);
      }

      .about-hero-collage .col-img-top {
         position: absolute;
         right: 2%;
         top: 5%;
         width: 36%;
         aspect-ratio: 2 / 3;
         border-radius: 1rem;
         overflow: hidden;
         box-shadow: 0 1.4rem 3.6rem rgba(0,0,0,.12);
      }

      .about-hero-collage .col-img-bot {
         position: absolute;
         right: 2%;
         bottom: 5%;
         width: 36%;
         aspect-ratio: 2 / 3;
         border-radius: 1rem;
         overflow: hidden;
         box-shadow: 0 1.4rem 3.6rem rgba(0,0,0,.12);
      }

      .about-hero-collage img {
         width: 100%; height: 100%;
         object-fit: cover; display: block;
         transition: transform .7s cubic-bezier(.25,.46,.45,.94);
      }

      .about-hero-collage .col-img-big:hover img,
      .about-hero-collage .col-img-top:hover img,
      .about-hero-collage .col-img-bot:hover img {
         transform: scale(1.06);
      }

      /* ============================================================
         STATS BAR
      ============================================================ */
      .about-stats {
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         display: flex;
         justify-content: center;
         gap: 0;
         padding: 4rem 5%;
      }

      .stat-item {
         flex: 1;
         max-width: 22rem;
         text-align: center;
         padding: 1rem 2rem;
         border-right: 1px solid rgba(255,255,255,.15);
      }

      .stat-item:last-child {
         border-right: none;
      }

      .stat-num {
         font-size: clamp(3.2rem, 4vw, 4.8rem);
         font-weight: 800;
         color: #fff;
         line-height: 1;
         margin-bottom: .6rem;
         font-family: Georgia, serif;
      }

      .stat-label {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .18rem;
         text-transform: uppercase;
         color: rgba(255,255,255,.6);
      }

      /* ============================================================
         COMMITMENT SPLIT — gambar kiri, teks kanan
      ============================================================ */
      .about-split {
         display: grid;
         grid-template-columns: 1fr 1fr;
         min-height: 70vh;
         background: #fff;
         overflow: hidden;
      }

      .about-split-img {
         position: relative;
         overflow: hidden;
      }

      .about-split-img img {
         width: 100%; height: 100%;
         object-fit: cover; display: block;
         transition: transform .7s cubic-bezier(.25,.46,.45,.94);
      }

      .about-split-img:hover img {
         transform: scale(1.04);
      }

      .about-split-text {
         display: flex;
         flex-direction: column;
         justify-content: center;
         padding: 7rem 7% 7rem 6rem;
         background: #fff;
      }

      .about-split-text .eyebrow {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .22rem;
         text-transform: uppercase;
         color: #aaa;
         margin-bottom: 2.2rem;
      }

      .about-split-text h2 {
         font-size: clamp(2.8rem, 3.4vw, 4.4rem);
         font-weight: 400;
         line-height: 1.2;
         color: #111;
         margin: 0 0 2.6rem;
      }

      .about-split-text p {
         font-size: 1.6rem;
         line-height: 1.9;
         color: #666;
         margin-bottom: 1.6rem;
         max-width: 50rem;
      }

      .about-split-stats {
         display: flex;
         gap: 4rem;
         margin-top: 3rem;
         padding-top: 3rem;
         border-top: 1px solid #eee;
      }

      .split-stat-num {
         font-size: 3.6rem;
         font-weight: 800;
         color: #111;
         line-height: 1;
         font-family: Georgia, serif;
      }

      .split-stat-label {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.15rem;
         letter-spacing: .16rem;
         text-transform: uppercase;
         color: #aaa;
         margin-top: .5rem;
      }

      /* ============================================================
         NILAI / VALUES SECTION
      ============================================================ */
      .about-values {
         padding: 8rem 5%;
         background: #f5f3ef;
      }

      .about-values .section-header {
         text-align: center;
         margin-bottom: 5.6rem;
      }

      .about-values .section-header .eyebrow {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .22rem;
         text-transform: uppercase;
         color: #aaa;
         display: block;
         margin-bottom: 1.4rem;
      }

      .about-values .section-header h2 {
         font-size: clamp(2.8rem, 3.5vw, 4.6rem);
         font-weight: 400;
         color: #111;
         margin: 0;
         line-height: 1.15;
      }

      .values-grid {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 2.8rem;
         max-width: 1200px;
         margin: 0 auto;
      }

      .value-card {
         background: #fff;
         border-radius: 1.4rem;
         padding: 3.6rem 3rem;
         border: 1px solid #eeecea;
         transition: box-shadow .3s ease, transform .3s ease;
      }

      .value-card:hover {
         box-shadow: 0 1.2rem 3.5rem rgba(0,0,0,.09);
         transform: translateY(-6px);
      }

      .value-icon {
         width: 5.2rem; height: 5.2rem;
         border-radius: 1rem;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         display: flex; align-items: center; justify-content: center;
         margin-bottom: 2.4rem;
         font-size: 2.2rem;
         color: #fff;
      }

      .value-card h3 {
         font-size: 2rem;
         font-weight: 600;
         color: #111;
         margin: 0 0 1.2rem;
         font-family: Georgia, serif;
      }

      .value-card p {
         font-size: 1.5rem;
         line-height: 1.8;
         color: #777;
         margin: 0;
      }

      /* ============================================================
         VIDEO SECTION
      ============================================================ */
      .about-video-section {
         position: relative;
         width: 100%;
         min-height: 60vh;
         overflow: hidden;
         background: #111;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .about-video-section video {
         position: absolute;
         inset: 0;
         width: 100%;
         height: 100%;
         object-fit: cover;
         z-index: 0;
         opacity: .55;
      }

      .about-video-placeholder {
         position: absolute;
         inset: 0;
         display: flex;
         align-items: center;
         justify-content: center;
         background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
         z-index: 0;
      }

      .about-video-placeholder .placeholder-text {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.4rem;
         letter-spacing: .2rem;
         color: rgba(255,255,255,.25);
         text-transform: uppercase;
      }

      .about-video-overlay {
         position: absolute;
         inset: 0;
         background: linear-gradient(to right,
            rgba(0,0,0,.55) 0%,
            rgba(0,0,0,.2) 60%,
            rgba(0,0,0,.4) 100%);
         z-index: 1;
      }

      .about-video-content {
         position: relative;
         z-index: 2;
         text-align: center;
         color: #fff;
         padding: 8rem 5%;
      }

      .about-video-content .eyebrow {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .28rem;
         text-transform: uppercase;
         color: rgba(255,255,255,.6);
         display: block;
         margin-bottom: 2rem;
      }

      .about-video-content h2 {
         font-size: clamp(3.2rem, 5vw, 6rem);
         font-weight: 300;
         line-height: 1.1;
         text-transform: uppercase;
         letter-spacing: -.01em;
         margin: 0 0 2.4rem;
         color: #fff;
      }

      .about-video-content h2 strong {
         font-weight: 800;
      }

      .about-video-content p {
         font-size: 1.7rem;
         line-height: 1.8;
         color: rgba(255,255,255,.8);
         max-width: 55rem;
         margin: 0 auto 3.6rem;
      }

      .about-video-btn {
         display: inline-flex;
         align-items: center;
         gap: 1rem;
         padding: 1.5rem 3.2rem;
         background: #fff;
         color: #111;
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.35rem;
         font-weight: 700;
         letter-spacing: .12rem;
         text-transform: uppercase;
         border-radius: .5rem;
         text-decoration: none;
         transition: background .2s ease, color .2s ease, transform .2s ease;
      }

      .about-video-btn:hover {
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff;
         transform: translateY(-2px);
      }

      /* ============================================================
         CTA BOTTOM
      ============================================================ */
      .about-cta {
         padding: 8rem 5%;
         background: #fff;
         text-align: center;
      }

      .about-cta .eyebrow {
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.2rem;
         letter-spacing: .24rem;
         text-transform: uppercase;
         color: #aaa;
         display: block;
         margin-bottom: 1.8rem;
      }

      .about-cta h2 {
         font-size: clamp(2.8rem, 3.8vw, 5rem);
         font-weight: 400;
         color: #111;
         line-height: 1.15;
         margin: 0 0 1.8rem;
      }

      .about-cta p {
         font-size: 1.7rem;
         line-height: 1.8;
         color: #777;
         max-width: 52rem;
         margin: 0 auto 3.8rem;
      }

      .about-cta-btns {
         display: flex;
         gap: 1.6rem;
         justify-content: center;
         flex-wrap: wrap;
      }

      .btn-dark {
         display: inline-block;
         padding: 1.4rem 3.2rem;
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.4rem;
         font-weight: 700;
         letter-spacing: .12rem;
         text-transform: uppercase;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff;
         border-radius: .5rem;
         text-decoration: none;
         transition: opacity .2s ease, transform .2s ease;
      }

      .btn-dark:hover {
         opacity: .82;
         transform: translateY(-2px);
      }

      .btn-outline {
         display: inline-block;
         padding: 1.4rem 3.2rem;
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.4rem;
         font-weight: 700;
         letter-spacing: .12rem;
         text-transform: uppercase;
         background: transparent;
         color: #111;
         border: 2px solid #111;
         border-radius: .5rem;
         text-decoration: none;
         transition: background .2s ease, color .2s ease, transform .2s ease;
      }

      .btn-outline:hover {
         background: #111;
         color: #fff;
         transform: translateY(-2px);
      }

      /* ============================================================
         RESPONSIVE
      ============================================================ */
      @media (max-width: 1024px) {
         .about-hero { grid-template-columns: 1fr; min-height: auto; }
         .about-hero-text { padding: 6rem 5%; }
         .about-hero-collage { min-height: 55vw; }
         .about-hero-collage .col-img-big { left: 3%; width: 54%; }
         .about-split { grid-template-columns: 1fr; }
         .about-split-img { min-height: 55vw; }
         .about-split-text { padding: 5rem 5%; }
         .values-grid { grid-template-columns: repeat(2, 1fr); }
      }

      @media (max-width: 640px) {
         .about-stats { flex-wrap: wrap; gap: 2rem; }
         .stat-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,.12); flex: 0 0 45%; }
         .values-grid { grid-template-columns: 1fr; }
         .about-split-stats { gap: 2.4rem; flex-wrap: wrap; }
      }
   </style>
</head>

<body class="about-page">

<?php include 'components/user_header.php'; ?>

<!-- ===================================================
     1. HERO — DISCOVER OUR STORY
=================================================== -->
<section class="about-hero">

   <!-- Text Side -->
   <div class="about-hero-text">
      <p class="about-hero-eyebrow fade-up">— Gals Collection</p>
      <h1 class="about-hero-h1 fade-up delay-1">
         Discover<br><em>Our</em><br>Story
      </h1>
      <p class="about-hero-sub fade-up delay-2">
         Kami hadir untuk kamu yang ingin tampil stylish, elegan, dan percaya diri.
         Setiap koleksi dipilih dengan penuh cinta untuk menemani gaya harian terbaikmu.
      </p>
      <a href="shop.php" class="about-hero-btn fade-up delay-3">Lihat Koleksi</a>
   </div>

   <!-- Collage Side -->
   <div class="about-hero-collage">
      <div class="col-img-big zoom-in delay-1">
         <img src="images/picsabout/Miu.webp" alt="Koleksi Gals Collection">
      </div>
      <div class="col-img-top zoom-in delay-2">
         <img src="images/picsabout/C1.webp" alt="Tote Collection">
      </div>
      <div class="col-img-bot zoom-in delay-3">
         <img src="images/picsabout/E2.webp" alt="Slingbag Collection">
      </div>
   </div>

</section>

<!-- ===================================================
     2. STATS BAR
=================================================== -->
<div class="about-stats">
   <div class="stat-item fade-up">
      <div class="stat-num">3+</div>
      <div class="stat-label">Tahun Berdiri</div>
   </div>
   <div class="stat-item fade-up delay-1">
      <div class="stat-num">500+</div>
      <div class="stat-label">Pelanggan Puas</div>
   </div>
   <div class="stat-item fade-up delay-2">
      <div class="stat-num">100+</div>
      <div class="stat-label">Produk Pilihan</div>
   </div>
   <div class="stat-item fade-up delay-3">
      <div class="stat-num">8</div>
      <div class="stat-label">Kategori Produk</div>
   </div>
</div>

<!-- ===================================================
     3. COMMITMENT SPLIT
=================================================== -->
<div class="about-split">

   <div class="about-split-img zoom-in">
      <img src="Kategori_images/TopHandleCoach1.webp" alt="Our Commitment">
   </div>

   <div class="about-split-text">
      <span class="eyebrow fade-up">Komitmen Kami</span>
      <h2 class="fade-up delay-1">
         Kualitas dan Gaya<br>
         yang Tak Pernah<br>
         Kompromi
      </h2>
      <p class="fade-up delay-2">
         Setiap produk di Gals_Collection dipilih dengan teliti agar memberikan kenyamanan,
         keindahan, dan kepuasan saat digunakan. Kami percaya bahwa penampilan yang menarik
         bisa meningkatkan rasa percaya diri.
      </p>
      <p class="fade-up delay-3">
         Kami selalu berusaha menghadirkan koleksi terbaik untuk menemani gaya harian kamu —
         dari tas, dompet, sepatu, hingga aksesoris dengan model yang selalu up to date.
      </p>
      <div class="about-split-stats fade-up delay-4">
         <div>
            <div class="split-stat-num">100%</div>
            <div class="split-stat-label">Produk Terkurasi</div>
         </div>
         <div>
            <div class="split-stat-num">500+</div>
            <div class="split-stat-label">Happy Client</div>
         </div>
         <div>
            <div class="split-stat-num">3+</div>
            <div class="split-stat-label">Tahun Pengalaman</div>
         </div>
      </div>
   </div>

</div>

<!-- ===================================================
     4. VALUES / KEUNGGULAN
=================================================== -->
<section class="about-values">

   <div class="section-header">
      <span class="eyebrow fade-up">Keunggulan Kami</span>
      <h2 class="fade-up delay-1">Kenapa Memilih<br>Gals_Collection?</h2>
   </div>

   <div class="values-grid">

      <div class="value-card fade-up delay-1">
         <div class="value-icon"><i class="fas fa-gem"></i></div>
         <h3>Kualitas Terjamin</h3>
         <p>Setiap produk melewati seleksi ketat untuk memastikan kamu mendapatkan item terbaik dengan kualitas premium di harga yang terjangkau.</p>
      </div>

      <div class="value-card fade-up delay-2">
         <div class="value-icon"><i class="fas fa-shipping-fast"></i></div>
         <h3>Pengiriman Cepat</h3>
         <p>Pesanan kamu diproses dan dikirim dengan cepat ke seluruh Indonesia. Nikmati gratis ongkir untuk pembelian di atas minimum tertentu.</p>
      </div>

      <div class="value-card fade-up delay-3">
         <div class="value-icon"><i class="fas fa-headset"></i></div>
         <h3>Layanan Ramah</h3>
         <p>Tim kami siap membantu kamu 7 hari seminggu. Punya pertanyaan? Hubungi kami lewat chat dan dapatkan respons cepat dari admin kami.</p>
      </div>

      <div class="value-card fade-up delay-1">
         <div class="value-icon"><i class="fas fa-sync-alt"></i></div>
         <h3>Mudah Dikembalikan</h3>
         <p>Tidak puas dengan produk yang diterima? Kami punya kebijakan pengembalian yang mudah dan transparan demi kepuasanmu.</p>
      </div>

      <div class="value-card fade-up delay-2">
         <div class="value-icon"><i class="fas fa-lock"></i></div>
         <h3>Transaksi Aman</h3>
         <p>Sistem pembayaran kami terenkripsi dan aman. Belanja dengan tenang karena data dan transaksimu terlindungi sepenuhnya.</p>
      </div>

      <div class="value-card fade-up delay-3">
         <div class="value-icon"><i class="fas fa-star"></i></div>
         <h3>Koleksi Terkini</h3>
         <p>Kami selalu update koleksi mengikuti tren terbaru agar kamu selalu tampil stylish dan tidak ketinggalan fashion terkini.</p>
      </div>

   </div>

</section>

<!-- ===================================================
     5. VIDEO SECTION
=================================================== -->
<div class="about-video-section">

   <video autoplay muted loop playsinline>
      <source src="" type="video/mp4">
   </video>

   <div class="about-video-placeholder">
      <span class="placeholder-text">Tambahkan Video Kamu Di Sini</span>
   </div>

   <div class="about-video-overlay"></div>

   <div class="about-video-content">
      <span class="eyebrow fade-in">Behind The Brand</span>
      <h2 class="fade-up delay-1">
         Fashion is<br><strong>Our Passion</strong>
      </h2>
      <p class="fade-up delay-2">
         Lebih dari sekadar produk — kami menjual kepercayaan diri.
         Lihat bagaimana setiap koleksi Gals_Collection lahir dengan penuh dedikasi.
      </p>
      <a href="shop.php" class="about-video-btn fade-up delay-3">
         <i class="fas fa-shopping-bag"></i> Belanja Sekarang
      </a>
   </div>

</div>

<!-- ===================================================
     6. CTA BOTTOM
=================================================== -->
<section class="about-cta">
   <span class="eyebrow fade-up">Mulai Sekarang</span>
   <h2 class="fade-up delay-1">Siap Tampil<br>Lebih Stylish?</h2>
   <p class="fade-up delay-2">
      Temukan produk favoritmu, atau hubungi kami langsung.
      Kami dengan senang hati membantu kamu menemukan koleksi yang sempurna.
   </p>
   <div class="about-cta-btns fade-up delay-3">
      <a href="shop.php"  class="btn-dark">Lihat Produk</a>
      <a href="chat.php"  class="btn-outline">Chat Admin</a>
   </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
   /* ============================================================
      SCROLL OBSERVER — fade-up, fade-in, zoom-in
   ============================================================ */
   const animEls = document.querySelectorAll('.fade-up, .fade-in, .zoom-in');

   const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
         if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
         }
      });
   }, { threshold: 0.12 });

   animEls.forEach(el => observer.observe(el));

   /* ============================================================
      COUNTER ANIMATION (stats bar)
   ============================================================ */
   function animateCounter(el, target, suffix = '') {
      let current = 0;
      const step = Math.ceil(target / 60);
      const timer = setInterval(() => {
         current += step;
         if (current >= target) {
            current = target;
            clearInterval(timer);
         }
         el.textContent = current + suffix;
      }, 24);
   }

   const statsObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
         if (entry.isIntersecting) {
            const nums = entry.target.querySelectorAll('.stat-num');
            const data = [
               { val: 3,   suffix: '+' },
               { val: 500, suffix: '+' },
               { val: 100, suffix: '+' },
               { val: 8,   suffix: ''  }
            ];
            nums.forEach((el, i) => {
               if (data[i]) animateCounter(el, data[i].val, data[i].suffix);
            });
            statsObserver.unobserve(entry.target);
         }
      });
   }, { threshold: 0.3 });

   const statsBar = document.querySelector('.about-stats');
   if (statsBar) statsObserver.observe(statsBar);
</script>

</body>
</html>