<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-dashboard">

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">
<div class="admin-dashboard">

   <!-- ===== PAGE HEADER ===== -->
   <div class="dash-page-header">
      <h1>Admin <span>Dashboard</span></h1>
      <div class="dash-date-badge">
         <i class="fas fa-calendar-alt"></i>
         <span id="current-date">Memuat tanggal...</span>
      </div>
   </div>

   <!-- ===== WELCOME BANNER ===== -->
   <div class="dash-welcome-banner">
      <div class="welcome-text">
         <h2>Selamat datang kembali, <strong><?= htmlspecialchars($fetch_profile['name'] ?? 'Admin'); ?> 👋</strong></h2>
         <p>Berikut ringkasan aktivitas toko kamu hari ini. Semua berjalan dengan baik!</p>
      </div>
      <div class="welcome-actions">
         <a href="update_profile.php" class="btn-banner btn-banner-primary">
            <i class="fas fa-user-edit"></i> Update Profil
         </a>
         <a href="add_products.php" class="btn-banner btn-banner-outline">
            <i class="fas fa-plus"></i> Tambah Produk
         </a>
      </div>
   </div>

   <!-- ===== STATS SECTION LABEL ===== -->
   <div class="dash-section-label">Ringkasan Statistik</div>

   <!-- ===== STAT CARDS ===== -->
   <div class="dash-stats-grid">

      <?php
         /* ---- Pending Orders ---- */
         $total_pendings = 0;
         $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
         $select_pendings->execute(['pending']);
         if($select_pendings->rowCount() > 0){
            while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
               $total_pendings += $fetch_pendings['total_price'];
            }
         }
      ?>
      <div class="stat-card accent-amber">
         <div class="stat-card-top">
            <div class="stat-icon bg-amber"><i class="fas fa-clock"></i></div>
            <span class="stat-trend down"><i class="fas fa-exclamation"></i> Pending</span>
         </div>
         <div>
            <div class="stat-card-value"><small>Rp</small> <?= number_format($total_pendings, 0, ',', '.'); ?></div>
            <div class="stat-card-label">Butuh di Proses</div>
         </div>
         <div class="stat-card-footer">
            <a href="placed_orders.php" class="stat-card-link">Lihat Pesanan <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Completed Orders ---- */
         $total_completes = 0;
         $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
         $select_completes->execute(['completed']);
         if($select_completes->rowCount() > 0){
            while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
               $total_completes += $fetch_completes['total_price'];
            }
         }
      ?>
      <div class="stat-card accent-green">
         <div class="stat-card-top">
            <div class="stat-icon bg-green"><i class="fas fa-check-circle"></i></div>
            <span class="stat-trend up"><i class="fas fa-arrow-up"></i> Selesai</span>
         </div>
         <div>
            <div class="stat-card-value"><small>Rp</small> <?= number_format($total_completes, 0, ',', '.'); ?></div>
            <div class="stat-card-label">Total Pendapatan</div>
         </div>
         <div class="stat-card-footer">
            <a href="placed_orders.php" class="stat-card-link">Lihat Pesanan <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Total Orders ---- */
         $select_orders = $conn->prepare("SELECT * FROM `orders`");
         $select_orders->execute();
         $number_of_orders = $select_orders->rowCount();
      ?>
      <div class="stat-card accent-blue">
         <div class="stat-card-top">
            <div class="stat-icon bg-blue"><i class="fas fa-shopping-bag"></i></div>
            <span class="stat-trend neu"><i class="fas fa-minus"></i> Total</span>
         </div>
         <div>
            <div class="stat-card-value"><?= $number_of_orders; ?></div>
            <div class="stat-card-label">Jumlah Pesanan</div>
         </div>
         <div class="stat-card-footer">
            <a href="placed_orders.php" class="stat-card-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Products ---- */
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         $number_of_products = $select_products->rowCount();
      ?>
      <div class="stat-card accent-purple">
         <div class="stat-card-top">
            <div class="stat-icon bg-purple"><i class="fas fa-box-open"></i></div>
            <span class="stat-trend up"><i class="fas fa-arrow-up"></i> Aktif</span>
         </div>
         <div>
            <div class="stat-card-value"><?= $number_of_products; ?></div>
            <div class="stat-card-label">Produk Terdaftar</div>
         </div>
         <div class="stat-card-footer">
            <a href="products.php" class="stat-card-link">Lihat Produk <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Users ---- */
         $select_users = $conn->prepare("SELECT * FROM `users`");
         $select_users->execute();
         $number_of_users = $select_users->rowCount();
      ?>
      <div class="stat-card accent-cyan">
         <div class="stat-card-top">
            <div class="stat-icon bg-cyan"><i class="fas fa-users"></i></div>
            <span class="stat-trend up"><i class="fas fa-arrow-up"></i> Bergabung</span>
         </div>
         <div>
            <div class="stat-card-value"><?= $number_of_users; ?></div>
            <div class="stat-card-label">Pengguna Terdaftar</div>
         </div>
         <div class="stat-card-footer">
            <a href="users_accounts.php" class="stat-card-link">Lihat Pengguna <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Admins ---- */
         $select_admins = $conn->prepare("SELECT * FROM `admins`");
         $select_admins->execute();
         $number_of_admins = $select_admins->rowCount();
      ?>
      <div class="stat-card accent-rose">
         <div class="stat-card-top">
            <div class="stat-icon bg-rose"><i class="fas fa-user-shield"></i></div>
            <span class="stat-trend neu"><i class="fas fa-minus"></i> Aktif</span>
         </div>
         <div>
            <div class="stat-card-value"><?= $number_of_admins; ?></div>
            <div class="stat-card-label">Jumlah Admin</div>
         </div>
         <div class="stat-card-footer">
            <a href="admin_accounts.php" class="stat-card-link">Lihat Admin <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

      <?php
         /* ---- Messages ---- */
         $select_messages = $conn->prepare("SELECT * FROM `messages`");
         $select_messages->execute();
         $number_of_messages = $select_messages->rowCount();
      ?>
      <div class="stat-card accent-teal">
         <div class="stat-card-top">
            <div class="stat-icon bg-teal"><i class="fas fa-envelope"></i></div>
            <?php if($number_of_messages > 0): ?>
               <span class="stat-trend down"><i class="fas fa-bell"></i> Baru</span>
            <?php else: ?>
               <span class="stat-trend neu"><i class="fas fa-minus"></i> Kosong</span>
            <?php endif; ?>
         </div>
         <div>
            <div class="stat-card-value"><?= $number_of_messages; ?></div>
            <div class="stat-card-label">Pesan Masuk</div>
         </div>
         <div class="stat-card-footer">
            <a href="messages.php" class="stat-card-link">Baca Pesan <i class="fas fa-arrow-right"></i></a>
         </div>
      </div>

   </div><!-- /stats-grid -->

   <!-- ===== BOTTOM GRID ===== -->
   <div class="dash-bottom-grid">

      <!-- Quick Actions -->
      <div class="dash-panel">
         <div class="dash-panel-title">
            <i class="fas fa-bolt"></i> Aksi Cepat
         </div>
         <div class="quick-actions-grid">
            <a href="add_products.php" class="quick-action-btn">
               <i class="fas fa-plus-circle qa-blue"></i>
               Tambah Produk
            </a>
            <a href="placed_orders.php" class="quick-action-btn">
               <i class="fas fa-clipboard-list qa-amber"></i>
               Kelola Pesanan
            </a>
            <a href="products.php" class="quick-action-btn">
               <i class="fas fa-boxes qa-purple"></i>
               Lihat Produk
            </a>
            <a href="chat.php" class="quick-action-btn">
               <i class="fas fa-comments qa-cyan"></i>
               Cek Pesan
            </a>
            <a href="users_accounts.php" class="quick-action-btn">
               <i class="fas fa-user-friends qa-green"></i>
               Pengguna
            </a>
            <a href="update_profile.php" class="quick-action-btn">
               <i class="fas fa-sliders-h qa-rose"></i>
               Pengaturan
            </a>
         </div>
      </div>

      <!-- Tips & Info -->
      <div class="dash-panel">
         <div class="dash-panel-title">
            <i class="fas fa-lightbulb"></i> Info & Tips
         </div>
         <div class="tips-list">
            <div class="tip-item">
               <div class="tip-icon"><i class="fas fa-chart-line"></i></div>
               <div class="tip-text">
                  <strong>Pantau Pesanan Pending</strong>
                  <span>Segera proses pesanan yang masuk agar pelanggan puas dan rating toko meningkat.</span>
               </div>
            </div>
            <div class="tip-item amber">
               <div class="tip-icon"><i class="fas fa-camera"></i></div>
               <div class="tip-text">
                  <strong>Kualitas Foto Produk</strong>
                  <span>Produk dengan foto berkualitas tinggi memiliki tingkat konversi 3× lebih tinggi.</span>
               </div>
            </div>
            <div class="tip-item green">
               <div class="tip-icon"><i class="fas fa-tag"></i></div>
               <div class="tip-text">
                  <strong>Update Stok & Harga</strong>
                  <span>Pastikan data produk selalu akurat agar tidak ada pesanan yang gagal diproses.</span>
               </div>
            </div>
         </div>
      </div>

   </div><!-- /bottom-grid -->

</div><!-- /admin-dashboard -->
</section>

<script src="../js/admin_script.js"></script>
<script>
   // Live date display
   const dateEl = document.getElementById('current-date');
   if(dateEl){
      const now = new Date();
      const options = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
      dateEl.textContent = now.toLocaleDateString('id-ID', options);
   }

   // Animated number counter
   document.querySelectorAll('.stat-card-value').forEach(el => {
      const text = el.textContent.trim();
      const isRupiah = text.includes('Rp') || el.querySelector('small');
      if(isRupiah) return; // skip rupiah cards (already formatted)

      const target = parseInt(el.textContent.replace(/\D/g,''));
      if(isNaN(target) || target === 0) return;

      let start = 0;
      const duration = 700;
      const step = Math.ceil(target / (duration / 16));

      const timer = setInterval(() => {
         start += step;
         if(start >= target){
            start = target;
            clearInterval(timer);
         }
         el.textContent = start.toLocaleString('id-ID');
      }, 16);
   });
</script>
</body>
</html>