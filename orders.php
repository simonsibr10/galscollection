<?php

include 'components/connect.php';
include 'components/crypto.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

$status_filter = '';
if(isset($_GET['status'])){
   $allowed_status = ['diproses','dikemas','dikirim','selesai'];
   if(in_array($_GET['status'], $allowed_status)) $status_filter = $_GET['status'];
}

function format_payment_status($status){
   $s = strtolower(trim($status));
   if($s=='pending')    return 'Menunggu Pembayaran';
   if($s=='completed')  return 'Pembayaran Berhasil';
   return ucfirst($s);
}

// Count orders per status for summary
$count_data = [];
if($user_id != ''){
   foreach(['all','diproses','dikemas','dikirim','selesai'] as $s){
      if($s === 'all'){
         $q = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE user_id = ?");
         $q->execute([$user_id]);
      } else {
         $q = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE user_id = ? AND shipping_status = ?");
         $q->execute([$user_id, $s]);
      }
      $count_data[$s] = (int)$q->fetchColumn();
   }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pesanan Saya — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }

      /* ===== PAGE ===== */
      .orders-page {
         max-width: 1200px;
         margin: 0 auto;
         padding: 3rem 2.4rem 6rem;
      }

      /* Breadcrumb */
      .orders-breadcrumb {
         font-size: 1.4rem;
         color: #94a3b8;
         margin-bottom: 2.4rem;
         display: flex;
         align-items: center;
         gap: .6rem;
         animation: fadeSlideDown .4s ease both;
      }

      .orders-breadcrumb a { color: #94a3b8; text-decoration: none; transition: color .15s; }
      .orders-breadcrumb a:hover { color: #1a2a6c; }
      .orders-breadcrumb i { font-size: 1.1rem; color: #cbd5e1; }

      /* Page header */
      .orders-page-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 1rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideDown .45s ease both;
      }

      .orders-page-header h1 {
         font-size: 2.8rem;
         font-weight: 800;
         color: #1e293b;
         letter-spacing: -.4px;
      }

      .orders-page-header h1 i { color: #e11d48; margin-right: .5rem; }

      /* ===== SUMMARY STRIP ===== */
      .orders-summary-strip {
         display: grid;
         grid-template-columns: repeat(5, 1fr);
         gap: 1rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideUp .5s ease both;
      }

      .summary-chip {
         background: #fff;
         border-radius: 1rem;
         padding: 1.4rem 1.2rem;
         display: flex;
         flex-direction: column;
         align-items: center;
         text-align: center;
         gap: .4rem;
         box-shadow: 0 2px 10px rgba(0,0,0,.05);
         position: relative;
         overflow: hidden;
         transition: transform .2s, box-shadow .2s;
      }

      .summary-chip:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }

      .summary-chip::before {
         content: '';
         position: absolute;
         top: 0; left: 0; right: 0;
         height: 3px;
         border-radius: 1rem 1rem 0 0;
      }

      .chip-all::before    { background: linear-gradient(90deg,#1a2a6c,#4f6ef7); }
      .chip-proc::before   { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
      .chip-pack::before   { background: linear-gradient(90deg,#0891b2,#22d3ee); }
      .chip-ship::before   { background: linear-gradient(90deg,#059669,#34d399); }
      .chip-done::before   { background: linear-gradient(90deg,#64748b,#94a3b8); }

      .summary-chip-num  { font-size: 2.4rem; font-weight: 800; color: #1e293b; line-height: 1; }
      .summary-chip-lbl  { font-size: 1.2rem; font-weight: 600; color: #64748b; }

      /* ===== FILTER TABS ===== */
      .order-filter-tabs {
         display: flex;
         flex-wrap: wrap;
         gap: .7rem;
         margin-bottom: 2.4rem;
         background: #fff;
         padding: 1.2rem 1.6rem;
         border-radius: 1.2rem;
         box-shadow: 0 2px 10px rgba(0,0,0,.05);
         animation: fadeSlideUp .5s .05s ease both;
      }

      .filter-tab {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         padding: .8rem 1.6rem;
         border-radius: 3rem;
         font-size: 1.3rem;
         font-weight: 600;
         color: #475569;
         background: #f1f5f9;
         text-decoration: none;
         border: 1.5px solid transparent;
         transition: all .18s;
         white-space: nowrap;
      }

      .filter-tab:hover { background: #e0e7ff; color: #1a2a6c; }

      .filter-tab.active {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         box-shadow: 0 4px 12px rgba(79,110,247,.3);
      }

      .tab-count {
         background: rgba(255,255,255,.22);
         border-radius: 1rem;
         padding: .1rem .6rem;
         font-size: 1.1rem;
         font-weight: 700;
      }

      .filter-tab:not(.active) .tab-count { background: #e2e8f0; color: #475569; }

      /* ===== ORDERS GRID ===== */
      .orders-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(34rem, 1fr));
         gap: 1.8rem;
      }

      /* ===== ORDER CARD ===== */
      .order-card {
         background: #fff;
         border-radius: 1.4rem;
         overflow: hidden;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         display: flex;
         flex-direction: column;
         transition: transform .22s, box-shadow .22s;
         animation: fadeSlideUp .5s ease both;
         position: relative;
      }

      .order-card:hover { transform: translateY(-5px); box-shadow: 0 14px 36px rgba(0,0,0,.1); }

      /* Top bar color */
      .order-card-bar { height: 4px; width: 100%; }
      .bar-diproses { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
      .bar-dikemas  { background: linear-gradient(90deg,#0891b2,#22d3ee); }
      .bar-dikirim  { background: linear-gradient(90deg,#059669,#34d399); }
      .bar-selesai  { background: linear-gradient(90deg,#64748b,#94a3b8); }

      .order-card-inner { padding: 1.8rem; display: flex; flex-direction: column; gap: 1.2rem; flex: 1; }

      /* Header */
      .order-card-head {
         display: flex;
         align-items: flex-start;
         justify-content: space-between;
         gap: .8rem;
      }

      .order-num  { font-size: 1.5rem; font-weight: 800; color: #1e293b; letter-spacing: -.2px; }
      .order-date { font-size: 1.25rem; color: #94a3b8; margin-top: .3rem; display: flex; align-items: center; gap: .4rem; }

      /* Status badges */
      .status-badge {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         padding: .5rem 1.1rem;
         border-radius: 2rem;
         font-size: 1.2rem;
         font-weight: 700;
         white-space: nowrap;
         flex-shrink: 0;
      }

      .badge-diproses { background: #fffbeb; color: #b45309; }
      .badge-dikemas  { background: #ecfeff; color: #0e7490; }
      .badge-dikirim  { background: #ecfdf5; color: #065f46; }
      .badge-selesai  { background: #f1f5f9; color: #475569; }

      /* Info section */
      .order-card-info {
         background: #f8fafc;
         border-radius: .8rem;
         padding: 1.2rem;
         display: flex;
         flex-direction: column;
         gap: .8rem;
      }

      .info-row {
         display: flex;
         align-items: flex-start;
         gap: .8rem;
         font-size: 1.3rem;
      }

      .info-row i { color: #94a3b8; font-size: 1.2rem; margin-top: .15rem; flex-shrink: 0; width: 1.4rem; }
      .info-label { color: #94a3b8; font-weight: 600; white-space: nowrap; }
      .info-val   { color: #334155; font-weight: 500; }

      .pay-badge {
         display: inline-flex;
         align-items: center;
         gap: .3rem;
         padding: .2rem .7rem;
         border-radius: 2rem;
         font-size: 1.15rem;
         font-weight: 700;
      }

      .pay-pending   { background: #fff1f2; color: #be123c; }
      .pay-completed { background: #ecfdf5; color: #065f46; }

      .product-preview {
         font-size: 1.3rem;
         color: #334155;
         line-height: 1.5;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }

      /* Footer */
      .order-card-footer {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1rem;
         margin-top: auto;
         padding-top: 1.2rem;
         border-top: 1px solid #f1f5f9;
      }

      .order-total { font-size: 2rem; font-weight: 800; color: #e11d48; }

      .btn-order-detail {
         display: inline-flex;
         align-items: center;
         gap: .5rem;
         padding: .9rem 1.8rem;
         border-radius: .8rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         font-size: 1.35rem;
         font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
         white-space: nowrap;
      }

      .btn-order-detail:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79,110,247,.3); }

      /* ===== EMPTY STATE ===== */
      .orders-empty {
         grid-column: 1/-1;
         text-align: center;
         padding: 7rem 2rem;
         background: #fff;
         border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.04);
         color: #94a3b8;
      }

      .orders-empty i { font-size: 5.6rem; display: block; margin-bottom: 1.6rem; color: #cbd5e1; }
      .orders-empty h3 { font-size: 2rem; font-weight: 700; color: #475569; margin-bottom: .8rem; }
      .orders-empty p  { font-size: 1.4rem; margin-bottom: 2.4rem; }

      .btn-shop-now {
         display: inline-flex;
         align-items: center;
         gap: .7rem;
         padding: 1.2rem 2.8rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border-radius: 1rem;
         font-size: 1.5rem;
         font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .2s;
      }

      .btn-shop-now:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      .order-card:nth-child(1){animation-delay:.05s} .order-card:nth-child(2){animation-delay:.10s}
      .order-card:nth-child(3){animation-delay:.15s} .order-card:nth-child(4){animation-delay:.20s}
      .order-card:nth-child(n+5){animation-delay:.25s}

      @media(max-width:900px){
         .orders-page{padding:2rem 1.6rem 4rem}
         .orders-summary-strip{grid-template-columns:repeat(3,1fr)}
         .orders-grid{grid-template-columns:1fr}
      }

      @media(max-width:580px){
         .orders-summary-strip{grid-template-columns:repeat(2,1fr)}
         .order-card-footer{flex-direction:column;align-items:stretch}
         .btn-order-detail{justify-content:center}
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="orders-page">

   <!-- Breadcrumb -->
   <nav class="orders-breadcrumb">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <span style="color:#475569;font-weight:600;">Pesanan Saya</span>
   </nav>

   <!-- Page header -->
   <div class="orders-page-header">
      <h1><i class="fas fa-bag-shopping"></i> Pesanan Saya</h1>
   </div>

   <?php if($user_id != ''): ?>

   <!-- Summary strip -->
   <div class="orders-summary-strip">
      <div class="summary-chip chip-all">
         <div class="summary-chip-num"><?= $count_data['all'] ?? 0; ?></div>
         <div class="summary-chip-lbl">Semua</div>
      </div>
      <div class="summary-chip chip-proc">
         <div class="summary-chip-num"><?= $count_data['diproses'] ?? 0; ?></div>
         <div class="summary-chip-lbl">Diproses</div>
      </div>
      <div class="summary-chip chip-pack">
         <div class="summary-chip-num"><?= $count_data['dikemas'] ?? 0; ?></div>
         <div class="summary-chip-lbl">Dikemas</div>
      </div>
      <div class="summary-chip chip-ship">
         <div class="summary-chip-num"><?= $count_data['dikirim'] ?? 0; ?></div>
         <div class="summary-chip-lbl">Dikirim</div>
      </div>
      <div class="summary-chip chip-done">
         <div class="summary-chip-num"><?= $count_data['selesai'] ?? 0; ?></div>
         <div class="summary-chip-lbl">Selesai</div>
      </div>
   </div>

   <?php endif; ?>

   <!-- Filter tabs -->
   <div class="order-filter-tabs">
      <a href="orders.php"                 class="filter-tab <?= $status_filter===''?'active':''; ?>">
         <i class="fas fa-th-large"></i> Semua
         <span class="tab-count"><?= $count_data['all'] ?? 0; ?></span>
      </a>
      <a href="orders.php?status=diproses" class="filter-tab <?= $status_filter==='diproses'?'active':''; ?>">
         <i class="fas fa-clock"></i> Diproses
         <span class="tab-count"><?= $count_data['diproses'] ?? 0; ?></span>
      </a>
      <a href="orders.php?status=dikemas"  class="filter-tab <?= $status_filter==='dikemas'?'active':''; ?>">
         <i class="fas fa-box"></i> Dikemas
         <span class="tab-count"><?= $count_data['dikemas'] ?? 0; ?></span>
      </a>
      <a href="orders.php?status=dikirim"  class="filter-tab <?= $status_filter==='dikirim'?'active':''; ?>">
         <i class="fas fa-truck"></i> Dikirim
         <span class="tab-count"><?= $count_data['dikirim'] ?? 0; ?></span>
      </a>
      <a href="orders.php?status=selesai"  class="filter-tab <?= $status_filter==='selesai'?'active':''; ?>">
         <i class="fas fa-check-circle"></i> Selesai
         <span class="tab-count"><?= $count_data['selesai'] ?? 0; ?></span>
      </a>
   </div>

   <!-- Orders grid -->
   <div class="orders-grid">

   <?php
   if($user_id == ''){
      echo '<div class="orders-empty"><i class="fas fa-lock"></i><h3>Login Diperlukan</h3><p>Silakan login untuk melihat pesanan kamu.</p><a href="user_login.php" class="btn-shop-now"><i class="fas fa-right-to-bracket"></i> Login Sekarang</a></div>';
   } else {
      if($status_filter != ''){
         $q = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND shipping_status = ? ORDER BY id DESC");
         $q->execute([$user_id, $status_filter]);
      } else {
         $q = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
         $q->execute([$user_id]);
      }

      if($q->rowCount() > 0){
         while($fo = $q->fetch(PDO::FETCH_ASSOC)){
            $order_number    = $fo['order_number']    ?? '-';
            $shipping_status = strtolower($fo['shipping_status'] ?? 'diproses');
            $tracking_number = $fo['tracking_number'] ?? '';
            $total_products  = $fo['total_products']  ?? '';
            $total_price     = $fo['total_price']     ?? 0;
            $payment_status  = $fo['payment_status']  ?? 'pending';

            $product_summary = trim($total_products);
            if(strlen($product_summary) > 120) $product_summary = substr($product_summary,0,120).'...';

            $status_icons = ['diproses'=>'fa-clock','dikemas'=>'fa-box','dikirim'=>'fa-truck','selesai'=>'fa-check-circle'];
            $sIcon = $status_icons[$shipping_status] ?? 'fa-circle';
   ?>

   <div class="order-card">
      <div class="order-card-bar bar-<?= htmlspecialchars($shipping_status); ?>"></div>
      <div class="order-card-inner">

         <div class="order-card-head">
            <div>
               <div class="order-num"><?= htmlspecialchars($order_number); ?></div>
               <div class="order-date"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($fo['placed_on']); ?></div>
            </div>
            <span class="status-badge badge-<?= htmlspecialchars($shipping_status); ?>">
               <i class="fas <?= $sIcon; ?>"></i>
               <?= ucfirst(htmlspecialchars($shipping_status)); ?>
            </span>
         </div>

         <div class="order-card-info">
            <div class="info-row">
               <i class="fas fa-shopping-bag"></i>
               <span class="info-label">Produk:</span>
               <div class="product-preview"><?= htmlspecialchars($product_summary); ?></div>
            </div>
            <div class="info-row">
               <i class="fas fa-credit-card"></i>
               <span class="info-label">Pembayaran:</span>
               <span class="pay-badge <?= $payment_status==='completed'?'pay-completed':'pay-pending'; ?>">
                  <i class="fas <?= $payment_status==='completed'?'fa-check':'fa-hourglass-half'; ?>"></i>
                  <?= $payment_status==='completed'?'Lunas':'Pending'; ?>
               </span>
            </div>
            <div class="info-row">
               <i class="fas fa-barcode"></i>
               <span class="info-label">Resi:</span>
               <span class="info-val"><?= !empty($tracking_number)?htmlspecialchars($tracking_number):'Belum tersedia'; ?></span>
            </div>
         </div>

         <div class="order-card-footer">
            <div class="order-total">Rp <?= number_format((int)$total_price,0,',','.'); ?></div>
            <a href="order_detail.php?id=<?= $fo['id']; ?>" class="btn-order-detail">
               <i class="fas fa-eye"></i> Lihat Detail
            </a>
         </div>

      </div>
   </div>

   <?php
         }
      } else {
   ?>
      <div class="orders-empty">
         <i class="fas fa-clipboard-list"></i>
         <h3>Belum Ada Pesanan</h3>
         <p>Kamu belum memiliki pesanan <?= $status_filter?'dengan status '.htmlspecialchars($status_filter):''; ?>. Yuk mulai belanja!</p>
         <a href="shop.php" class="btn-shop-now"><i class="fas fa-bag-shopping"></i> Mulai Belanja</a>
      </div>
   <?php
      }
   }
   ?>

   </div>
</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>