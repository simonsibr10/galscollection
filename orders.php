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
</head>
<body class="php-orders">

<?php include 'components/user_header.php'; ?>

<div class="orders-page">

   <!-- Breadcrumb -->
   <nav class="orders-breadcrumb">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <span class="u-inline-style-006">Pesanan Saya</span>
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