<?php

include 'components/connect.php';
include 'components/crypto.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
   header('location:orders.php');
   exit;
}

function safe_decrypt_order_value($value, $fallback = '-'){
   return aes_decrypt_display($value, $fallback);
}

function format_payment_status($status){
   $s = strtolower(trim($status));
   if($s=='pending')   return 'Menunggu Pembayaran';
   if($s=='completed') return 'Pembayaran Berhasil';
   return ucfirst($s);
}

function format_order_status($status){
   $map = ['diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai'];
   return $map[strtolower(trim($status))] ?? ucfirst($status);
}

$order_id = $_GET['id'];

$select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? LIMIT 1");
$select_order->execute([$order_id, $user_id]);

if($select_order->rowCount() == 0){ header('location:orders.php'); exit; }

$fo = $select_order->fetch(PDO::FETCH_ASSOC);

$order_name    = safe_decrypt_order_value($fo['name']);
$order_phone   = safe_decrypt_order_value($fo['number']);
$order_email   = safe_decrypt_order_value($fo['email']);
$order_address = safe_decrypt_order_value($fo['address']);

$order_number    = $fo['order_number']    ?? '-';
$tracking_number = $fo['tracking_number'] ?? '';
$shipping_status = strtolower($fo['shipping_status'] ?? 'diproses');
$payment_status  = strtolower($fo['payment_status']  ?? 'pending');
$payment_file    = $fo['payment_proof']   ?? '';
$payment_path    = 'payment_proofs/'.$payment_file;
$file_ext        = strtolower(pathinfo($payment_file, PATHINFO_EXTENSION));

$total_products_raw = $fo['total_products'] ?? '';
$product_lines = [];
if($total_products_raw != ''){
   foreach(explode(' - ', $total_products_raw) as $line){
      $line = trim($line);
      if($line != '') $product_lines[] = $line;
   }
}

$steps = ['diproses','dikemas','dikirim','selesai'];
$current_step = array_search($shipping_status, $steps);
if($current_step === false) $current_step = 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Detail Pesanan — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="php-order-detail">

<?php include 'components/user_header.php'; ?>

<div class="order-detail-page">

   <!-- Breadcrumb -->
   <nav class="detail-bc">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="orders.php">Pesanan Saya</a>
      <i class="fas fa-chevron-right"></i>
      <span class="u-inline-style-006"><?= htmlspecialchars($order_number); ?></span>
   </nav>

   <!-- Back -->
   <a href="orders.php" class="btn-back-o">
      <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
   </a>

   <!-- Banner -->
   <div class="detail-banner">
      <div class="banner-left">
         <h2><?= htmlspecialchars($order_number); ?></h2>
         <p><i class="fas fa-calendar-alt"></i> &nbsp;Dipesan <?= htmlspecialchars($fo['placed_on']); ?></p>
      </div>
      <div class="banner-badges">
         <span class="banner-badge"><i class="fas fa-credit-card"></i> <?= format_payment_status($payment_status); ?></span>
         <span class="banner-badge">
            <i class="fas fa-<?= ['diproses'=>'clock','dikemas'=>'box','dikirim'=>'truck','selesai'=>'check'][$shipping_status]??'circle'; ?>"></i>
            <?= format_order_status($shipping_status); ?>
         </span>
         <span class="banner-badge"><i class="fas fa-money-bill-wave"></i> Rp <?= number_format((int)$fo['total_price'],0,',','.'); ?></span>
      </div>
   </div>

   <!-- Progress -->
   <div class="progress-panel">
      <div class="progress-title"><i class="fas fa-route u-inline-style-012"></i> &nbsp;Status Pesanan</div>
      <div class="progress-track">
         <?php
         $step_icons  = ['diproses'=>'fa-clock','dikemas'=>'fa-box','dikirim'=>'fa-truck','selesai'=>'fa-check'];
         $step_labels = ['diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai'];
         foreach($steps as $idx => $step):
            $cls = ($idx < $current_step) ? 'done' : (($idx==$current_step)?'current':'');
         ?>
         <div class="progress-step <?= $cls; ?>">
            <div class="step-dot"><i class="fas <?= $step_icons[$step]; ?>"></i></div>
            <div class="step-label"><?= $step_labels[$step]; ?></div>
         </div>
         <?php if($idx < count($steps)-1): ?>
            <div class="progress-line <?= ($idx<$current_step)?'filled':''; ?>"></div>
         <?php endif; endforeach; ?>
      </div>
   </div>

   <!-- Info grid -->
   <div class="detail-info-grid">

      <!-- Order info -->
      <div class="detail-panel">
         <div class="panel-title"><i class="fas fa-receipt"></i> Informasi Pesanan</div>
         <div class="detail-info-rows">
            <div class="dir"><div class="dir-lbl"><i class="fas fa-hashtag"></i> No. Pesanan</div><div class="dir-val"><?= htmlspecialchars($order_number); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-calendar"></i> Tanggal</div><div class="dir-val"><?= htmlspecialchars($fo['placed_on']); ?></div></div>
            <div class="dir">
               <div class="dir-lbl"><i class="fas fa-credit-card"></i> Pembayaran</div>
               <div class="dir-val">
                  <span class="pb <?= $payment_status==='completed'?'pb-c':'pb-p'; ?>">
                     <i class="fas <?= $payment_status==='completed'?'fa-check':'fa-hourglass-half'; ?>"></i>
                     <?= format_payment_status($payment_status); ?>
                  </span>
               </div>
            </div>
            <div class="dir">
               <div class="dir-lbl"><i class="fas fa-truck"></i> Status</div>
               <div class="dir-val"><span class="sb sb-<?= $shipping_status; ?>"><?= format_order_status($shipping_status); ?></span></div>
            </div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-barcode"></i> Nomor Resi</div><div class="dir-val"><?= !empty($tracking_number)?htmlspecialchars($tracking_number):'Belum tersedia'; ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-wallet"></i> Metode Bayar</div><div class="dir-val"><?= htmlspecialchars($fo['method']); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-tag"></i> Total</div><div class="dir-price">Rp <?= number_format((int)$fo['total_price'],0,',','.'); ?></div></div>
         </div>
      </div>

      <!-- Buyer info -->
      <div class="detail-panel">
         <div class="panel-title"><i class="fas fa-user-circle"></i> Informasi Pembeli</div>
         <div class="detail-info-rows">
            <div class="dir"><div class="dir-lbl"><i class="fas fa-user"></i> Nama</div><div class="dir-val"><?= htmlspecialchars($order_name); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-envelope"></i> Email</div><div class="dir-val u-inline-style-021"><?= htmlspecialchars($order_email); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-phone"></i> Kontak</div><div class="dir-val"><?= htmlspecialchars($order_phone); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-map-marker-alt"></i> Alamat</div><div class="dir-val u-inline-style-022"><?= htmlspecialchars($order_address); ?></div></div>
         </div>
         <div class="privacy-note">
            <i class="fas fa-shield-halved u-inline-style-023"></i>
            Data kamu disimpan dengan aman dan terenkripsi.
         </div>
      </div>

   </div>

   <!-- Products -->
   <div class="products-panel">
      <div class="panel-title"><i class="fas fa-bag-shopping"></i> Produk yang Dipesan</div>
      <?php if(!empty($product_lines)): foreach($product_lines as $line): ?>
         <div class="pline"><i class="fas fa-tag"></i><?= htmlspecialchars($line); ?></div>
      <?php endforeach; else: ?>
         <div class="pline"><i class="fas fa-tag"></i><?= htmlspecialchars($total_products_raw); ?></div>
      <?php endif; ?>
   </div>

   <!-- Payment proof -->
   <div class="proof-panel">
      <div class="panel-title"><i class="fas fa-file-image"></i> Bukti Pembayaran</div>
      <?php $pex = !empty($payment_file) && file_exists($payment_path); ?>
      <div class="proof-row">
         <i class="fas proof-file-icon <?= $pex?'fa-circle-check status-positive':'fa-circle-xmark status-muted'; ?>"></i>
         <span class="<?= $pex?'proof-available':'proof-none'; ?>"><?= $pex?'Bukti pembayaran tersedia':'Belum ada bukti pembayaran'; ?></span>
      </div>
      <?php if($pex): ?>
         <?php if(in_array($file_ext,['jpg','jpeg','png','webp'])): ?>
            <img src="payment_proofs/<?= htmlspecialchars($payment_file); ?>" alt="Bukti Pembayaran" class="proof-img-d">
            <a href="payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="btn-proof">
               <i class="fas fa-expand"></i> Lihat Ukuran Penuh
            </a>
         <?php elseif($file_ext=='pdf'): ?>
            <a href="payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="btn-proof">
               <i class="fas fa-file-pdf"></i> Buka Bukti Pembayaran (PDF)
            </a>
         <?php else: ?>
            <a href="payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="btn-proof">
               <i class="fas fa-download"></i> Download Bukti Pembayaran
            </a>
         <?php endif; ?>
      <?php endif; ?>
   </div>

</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
