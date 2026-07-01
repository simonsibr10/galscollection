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
   <link rel="stylesheet" href="css/style.css?v=4">
</head>

<body class="home-modern php-index">

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
   <div class="category-editorial" id="kategori">
      <span class="cat-spark cat-spark-one" aria-hidden="true"></span>
      <span class="cat-spark cat-spark-two" aria-hidden="true"></span>
      <span class="cat-spark cat-spark-three" aria-hidden="true"></span>

      <div class="cat-header">
         <div class="cat-title">
            <h2>Kategori</h2>
            <p>Temukan koleksi pilihan yang sesuai dengan gaya dan kebutuhanmu setiap hari.</p>
         </div>
         <a href="shop.php" class="cat-view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="cat-layout">

         <div class="cat-grid" aria-label="Kategori produk Gals Collection">

            <a href="category.php?category=Tote" class="cat-grid-card cat-card-tote" aria-label="Lihat kategori Tote">
               <img src="Kategori_images/Tote.jpeg" alt="Tote">
               <div class="cat-card-label">
                  <h3>Tote</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Slingbag" class="cat-grid-card cat-card-sling" aria-label="Lihat kategori Slingbag">
               <img src="Kategori_images/Slingbag.jpeg" alt="Slingbag">
               <div class="cat-card-label">
                  <h3>Slingbag</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Dompet" class="cat-grid-card cat-card-dompet" aria-label="Lihat kategori Dompet">
               <span class="cat-badge cat-badge-new"><i class="fas fa-circle"></i> New</span>
               <img src="Kategori_images/Dompet.jpeg" alt="Dompet">
               <div class="cat-card-label">
                  <h3>Dompet</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Heels" class="cat-grid-card cat-card-heels" aria-label="Lihat kategori Heels">
               <img src="Kategori_images/Heels.jpeg" alt="Heels">
               <div class="cat-card-label">
                  <h3>Heels</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Flat Shoes" class="cat-grid-card cat-card-flat" aria-label="Lihat kategori Flat Shoes">
               <span class="cat-badge cat-badge-best"><i class="fas fa-crown"></i> Best Seller</span>
               <img src="Kategori_images/Flat.jpeg" alt="Flat Shoes">
               <div class="cat-card-label">
                  <h3>Flat Shoes</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Top Handle" class="cat-grid-card cat-card-tophandle" aria-label="Lihat kategori Top Handle">
               <img src="Kategori_images/TopHandle.jpeg" alt="Top Handle">
               <div class="cat-card-label">
                  <h3>Top Handle</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Clutch" class="cat-grid-card cat-card-clutch" aria-label="Lihat kategori Clutch">
               <img src="Kategori_images/Clutch.jpeg" alt="Clutch">
               <div class="cat-card-label">
                  <h3>Clutch</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <a href="category.php?category=Ransel" class="cat-grid-card cat-card-ransel" aria-label="Lihat kategori Ransel">
               <img src="Kategori_images/Ransel.jpeg" alt="Ransel">
               <div class="cat-card-label">
                  <h3>Ransel</h3>
                  <span>Lihat Koleksi <i class="fas fa-arrow-right"></i></span>
               </div>
            </a>

            <div class="cat-orbit" aria-hidden="true">
               <span>Jelajahi</span>
               <i class="fas fa-star"></i>
               <span>Koleksi</span>
            </div>

         </div>
      </div>

      <div class="cat-benefits" aria-label="Keunggulan belanja di Gals Collection">
         <div class="cat-benefit">
            <i class="fas fa-award"></i>
            <div>
               <strong>Kualitas Terjamin</strong>
               <span>Produk original &amp; berkualitas</span>
            </div>
         </div>
         <div class="cat-benefit">
            <i class="fas fa-truck-fast"></i>
            <div>
               <strong>Pengiriman Cepat</strong>
               <span>Pengiriman ke seluruh Indonesia</span>
            </div>
         </div>
         <div class="cat-benefit">
            <i class="fas fa-shield-halved"></i>
            <div>
               <strong>Pembayaran Aman</strong>
               <span>100% transaksi aman</span>
            </div>
         </div>
         <div class="cat-benefit">
            <i class="fas fa-headset"></i>
            <div>
               <strong>Customer Service</strong>
               <span>Siap membantu 24/7</span>
            </div>
         </div>
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
            echo '<p class="empty u-inline-style-020">Belum ada produk yang ditambahkan!</p>';
         }
         ?>

      </div>

      </div>

   <?php include 'components/footer.php'; ?>

   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
   <script src="js/script.js"></script>

</body>

</html>
