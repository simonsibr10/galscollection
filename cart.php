<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit;
}

if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
   $delete_cart_item->execute([$cart_id, $user_id]);
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
   exit;
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = (int)($_POST['qty'] ?? 1);
   if($qty < 1){ $qty = 1; }
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
   $update_qty->execute([$qty, $cart_id, $user_id]);
}

if(isset($_POST['toggle_selected'])){
   $cart_id = $_POST['cart_id'];
   $selected = isset($_POST['selected']) ? 1 : 0;
   $update_selected = $conn->prepare("UPDATE `cart` SET selected = ? WHERE id = ? AND user_id = ?");
   $update_selected->execute([$selected, $cart_id, $user_id]);
   header('location:cart.php');
   exit;
}

if(isset($_POST['select_all_items'])){
   $update_all = $conn->prepare("UPDATE `cart` SET selected = 1 WHERE user_id = ?");
   $update_all->execute([$user_id]);
   header('location:cart.php');
   exit;
}

if(isset($_POST['reset_selected_items'])){
   $reset_all = $conn->prepare("UPDATE `cart` SET selected = 0 WHERE user_id = ?");
   $reset_all->execute([$user_id]);
   header('location:cart.php');
   exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Keranjang Belanja</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      /* ===== RESET & BASE ===== */
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      .cart-page-wrapper {
         background: #f5f5f5;
         min-height: 100vh;
         padding: 2rem 0 6rem;
         font-family: 'Segoe UI', sans-serif;
      }

      .cart-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 1.6rem;
      }

      .cart-page-title {
         font-size: 2rem;
         font-weight: 700;
         color: #222;
         margin-bottom: 1.6rem;
         letter-spacing: -.3px;
      }

      /* ===== HEADER ROW ===== */
      .cart-header-row {
         background: #fff;
         border-radius: 1rem;
         padding: 1.2rem 2rem;
         display: grid;
         grid-template-columns: 2.4rem 1fr 14rem 14rem 14rem 10rem;
         align-items: center;
         gap: 1rem;
         margin-bottom: .8rem;
         font-size: 1.3rem;
         color: #888;
         font-weight: 500;
      }

      /* ===== CART ITEM CARD ===== */
      .cart-item-card {
         background: #fff;
         border-radius: 1rem;
         padding: 1.6rem 2rem;
         display: grid;
         grid-template-columns: 2.4rem 1fr 14rem 14rem 14rem 10rem;
         align-items: center;
         gap: 1rem;
         margin-bottom: .8rem;
         transition: box-shadow .2s;
      }

      .cart-item-card:hover {
         box-shadow: 0 4px 16px rgba(0,0,0,.07);
      }

      /* Checkbox */
      .cart-check input[type="checkbox"] {
         width: 1.8rem;
         height: 1.8rem;
         cursor: pointer;
         accent-color: #1a2a6c;
      }

      /* Product info */
      .cart-product-info {
         display: flex;
         align-items: flex-start;
         gap: 1.2rem;
      }

      .cart-product-info a.product-img-link {
         flex-shrink: 0;
      }

      .cart-product-info img {
         width: 7.2rem;
         height: 7.2rem;
         object-fit: cover;
         border-radius: .6rem;
         border: 1px solid #f0f0f0;
      }

      .cart-product-meta {
         display: flex;
         flex-direction: column;
         gap: .4rem;
      }

      .cart-product-name {
         font-size: 1.4rem;
         font-weight: 500;
         color: #222;
         line-height: 1.5;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
      }

      .cart-variation-tag {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         background: #f5f5f5;
         border-radius: .4rem;
         padding: .3rem .8rem;
         font-size: 1.2rem;
         color: #555;
         width: fit-content;
      }

      .cart-variation-tag i {
         font-size: 1rem;
         color: #aaa;
      }

      /* Price */
      .cart-unit-price {
         font-size: 1.4rem;
         color: #444;
         text-align: center;
      }

      /* Qty stepper */
      .cart-qty-cell {
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .qty-form {
         display: flex;
         align-items: center;
         gap: 0;
      }

      .qty-form input[type="number"] {
         width: 4.4rem;
         height: 3.2rem;
         text-align: center;
         border: 1px solid #e0e0e0;
         border-left: none;
         border-right: none;
         font-size: 1.4rem;
         color: #222;
         -moz-appearance: textfield;
         outline: none;
      }

      .qty-form input[type="number"]::-webkit-inner-spin-button,
      .qty-form input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }

      .qty-btn {
         width: 3.2rem;
         height: 3.2rem;
         background: #fff;
         border: 1px solid #e0e0e0;
         font-size: 1.6rem;
         color: #444;
         cursor: pointer;
         transition: background .15s;
         display: flex;
         align-items: center;
         justify-content: center;
         line-height: 1;
      }

      .qty-btn:first-child { border-radius: .4rem 0 0 .4rem; }
      .qty-btn:last-child  { border-radius: 0 .4rem .4rem 0; }
      .qty-btn:hover { background: #f5f5f5; }

      /* Subtotal */
      .cart-subtotal {
         font-size: 1.4rem;
         font-weight: 600;
         color: #1a2a6c;
         text-align: center;
      }

      /* Actions */
      .cart-actions-cell {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: .8rem;
      }

      .btn-hapus-item {
         background: none;
         border: none;
         color: #888;
         font-size: 1.3rem;
         cursor: pointer;
         padding: .4rem .8rem;
         border-radius: .4rem;
         transition: color .15s, background .15s;
      }

      .btn-hapus-item:hover { color: #ee4d2d; background: #fff0ed; }

      .btn-view-item {
         color: #1a6ec7;
         font-size: 1.2rem;
         text-decoration: none;
         padding: .4rem .8rem;
         border-radius: .4rem;
         transition: background .15s;
      }

      .btn-view-item:hover { background: #eef4ff; }

      /* ===== STICKY BOTTOM BAR ===== */
      .cart-bottom-bar {
         position: fixed;
         bottom: 0;
         left: 0;
         right: 0;
         background: #fff;
         border-top: 1px solid #e8e8e8;
         z-index: 100;
         padding: 1.2rem 0;
         box-shadow: 0 -4px 20px rgba(0,0,0,.08);
      }

      .cart-bottom-inner {
         max-width: 1200px;
         margin: 0 auto;
         padding: 0 1.6rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 2rem;
         flex-wrap: wrap;
      }

      .cart-bottom-left {
         display: flex;
         align-items: center;
         gap: 2rem;
         flex-wrap: wrap;
      }

      .select-all-form, .reset-form, .delete-all-form {
         display: inline-flex;
         align-items: center;
      }

      .btn-select-all, .btn-reset, .btn-hapus-semua {
         background: none;
         border: none;
         font-size: 1.4rem;
         cursor: pointer;
         padding: .6rem 1rem;
         border-radius: .5rem;
         transition: background .15s;
         color: #444;
      }

      .btn-select-all:hover { background: #f5f5f5; }
      .btn-reset:hover { color: #ee4d2d; background: #fff0ed; }
      .btn-hapus-semua { color: #ee4d2d; }
      .btn-hapus-semua:hover { background: #fff0ed; }

      .cart-bottom-right {
         display: flex;
         align-items: center;
         gap: 2.4rem;
      }

      .cart-total-info {
         text-align: right;
      }

      .cart-total-label {
         font-size: 1.3rem;
         color: #888;
      }

      .cart-total-value {
         font-size: 2rem;
         font-weight: 700;
         color: #1a2a6c;
      }

      .cart-total-count {
         font-size: 1.2rem;
         color: #aaa;
         margin-top: .2rem;
      }

      .btn-checkout {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         border: none;
         padding: 1.2rem 3.2rem;
         border-radius: .6rem;
         font-size: 1.5rem;
         font-weight: 600;
         cursor: pointer;
         text-decoration: none;
         display: inline-block;
         transition: opacity .2s, transform .1s;
         white-space: nowrap;
      }

      .btn-checkout:hover { opacity: .9; }
      .btn-checkout:active { transform: scale(.97); }
      .btn-checkout.disabled {
         background: #ccc;
         cursor: not-allowed;
         pointer-events: none;
      }

      .btn-lanjut-belanja {
         color: #1a2a6c;
         font-size: 1.4rem;
         text-decoration: none;
         padding: .6rem 1rem;
         border-radius: .5rem;
         transition: background .15s;
      }

      .btn-lanjut-belanja:hover { background: #fff0ed; }

      /* ===== EMPTY STATE ===== */
      .cart-empty {
         background: #fff;
         border-radius: 1rem;
         padding: 6rem 2rem;
         text-align: center;
         color: #aaa;
         font-size: 1.6rem;
      }

      .cart-empty i {
         font-size: 5rem;
         display: block;
         margin-bottom: 1.6rem;
         color: #ddd;
      }

      /* ===== RESPONSIVE ===== */
      @media (max-width: 900px) {
         .cart-header-row { display: none; }

         .cart-item-card {
            grid-template-columns: 2.4rem 1fr auto;
            grid-template-rows: auto auto auto;
         }

         .cart-check { grid-row: 1; grid-column: 1; }
         .cart-product-info { grid-row: 1; grid-column: 2; }
         .cart-actions-cell { grid-row: 1; grid-column: 3; flex-direction: row; }
         .cart-unit-price { grid-row: 2; grid-column: 2; text-align: left; }
         .cart-qty-cell { grid-row: 3; grid-column: 2; justify-content: flex-start; }
         .cart-subtotal { grid-row: 2; grid-column: 3; text-align: right; }
      }

      @media (max-width: 600px) {
         .cart-bottom-inner { flex-direction: column; align-items: stretch; }
         .cart-bottom-right { justify-content: space-between; }
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="cart-page-wrapper">
   <div class="cart-container">
      <h2 class="cart-page-title">Keranjang Belanja</h2>

      <!-- Header Row -->
      <div class="cart-header-row">
         <div></div>
         <div>Produk</div>
         <div style="text-align:center">Harga Satuan</div>
         <div style="text-align:center">Kuantitas</div>
         <div style="text-align:center">Total Harga</div>
         <div style="text-align:center">Aksi</div>
      </div>

      <?php
         $grand_total_all      = 0;
         $grand_total_selected = 0;
         $selected_count       = 0;

         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? ORDER BY id DESC");
         $select_cart->execute([$user_id]);

         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){

               $sub_total       = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total_all += $sub_total;

               // FIX: gunakan ?? 0 agar tidak error jika kolom 'selected' NULL atau tidak ada
               $is_selected = (int)($fetch_cart['selected'] ?? 0);

               if($is_selected == 1){
                  $grand_total_selected += $sub_total;
                  $selected_count++;
               }
      ?>

      <!-- Cart Item -->
      <div class="cart-item-card">

         <!-- Checkbox -->
         <div class="cart-check">
            <form action="" method="post">
               <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
               <input type="hidden" name="toggle_selected" value="1">
               <input
                  type="checkbox"
                  name="selected"
                  onchange="this.form.submit()"
                  <?= $is_selected == 1 ? 'checked' : ''; ?>
               >
            </form>
         </div>

         <!-- Product Info -->
         <div class="cart-product-info">
            <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="product-img-link">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="<?= htmlspecialchars($fetch_cart['name']); ?>">
            </a>
            <div class="cart-product-meta">
               <div class="cart-product-name"><?= htmlspecialchars($fetch_cart['name']); ?></div>

               <?php if(!empty($fetch_cart['selected_variation_1_value'])){ ?>
                  <span class="cart-variation-tag">
                     <i class="fas fa-tag"></i>
                     <?= htmlspecialchars($fetch_cart['selected_variation_1_name']); ?>:
                     <?= htmlspecialchars($fetch_cart['selected_variation_1_value']); ?>
                  </span>
               <?php } ?>

               <?php if(!empty($fetch_cart['selected_variation_2_value'])){ ?>
                  <span class="cart-variation-tag">
                     <i class="fas fa-tag"></i>
                     <?= htmlspecialchars($fetch_cart['selected_variation_2_name']); ?>:
                     <?= htmlspecialchars($fetch_cart['selected_variation_2_value']); ?>
                  </span>
               <?php } ?>
            </div>
         </div>

         <!-- Unit Price -->
         <div class="cart-unit-price">
            Rp<?= number_format($fetch_cart['price'], 0, ',', '.'); ?>
         </div>

         <!-- Quantity Stepper -->
         <div class="cart-qty-cell">
            <form action="" method="post" class="qty-form">
               <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
               <button type="button" class="qty-btn"
                  onclick="
                     var inp = this.nextElementSibling;
                     if(parseInt(inp.value) > 1){ inp.value = parseInt(inp.value)-1; this.form.submit(); }
                  ">−</button>
               <input
                  type="number"
                  name="qty"
                  min="1"
                  max="99"
                  value="<?= htmlspecialchars($fetch_cart['quantity']); ?>"
                  onchange="this.form.submit()"
               >
               <button type="button" class="qty-btn"
                  onclick="
                     var inp = this.previousElementSibling;
                     if(parseInt(inp.value) < 99){ inp.value = parseInt(inp.value)+1; this.form.submit(); }
                  ">+</button>
               <input type="hidden" name="update_qty" value="1">
            </form>
         </div>

         <!-- Subtotal -->
         <div class="cart-subtotal">
            Rp<?= number_format($sub_total, 0, ',', '.'); ?>
         </div>

         <!-- Actions -->
         <div class="cart-actions-cell">
            <form action="" method="post">
               <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
               <button
                  type="submit"
                  name="delete"
                  class="btn-hapus-item"
                  onclick="return confirm('Hapus item ini dari keranjang?');"
               >
                  <i class="fas fa-trash-alt"></i> Hapus
               </button>
            </form>
            <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="btn-view-item">
               <i class="fas fa-eye"></i> Lihat
            </a>
         </div>

      </div>

      <?php
            }
         } else {
      ?>
      <div class="cart-empty">
         <i class="fas fa-shopping-cart"></i>
         Keranjang kamu masih kosong, yuk mulai belanja!
      </div>
      <?php } ?>

   </div><!-- /cart-container -->
</div><!-- /cart-page-wrapper -->


<!-- ===== STICKY BOTTOM BAR ===== -->
<div class="cart-bottom-bar">
   <div class="cart-bottom-inner">

      <div class="cart-bottom-left">
         <!-- Select All -->
         <form action="" method="post" class="select-all-form">
            <button type="submit" name="select_all_items" class="btn-select-all">
               <i class="fas fa-check-square"></i> Pilih Semua
            </button>
         </form>

         <!-- Reset -->
         <form action="" method="post" class="reset-form">
            <button type="submit" name="reset_selected_items" class="btn-reset">
               <i class="fas fa-times-circle"></i> Reset Pilihan
            </button>
         </form>

         <!-- Hapus Semua -->
         <?php if($grand_total_all > 0){ ?>
         <form action="cart.php?delete_all" method="get" class="delete-all-form">
            <button
               type="submit"
               class="btn-hapus-semua"
               onclick="return confirm('Hapus semua item dari keranjang?');"
            >
               <i class="fas fa-trash"></i> Hapus Semua
            </button>
         </form>
         <?php } ?>

         <a href="shop.php" class="btn-lanjut-belanja">
            <i class="fas fa-store"></i> Lanjut Belanja
         </a>
      </div>

      <div class="cart-bottom-right">
         <div class="cart-total-info">
            <div class="cart-total-label">Total (<?= $selected_count; ?> produk dipilih):</div>
            <div class="cart-total-value">Rp<?= number_format($grand_total_selected, 0, ',', '.'); ?></div>
            <div class="cart-total-count">Total semua: Rp<?= number_format($grand_total_all, 0, ',', '.'); ?></div>
         </div>

         <a
            href="checkout.php"
            class="btn-checkout <?= ($selected_count > 0) ? '' : 'disabled'; ?>"
         >
            Checkout (<?= $selected_count; ?>)
         </a>
      </div>

   </div>
</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>