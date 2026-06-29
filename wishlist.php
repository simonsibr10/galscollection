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

   <style>
      /* ===== WISHLIST PAGE ===== */
      .wishlist-page {
         background: #f8f9fc;
         min-height: 100vh;
         padding: 2.4rem 0 6rem;
         font-family: 'Segoe UI', sans-serif;
      }

      .wishlist-container {
         max-width: 1300px;
         margin: 0 auto;
         padding: 0 2rem;
      }

      /* ===== PAGE HEADER ===== */
      .wishlist-page-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 1rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideDown .45s ease both;
      }

      .wishlist-page-header-left {
         display: flex;
         align-items: center;
         gap: 1.2rem;
         flex-wrap: wrap;
      }

      .wishlist-page-header h2 {
         font-size: 2.8rem;
         font-weight: 800;
         color: #1e293b;
         display: flex;
         align-items: center;
         gap: .8rem;
      }

      .wishlist-page-header h2 i { color: #e11d48; }

      .wish-count-badge {
         background: #fff0f3;
         color: #be123c;
         border-radius: 3rem;
         padding: .5rem 1.2rem;
         font-size: 1.3rem;
         font-weight: 700;
         border: 1px solid #fecdd3;
      }

      .wishlist-header-actions {
         display: flex;
         gap: 1rem;
         align-items: center;
         flex-wrap: wrap;
      }

      .btn-continue-shop {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         padding: .9rem 1.8rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border-radius: .8rem;
         font-size: 1.35rem;
         font-weight: 600;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
      }

      .btn-continue-shop:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79,110,247,.3); }

      .btn-clear-wish {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         padding: .9rem 1.8rem;
         background: #fff0f3;
         color: #be123c;
         border: 1px solid #fecdd3;
         border-radius: .8rem;
         font-size: 1.35rem;
         font-weight: 600;
         text-decoration: none;
         transition: background .15s;
      }

      .btn-clear-wish:hover { background: #ffe4e6; }

      /* ===== NOTIFICATIONS ===== */
      .wish-notif {
         padding: 1.2rem 1.6rem;
         border-radius: .8rem;
         font-size: 1.35rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         margin-bottom: 1.6rem;
         animation: fadeSlideDown .3s ease both;
      }

      .wish-notif.success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }
      .wish-notif.info    { background: #eff2ff; border-left: 4px solid #4f6ef7; color: #1e40af; }
      .wish-notif.warn    { background: #fffbeb; border-left: 4px solid #f59e0b; color: #78350f; }

      /* ===== PRODUCT GRID ===== */
      .wishlist-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(26rem, 1fr));
         gap: 2rem;
         margin-bottom: 2.4rem;
      }

      /* ===== PRODUCT CARD ===== */
      .wish-card {
         background: #fff;
         border-radius: 1.4rem;
         overflow: hidden;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         display: flex;
         flex-direction: column;
         transition: transform .22s, box-shadow .22s;
         animation: fadeSlideUp .5s ease both;
         position: relative;
      }

      .wish-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 14px 36px rgba(0,0,0,.1);
      }

      /* Heart badge */
      .wish-heart-badge {
         position: absolute;
         top: 1rem; right: 1rem;
         width: 3.4rem; height: 3.4rem;
         border-radius: 50%;
         background: rgba(255,255,255,.92);
         display: flex; align-items: center; justify-content: center;
         color: #e11d48;
         font-size: 1.5rem;
         z-index: 2;
         box-shadow: 0 2px 8px rgba(0,0,0,.1);
      }

      /* Quick view link */
      .wish-view-link {
         position: absolute;
         top: 1rem; left: 1rem;
         width: 3.4rem; height: 3.4rem;
         border-radius: 50%;
         background: rgba(255,255,255,.92);
         display: flex; align-items: center; justify-content: center;
         color: #475569;
         font-size: 1.4rem;
         z-index: 2;
         text-decoration: none;
         box-shadow: 0 2px 8px rgba(0,0,0,.1);
         transition: color .15s, background .15s;
         opacity: 0;
         transition: opacity .2s;
      }

      .wish-card:hover .wish-view-link { opacity: 1; }
      .wish-view-link:hover { background: #fff; color: #4f6ef7; }

      /* Image */
      .wish-img-wrap {
         width: 100%;
         aspect-ratio: 1/1;
         overflow: hidden;
         background: #f8fafc;
         display: block;
         text-decoration: none;
      }

      .wish-img-wrap img {
         width: 100%; height: 100%;
         object-fit: cover;
         display: block;
         transition: transform .3s;
      }

      .wish-card:hover .wish-img-wrap img { transform: scale(1.06); }

      /* Card body */
      .wish-card-body {
         padding: 1.6rem;
         display: flex;
         flex-direction: column;
         gap: 1rem;
         flex: 1;
      }

      .wish-product-name {
         font-size: 1.5rem;
         font-weight: 700;
         color: #1e293b;
         line-height: 1.4;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         overflow: hidden;
         text-decoration: none;
      }

      .wish-product-name:hover { color: #4f6ef7; }

      .wish-price-row {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: .8rem;
      }

      .wish-price {
         font-size: 2rem;
         font-weight: 800;
         color: #e11d48;
      }

      /* Qty input */
      .wish-qty-wrap {
         display: flex;
         align-items: center;
         gap: 0;
         border: 1.5px solid #e2e8f0;
         border-radius: .6rem;
         overflow: hidden;
      }

      .wish-qty-btn {
         width: 3rem; height: 3rem;
         background: #f8fafc;
         border: none;
         font-size: 1.5rem;
         color: #475569;
         cursor: pointer;
         display: flex; align-items: center; justify-content: center;
         transition: background .15s;
         flex-shrink: 0;
         line-height: 1;
      }

      .wish-qty-btn:hover { background: #eff2ff; color: #1a2a6c; }

      .wish-qty-input {
         width: 4rem; height: 3rem;
         text-align: center;
         border: none;
         border-left: 1.5px solid #e2e8f0;
         border-right: 1.5px solid #e2e8f0;
         font-size: 1.4rem;
         color: #1e293b;
         font-weight: 600;
         font-family: inherit;
         outline: none;
         -moz-appearance: textfield;
      }

      .wish-qty-input::-webkit-inner-spin-button,
      .wish-qty-input::-webkit-outer-spin-button { -webkit-appearance: none; }

      /* Card actions */
      .wish-card-actions {
         display: grid;
         grid-template-columns: 1fr auto;
         gap: .8rem;
         margin-top: auto;
         padding-top: 1rem;
         border-top: 1px solid #f8fafc;
      }

      .btn-add-to-cart {
         display: inline-flex;
         align-items: center;
         justify-content: center;
         gap: .5rem;
         padding: 1rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border: none;
         border-radius: .8rem;
         font-size: 1.35rem;
         font-weight: 600;
         cursor: pointer;
         transition: transform .15s, box-shadow .15s;
         font-family: inherit;
         width: 100%;
      }

      .btn-add-to-cart:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(79,110,247,.3); }
      .btn-add-to-cart:active { transform: scale(.97); }

      .btn-remove-wish {
         display: inline-flex;
         align-items: center;
         justify-content: center;
         padding: 1rem 1.2rem;
         background: #fff0f3;
         color: #be123c;
         border: 1px solid #fecdd3;
         border-radius: .8rem;
         font-size: 1.4rem;
         cursor: pointer;
         transition: background .15s;
         font-family: inherit;
         white-space: nowrap;
      }

      .btn-remove-wish:hover { background: #ffe4e6; }

      /* ===== BOTTOM SUMMARY BAR ===== */
      .wish-summary-bar {
         position: fixed;
         bottom: 0; left: 0; right: 0;
         background: #fff;
         border-top: 1px solid #e8e8e8;
         box-shadow: 0 -4px 20px rgba(0,0,0,.08);
         padding: 1.2rem 0;
         z-index: 100;
         animation: slideUp .3s ease both;
      }

      @keyframes slideUp { from{transform:translateY(100%)} to{transform:translateY(0)} }

      .wish-summary-inner {
         max-width: 1300px;
         margin: 0 auto;
         padding: 0 2rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 2rem;
         flex-wrap: wrap;
      }

      .wish-summary-left {
         display: flex;
         flex-direction: column;
      }

      .wish-total-label { font-size: 1.3rem; color: #64748b; }

      .wish-total-value {
         font-size: 2.2rem;
         font-weight: 800;
         color: #e11d48;
      }

      .wish-summary-right {
         display: flex;
         gap: 1rem;
         flex-wrap: wrap;
         align-items: center;
      }

      /* ===== EMPTY STATE ===== */
      .wish-empty-state {
         text-align: center;
         padding: 7rem 2rem;
         color: #94a3b8;
         background: #fff;
         border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.04);
         animation: fadeSlideUp .5s ease both;
      }

      .wish-empty-state i {
         font-size: 6.4rem;
         display: block;
         margin-bottom: 1.6rem;
         color: #fca5a5;
      }

      .wish-empty-state h3 {
         font-size: 2.2rem;
         font-weight: 700;
         color: #475569;
         margin-bottom: .8rem;
      }

      .wish-empty-state p {
         font-size: 1.4rem;
         margin-bottom: 2.4rem;
         max-width: 36rem;
         margin-left: auto;
         margin-right: auto;
         line-height: 1.7;
      }

      .btn-shop-now {
         display: inline-flex;
         align-items: center;
         gap: .7rem;
         padding: 1.3rem 3rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border-radius: 1rem;
         font-size: 1.5rem;
         font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .2s;
      }

      .btn-shop-now:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      .wish-card:nth-child(1) { animation-delay:.04s }
      .wish-card:nth-child(2) { animation-delay:.08s }
      .wish-card:nth-child(3) { animation-delay:.12s }
      .wish-card:nth-child(4) { animation-delay:.16s }
      .wish-card:nth-child(n+5) { animation-delay:.20s }

      @media(max-width:900px){
         .wishlist-container { padding: 0 1.4rem; }
         .wishlist-grid { grid-template-columns: repeat(2, 1fr); }
      }

      @media(max-width:520px){
         .wishlist-grid { grid-template-columns: 1fr; }
         .wish-summary-inner { flex-direction: column; align-items: stretch; }
         .wish-summary-right { justify-content: space-between; }
      }
   </style>
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