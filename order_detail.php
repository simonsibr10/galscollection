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
   <style>
      *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
      body{background:#f8f9fc;font-family:'Segoe UI',sans-serif}

      .order-detail-page{max-width:900px;margin:0 auto;padding:3rem 2.4rem 6rem}

      /* Breadcrumb */
      .detail-bc{font-size:1.4rem;color:#94a3b8;margin-bottom:2rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;animation:fadeSlideDown .4s ease both}
      .detail-bc a{color:#94a3b8;text-decoration:none;transition:color .15s}
      .detail-bc a:hover{color:#1a2a6c}
      .detail-bc i{font-size:1.1rem;color:#cbd5e1}

      /* Back btn */
      .btn-back-o{display:inline-flex;align-items:center;gap:.7rem;padding:.8rem 1.8rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:3rem;font-size:1.4rem;font-weight:600;color:#475569;text-decoration:none;margin-bottom:2rem;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:color .15s,box-shadow .15s,border-color .15s;animation:fadeSlideDown .4s ease both}
      .btn-back-o:hover{color:#1a2a6c;box-shadow:0 4px 14px rgba(0,0,0,.1);border-color:#c7d2fe}

      /* Banner */
      .detail-banner{background:linear-gradient(135deg,#0f2027 0%,#1a2a6c 55%,#2c3e8f 100%);border-radius:1.6rem;padding:2.4rem 2.8rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.6rem;margin-bottom:2.4rem;position:relative;overflow:hidden;animation:fadeSlideDown .5s ease both}
      .detail-banner::before{content:'';position:absolute;top:-40%;right:-5%;width:28rem;height:28rem;background:radial-gradient(circle,rgba(79,110,247,.25) 0%,transparent 70%);pointer-events:none}
      .banner-left{position:relative;z-index:1}
      .banner-left h2{font-size:2rem;font-weight:800;color:#fff;margin-bottom:.4rem}
      .banner-left p{font-size:1.3rem;color:rgba(255,255,255,.6)}
      .banner-badges{display:flex;gap:.8rem;flex-wrap:wrap;position:relative;z-index:1}
      .banner-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:2rem;padding:.6rem 1.3rem;font-size:1.2rem;font-weight:600;color:#fff}

      /* Progress */
      .progress-panel{background:#fff;border-radius:1.4rem;padding:2.2rem 2.4rem;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:1.4rem;animation:fadeSlideUp .5s ease both}
      .progress-title{font-size:1.2rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2rem;display:flex;align-items:center;gap:.6rem}
      .progress-track{display:flex;align-items:center}
      .progress-step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative;z-index:1}
      .step-dot{width:4rem;height:4rem;border-radius:50%;border:3px solid #e2e8f0;background:#fff;color:#cbd5e1;display:flex;align-items:center;justify-content:center;font-size:1.5rem;position:relative;z-index:2;transition:all .3s}
      .progress-step.done    .step-dot{background:linear-gradient(135deg,#059669,#34d399);border-color:#059669;color:#fff}
      .progress-step.current .step-dot{background:linear-gradient(135deg,#1a2a6c,#4f6ef7);border-color:#4f6ef7;color:#fff;box-shadow:0 0 0 5px rgba(79,110,247,.18);animation:stepPulse 2s infinite}
      @keyframes stepPulse{0%,100%{box-shadow:0 0 0 5px rgba(79,110,247,.18)}50%{box-shadow:0 0 0 10px rgba(79,110,247,.06)}}
      .step-label{font-size:1.2rem;font-weight:600;color:#94a3b8;margin-top:.6rem;text-align:center}
      .progress-step.done    .step-label{color:#059669}
      .progress-step.current .step-label{color:#1a2a6c}
      .progress-line{flex:1;height:3px;background:#e2e8f0;margin-top:-2rem;z-index:1}
      .progress-line.filled{background:linear-gradient(90deg,#059669,#34d399)}

      /* Info grid */
      .detail-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;margin-bottom:1.4rem;animation:fadeSlideUp .55s ease both}

      /* Panel */
      .detail-panel{background:#fff;border-radius:1.4rem;padding:2rem 2.2rem;box-shadow:0 2px 12px rgba(0,0,0,.05)}
      .panel-title{font-size:1.45rem;font-weight:700;color:#0f172a;margin-bottom:1.4rem;display:flex;align-items:center;gap:.7rem;padding-bottom:1.2rem;border-bottom:1px solid #f1f5f9}
      .panel-title i{color:#4f6ef7}

      .detail-info-rows{display:flex;flex-direction:column;gap:.8rem}
      .dir{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:.8rem 1rem;border-radius:.6rem;background:#f8fafc;font-size:1.3rem}
      .dir-lbl{color:#64748b;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:.5rem}
      .dir-lbl i{color:#94a3b8;font-size:1.2rem}
      .dir-val{color:#0f172a;font-weight:600;text-align:right;word-break:break-word}
      .dir-price{font-size:2rem;font-weight:800;color:#e11d48}

      /* Badges */
      .sb{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:2rem;font-size:1.2rem;font-weight:700}
      .sb-diproses{background:#fffbeb;color:#b45309}
      .sb-dikemas{background:#ecfeff;color:#0e7490}
      .sb-dikirim{background:#ecfdf5;color:#065f46}
      .sb-selesai{background:#f1f5f9;color:#475569}
      .pb{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .8rem;border-radius:2rem;font-size:1.2rem;font-weight:700}
      .pb-p{background:#fff1f2;color:#be123c}
      .pb-c{background:#ecfdf5;color:#065f46}

      /* Products */
      .products-panel{background:#fff;border-radius:1.4rem;padding:2rem 2.2rem;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:1.4rem;animation:fadeSlideUp .6s ease both}
      .pline{display:flex;align-items:flex-start;gap:1rem;padding:1.1rem 1.2rem;border-radius:.8rem;background:#f8fafc;margin-bottom:.8rem;font-size:1.35rem;color:#334155;font-weight:500;line-height:1.5;border-left:3px solid #c7d2fe}
      .pline i{color:#4f6ef7;flex-shrink:0;margin-top:.15rem}

      /* Proof */
      .proof-panel{background:#fff;border-radius:1.4rem;padding:2rem 2.2rem;box-shadow:0 2px 12px rgba(0,0,0,.05);animation:fadeSlideUp .65s ease both}
      .proof-row{display:flex;align-items:center;gap:.8rem;font-size:1.4rem;font-weight:500;color:#475569;margin-bottom:1.4rem}
      .proof-available{color:#059669;font-weight:700}
      .proof-none{color:#94a3b8}
      .proof-img-d{width:100%;max-width:36rem;border-radius:1rem;display:block;margin-bottom:1.2rem;box-shadow:0 4px 16px rgba(0,0,0,.1)}
      .btn-proof{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 1.8rem;border-radius:.7rem;background:#eff2ff;color:#1a2a6c;font-size:1.4rem;font-weight:600;text-decoration:none;transition:background .15s}
      .btn-proof:hover{background:#e0e7ff}

      /* Privacy note */
      .privacy-note{margin-top:1.2rem;padding:1rem 1.2rem;background:#fffbeb;border-left:3px solid #f59e0b;border-radius:.6rem;font-size:1.25rem;color:#78350f;display:flex;align-items:flex-start;gap:.7rem;line-height:1.5}

      @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
      @keyframes fadeSlideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

      @media(max-width:700px){
         .order-detail-page{padding:2rem 1.4rem 4rem}
         .detail-info-grid{grid-template-columns:1fr}
         .step-label{font-size:1rem}
         .detail-banner{flex-direction:column;align-items:flex-start}
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="order-detail-page">

   <!-- Breadcrumb -->
   <nav class="detail-bc">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="orders.php">Pesanan Saya</a>
      <i class="fas fa-chevron-right"></i>
      <span style="color:#475569;font-weight:600;"><?= htmlspecialchars($order_number); ?></span>
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
      <div class="progress-title"><i class="fas fa-route" style="color:#4f6ef7"></i> &nbsp;Status Pesanan</div>
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
            <div class="dir"><div class="dir-lbl"><i class="fas fa-envelope"></i> Email</div><div class="dir-val" style="font-size:1.2rem;"><?= htmlspecialchars($order_email); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-phone"></i> Kontak</div><div class="dir-val"><?= htmlspecialchars($order_phone); ?></div></div>
            <div class="dir"><div class="dir-lbl"><i class="fas fa-map-marker-alt"></i> Alamat</div><div class="dir-val" style="max-width:18rem;"><?= htmlspecialchars($order_address); ?></div></div>
         </div>
         <div class="privacy-note">
            <i class="fas fa-shield-halved" style="flex-shrink:0;margin-top:.1rem;"></i>
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
         <i class="fas <?= $pex?'fa-circle-check':'fa-circle-xmark'; ?>" style="color:<?= $pex?'#059669':'#94a3b8'; ?>;font-size:1.6rem;"></i>
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