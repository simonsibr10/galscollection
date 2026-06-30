<?php

include '../components/connect.php';
include '../components/crypto.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
   header('location:placed_orders.php');
   exit;
}

function safe_decrypt_value($value){
   $decrypted = aes_decrypt($value);
   if($decrypted === false || $decrypted === null || $decrypted === ''){
      return $value;
   }
   return $decrypted;
}

function format_payment_status($status){
   $status = strtolower(trim($status));
   if($status == 'pending') return 'Menunggu Pembayaran';
   if($status == 'completed') return 'Pembayaran Berhasil';
   return ucfirst($status);
}

function format_order_status($status){
   $map = ['diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai'];
   return $map[strtolower(trim($status))] ?? ucfirst($status);
}

function get_next_order_statuses($current_status){
   $flow = ['diproses','dikemas','dikirim','selesai'];
   $index = array_search($current_status, $flow);
   if($index === false) return ['diproses'];
   $allowed = [$flow[$index]];
   if(isset($flow[$index + 1])) $allowed[] = $flow[$index + 1];
   return $allowed;
}

$order_id = $_GET['id'];

if(isset($_POST['update_order'])){

   $new_payment_status  = filter_var($_POST['payment_status'] ?? '', FILTER_SANITIZE_STRING);
   $new_order_status    = filter_var($_POST['order_status'] ?? '', FILTER_SANITIZE_STRING);
   $new_tracking_number = filter_var(trim($_POST['tracking_number'] ?? ''), FILTER_SANITIZE_STRING);

   $select_current = $conn->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
   $select_current->execute([$order_id]);

   if($select_current->rowCount() > 0){
      $current_order          = $select_current->fetch(PDO::FETCH_ASSOC);
      $current_payment_status = strtolower($current_order['payment_status'] ?? 'pending');
      $current_order_status   = strtolower($current_order['shipping_status'] ?? 'diproses');
      $current_tracking       = trim($current_order['tracking_number'] ?? '');

      if($current_payment_status !== 'completed'){
         if(in_array($new_payment_status, ['pending','completed'])){
            $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?")->execute([$new_payment_status, $order_id]);
         }
      }

      $allowed_next = get_next_order_statuses($current_order_status);
      if(in_array($new_order_status, $allowed_next)){
         if($current_order_status !== 'selesai' && $new_order_status !== $current_order_status){
            $conn->prepare("UPDATE `orders` SET shipping_status = ? WHERE id = ?")->execute([$new_order_status, $order_id]);
         }
      }else{
         $new_order_status = $current_order_status;
      }

      $final_order_status = $new_order_status ?: $current_order_status;
      if($current_tracking == '' && $new_tracking_number != '' && $final_order_status == 'dikemas'){
         $conn->prepare("UPDATE `orders` SET tracking_number = ? WHERE id = ?")->execute([$new_tracking_number, $order_id]);
      }
   }

   header("location:order_detail.php?id=".$order_id);
   exit;
}

$select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
$select_order->execute([$order_id]);

if($select_order->rowCount() == 0){
   header('location:placed_orders.php');
   exit;
}

$fetch_order = $select_order->fetch(PDO::FETCH_ASSOC);

$order_name    = safe_decrypt_value($fetch_order['name']);
$order_phone   = safe_decrypt_value($fetch_order['number']);
$order_email   = safe_decrypt_value($fetch_order['email']);
$order_address = safe_decrypt_value($fetch_order['address']);

$masked_name    = mask_name($order_name);
$masked_phone   = mask_phone($order_phone);
$masked_email   = mask_email($order_email);
$masked_address = mask_address($order_address);

$order_number       = $fetch_order['order_number'] ?? '-';
$tracking_number    = $fetch_order['tracking_number'] ?? '';
$order_status       = strtolower($fetch_order['shipping_status'] ?? 'diproses');
$payment_status     = strtolower($fetch_order['payment_status'] ?? 'pending');
$payment_file       = $fetch_order['payment_proof'] ?? '';
$payment_file_path  = '../payment_proofs/'.$payment_file;
$file_extension     = strtolower(pathinfo($payment_file, PATHINFO_EXTENSION));
$total_products_raw = $fetch_order['total_products'] ?? '';

$product_lines = [];
if($total_products_raw != ''){
   foreach(explode(' - ', $total_products_raw) as $line){
      $line = trim($line);
      if($line != '') $product_lines[] = $line;
   }
}

$is_payment_final = ($payment_status === 'completed');
$is_order_final   = ($order_status === 'selesai');
$allowed_statuses = get_next_order_statuses($order_status);
$can_input_tracking = ($tracking_number == '' && $order_status == 'dikemas');

// Progress step
$steps = ['diproses','dikemas','dikirim','selesai'];
$current_step = array_search($order_status, $steps);
if($current_step === false) $current_step = 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Detail Pesanan</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-order-detail">

<?php include '../components/admin_header.php'; ?>

<div class="detail-page">

   <!-- Back -->
   <a href="placed_orders.php" class="back-link">
      <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
   </a>

   <!-- ===== BANNER ===== -->
   <div class="detail-banner">
      <div class="banner-left">
         <h2><?= htmlspecialchars($order_number); ?></h2>
         <p><i class="fas fa-calendar-alt"></i> &nbsp;Dipesan pada <?= htmlspecialchars($fetch_order['placed_on']); ?></p>
      </div>
      <div class="banner-right">
         <span class="banner-badge">
            <i class="fas fa-credit-card"></i>
            <?= format_payment_status($payment_status); ?>
         </span>
         <span class="banner-badge">
            <i class="fas fa-<?= ['diproses'=>'clock','dikemas'=>'box','dikirim'=>'truck','selesai'=>'check-circle'][$order_status] ?? 'circle'; ?>"></i>
            <?= format_order_status($order_status); ?>
         </span>
         <span class="banner-badge">
            <i class="fas fa-money-bill-wave"></i>
            Rp <?= number_format($fetch_order['total_price'], 0, ',', '.'); ?>
         </span>
      </div>
   </div>

   <!-- ===== PROGRESS TRACKER ===== -->
   <div class="progress-panel">
      <div class="progress-title"><i class="fas fa-route"></i> &nbsp;Progress Pesanan</div>
      <div class="progress-track">
         <?php
         $step_icons  = ['diproses'=>'fa-clock','dikemas'=>'fa-box','dikirim'=>'fa-truck','selesai'=>'fa-check'];
         $step_labels = ['diproses'=>'Diproses','dikemas'=>'Dikemas','dikirim'=>'Dikirim','selesai'=>'Selesai'];

         foreach($steps as $idx => $step):
            $cls = ($idx < $current_step) ? 'done' : (($idx == $current_step) ? 'current' : '');
         ?>
         <div class="progress-step <?= $cls; ?>">
            <div class="progress-step-dot">
               <i class="fas <?= $step_icons[$step]; ?>"></i>
            </div>
            <div class="progress-step-label"><?= $step_labels[$step]; ?></div>
         </div>
         <?php if($idx < count($steps)-1): ?>
            <div class="progress-line <?= ($idx < $current_step) ? 'filled' : ''; ?>"></div>
         <?php endif; endforeach; ?>
      </div>
   </div>

   <!-- ===== INFO CARDS ===== -->
   <div class="detail-main-grid">

      <!-- Order Info -->
      <div class="info-panel">
         <div class="panel-title"><i class="fas fa-receipt"></i> Informasi Pesanan</div>
         <div class="info-rows">
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-hashtag"></i> No. Pesanan</div>
               <div class="ir-val"><?= htmlspecialchars($order_number); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-calendar"></i> Tanggal</div>
               <div class="ir-val"><?= htmlspecialchars($fetch_order['placed_on']); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-credit-card"></i> Pembayaran</div>
               <div class="ir-val">
                  <span class="pay-badge <?= $payment_status=='completed'?'pay-completed':'pay-pending'; ?>">
                     <i class="fas <?= $payment_status=='completed'?'fa-check':'fa-hourglass-half'; ?>"></i>
                     <?= format_payment_status($payment_status); ?>
                  </span>
               </div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-truck"></i> Status</div>
               <div class="ir-val">
                  <span class="status-badge badge-<?= $order_status; ?>">
                     <?= format_order_status($order_status); ?>
                  </span>
               </div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-barcode"></i> Nomor Resi</div>
               <div class="ir-val"><?= !empty($tracking_number) ? htmlspecialchars($tracking_number) : 'Belum tersedia'; ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-wallet"></i> Metode Bayar</div>
               <div class="ir-val"><?= htmlspecialchars($fetch_order['method']); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-tag"></i> Total Harga</div>
               <div class="total-price-big">Rp <?= number_format($fetch_order['total_price'],0,',','.'); ?></div>
            </div>
         </div>
      </div>

      <!-- Buyer Info -->
      <div class="info-panel">
         <div class="panel-title"><i class="fas fa-user-circle"></i> Informasi Pembeli</div>
         <div class="info-rows">
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-user"></i> Nama</div>
               <div class="ir-val"><?= htmlspecialchars($masked_name); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-envelope"></i> Email</div>
               <div class="ir-val"><?= htmlspecialchars($masked_email); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-phone"></i> Kontak</div>
               <div class="ir-val"><?= htmlspecialchars($masked_phone); ?></div>
            </div>
            <div class="info-row-item">
               <div class="ir-label"><i class="fas fa-map-marker-alt"></i> Alamat</div>
               <div class="ir-val"><?= htmlspecialchars($masked_address); ?></div>
            </div>
         </div>
         <div class="privacy-note">
            <i class="fas fa-shield-alt"></i>
            Data pembeli ditampilkan dalam bentuk tersamarkan untuk menjaga privasi pelanggan.
         </div>
      </div>

   </div>

   <!-- ===== PRODUCTS ===== -->
   <div class="products-panel">
      <div class="panel-title"><i class="fas fa-shopping-bag"></i> Produk yang Dipesan</div>
      <?php if(!empty($product_lines)): ?>
         <?php foreach($product_lines as $line): ?>
            <div class="product-line-item">
               <i class="fas fa-tag"></i>
               <?= htmlspecialchars($line); ?>
            </div>
         <?php endforeach; ?>
      <?php else: ?>
         <div class="product-line-item">
            <i class="fas fa-tag"></i>
            <?= htmlspecialchars($total_products_raw); ?>
         </div>
      <?php endif; ?>
   </div>

   <!-- ===== UPDATE PANEL ===== -->
   <div class="update-panel">
      <div class="panel-title"><i class="fas fa-edit"></i> Update Status Pesanan</div>

      <form action="" method="post">
         <div class="update-form-grid">

            <!-- Payment Status -->
            <div class="form-field">
               <label><i class="fas fa-credit-card"></i> Status Pembayaran</label>
               <select name="payment_status" class="form-select <?= $is_payment_final?'disabled':''; ?>" <?= $is_payment_final?'disabled':''; ?>>
                  <option value="pending"    <?= $payment_status=='pending'   ?'selected':''; ?>>Menunggu Pembayaran</option>
                  <option value="completed"  <?= $payment_status=='completed' ?'selected':''; ?>>Pembayaran Berhasil</option>
               </select>
            </div>

            <!-- Order Status -->
            <div class="form-field">
               <label><i class="fas fa-truck"></i> Status Pesanan</label>
               <select name="order_status" class="form-select <?= $is_order_final?'disabled':''; ?>" <?= $is_order_final?'disabled':''; ?>>
                  <?php foreach($allowed_statuses as $opt): ?>
                     <option value="<?= htmlspecialchars($opt); ?>" <?= $order_status==$opt?'selected':''; ?>>
                        <?= format_order_status($opt); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
            </div>

            <!-- Tracking Number -->
            <div class="form-field">
               <label><i class="fas fa-barcode"></i> Nomor Resi</label>
               <input type="text" name="tracking_number"
                  class="form-input-text <?= !$can_input_tracking?'disabled':''; ?>"
                  placeholder="<?= $can_input_tracking?'Masukkan nomor resi...':'Resi hanya diisi saat status Dikemas'; ?>"
                  value="<?= htmlspecialchars($tracking_number); ?>"
                  <?= !$can_input_tracking?'readonly':''; ?>
               >
            </div>

         </div>

         <!-- Notes -->
         <div class="update-notes">
            <?php if($is_payment_final): ?>
               <div class="note-item green"><i class="fas fa-check-circle"></i> Status pembayaran sudah final (Lunas) dan tidak bisa diubah.</div>
            <?php endif; ?>

            <?php if($order_status == 'diproses'): ?>
               <div class="note-item blue"><i class="fas fa-info-circle"></i> Status pesanan hanya bisa tetap di <strong>Diproses</strong> atau maju ke <strong>Dikemas</strong>.</div>
            <?php elseif($order_status == 'dikemas'): ?>
               <div class="note-item amber"><i class="fas fa-box"></i> Status bisa tetap <strong>Dikemas</strong> atau maju ke <strong>Dikirim</strong>. Nomor resi hanya bisa diinput sekali pada tahap ini.</div>
            <?php elseif($order_status == 'dikirim'): ?>
               <div class="note-item blue"><i class="fas fa-truck"></i> Status bisa tetap <strong>Dikirim</strong> atau maju ke <strong>Selesai</strong>.</div>
            <?php endif; ?>

            <?php if(!empty($tracking_number)): ?>
               <div class="note-item green"><i class="fas fa-lock"></i> Nomor resi sudah tersimpan dan tidak bisa diubah lagi.</div>
            <?php endif; ?>

            <?php if($is_order_final): ?>
               <div class="note-item gray"><i class="fas fa-flag-checkered"></i> Pesanan sudah <strong>Selesai</strong>. Semua status sudah final.</div>
            <?php endif; ?>
         </div>

         <?php if(!$is_payment_final || !$is_order_final): ?>
            <button type="submit" name="update_order" class="btn-update-submit">
               <i class="fas fa-save"></i> Simpan Perubahan
            </button>
         <?php else: ?>
            <div class="note-item gray"><i class="fas fa-info-circle"></i> Semua data pesanan sudah final dan tidak bisa diubah.</div>
         <?php endif; ?>
      </form>
   </div>

   <!-- ===== PAYMENT PROOF ===== -->
   <div class="proof-panel">
      <div class="panel-title"><i class="fas fa-file-image"></i> Bukti Pembayaran</div>

      <?php $proof_exists = !empty($payment_file) && file_exists($payment_file_path); ?>

      <div class="proof-status-row">
         <i class="fas <?= $proof_exists ? 'fa-check-circle status-positive' : 'fa-times-circle status-muted'; ?>"></i>
         <span class="<?= $proof_exists ? 'proof-available' : 'proof-none'; ?>">
            <?= $proof_exists ? 'Bukti pembayaran tersedia' : 'Belum ada bukti pembayaran'; ?>
         </span>
      </div>

      <?php if($proof_exists): ?>
         <?php if(in_array($file_extension, ['jpg','jpeg','png','webp'])): ?>
            <img src="../payment_proofs/<?= htmlspecialchars($payment_file); ?>" alt="Bukti Pembayaran" class="proof-img">
            <a href="../payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="proof-link">
               <i class="fas fa-expand"></i> Lihat Ukuran Penuh
            </a>
         <?php elseif($file_extension == 'pdf'): ?>
            <a href="../payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="proof-link">
               <i class="fas fa-file-pdf"></i> Buka Bukti Pembayaran (PDF)
            </a>
         <?php else: ?>
            <a href="../payment_proofs/<?= htmlspecialchars($payment_file); ?>" target="_blank" class="proof-link">
               <i class="fas fa-download"></i> Download Bukti Pembayaran
            </a>
         <?php endif; ?>
      <?php endif; ?>
   </div>

</div>

<script src="../js/admin_script.js"></script>
</body>
</html>
