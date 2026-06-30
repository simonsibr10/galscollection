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
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-placed-orders">

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