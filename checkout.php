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
</head>
<body class="php-checkout">

<?php include 'components/user_header.php'; ?>

<div class="checkout-page">

   <!-- Breadcrumb -->
   <nav class="checkout-bc">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="cart.php">Keranjang</a>
      <i class="fas fa-chevron-right"></i>
      <span class="u-inline-style-006">Checkout</span>
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
         <div class="co-panel u-inline-style-007">
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
                     <div class="u-inline-style-008">
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
                     <span class="bank-row-val u-inline-style-009">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                  </div>
               </div>

               <div class="u-inline-style-010">
                  <p class="u-inline-style-011">
                     <i class="fas fa-info-circle u-inline-style-012"></i>
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
                     <span class="val u-inline-style-013">Gratis</span>
                  </div>
                  <div class="total-row grand u-inline-style-014">
                     <span class="lbl">Total</span>
                     <span class="val">Rp<?= number_format($grand_total,0,',','.'); ?></span>
                  </div>
               </div>

               <!-- Submit -->
               <button type="submit" name="order" class="btn-checkout-submit">
                  <i class="fas fa-bag-shopping"></i> Proses Pesanan
               </button>

               <div class="u-inline-style-015">
                  <a href="cart.php" class="btn-back-cart">
                     <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                  </a>
               </div>

               <!-- Trust badges -->
               <div class="u-inline-style-016">
                  <div class="u-inline-style-017">
                     <i class="fas fa-lock u-inline-style-018"></i> Aman & Terenkripsi
                  </div>
                  <div class="u-inline-style-017">
                     <i class="fas fa-shield-halved u-inline-style-018"></i> Data Terlindungi
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