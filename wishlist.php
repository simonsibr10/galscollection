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

include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="wishlist-page">
<div class="wishlist-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      $total_wish   = $select_wishlist->rowCount();
      $wish_items   = $select_wishlist->fetchAll(PDO::FETCH_ASSOC);
      foreach($wish_items as $wi) $grand_total += $wi['price'];
   ?>

   <!-- PAGE HEADER -->
   <div class="wishlist-page-header">
      <div class="wishlist-page-header-left">
         <h2><i class="fas fa-heart"></i> Produk Kesukaan Kamu</h2>
         <?php if($total_wish > 0): ?>
            <span class="wish-count-badge"><?= $total_wish; ?> produk</span>
         <?php endif; ?>
      </div>
      <div class="wishlist-header-actions">
         <a href="shop.php" class="btn-continue-shop">
            <i class="fas fa-store"></i> Lanjut Belanja
         </a>
         <?php if($grand_total > 0): ?>
            <a
               href="wishlist.php?delete_all"
               class="btn-clear-wish"
               onclick="return confirm('Hapus semua dari daftar suka?');"
            >
               <i class="fas fa-trash"></i> Hapus Semua
            </a>
         <?php endif; ?>
      </div>
   </div>

   <!-- NOTIFICATIONS -->
   <?php if(isset($message)): foreach($message as $msg):
      $nt = (stripos($msg,'keranjang') !== false && stripos($msg,'sudah') === false) ? 'success' :
            (stripos($msg,'variasi') !== false ? 'warn' : 'info');
   ?>
      <div class="wish-notif <?= $nt; ?>">
         <i class="fas <?= $nt==='success'?'fa-circle-check':($nt==='warn'?'fa-triangle-exclamation':'fa-circle-info'); ?>"></i>
         <?= htmlspecialchars($msg); ?>
      </div>
   <?php endforeach; endif; ?>

   <!-- GRID -->
   <?php if($total_wish > 0): ?>
   <div class="wishlist-grid">

      <?php foreach($wish_items as $fetch_wishlist): ?>
      <form action="" method="post" class="wish-card">

         <!-- Hidden fields -->
         <input type="hidden" name="pid"         value="<?= htmlspecialchars($fetch_wishlist['pid']); ?>">
         <input type="hidden" name="wishlist_id" value="<?= htmlspecialchars($fetch_wishlist['id']); ?>">
         <input type="hidden" name="name"        value="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
         <input type="hidden" name="price"       value="<?= htmlspecialchars($fetch_wishlist['price']); ?>">
         <input type="hidden" name="image"       value="<?= htmlspecialchars($fetch_wishlist['image']); ?>">

         <!-- Heart badge -->
         <div class="wish-heart-badge"><i class="fas fa-heart"></i></div>

         <!-- Quick view button -->
         <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_wishlist['pid']); ?>"
            class="wish-view-link" title="Lihat Detail">
            <i class="fas fa-eye"></i>
         </a>

         <!-- Product image -->
         <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_wishlist['pid']); ?>"
            class="wish-img-wrap">
            <img
               src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image']); ?>"
               alt="<?= htmlspecialchars($fetch_wishlist['name']); ?>"
               loading="lazy"
            >
         </a>

         <div class="wish-card-body">

            <!-- Product name -->
            <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_wishlist['pid']); ?>"
               class="wish-product-name">
               <?= htmlspecialchars($fetch_wishlist['name']); ?>
            </a>

            <!-- Price & Qty -->
            <div class="wish-price-row">
               <div class="wish-price">
                  Rp<?= number_format($fetch_wishlist['price'], 0, ',', '.'); ?>
               </div>

               <!-- Qty stepper -->
               <div class="wish-qty-wrap">
                  <button type="button" class="wish-qty-btn"
                     onclick="changeQty(this, -1)">−</button>
                  <input
                     type="number"
                     name="qty"
                     class="wish-qty-input"
                     min="1"
                     max="99"
                     value="1"
                     onchange="if(this.value<1)this.value=1; if(this.value>99)this.value=99;"
                  >
                  <button type="button" class="wish-qty-btn"
                     onclick="changeQty(this, 1)">+</button>
               </div>
            </div>

            <!-- Actions -->
            <div class="wish-card-actions">
               <button type="submit" name="add_to_cart" class="btn-add-to-cart">
                  <i class="fas fa-cart-shopping"></i> Tambah ke Keranjang
               </button>
               <button
                  type="submit"
                  name="delete"
                  class="btn-remove-wish"
                  onclick="return confirm('Hapus dari daftar suka?');"
                  title="Hapus"
               >
                  <i class="fas fa-trash"></i>
               </button>
            </div>

         </div>
      </form>
      <?php endforeach; ?>

   </div>

   <!-- STICKY BOTTOM SUMMARY BAR -->
   <div class="wish-summary-bar">
      <div class="wish-summary-inner">
         <div class="wish-summary-left">
            <span class="wish-total-label">Total Keseluruhan (<?= $total_wish; ?> produk):</span>
            <span class="wish-total-value">Rp<?= number_format($grand_total, 0, ',', '.'); ?></span>
         </div>
         <div class="wish-summary-right">
            <a href="shop.php" class="btn-continue-shop">
               <i class="fas fa-store"></i> Lanjut Belanja
            </a>
            <a
               href="wishlist.php?delete_all"
               class="btn-clear-wish"
               onclick="return confirm('Hapus semua dari daftar suka?');"
            >
               <i class="fas fa-trash"></i> Hapus Semua
            </a>
         </div>
      </div>
   </div>

   <?php else: ?>

   <div class="wish-empty-state">
      <i class="fas fa-heart-crack"></i>
      <h3>Daftar Suka Masih Kosong</h3>
      <p>Kamu belum menambahkan produk apapun ke daftar suka. Yuk temukan produk yang kamu sukai!</p>
      <a href="shop.php" class="btn-shop-now">
         <i class="fas fa-bag-shopping"></i> Lihat Produk
      </a>
   </div>

   <?php endif; ?>

</div>
</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
   // Qty stepper buttons
   function changeQty(btn, delta){
      const wrap  = btn.closest('.wish-qty-wrap');
      const input = wrap.querySelector('.wish-qty-input');
      let val = parseInt(input.value) + delta;
      if(val < 1)  val = 1;
      if(val > 99) val = 99;
      input.value = val;
   }

   // Auto-dismiss notifications
   document.querySelectorAll('.wish-notif').forEach(n => {
      setTimeout(() => {
         n.style.transition = 'opacity .4s ease';
         n.style.opacity    = '0';
         setTimeout(() => n.remove(), 420);
      }, 4000);
   });
</script>
</body>
</html>
