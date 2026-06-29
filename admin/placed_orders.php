<?php

include '../components/connect.php';
include '../components/crypto.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

$status_filter = '';

if(isset($_GET['status'])){
   $allowed_status = ['diproses', 'dikemas', 'dikirim', 'selesai'];
   if(in_array($_GET['status'], $allowed_status)){
      $status_filter = $_GET['status'];
   }
}

// Count per status
$counts = [];
foreach(['diproses','dikemas','dikirim','selesai'] as $s){
   $q = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE shipping_status = ?");
   $q->execute([$s]);
   $counts[$s] = $q->fetchColumn();
}
$q_all = $conn->prepare("SELECT COUNT(*) FROM `orders`");
$q_all->execute();
$counts['all'] = $q_all->fetchColumn();

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kelola Pesanan</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      section, .orders, .dashboard { background: transparent !important; }
      .header { background: #fff !important; }
      .footer { background: #fff !important; }

      /* ===== PAGE WRAPPER ===== */
      .orders-page {
         max-width: 1400px;
         margin: 0 auto;
         padding: 2.4rem 2.8rem 5rem;
      }

      /* ===== PAGE HEADER ===== */
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
         font-size: 2.6rem;
         font-weight: 800;
         color: #0f172a;
         letter-spacing: -.5px;
      }

      .orders-page-header h1 span {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      .orders-date-badge {
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

      .orders-date-badge i { color: #4f6ef7; }

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
         padding: 1.4rem 1.6rem;
         display: flex;
         flex-direction: column;
         align-items: flex-start;
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

      .summary-chip.chip-all::before    { background: linear-gradient(90deg,#1a2a6c,#4f6ef7); }
      .summary-chip.chip-proc::before   { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
      .summary-chip.chip-pack::before   { background: linear-gradient(90deg,#0891b2,#22d3ee); }
      .summary-chip.chip-ship::before   { background: linear-gradient(90deg,#059669,#34d399); }
      .summary-chip.chip-done::before   { background: linear-gradient(90deg,#64748b,#94a3b8); }

      .summary-chip-count {
         font-size: 2.4rem;
         font-weight: 800;
         color: #0f172a;
         line-height: 1;
      }

      .summary-chip-label {
         font-size: 1.2rem;
         font-weight: 600;
         color: #64748b;
         text-transform: uppercase;
         letter-spacing: .06em;
      }

      /* ===== FILTER TABS ===== */
      .filter-tabs {
         display: flex;
         flex-wrap: wrap;
         gap: .8rem;
         margin-bottom: 2rem;
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
         transition: all .2s;
         white-space: nowrap;
      }

      .filter-tab:hover { background: #e0e7ff; color: #1a2a6c; border-color: #c7d2fe; }

      .filter-tab.active {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         border-color: transparent;
         box-shadow: 0 4px 12px rgba(79,110,247,.3);
      }

      .filter-tab .tab-count {
         background: rgba(255,255,255,.25);
         border-radius: 1rem;
         padding: .1rem .6rem;
         font-size: 1.1rem;
         font-weight: 700;
      }

      .filter-tab:not(.active) .tab-count {
         background: #e2e8f0;
         color: #475569;
      }

      /* ===== ORDERS GRID ===== */
      .orders-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(32rem, 1fr));
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

      /* Top colored bar */
      .order-card-bar {
         height: 4px;
         width: 100%;
      }

      .bar-diproses { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
      .bar-dikemas  { background: linear-gradient(90deg,#0891b2,#22d3ee); }
      .bar-dikirim  { background: linear-gradient(90deg,#059669,#34d399); }
      .bar-selesai  { background: linear-gradient(90deg,#64748b,#94a3b8); }

      .order-card-inner { padding: 1.8rem; display: flex; flex-direction: column; flex: 1; gap: 1.2rem; }

      /* Header row */
      .order-card-head {
         display: flex;
         align-items: flex-start;
         justify-content: space-between;
         gap: .8rem;
      }

      .order-number {
         font-size: 1.5rem;
         font-weight: 800;
         color: #0f172a;
         letter-spacing: -.2px;
      }

      .order-date {
         font-size: 1.25rem;
         color: #94a3b8;
         margin-top: .3rem;
         display: flex;
         align-items: center;
         gap: .4rem;
      }

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

      .pay-badge {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         padding: .3rem .9rem;
         border-radius: 2rem;
         font-size: 1.15rem;
         font-weight: 700;
      }

      .pay-pending   { background: #fff1f2; color: #be123c; }
      .pay-completed { background: #ecfdf5; color: #065f46; }

      /* Info rows */
      .order-card-info {
         display: flex;
         flex-direction: column;
         gap: .8rem;
         padding: 1.2rem;
         background: #f8fafc;
         border-radius: .8rem;
      }

      .info-row {
         display: flex;
         align-items: flex-start;
         gap: .8rem;
         font-size: 1.3rem;
      }

      .info-row i { color: #94a3b8; font-size: 1.2rem; margin-top: .2rem; flex-shrink: 0; width: 1.4rem; }
      .info-row .info-label { color: #94a3b8; font-weight: 600; white-space: nowrap; }
      .info-row .info-val { color: #334155; font-weight: 500; }

      .product-summary-text {
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

      .order-total {
         font-size: 2rem;
         font-weight: 800;
         color: #e11d48;
      }

      .btn-detail {
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

      .btn-detail:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79,110,247,.3); }

      /* Empty */
      .orders-empty {
         grid-column: 1/-1;
         text-align: center;
         padding: 6rem 2rem;
         color: #94a3b8;
      }

      .orders-empty i { font-size: 5rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .orders-empty p { font-size: 1.6rem; }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown {
         from { opacity: 0; transform: translateY(-14px); }
         to   { opacity: 1; transform: translateY(0); }
      }

      @keyframes fadeSlideUp {
         from { opacity: 0; transform: translateY(18px); }
         to   { opacity: 1; transform: translateY(0); }
      }

      .order-card:nth-child(1) { animation-delay:.05s; }
      .order-card:nth-child(2) { animation-delay:.10s; }
      .order-card:nth-child(3) { animation-delay:.15s; }
      .order-card:nth-child(4) { animation-delay:.20s; }
      .order-card:nth-child(5) { animation-delay:.25s; }
      .order-card:nth-child(6) { animation-delay:.30s; }
      .order-card:nth-child(n+7) { animation-delay:.35s; }

      @media (max-width:900px) {
         .orders-page { padding: 1.6rem; }
         .orders-summary-strip { grid-template-columns: repeat(3,1fr); }
      }

      @media (max-width:600px) {
         .orders-grid { grid-template-columns: 1fr; }
         .orders-summary-strip { grid-template-columns: repeat(2,1fr); }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="orders-page">

   <!-- ===== PAGE HEADER ===== -->
   <div class="orders-page-header">
      <h1>Kelola <span>Pesanan</span></h1>
      <div class="orders-date-badge">
         <i class="fas fa-calendar-alt"></i>
         <span id="current-date"></span>
      </div>
   </div>

   <!-- ===== SUMMARY STRIP ===== -->
   <div class="orders-summary-strip">
      <div class="summary-chip chip-all">
         <div class="summary-chip-count"><?= $counts['all']; ?></div>
         <div class="summary-chip-label">Semua Pesanan</div>
      </div>
      <div class="summary-chip chip-proc">
         <div class="summary-chip-count"><?= $counts['diproses']; ?></div>
         <div class="summary-chip-label">Diproses</div>
      </div>
      <div class="summary-chip chip-pack">
         <div class="summary-chip-count"><?= $counts['dikemas']; ?></div>
         <div class="summary-chip-label">Dikemas</div>
      </div>
      <div class="summary-chip chip-ship">
         <div class="summary-chip-count"><?= $counts['dikirim']; ?></div>
         <div class="summary-chip-label">Dikirim</div>
      </div>
      <div class="summary-chip chip-done">
         <div class="summary-chip-count"><?= $counts['selesai']; ?></div>
         <div class="summary-chip-label">Selesai</div>
      </div>
   </div>

   <!-- ===== FILTER TABS ===== -->
   <div class="filter-tabs">
      <a href="placed_orders.php" class="filter-tab <?= $status_filter=='' ? 'active':'' ?>">
         <i class="fas fa-th-large"></i> Semua
         <span class="tab-count"><?= $counts['all']; ?></span>
      </a>
      <a href="placed_orders.php?status=diproses" class="filter-tab <?= $status_filter=='diproses'?'active':'' ?>">
         <i class="fas fa-clock"></i> Diproses
         <span class="tab-count"><?= $counts['diproses']; ?></span>
      </a>
      <a href="placed_orders.php?status=dikemas" class="filter-tab <?= $status_filter=='dikemas'?'active':'' ?>">
         <i class="fas fa-box"></i> Dikemas
         <span class="tab-count"><?= $counts['dikemas']; ?></span>
      </a>
      <a href="placed_orders.php?status=dikirim" class="filter-tab <?= $status_filter=='dikirim'?'active':'' ?>">
         <i class="fas fa-truck"></i> Dikirim
         <span class="tab-count"><?= $counts['dikirim']; ?></span>
      </a>
      <a href="placed_orders.php?status=selesai" class="filter-tab <?= $status_filter=='selesai'?'active':'' ?>">
         <i class="fas fa-check-circle"></i> Selesai
         <span class="tab-count"><?= $counts['selesai']; ?></span>
      </a>
   </div>

   <!-- ===== ORDERS GRID ===== -->
   <div class="orders-grid">

   <?php
      if($status_filter != ''){
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE shipping_status = ? ORDER BY id DESC");
         $select_orders->execute([$status_filter]);
      }else{
         $select_orders = $conn->prepare("SELECT * FROM `orders` ORDER BY id DESC");
         $select_orders->execute();
      }

      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){

            $order_name_plain = aes_decrypt($fetch_orders['name']);
            $order_name = mask_name($order_name_plain);

            $order_number      = $fetch_orders['order_number'] ?? '-';
            $shipping_status   = $fetch_orders['shipping_status'] ?? 'diproses';
            $payment_status    = $fetch_orders['payment_status'] ?? 'pending';
            $tracking_number   = $fetch_orders['tracking_number'] ?? '';
            $total_products    = $fetch_orders['total_products'] ?? '';
            $total_price       = $fetch_orders['total_price'] ?? 0;

            $status_icons = [
               'diproses' => 'fa-clock',
               'dikemas'  => 'fa-box',
               'dikirim'  => 'fa-truck',
               'selesai'  => 'fa-check-circle',
            ];
            $icon = $status_icons[$shipping_status] ?? 'fa-circle';
   ?>
   <div class="order-card">
      <div class="order-card-bar bar-<?= htmlspecialchars($shipping_status); ?>"></div>
      <div class="order-card-inner">

         <div class="order-card-head">
            <div>
               <div class="order-number"><?= htmlspecialchars($order_number); ?></div>
               <div class="order-date"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($fetch_orders['placed_on']); ?></div>
            </div>
            <span class="status-badge badge-<?= htmlspecialchars($shipping_status); ?>">
               <i class="fas <?= $icon; ?>"></i>
               <?= ucfirst(htmlspecialchars($shipping_status)); ?>
            </span>
         </div>

         <div class="order-card-info">
            <div class="info-row">
               <i class="fas fa-user"></i>
               <span class="info-label">Pembeli:</span>
               <span class="info-val"><?= htmlspecialchars($order_name); ?></span>
            </div>

            <div class="info-row">
               <i class="fas fa-shopping-bag"></i>
               <span class="info-label">Produk:</span>
               <div class="product-summary-text"><?= htmlspecialchars($total_products); ?></div>
            </div>

            <div class="info-row">
               <i class="fas fa-credit-card"></i>
               <span class="info-label">Pembayaran:</span>
               <span class="pay-badge <?= $payment_status=='completed'?'pay-completed':'pay-pending'; ?>">
                  <i class="fas <?= $payment_status=='completed'?'fa-check':'fa-hourglass-half'; ?>"></i>
                  <?= $payment_status=='completed'?'Lunas':'Pending'; ?>
               </span>
            </div>

            <div class="info-row">
               <i class="fas fa-barcode"></i>
               <span class="info-label">Resi:</span>
               <span class="info-val"><?= !empty($tracking_number) ? htmlspecialchars($tracking_number) : 'Belum ada'; ?></span>
            </div>
         </div>

         <div class="order-card-footer">
            <div class="order-total">Rp <?= number_format($total_price, 0, ',', '.'); ?></div>
            <a href="order_detail.php?id=<?= $fetch_orders['id']; ?>" class="btn-detail">
               <i class="fas fa-eye"></i> Lihat Detail
            </a>
         </div>

      </div>
   </div>
   <?php
         }
      }else{
   ?>
      <div class="orders-empty">
         <i class="fas fa-clipboard-list"></i>
         <p>Belum ada pesanan pada kategori ini.</p>
      </div>
   <?php } ?>
   </div>

</div>

<script src="../js/admin_script.js"></script>
<script>
   const d = new Date();
   document.getElementById('current-date').textContent = d.toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
</script>
</body>
</html>