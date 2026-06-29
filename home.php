<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

include 'components/wishlist_cart.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Beranda</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>

      /* ============================================================
         GLOBAL
      ============================================================ */
      :root {
         --galaxy-blue: linear-gradient(135deg, #0f2faa, #1a6fff, #00c6ff);
         --galaxy-blue-solid: #1a6fff;
         --galaxy-blue-dark: #0f2faa;
      }

      body.home-modern {
         padding-top: 0 !important;
         background: #f5f3ef;
      }


      /* ============================================================
         BUTTON GLOBAL — BIRU GALAXY
      ============================================================ */
      .btn,
      .option-btn,
      .hero-btn-main,
      .promo-split-btn,
      .cat-view-all {
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff !important;
         border: none !important;
         transition: opacity .25s ease, transform .2s ease !important;
      }

      .btn:hover,
      .option-btn:hover,
      .hero-btn-main:hover,
      .promo-split-btn:hover,
      .cat-view-all:hover {
         opacity: .85 !important;
         transform: translateY(-2px) !important;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff !important;
      }

      /* ============================================================
         HERO VIDEO
      ============================================================ */
      .hero-fashion {
         position: relative;
         width: 100%;
         min-height: 100vh;
         overflow: hidden;
         background: #000;
      }

      .hero-fashion video {
         position: absolute; inset: 0;
         width: 100%; height: 100%;
         object-fit: cover; z-index: 0;
      }

      .hero-overlay {
         position: absolute; inset: 0;
         background: linear-gradient(to right, rgba(0,0,0,.58), rgba(0,0,0,.08));
         z-index: 1;
      }

      .hero-content {
         position: absolute;
         top: 54%; left: 5%;
         transform: translateY(-50%);
         z-index: 3;
         max-width: 70rem;
         color: #fff;
      }

      .hero-mini {
         display: inline-block;
         font-size: 1.3rem; font-weight: 600;
         letter-spacing: .14rem; text-transform: uppercase;
         margin-bottom: 1.6rem;
         background: rgba(255,255,255,.14);
         backdrop-filter: blur(4px);
         padding: .7rem 1.4rem; border-radius: 3rem;
      }

      .hero-content h1 {
         font-size: clamp(3.2rem, 5.5vw, 6.5rem);
         line-height: 1.08; font-weight: 300;
         margin: 0 0 2rem; text-transform: uppercase; color: #fff;
      }

      .hero-content h1 strong { font-weight: 800; }

      .hero-content p {
         max-width: 54rem; font-size: 1.9rem; line-height: 1.85;
         margin-bottom: 2.5rem; color: rgba(255,255,255,.9);
      }

      .hero-btn-main {
         display: inline-block; padding: 1.4rem 3rem;
         font-size: 1.6rem; font-weight: 600;
         border-radius: .6rem; letter-spacing: .04rem;
      }

      /* ============================================================
         SECTION KATEGORI
      ============================================================ */
      .category-editorial {
         padding: 6rem 5%;
         background: #f5f3ef;
      }

      .category-editorial .cat-header {
         display: flex; align-items: flex-end;
         justify-content: space-between;
         margin-bottom: 3.2rem; gap: 2rem; flex-wrap: wrap;
      }

      .category-editorial .cat-header h2 {
         font-size: clamp(2.6rem, 4vw, 4rem);
         font-weight: 800; letter-spacing: -.02em;
         text-transform: uppercase; color: #111; margin: 0; line-height: 1;
      }

      .cat-view-all {
         display: inline-flex; align-items: center; gap: .8rem;
         font-size: 1.4rem; font-weight: 600; letter-spacing: .06rem;
         text-transform: uppercase;
         border-radius: 3rem; padding: .8rem 1.8rem;
         white-space: nowrap;
      }

      .cat-layout {
         display: grid;
         grid-template-columns: 30rem 1fr;
         gap: 1.6rem; align-items: start;
      }

      .cat-hero-card {
         position: relative; overflow: hidden;
         border-radius: 1.4rem; background: #ddd;
         aspect-ratio: 3 / 4; cursor: pointer;
         display: block; text-decoration: none;
      }

      .cat-hero-card img {
         width: 100%; height: 100%; object-fit: cover; display: block;
         transition: transform .55s cubic-bezier(.25,.46,.45,.94);
      }

      .cat-hero-card:hover img { transform: scale(1.07); }

      .cat-hero-card .cat-card-label {
         position: absolute; bottom: 0; left: 0; right: 0;
         padding: 3rem 2rem 2.2rem;
         background: linear-gradient(to top, rgba(0,0,0,.72) 0%, transparent 100%);
         color: #fff;
      }

      .cat-hero-card .cat-card-label h3 {
         font-size: 2.4rem; font-weight: 700;
         text-transform: uppercase; letter-spacing: .04em;
         margin: 0 0 .5rem; color: #fff;
      }

      .cat-hero-card .cat-card-label span {
         font-size: 1.3rem; letter-spacing: .08rem; opacity: .8; text-transform: uppercase;
      }

      .cat-grid {
         display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.6rem;
      }

      .cat-grid-card {
         position: relative; overflow: hidden;
         border-radius: 1.2rem; background: #e0ddd8;
         aspect-ratio: 1 / 1.15; cursor: pointer;
         display: block; text-decoration: none;
      }

      .cat-grid-card img {
         width: 100%; height: 100%; object-fit: cover; display: block;
         transition: transform .5s cubic-bezier(.25,.46,.45,.94);
      }

      .cat-grid-card:hover img { transform: scale(1.09); }

      .cat-grid-card .cat-card-label {
         position: absolute; bottom: 0; left: 0; right: 0;
         padding: 2.5rem 1.4rem 1.4rem;
         background: linear-gradient(to top, rgba(0,0,0,.68) 0%, transparent 100%);
         color: #fff;
      }

      .cat-grid-card .cat-card-label h3 {
         font-size: 1.7rem; font-weight: 700;
         text-transform: uppercase; letter-spacing: .04em;
         margin: 0; color: #fff; line-height: 1.2;
      }

      .cat-badge {
         position: absolute; top: 1.2rem; left: 1.2rem;
         font-size: 1.1rem; font-weight: 700; letter-spacing: .06rem;
         text-transform: uppercase; background: #fff; color: #111;
         border-radius: 3rem; padding: .4rem 1rem; z-index: 2; pointer-events: none;
      }

      /* ============================================================
         PROMO SPLIT
      ============================================================ */
      .promo-split {
         display: grid;
         grid-template-columns: 1fr 1fr;
         min-height: 62vh;
      }

      .promo-split-text {
         display: flex;
         flex-direction: column;
         justify-content: center;
         padding: 7rem 6rem 7rem 7rem;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
      }

      .promo-split-text .promo-eyebrow {
         font-size: 1.3rem;
         font-weight: 700;
         letter-spacing: .22rem;
         text-transform: uppercase;
         color: rgba(255,255,255,.65);
         margin-bottom: 2.4rem;
         font-family: 'Courier New', Courier, monospace;
      }

      .promo-split-text h2 {
         font-family: 'Courier New', Courier, monospace;
         font-size: clamp(3.2rem, 3.8vw, 5.2rem);
         font-weight: 900;
         line-height: 1.1;
         text-transform: uppercase;
         letter-spacing: -.01em;
         color: #ffffff;
         margin: 0 0 3.5rem;
      }

      .promo-split-btn {
         display: inline-block;
         padding: 1.6rem 3.2rem;
         background: #0f2027;
         color: #ffffff;
         font-size: 1.4rem;
         font-weight: 700;
         letter-spacing: .12rem;
         text-transform: uppercase;
         font-family: 'Courier New', Courier, monospace;
         border: 2px solid #ffffff;
         transition: .25s ease;
         align-self: flex-start;
      }

      .promo-split-btn:hover {
         background: transparent;
         color: #ffffff;
      }

      .promo-split-img {
         position: relative;
         overflow: hidden;
      }

      .promo-split-img img {
         width: 100%; height: 100%;
         object-fit: cover; display: block;
         transition: transform .65s cubic-bezier(.25,.46,.45,.94);
      }

      .promo-split-img:hover img { transform: scale(1.05); }

      /* ============================================================
         TICKER
      ============================================================ */
      .promo-ticker {
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         overflow: hidden;
         padding: 1.7rem 0;
         white-space: nowrap;
         user-select: none;
         position: relative;
      }

      .promo-ticker::before {
         content: "";
         position: absolute; inset: 0;
         background: radial-gradient(circle at 70% 40%, rgba(0,150,255,.25), transparent 60%),
                     radial-gradient(circle at 30% 60%, rgba(100,0,255,.2), transparent 60%);
         pointer-events: none;
      }

      .promo-ticker-track {
         display: inline-flex;
         animation: ticker-scroll 32s linear infinite;
      }

      .promo-ticker-track:hover { animation-play-state: paused; }

      .promo-ticker-item {
         display: inline-flex; align-items: center; gap: 1.8rem;
         padding: 0 3rem;
         font-family: 'Courier New', Courier, monospace;
         font-size: 1.5rem; font-weight: 700;
         letter-spacing: .15rem; text-transform: uppercase; color: #fdf6ee;
      }

      .promo-ticker-dot {
         width: .65rem; height: .65rem; border-radius: 50%;
         background: rgba(253,246,238,.55); flex-shrink: 0;
      }

      @keyframes ticker-scroll {
         0%   { transform: translateX(0); }
         100% { transform: translateX(-50%); }
      }

      /* ============================================================
         PRODUK TERBARU — LAYOUT BARU (inspirasi ShoEZ)
      ============================================================ */
      .latest-products {
         padding: 7rem 5%;
         background: #fff;
      }

      .latest-products .lp-header {
         text-align: center;
         margin-bottom: 1.4rem;
      }

      .latest-products .lp-header h2 {
         font-size: clamp(2.8rem, 4vw, 4.2rem);
         font-weight: 800;
         color: #111;
         letter-spacing: -.02em;
         margin: 0 0 1rem;
      }

      .latest-products .lp-header p {
         font-size: 1.7rem;
         color: #888;
         max-width: 55rem;
         margin: 0 auto 3.6rem;
         line-height: 1.7;
      }

      /* Grid produk — 3 kartu per baris */
      .lp-grid {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 2.4rem;
      }

      /* Kartu produk */
      .lp-card {
         background: #f7f8fc;
         border-radius: 1.6rem;
         overflow: hidden;
         display: flex;
         flex-direction: column;
         transition: transform .28s ease, box-shadow .28s ease;
         border: 1.5px solid #ececec;
         position: relative;
      }

      .lp-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 1.6rem 4rem rgba(26,111,255,.13);
      }

      /* Nama produk di atas gambar */
      .lp-card-top {
         padding: 2rem 2.4rem 1rem;
      }

      .lp-card-top .lp-name {
         font-size: 1.9rem;
         font-weight: 700;
         color: #111;
         line-height: 1.3;
         margin: 0 0 .4rem;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }

      .lp-card-top .lp-shop-link {
         font-size: 1.4rem;
         font-weight: 700;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         background-clip: text;
         text-decoration: none;
         letter-spacing: .03rem;
      }

      .lp-card-top .lp-shop-link:hover {
         opacity: .75;
      }

      /* Gambar produk */
      .lp-card-img {
         position: relative;
         width: 100%;
         aspect-ratio: 1 / 1;
         overflow: hidden;
         background: #eef0f7;
         flex: 1;
      }

      .lp-card-img img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         display: block;
         transition: transform .4s cubic-bezier(.25,.46,.45,.94);
      }

      .lp-card:hover .lp-card-img img {
         transform: scale(1.07);
      }

      /* Icon wishlist & eye */
      .lp-card .lp-actions {
         position: absolute;
         top: 1rem; right: 1rem;
         display: flex; flex-direction: column; gap: .6rem;
         z-index: 5;
      }

      .lp-card .lp-actions a,
      .lp-card .lp-actions button {
         width: 3.8rem; height: 3.8rem;
         border-radius: 50%;
         background: #fff;
         box-shadow: 0 .2rem .8rem rgba(0,0,0,.13);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.6rem; color: #111;
         cursor: pointer; border: none;
         transition: background .2s, color .2s;
      }

      .lp-card .lp-actions a:hover,
      .lp-card .lp-actions button:hover {
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         color: #fff;
      }

      /* Footer kartu — harga, qty, tombol */
      .lp-card-footer {
         padding: 1.6rem 2.4rem 2rem;
         display: flex;
         flex-direction: column;
         gap: 1.2rem;
         background: #fff;
      }

      .lp-card-footer .lp-price-row {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1rem;
      }

      .lp-card-footer .lp-price {
         font-size: 2rem;
         font-weight: 800;
         color: #e74c3c;
      }

      .lp-card-footer .lp-qty {
         width: 7rem; height: 4rem;
         border: 1.5px solid #ddd;
         border-radius: .6rem;
         text-align: center;
         font-size: 1.6rem; color: #333;
      }

      .lp-card-footer .lp-btn-cart {
         width: 100%;
         padding: 1.2rem;
         border: none; border-radius: .8rem;
         font-size: 1.5rem; font-weight: 700;
         cursor: pointer; letter-spacing: .04rem;
         color: #fff;
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000);
         transition: opacity .2s, transform .2s;
      }

      .lp-card-footer .lp-btn-cart:hover {
         opacity: .85;
         transform: translateY(-2px);
      }

      /* ============================================================
         RESPONSIVE
      ============================================================ */
      @media (max-width: 1200px) {
         .cat-layout { grid-template-columns: 26rem 1fr; }
         .promo-split-text { padding: 5rem 4rem 5rem 5rem; }
      }

      @media (max-width: 900px) {
         .cat-layout { grid-template-columns: 1fr; }
         .cat-hero-card { aspect-ratio: 16 / 9; }
         .cat-grid { grid-template-columns: repeat(3, 1fr); }
         .promo-split { grid-template-columns: 1fr; }
         .promo-split-img { min-height: 55vw; }
         .promo-split-text { padding: 5rem 5%; order: 2; }
         .promo-split-img { order: 1; }
         .lp-grid { grid-template-columns: repeat(2, 1fr); }
      }

      @media (max-width: 640px) {
         .cat-grid { grid-template-columns: repeat(2, 1fr); }
         .category-editorial { padding: 4rem 4%; }
         .hero-content { left: 5%; right: 5%; }
         .hero-content h1 { font-size: clamp(3rem, 10vw, 5rem); }
         .hero-content p { font-size: 1.7rem; }
         .promo-split-text h2 { font-size: clamp(2.8rem, 8vw, 4.2rem); }
         .promo-ticker-item { font-size: 1.35rem; padding: 0 2rem; }
         .lp-grid { grid-template-columns: 1fr; }
         .latest-products { padding: 5rem 4%; }
      }

      /* =========================
      FOOTER (FIX FINAL)
      ========================= */

      .footer{
         background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000000) !important;
         color: #fff !important;
         padding: 3rem 2rem;
      }

      /* grid layout */
      .footer .grid{
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
         gap: 2rem;
      }

      /* title */
      .footer .grid .box h3{
         font-size:2rem;
         color:#fff !important;
         margin-bottom:2rem;
         text-transform:capitalize;
      }

      /* link */
      .footer .grid .box a{
         display:block;
         margin:1.5rem 0;
         font-size:1.7rem;
         color:rgba(255,255,255,.7) !important;
         transition:.3s;
      }

      /* icon */
      .footer .grid .box a i{
         padding-right:1rem;
         color:#fff !important;
         transition:.3s;
      }

      /* hover */
      .footer .grid .box a:hover{
         color:#fff !important;
         transform: translateX(5px);
      }

      .footer .grid .box a:hover i{
         padding-right:2rem;
      }

      /* bottom credit */
      .footer .credit{
         text-align:center;
         padding:2.5rem 2rem;
         border-top:1px solid rgba(255,255,255,.15);
         font-size:1.8rem;
         color:rgba(255,255,255,.8) !important;
      }

      /* highlight */
      .footer .credit span{
         color:#00d4ff !important;
      }

      .footer{
         box-shadow: inset 0 20px 40px rgba(0,0,0,0.6);
      }

   </style>

</head>

<body class="home-modern">

   <?php include 'components/user_header.php'; ?>

   <!-- ===== HERO VIDEO ===== -->
   <div class="hero-fashion">
      <video autoplay muted loop playsinline>
         <source src="video/Jordan Galaxy Vid2.mp4" type="video/mp4">
      </video>
      <div class="hero-overlay"></div>
      <div class="hero-content">
         <span class="hero-mini">Premium Fashion Collection</span>
         <h1>
            Elevate Your<br>
            <strong>Style</strong> With<br>
            Gals_Collection
         </h1>
         <p>
            Temukan tas, dompet, sepatu, dan aksesoris pilihan dengan model stylish, elegan, dan siap menunjang penampilan terbaikmu setiap hari.
         </p>
         <a href="shop.php" class="hero-btn-main">Shop Now</a>
      </div>
   </div>

   <!-- ===== KATEGORI ===== -->
   <div class="category-editorial">

      <div class="cat-header">
         <h2>Kategori</h2>
         <a href="shop.php" class="cat-view-all">Lihat Semua &rarr;</a>
      </div>

      <div class="cat-layout">

         <a href="category.php?category=Tote" class="cat-hero-card">
            <img src="Kategori_images/Tote.jpeg" alt="Tote">
            <div class="cat-card-label">
               <h3>Tote</h3>
               <span>Lihat Koleksi</span>
            </div>
         </a>

         <div class="cat-grid">

            <a href="category.php?category=Slingbag" class="cat-grid-card">
               <img src="Kategori_images/Slingbag.jpeg" alt="Slingbag">
               <div class="cat-card-label"><h3>Slingbag</h3></div>
            </a>

            <a href="category.php?category=Dompet" class="cat-grid-card">
               <span class="cat-badge">New</span>
               <img src="Kategori_images/Dompet.jpeg" alt="Dompet">
               <div class="cat-card-label"><h3>Dompet</h3></div>
            </a>

            <a href="category.php?category=Heels" class="cat-grid-card">
               <img src="Kategori_images/Heels.jpeg" alt="Heels">
               <div class="cat-card-label"><h3>Heels</h3></div>
            </a>

            <a href="category.php?category=Flat Shoes" class="cat-grid-card">
               <span class="cat-badge">Best Seller</span>
               <img src="Kategori_images/Flat.jpeg" alt="Flat Shoes">
               <div class="cat-card-label"><h3>Flat Shoes</h3></div>
            </a>

            <a href="category.php?category=Top Handle" class="cat-grid-card">
               <img src="Kategori_images/TopHandle.jpeg" alt="Top Handle">
               <div class="cat-card-label"><h3>Top Handle</h3></div>
            </a>

            <a href="category.php?category=Clutch" class="cat-grid-card">
               <img src="Kategori_images/Clutch.jpeg" alt="Clutch">
               <div class="cat-card-label"><h3>Clutch</h3></div>
            </a>

         </div>
      </div>

      <div style="margin-top:1.6rem;">
         <a href="category.php?category=Ransel" class="cat-grid-card"
            style="display:block; aspect-ratio:21/6; border-radius:1.4rem;">
            <img src="Kategori_images/Ransel.png" alt="Ransel"
                 style="width:100%;height:100%;object-fit:cover;display:block;
                        transition:transform .55s cubic-bezier(.25,.46,.45,.94);">
            <div class="cat-card-label"
                 style="padding:3rem 2.4rem 2rem;
                        background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 100%);">
               <h3 style="font-size:2.6rem;">Ransel</h3>
            </div>
         </a>
      </div>

   </div>

   <!-- ===== PROMO SPLIT ===== -->
   <div class="promo-split">

      <div class="promo-split-text">
         <span class="promo-eyebrow">Koleksi Spesial</span>
         <h2>
            Setiap Detail<br>
            Mencerminkan<br>
            Gaya Kamu
         </h2>
         <a href="shop.php" class="promo-split-btn">Belanja Sekarang</a>
      </div>

      <div class="promo-split-img">
         <img src="Kategori_images/TopHandleCoach1.webp" alt="Koleksi Gals Collection">
      </div>

   </div>

   <!-- ===== TICKER ===== -->
   <div class="promo-ticker" aria-hidden="true">
      <div class="promo-ticker-track">
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Flash Sale Setiap Hari Jumat</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Dapatkan Produk Spesial</span>
         <span class="promo-ticker-item"><span class="promo-ticker-dot"></span>Koleksi Terbaru Sudah Hadir</span>
      </div>
   </div>

   <!-- ===== PRODUK TERBARU — LAYOUT BARU ===== -->
   <Div class="latest-products">

      <div class="lp-header">
         <h2>Produk Terbaru</h2>
         <p>Temukan koleksi terbaru pilihan kami — stylish, berkualitas, dan siap untuk kamu.</p>
      </div>

      <div class="lp-grid">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="lp-card">
                  <input type="hidden" name="pid"   value="<?= $fetch_product['id']; ?>">
                  <input type="hidden" name="name"  value="<?= htmlspecialchars($fetch_product['name']); ?>">
                  <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price']); ?>">
                  <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01']); ?>">

                  <!-- Nama & Shop Now di atas -->
                  <div class="lp-card-top">
                     <div class="lp-name"><?= htmlspecialchars($fetch_product['name']); ?></div>
                     <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="lp-shop-link">Shop Now →</a>
                  </div>

                  <!-- Gambar produk -->
                  <div class="lp-card-img">
                     <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="">

                     <!-- Ikon wishlist & eye di atas gambar -->
                     <div class="lp-actions">
                        <button type="submit" name="add_to_wishlist" title="Wishlist">
                           <i class="fas fa-heart"></i>
                        </button>
                        <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" title="Quick View">
                           <i class="fas fa-eye"></i>
                        </a>
                     </div>
                  </div>

                  <!-- Footer kartu -->
                  <div class="lp-card-footer">
                     <div class="lp-price-row">
                        <div class="lp-price">RP <?= htmlspecialchars($fetch_product['price']); ?></div>
                        <input type="number" name="qty" class="lp-qty" min="1" max="99"
                               onkeypress="if(this.value.length==2)return false;" value="1">
                     </div>
                     <button type="submit" name="add_to_cart" class="lp-btn-cart">
                        Masukkan Ke Keranjang
                     </button>
                  </div>

               </form>
         <?php
            }
         } else {
            echo '<p class="empty" style="grid-column:1/-1;text-align:center;">Belum ada produk yang ditambahkan!</p>';
         }
         ?>

      </div>

      </div>

   <?php include 'components/footer.php'; ?>

   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
   <script src="js/script.js"></script>

</body>

</html>