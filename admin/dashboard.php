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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>

      /* ===== OVERRIDE BASE ===== */
      *, *::before, *::after { box-sizing: border-box; }

      body {
         background: #f0f2f8 !important;
         font-family: 'Segoe UI', sans-serif !important;
         margin: 0;
      }

      section, .dashboard {
         background: transparent !important;
      }

      /* ===== DASHBOARD LAYOUT ===== */
      .admin-dashboard {
         padding: 2.4rem 2.8rem 4rem;
         max-width: 1400px;
         margin: 0 auto;
      }

      /* ===== PAGE HEADER ===== */
      .dash-page-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         margin-bottom: 2.8rem;
         flex-wrap: wrap;
         gap: 1rem;
      }

      .dash-page-header h1 {
         font-size: 2.6rem;
         font-weight: 800;
         color: #0f172a;
         letter-spacing: -.5px;
      }

      .dash-page-header h1 span {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      .dash-date-badge {
         background: #fff;
         border-radius: 3rem;
         padding: .7rem 1.6rem;
         font-size: 1.3rem;
         color: #64748b;
         box-shadow: 0 2px 8px rgba(0,0,0,.07);
         display: flex;
         align-items: center;
         gap: .6rem;
      }

      .dash-date-badge i { color: #4f6ef7; }

      /* ===== WELCOME BANNER ===== */
      .dash-welcome-banner {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 50%, #2c3e8f 100%);
         border-radius: 1.6rem;
         padding: 2.8rem 3.2rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 2rem;
         margin-bottom: 2.8rem;
         position: relative;
         overflow: hidden;
         animation: fadeSlideDown .5s ease both;
      }

      .dash-welcome-banner::before {
         content: '';
         position: absolute;
         top: -40%;
         right: -5%;
         width: 36rem;
         height: 36rem;
         background: radial-gradient(circle, rgba(79,110,247,.35) 0%, transparent 70%);
         pointer-events: none;
      }

      .dash-welcome-banner::after {
         content: '';
         position: absolute;
         bottom: -50%;
         left: 20%;
         width: 28rem;
         height: 28rem;
         background: radial-gradient(circle, rgba(255,255,255,.06) 0%, transparent 70%);
         pointer-events: none;
      }

      .welcome-text h2 {
         font-size: 2.4rem;
         font-weight: 700;
         color: #fff;
         margin-bottom: .5rem;
      }

      .welcome-text p {
         font-size: 1.4rem;
         color: rgba(255,255,255,.65);
      }

      .welcome-text strong {
         color: #7eb3ff;
         font-weight: 600;
      }

      .welcome-actions {
         display: flex;
         gap: 1rem;
         flex-wrap: wrap;
         position: relative;
         z-index: 1;
      }

      .btn-banner {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         padding: 1rem 2rem;
         border-radius: .8rem;
         font-size: 1.4rem;
         font-weight: 600;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
         cursor: pointer;
         border: none;
      }

      .btn-banner:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }

      .btn-banner-primary {
         background: #fff;
         color: #1a2a6c;
      }

      .btn-banner-outline {
         background: rgba(255,255,255,.12);
         color: #fff;
         border: 1px solid rgba(255,255,255,.25);
      }

      /* ===== SECTION LABEL ===== */
      .dash-section-label {
         font-size: 1.1rem;
         font-weight: 700;
         letter-spacing: .12em;
         text-transform: uppercase;
         color: #94a3b8;
         margin-bottom: 1.2rem;
         margin-top: .4rem;
      }

      /* ===== STAT CARDS GRID ===== */
      .dash-stats-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(24rem, 1fr));
         gap: 1.6rem;
         margin-bottom: 2.8rem;
      }

      .stat-card {
         background: #fff;
         border-radius: 1.4rem;
         padding: 2rem 2.2rem;
         display: flex;
         flex-direction: column;
         gap: 1.2rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         transition: transform .2s, box-shadow .2s;
         animation: fadeSlideUp .5s ease both;
         position: relative;
         overflow: hidden;
      }

      .stat-card:hover {
         transform: translateY(-4px);
         box-shadow: 0 12px 32px rgba(0,0,0,.1);
      }

      /* Colored top accent line */
      .stat-card::before {
         content: '';
         position: absolute;
         top: 0; left: 0; right: 0;
         height: 4px;
         border-radius: 1.4rem 1.4rem 0 0;
      }

      .stat-card.accent-blue::before   { background: linear-gradient(90deg, #1a2a6c, #4f6ef7); }
      .stat-card.accent-amber::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
      .stat-card.accent-green::before  { background: linear-gradient(90deg, #059669, #34d399); }
      .stat-card.accent-purple::before { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
      .stat-card.accent-rose::before   { background: linear-gradient(90deg, #e11d48, #fb7185); }
      .stat-card.accent-cyan::before   { background: linear-gradient(90deg, #0891b2, #22d3ee); }
      .stat-card.accent-orange::before { background: linear-gradient(90deg, #ea580c, #fb923c); }
      .stat-card.accent-teal::before   { background: linear-gradient(90deg, #0d9488, #2dd4bf); }

      .stat-card-top {
         display: flex;
         align-items: center;
         justify-content: space-between;
      }

      .stat-icon {
         width: 4.8rem;
         height: 4.8rem;
         border-radius: 1rem;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 2rem;
      }

      .stat-icon.bg-blue   { background: #eff2ff; color: #4f6ef7; }
      .stat-icon.bg-amber  { background: #fffbeb; color: #f59e0b; }
      .stat-icon.bg-green  { background: #ecfdf5; color: #059669; }
      .stat-icon.bg-purple { background: #f5f3ff; color: #7c3aed; }
      .stat-icon.bg-rose   { background: #fff1f2; color: #e11d48; }
      .stat-icon.bg-cyan   { background: #ecfeff; color: #0891b2; }
      .stat-icon.bg-orange { background: #fff7ed; color: #ea580c; }
      .stat-icon.bg-teal   { background: #f0fdfa; color: #0d9488; }

      .stat-trend {
         font-size: 1.2rem;
         font-weight: 600;
         padding: .3rem .8rem;
         border-radius: 2rem;
      }

      .stat-trend.up   { background: #ecfdf5; color: #059669; }
      .stat-trend.down { background: #fff1f2; color: #e11d48; }
      .stat-trend.neu  { background: #f1f5f9; color: #64748b; }

      .stat-card-value {
         font-size: 2.8rem;
         font-weight: 800;
         color: #0f172a;
         line-height: 1;
         letter-spacing: -.5px;
      }

      .stat-card-value small {
         font-size: 1.6rem;
         font-weight: 600;
         color: #64748b;
      }

      .stat-card-label {
         font-size: 1.3rem;
         color: #64748b;
         font-weight: 500;
      }

      .stat-card-footer {
         border-top: 1px solid #f1f5f9;
         padding-top: 1rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
      }

      .stat-card-link {
         font-size: 1.3rem;
         font-weight: 600;
         color: #4f6ef7;
         text-decoration: none;
         display: flex;
         align-items: center;
         gap: .4rem;
         transition: gap .15s;
      }

      .stat-card-link:hover { gap: .8rem; }
      .stat-card-link i { font-size: 1.1rem; }

      /* ===== ACTIVITY / QUICK ACTIONS ===== */
      .dash-bottom-grid {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.6rem;
      }

      @media (max-width: 800px) {
         .dash-bottom-grid { grid-template-columns: 1fr; }
      }

      .dash-panel {
         background: #fff;
         border-radius: 1.4rem;
         padding: 2rem 2.2rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         animation: fadeSlideUp .6s ease both;
      }

      .dash-panel-title {
         font-size: 1.5rem;
         font-weight: 700;
         color: #0f172a;
         margin-bottom: 1.6rem;
         display: flex;
         align-items: center;
         gap: .8rem;
      }

      .dash-panel-title i {
         color: #4f6ef7;
         font-size: 1.4rem;
      }

      /* Quick action buttons */
      .quick-actions-grid {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1rem;
      }

      .quick-action-btn {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         gap: .8rem;
         padding: 1.6rem 1rem;
         border-radius: 1rem;
         text-decoration: none;
         font-size: 1.3rem;
         font-weight: 600;
         color: #334155;
         background: #f8fafc;
         border: 1px solid #e2e8f0;
         transition: all .2s;
         text-align: center;
      }

      .quick-action-btn i {
         font-size: 2rem;
         width: 4rem;
         height: 4rem;
         border-radius: .8rem;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .quick-action-btn:hover {
         background: #eff2ff;
         border-color: #c7d2fe;
         color: #1a2a6c;
         transform: translateY(-2px);
         box-shadow: 0 6px 16px rgba(79,110,247,.12);
      }

      .quick-action-btn i.qa-blue   { background: #eff2ff; color: #4f6ef7; }
      .quick-action-btn i.qa-green  { background: #ecfdf5; color: #059669; }
      .quick-action-btn i.qa-amber  { background: #fffbeb; color: #f59e0b; }
      .quick-action-btn i.qa-rose   { background: #fff1f2; color: #e11d48; }
      .quick-action-btn i.qa-purple { background: #f5f3ff; color: #7c3aed; }
      .quick-action-btn i.qa-cyan   { background: #ecfeff; color: #0891b2; }

      /* Tips list */
      .tips-list {
         display: flex;
         flex-direction: column;
         gap: 1.2rem;
      }

      .tip-item {
         display: flex;
         align-items: flex-start;
         gap: 1.2rem;
         padding: 1.2rem 1.4rem;
         border-radius: 1rem;
         background: #f8fafc;
         border-left: 3px solid #4f6ef7;
      }

      .tip-item.amber { border-left-color: #f59e0b; }
      .tip-item.green { border-left-color: #059669; }

      .tip-icon {
         width: 3.6rem;
         height: 3.6rem;
         flex-shrink: 0;
         border-radius: .8rem;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.6rem;
         background: #eff2ff;
         color: #4f6ef7;
      }

      .tip-item.amber .tip-icon { background: #fffbeb; color: #f59e0b; }
      .tip-item.green .tip-icon { background: #ecfdf5; color: #059669; }

      .tip-text strong {
         display: block;
         font-size: 1.3rem;
         font-weight: 600;
         color: #0f172a;
         margin-bottom: .2rem;
      }

      .tip-text span {
         font-size: 1.2rem;
         color: #64748b;
      }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown {
         from { opacity: 0; transform: translateY(-16px); }
         to   { opacity: 1; transform: translateY(0); }
      }

      @keyframes fadeSlideUp {
         from { opacity: 0; transform: translateY(20px); }
         to   { opacity: 1; transform: translateY(0); }
      }

      .stat-card:nth-child(1) { animation-delay: .05s; }
      .stat-card:nth-child(2) { animation-delay: .10s; }
      .stat-card:nth-child(3) { animation-delay: .15s; }
      .stat-card:nth-child(4) { animation-delay: .20s; }
      .stat-card:nth-child(5) { animation-delay: .25s; }
      .stat-card:nth-child(6) { animation-delay: .30s; }
      .stat-card:nth-child(7) { animation-delay: .35s; }
      .stat-card:nth-child(8) { animation-delay: .40s; }

      /* Number counter animation */
      .stat-card-value { transition: all .3s; }

      /* ===== RESPONSIVE ===== */
      @media (max-width: 700px) {
         .admin-dashboard { padding: 1.6rem; }
         .dash-welcome-banner { flex-direction: column; align-items: flex-start; }
         .dash-stats-grid { grid-template-columns: 1fr 1fr; }
         .quick-actions-grid { grid-template-columns: 1fr 1fr; }
      }

      @media (max-width: 480px) {
         .dash-stats-grid { grid-template-columns: 1fr; }
      }

      /* Header override */
      .header { background: #fff !important; }
      .footer { background: #fff !important; }

      /* Override admin_style card borders */
      .dashboard .box-container .box,
      .dashboard .box-container { border: none !important; box-shadow: none !important; }
   </style>
</head>
<body>

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