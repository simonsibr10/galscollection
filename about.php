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
</head>

<body class="about-page php-about">

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