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
</head>
<body class="php-cart">

<?php include 'components/user_header.php'; ?>

<div class="cart-page-wrapper">
   <div class="cart-container">
      <h2 class="cart-page-title">Keranjang Belanja</h2>

      <!-- Header Row -->
      <div class="cart-header-row">
         <div></div>
         <div>Produk</div>
         <div class="u-inline-style-001">Harga Satuan</div>
         <div class="u-inline-style-001">Kuantitas</div>
         <div class="u-inline-style-001">Total Harga</div>
         <div class="u-inline-style-001">Aksi</div>
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