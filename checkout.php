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

function generateOrderNumber(){
   return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

if(isset($_POST['order'])){

   $name     = filter_var($_POST['name'],     FILTER_SANITIZE_STRING);
   $number   = filter_var($_POST['number'],   FILTER_SANITIZE_STRING);
   $email    = filter_var($_POST['email'],    FILTER_SANITIZE_EMAIL);
   $method   = 'Transfer Bank';
   $flat     = filter_var($_POST['flat'],     FILTER_SANITIZE_STRING);
   $street   = filter_var($_POST['street'],   FILTER_SANITIZE_STRING);
   $city     = filter_var($_POST['city'],     FILTER_SANITIZE_STRING);
   $state    = filter_var($_POST['state'],    FILTER_SANITIZE_STRING);
   $country  = filter_var($_POST['country'],  FILTER_SANITIZE_STRING);
   $pin_code = filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);

   $address = 'No Rumah: '.$flat.', Jalan: '.$street.', Kota: '.$city.', Provinsi: '.$state.', Kode Pos: '.$pin_code.', Negara: '.$country;

   $order_number    = generateOrderNumber();
   $placed_on       = date('Y-m-d');
   $payment_status  = 'pending';
   $tracking_number = '';
   $shipping_status = 'diproses';

   $payment_proof = '';
   if(isset($_FILES['payment_proof']) && !empty($_FILES['payment_proof']['name'])){
      $payment_proof      = filter_var($_FILES['payment_proof']['name'], FILTER_SANITIZE_STRING);
      $payment_proof_tmp  = $_FILES['payment_proof']['tmp_name'];
      $payment_proof_size = $_FILES['payment_proof']['size'];
      $payment_proof_folder = 'payment_proofs/'.$payment_proof;
   }

   $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND selected = 1 ORDER BY id DESC");
   $select_cart->execute([$user_id]);

   $cart_total_price = 0;
   $cart_products    = [];

   if($select_cart->rowCount() > 0){
      while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
         $line = $fetch_cart['name'];
         if(!empty($fetch_cart['selected_variation_1_value'])) $line .= ' - '.$fetch_cart['selected_variation_1_name'].': '.$fetch_cart['selected_variation_1_value'];
         if(!empty($fetch_cart['selected_variation_2_value'])) $line .= ' - '.$fetch_cart['selected_variation_2_name'].': '.$fetch_cart['selected_variation_2_value'];
         $line .= ' ('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')';
         $cart_products[] = $line;
         $cart_total_price += ($fetch_cart['price'] * $fetch_cart['quantity']);
      }

      $total_products = implode(' - ', $cart_products);

      if($payment_proof == ''){
         $message[] = 'error:Silakan upload bukti pembayaran!';
      } elseif(isset($payment_proof_size) && $payment_proof_size > 2000000){
         $message[] = 'error:Ukuran bukti pembayaran terlalu besar (maks 2MB)!';
      } else {

         // Enkripsi data pembeli
         $name_enc   = function_exists('aes_encrypt') ? aes_encrypt($name)   : $name;
         $number_enc = function_exists('aes_encrypt') ? aes_encrypt($number) : $number;
         $email_enc  = function_exists('aes_encrypt') ? aes_encrypt($email)  : $email;
         $addr_enc   = function_exists('aes_encrypt') ? aes_encrypt($address): $address;

         $insert_order = $conn->prepare("INSERT INTO `orders`
            (user_id,name,number,email,method,address,total_products,total_price,placed_on,payment_status,order_number,tracking_number,shipping_status,payment_proof)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

         $insert_order->execute([
            $user_id, $name_enc, $number_enc, $email_enc,
            $method, $addr_enc,
            $total_products, $cart_total_price,
            $placed_on, $payment_status,
            $order_number, $tracking_number,
            $shipping_status, $payment_proof
         ]);

         if($insert_order){
            if(!empty($payment_proof)) move_uploaded_file($payment_proof_tmp, $payment_proof_folder);
            $conn->prepare("DELETE FROM `cart` WHERE user_id = ? AND selected = 1")->execute([$user_id]);
            $message[] = 'success:Pesanan berhasil dibuat! Nomor pesanan: '.$order_number;
         } else {
            $message[] = 'error:Gagal membuat pesanan. Coba lagi.';
         }
      }
   } else {
      $message[] = 'error:Silakan pilih minimal 1 produk dari keranjang untuk checkout!';
   }
}

// Fetch selected cart items
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND selected = 1 ORDER BY id DESC");
$select_cart->execute([$user_id]);
$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

$grand_total = 0;
foreach($cart_items as $ci) $grand_total += ($ci['price'] * $ci['quantity']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }

      /* ===== PAGE ===== */
      .checkout-page {
         max-width: 1200px;
         margin: 0 auto;
         padding: 3rem 2.4rem 6rem;
      }

      /* Breadcrumb */
      .checkout-bc {
         font-size: 1.4rem; color: #94a3b8;
         margin-bottom: 2.4rem;
         display: flex; align-items: center; gap: .6rem;
         animation: fadeSlideDown .4s ease both;
      }

      .checkout-bc a { color: #94a3b8; text-decoration: none; transition: color .15s; }
      .checkout-bc a:hover { color: #1a2a6c; }
      .checkout-bc i { font-size: 1.1rem; color: #cbd5e1; }

      /* Page title */
      .checkout-page-title {
         font-size: 2.8rem; font-weight: 800; color: #1e293b;
         margin-bottom: 2.8rem; display: flex; align-items: center; gap: .8rem;
         animation: fadeSlideDown .45s ease both;
      }

      .checkout-page-title i { color: #4f6ef7; }

      /* ===== PROGRESS STEPS ===== */
      .checkout-steps {
         display: flex;
         align-items: center;
         margin-bottom: 3.2rem;
         animation: fadeSlideUp .5s ease both;
      }

      .checkout-step {
         display: flex; align-items: center; gap: .8rem;
         flex: 1;
         position: relative;
      }

      .step-circle {
         width: 3.8rem; height: 3.8rem; border-radius: 50%;
         display: flex; align-items: center; justify-content: center;
         font-size: 1.5rem; font-weight: 700;
         flex-shrink: 0;
         border: 2px solid #e2e8f0;
         background: #fff; color: #94a3b8;
         transition: all .3s;
      }

      .step-circle.done    { background: #059669; border-color: #059669; color: #fff; }
      .step-circle.current { background: linear-gradient(135deg,#1a2a6c,#4f6ef7); border-color: #4f6ef7; color: #fff; box-shadow: 0 0 0 4px rgba(79,110,247,.18); }

      .step-label { font-size: 1.25rem; font-weight: 600; color: #94a3b8; }
      .checkout-step.done    .step-label { color: #059669; }
      .checkout-step.current .step-label { color: #1a2a6c; }

      .step-line { flex: 1; height: 2px; background: #e2e8f0; margin: 0 .4rem; }
      .step-line.done { background: linear-gradient(90deg,#059669,#34d399); }

      /* ===== MAIN LAYOUT ===== */
      .checkout-layout {
         display: grid;
         grid-template-columns: 1fr 44rem;
         gap: 2.4rem;
         align-items: start;
      }

      /* ===== PANELS ===== */
      .co-panel {
         background: #fff; border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         overflow: hidden;
         animation: fadeSlideUp .5s ease both;
      }

      .co-panel:nth-child(2) { animation-delay: .06s; }

      .co-panel-header {
         padding: 1.8rem 2.2rem;
         border-bottom: 1px solid #f1f5f9;
         display: flex; align-items: center; gap: .8rem;
         font-size: 1.6rem; font-weight: 700; color: #0f172a;
      }

      .co-panel-header i { color: #4f6ef7; font-size: 1.5rem; }

      .co-panel-body { padding: 2.2rem; }

      /* ===== FORM ===== */
      .form-grid-2 {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.4rem 1.8rem;
      }

      .form-grid-2 .full { grid-column: 1/-1; }

      .form-field { display: flex; flex-direction: column; gap: .6rem; }

      .form-field label {
         font-size: 1.3rem; font-weight: 700; color: #475569;
         display: flex; align-items: center; gap: .5rem;
      }

      .form-field label i { color: #94a3b8; font-size: 1.2rem; }

      .input-wrap { position: relative; }

      .input-wrap i.fi {
         position: absolute; left: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8; font-size: 1.4rem;
         pointer-events: none; transition: color .2s;
      }

      .input-wrap:focus-within i.fi { color: #4f6ef7; }

      .form-input {
         width: 100%;
         padding: 1.2rem 1.4rem 1.2rem 4rem;
         border: 1.5px solid #e2e8f0; border-radius: .9rem;
         font-size: 1.4rem; color: #0f172a; background: #f8fafc;
         outline: none; font-family: inherit;
         transition: border-color .2s, box-shadow .2s, background .2s;
      }

      .form-input:focus {
         border-color: #4f6ef7; background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.12);
      }

      .form-input::placeholder { color: #94a3b8; }

      .form-input[readonly] {
         background: #f1f5f9; color: #64748b; cursor: not-allowed;
      }

      /* File upload */
      .file-upload-area {
         border: 2px dashed #c7d2fe; border-radius: .9rem;
         padding: 2.4rem 1.6rem; text-align: center;
         cursor: pointer; background: #f8fafc;
         position: relative; transition: border-color .2s, background .2s;
         display: flex; flex-direction: column; align-items: center; gap: .6rem;
      }

      .file-upload-area:hover { border-color: #4f6ef7; background: #eff2ff; }

      .file-upload-area input[type="file"] {
         position: absolute; inset: 0; opacity: 0; cursor: pointer;
         width: 100%; height: 100%;
      }

      .file-upload-area i { font-size: 3rem; color: #94a3b8; pointer-events: none; }
      .file-upload-area .fu-title { font-size: 1.4rem; font-weight: 600; color: #475569; pointer-events: none; }
      .file-upload-area .fu-sub   { font-size: 1.25rem; color: #94a3b8; pointer-events: none; }
      .file-upload-area .fu-name  { font-size: 1.3rem; color: #4f6ef7; font-weight: 700; margin-top: .4rem; }

      /* Preview proof */
      .proof-preview {
         display: none;
         width: 100%; max-height: 20rem;
         object-fit: cover; border-radius: .8rem;
         margin-top: .8rem;
         border: 2px solid #e2e8f0;
      }

      /* ===== BANK INFO ===== */
      .bank-info-card {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 55%, #2c3e8f 100%);
         border-radius: 1.2rem;
         padding: 2rem 2.2rem;
         margin-top: 1.6rem;
         position: relative;
         overflow: hidden;
      }

      .bank-info-card::before {
         content: '';
         position: absolute; top: -40%; right: -5%;
         width: 22rem; height: 22rem;
         background: radial-gradient(circle, rgba(79,110,247,.25) 0%, transparent 70%);
         pointer-events: none;
      }

      .bank-info-title {
         font-size: 1.3rem; font-weight: 700;
         color: rgba(255,255,255,.7);
         text-transform: uppercase; letter-spacing: .08em;
         margin-bottom: 1.4rem;
         position: relative; z-index: 1;
      }

      .bank-row {
         display: flex; align-items: center; justify-content: space-between;
         gap: 1rem; padding: .8rem 0;
         border-bottom: 1px solid rgba(255,255,255,.1);
         position: relative; z-index: 1;
      }

      .bank-row:last-child { border-bottom: none; }
      .bank-row-label { font-size: 1.25rem; color: rgba(255,255,255,.6); }
      .bank-row-val   { font-size: 1.4rem; font-weight: 700; color: #fff; }

      .copy-btn {
         background: rgba(255,255,255,.15); border: none; border-radius: .5rem;
         color: #fff; font-size: 1.1rem; padding: .3rem .8rem; cursor: pointer;
         transition: background .15s; font-family: inherit;
      }

      .copy-btn:hover { background: rgba(255,255,255,.25); }

      /* ===== RIGHT: ORDER SUMMARY ===== */
      .order-summary-panel { position: sticky; top: 9rem; }

      /* Cart items */
      .summary-items { display: flex; flex-direction: column; gap: .8rem; }

      .summary-item {
         display: flex; align-items: center; gap: 1.4rem;
         padding: 1.2rem; border-radius: .9rem; background: #f8fafc;
         transition: background .15s;
      }

      .summary-item:hover { background: #f1f5f9; }

      .sum-item-img {
         width: 6.4rem; height: 6.4rem; border-radius: .8rem;
         overflow: hidden; background: #e2e8f0; flex-shrink: 0;
      }

      .sum-item-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

      .sum-item-info { flex: 1; min-width: 0; }

      .sum-item-name {
         font-size: 1.35rem; font-weight: 700; color: #1e293b;
         white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
      }

      .sum-item-variation { font-size: 1.2rem; color: #64748b; margin-top: .3rem; }

      .sum-item-qty {
         display: inline-flex; align-items: center;
         background: #eff2ff; color: #1a2a6c;
         border-radius: .4rem; padding: .2rem .6rem;
         font-size: 1.2rem; font-weight: 700;
         margin-top: .3rem;
      }

      .sum-item-price { font-size: 1.4rem; font-weight: 800; color: #e11d48; flex-shrink: 0; }

      /* Totals */
      .summary-totals {
         padding: 1.6rem 0 0;
         border-top: 1px solid #f1f5f9;
         display: flex; flex-direction: column; gap: .8rem;
      }

      .total-row {
         display: flex; justify-content: space-between;
         align-items: center; font-size: 1.35rem;
      }

      .total-row .lbl { color: #64748b; }
      .total-row .val { font-weight: 700; color: #1e293b; }

      .total-row.grand .lbl { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
      .total-row.grand .val { font-size: 2rem; font-weight: 800; color: #e11d48; }

      /* Submit button */
      .btn-checkout-submit {
         width: 100%; padding: 1.6rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff; border: none; border-radius: 1rem;
         font-size: 1.6rem; font-weight: 700; cursor: pointer;
         transition: transform .15s, box-shadow .2s;
         font-family: inherit;
         display: flex; align-items: center; justify-content: center; gap: .7rem;
         margin-top: 1.6rem;
      }

      .btn-checkout-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }
      .btn-checkout-submit:active { transform: scale(.97); }

      .btn-checkout-submit.disabled {
         background: #e2e8f0; color: #94a3b8; cursor: not-allowed; pointer-events: none;
      }

      /* Back to cart */
      .btn-back-cart {
         display: inline-flex; align-items: center; gap: .6rem;
         font-size: 1.35rem; font-weight: 600; color: #64748b;
         text-decoration: none; padding: 1rem 0;
         transition: color .15s;
      }

      .btn-back-cart:hover { color: #1a2a6c; }

      /* ===== NOTIFICATIONS ===== */
      .co-notif {
         padding: 1.2rem 1.6rem; border-radius: .9rem;
         font-size: 1.35rem; display: flex; align-items: center; gap: .8rem;
         margin-bottom: 1.6rem; animation: fadeSlideDown .35s ease both;
      }

      .co-notif.success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }
      .co-notif.error   { background: #fff1f2; border-left: 4px solid #e11d48; color: #be123c; }

      /* ===== EMPTY CART ===== */
      .empty-checkout {
         text-align: center; padding: 5rem 2rem;
         background: #fff; border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         color: #94a3b8;
         animation: fadeSlideUp .5s ease both;
      }

      .empty-checkout i { font-size: 5rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .empty-checkout h3 { font-size: 2rem; font-weight: 700; color: #475569; margin-bottom: .8rem; }
      .empty-checkout p  { font-size: 1.4rem; margin-bottom: 2rem; }

      .btn-go-cart {
         display: inline-flex; align-items: center; gap: .7rem;
         padding: 1.2rem 2.8rem; background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff; border-radius: 1rem; font-size: 1.5rem; font-weight: 700;
         text-decoration: none; transition: transform .15s, box-shadow .2s;
      }

      .btn-go-cart:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      /* ===== RESPONSIVE ===== */
      @media(max-width:960px){
         .checkout-page { padding: 2rem 1.6rem 4rem; }
         .checkout-layout { grid-template-columns: 1fr; }
         .order-summary-panel { position: static; }
      }

      @media(max-width:600px){
         .form-grid-2 { grid-template-columns: 1fr; }
         .form-grid-2 .full { grid-column: 1; }
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="checkout-page">

   <!-- Breadcrumb -->
   <nav class="checkout-bc">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="cart.php">Keranjang</a>
      <i class="fas fa-chevron-right"></i>
      <span style="color:#475569;font-weight:600;">Checkout</span>
   </nav>

   <!-- Title -->
   <div class="checkout-page-title">
      <i class="fas fa-bag-shopping"></i> Checkout
   </div>

   <!-- Progress steps -->
   <div class="checkout-steps">
      <div class="checkout-step done">
         <div class="step-circle done"><i class="fas fa-check"></i></div>
         <div class="step-label">Keranjang</div>
      </div>
      <div class="step-line done"></div>
      <div class="checkout-step current">
         <div class="step-circle current"><i class="fas fa-credit-card"></i></div>
         <div class="step-label">Checkout</div>
      </div>
      <div class="step-line"></div>
      <div class="checkout-step">
         <div class="step-circle"><i class="fas fa-check-circle"></i></div>
         <div class="step-label">Selesai</div>
      </div>
   </div>

   <!-- Notifications -->
   <?php if(isset($message)): foreach($message as $msg):
      $parts = explode(':', $msg, 2);
      $mtype = $parts[0] ?? 'info';
      $mtext = $parts[1] ?? $msg;
   ?>
      <div class="co-notif <?= $mtype; ?>">
         <i class="fas <?= $mtype==='success'?'fa-circle-check':'fa-circle-xmark'; ?>"></i>
         <?= htmlspecialchars($mtext); ?>
      </div>
   <?php endforeach; endif; ?>

   <?php if($grand_total > 0): ?>

   <!-- ===== MAIN LAYOUT ===== -->
   <form action="" method="post" enctype="multipart/form-data">
   <div class="checkout-layout">

      <!-- ─── LEFT: FORM ─── -->
      <div>

         <!-- Delivery info -->
         <div class="co-panel" style="margin-bottom:1.6rem;">
            <div class="co-panel-header">
               <i class="fas fa-map-marker-alt"></i> Informasi Pengiriman
            </div>
            <div class="co-panel-body">
               <div class="form-grid-2">

                  <div class="form-field">
                     <label><i class="fas fa-user"></i> Nama Lengkap</label>
                     <div class="input-wrap">
                        <input type="text" name="name" class="form-input" required placeholder="Masukkan nama">
                        <i class="fas fa-user fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-phone"></i> Nomor Kontak</label>
                     <div class="input-wrap">
                        <input type="text" name="number" class="form-input" required placeholder="08xx-xxxx-xxxx">
                        <i class="fas fa-phone fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-envelope"></i> Email</label>
                     <div class="input-wrap">
                        <input type="email" name="email" class="form-input" required placeholder="nama@email.com">
                        <i class="fas fa-envelope fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-credit-card"></i> Metode Pembayaran</label>
                     <div class="input-wrap">
                        <input type="text" value="Transfer Bank" class="form-input" readonly>
                        <i class="fas fa-credit-card fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-home"></i> No. Rumah / Apartemen</label>
                     <div class="input-wrap">
                        <input type="text" name="flat" class="form-input" required placeholder="No. 12A">
                        <i class="fas fa-home fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-road"></i> Nama Jalan</label>
                     <div class="input-wrap">
                        <input type="text" name="street" class="form-input" required placeholder="Jl. Sudirman">
                        <i class="fas fa-road fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-city"></i> Kota</label>
                     <div class="input-wrap">
                        <input type="text" name="city" class="form-input" required placeholder="Jakarta">
                        <i class="fas fa-city fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-map"></i> Provinsi</label>
                     <div class="input-wrap">
                        <input type="text" name="state" class="form-input" required placeholder="DKI Jakarta">
                        <i class="fas fa-map fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-flag"></i> Negara</label>
                     <div class="input-wrap">
                        <input type="text" name="country" class="form-input" required value="Indonesia">
                        <i class="fas fa-flag fi"></i>
                     </div>
                  </div>

                  <div class="form-field">
                     <label><i class="fas fa-location-dot"></i> Kode Pos</label>
                     <div class="input-wrap">
                        <input type="number" name="pin_code" class="form-input" required placeholder="12345">
                        <i class="fas fa-location-dot fi"></i>
                     </div>
                  </div>

               </div>
            </div>
         </div>

         <!-- Payment proof -->
         <div class="co-panel">
            <div class="co-panel-header">
               <i class="fas fa-file-image"></i> Bukti Pembayaran
            </div>
            <div class="co-panel-body">

               <!-- Bank info card -->
               <div class="bank-info-card">
                  <div class="bank-info-title"><i class="fas fa-university"></i> &nbsp;Informasi Rekening</div>
                  <div class="bank-row">
                     <span class="bank-row-label">Bank</span>
                     <span class="bank-row-val">BCA</span>
                  </div>
                  <div class="bank-row">
                     <span class="bank-row-label">Nomor Rekening</span>
                     <div style="display:flex;align-items:center;gap:.8rem;">
                        <span class="bank-row-val" id="rek-number">1234567890</span>
                        <button type="button" class="copy-btn" onclick="copyRek()">Salin</button>
                     </div>
                  </div>
                  <div class="bank-row">
                     <span class="bank-row-label">Atas Nama</span>
                     <span class="bank-row-val">Gals Collection</span>
                  </div>
                  <div class="bank-row">
                     <span class="bank-row-label">Jumlah Transfer</span>
                     <span class="bank-row-val" style="color:#fbbf24;">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                  </div>
               </div>

               <div style="margin-top:1.6rem;">
                  <p style="font-size:1.35rem;color:#64748b;margin-bottom:1.2rem;line-height:1.6;">
                     <i class="fas fa-info-circle" style="color:#4f6ef7"></i>
                     Transfer sesuai total belanja ke rekening di atas, lalu upload bukti pembayaran di bawah ini.
                  </p>

                  <div class="file-upload-area" id="proof-upload-area">
                     <input type="file" name="payment_proof" id="proof-input"
                        accept="image/jpg,image/jpeg,image/png,image/webp"
                        required
                        onchange="previewProof(this)">
                     <i class="fas fa-cloud-upload-alt"></i>
                     <div class="fu-title">Klik untuk upload bukti transfer</div>
                     <div class="fu-sub">JPG, JPEG, PNG, WEBP · Maks. 2MB</div>
                     <div class="fu-name" id="proof-filename"></div>
                  </div>

                  <img id="proof-preview" class="proof-preview" alt="Preview bukti pembayaran">
               </div>

            </div>
         </div>

      </div>

      <!-- ─── RIGHT: ORDER SUMMARY ─── -->
      <div class="order-summary-panel">
         <div class="co-panel">
            <div class="co-panel-header">
               <i class="fas fa-receipt"></i> Ringkasan Pesanan
            </div>
            <div class="co-panel-body">

               <!-- Items -->
               <div class="summary-items">
                  <?php foreach($cart_items as $ci):
                     $sub = $ci['price'] * $ci['quantity'];
                  ?>
                  <div class="summary-item">
                     <div class="sum-item-img">
                        <img src="uploaded_img/<?= htmlspecialchars($ci['image']); ?>" alt="">
                     </div>
                     <div class="sum-item-info">
                        <div class="sum-item-name"><?= htmlspecialchars($ci['name']); ?></div>
                        <?php if(!empty($ci['selected_variation_1_value'])): ?>
                           <div class="sum-item-variation">
                              <?= htmlspecialchars($ci['selected_variation_1_name']); ?>: <?= htmlspecialchars($ci['selected_variation_1_value']); ?>
                              <?php if(!empty($ci['selected_variation_2_value'])): ?>
                                 &nbsp;·&nbsp; <?= htmlspecialchars($ci['selected_variation_2_name']); ?>: <?= htmlspecialchars($ci['selected_variation_2_value']); ?>
                              <?php endif; ?>
                           </div>
                        <?php endif; ?>
                        <div class="sum-item-qty">x<?= htmlspecialchars($ci['quantity']); ?></div>
                     </div>
                     <div class="sum-item-price">Rp<?= number_format($sub,0,',','.'); ?></div>
                  </div>
                  <?php endforeach; ?>
               </div>

               <!-- Totals -->
               <div class="summary-totals">
                  <div class="total-row">
                     <span class="lbl">Subtotal (<?= count($cart_items); ?> item)</span>
                     <span class="val">Rp<?= number_format($grand_total,0,',','.'); ?></span>
                  </div>
                  <div class="total-row">
                     <span class="lbl">Ongkos Kirim</span>
                     <span class="val" style="color:#059669;">Gratis</span>
                  </div>
                  <div class="total-row grand" style="padding-top:1rem;border-top:2px solid #f1f5f9;margin-top:.4rem;">
                     <span class="lbl">Total</span>
                     <span class="val">Rp<?= number_format($grand_total,0,',','.'); ?></span>
                  </div>
               </div>

               <!-- Submit -->
               <button type="submit" name="order" class="btn-checkout-submit">
                  <i class="fas fa-bag-shopping"></i> Proses Pesanan
               </button>

               <div style="text-align:center;">
                  <a href="cart.php" class="btn-back-cart">
                     <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                  </a>
               </div>

               <!-- Trust badges -->
               <div style="display:flex;justify-content:center;gap:1.6rem;flex-wrap:wrap;padding-top:1.6rem;border-top:1px solid #f1f5f9;margin-top:1rem;">
                  <div style="display:flex;align-items:center;gap:.5rem;font-size:1.2rem;color:#94a3b8;font-weight:600;">
                     <i class="fas fa-lock" style="color:#cbd5e1"></i> Aman & Terenkripsi
                  </div>
                  <div style="display:flex;align-items:center;gap:.5rem;font-size:1.2rem;color:#94a3b8;font-weight:600;">
                     <i class="fas fa-shield-halved" style="color:#cbd5e1"></i> Data Terlindungi
                  </div>
               </div>

            </div>
         </div>
      </div>

   </div>
   </form>

   <?php else: ?>

   <div class="empty-checkout">
      <i class="fas fa-cart-shopping"></i>
      <h3>Tidak Ada Produk Dipilih</h3>
      <p>Silakan kembali ke keranjang dan pilih produk yang ingin kamu checkout.</p>
      <a href="cart.php" class="btn-go-cart"><i class="fas fa-cart-shopping"></i> Ke Keranjang</a>
   </div>

   <?php endif; ?>

</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
   /* Preview bukti pembayaran */
   function previewProof(input){
      if(!input.files||!input.files[0]) return;
      const file   = input.files[0];
      const fnEl   = document.getElementById('proof-filename');
      const preEl  = document.getElementById('proof-preview');
      const areaEl = document.getElementById('proof-upload-area');

      if(fnEl) fnEl.textContent = file.name;

      const reader = new FileReader();
      reader.onload = e => {
         if(preEl){
            preEl.src = e.target.result;
            preEl.style.display = 'block';
         }
         if(areaEl) areaEl.style.borderColor = '#059669';
      };
      reader.readAsDataURL(file);
   }

   /* Copy nomor rekening */
   function copyRek(){
      const rek = document.getElementById('rek-number')?.textContent?.trim();
      if(!rek) return;
      navigator.clipboard.writeText(rek).then(()=>{
         const btn = document.querySelector('.copy-btn');
         if(btn){
            btn.textContent = 'Disalin!';
            btn.style.background = 'rgba(52,211,153,.35)';
            setTimeout(()=>{ btn.textContent='Salin'; btn.style.background=''; }, 2000);
         }
      }).catch(()=>{});
   }

   /* Auto-dismiss notifications */
   document.querySelectorAll('.co-notif').forEach(n=>{
      setTimeout(()=>{
         n.style.transition='opacity .4s';
         n.style.opacity='0';
         setTimeout(()=>n.remove(),420);
      }, 6000);
   });
</script>
</body>
</html>