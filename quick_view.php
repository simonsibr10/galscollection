<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Detail Produk — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body {
         background: #fafaf8;
         font-family: 'Segoe UI', sans-serif;
      }

      /* ===== PAGE WRAPPER ===== */
      .qv-page {
         max-width: 1200px;
         margin: 0 auto;
         padding: 3rem 2.4rem 6rem;
         animation: fadeUp .5s ease both;
      }

      @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

      /* Breadcrumb */
      .qv-breadcrumb {
         font-size: 1.4rem;
         color: #94a3b8;
         margin-bottom: 2.8rem;
         display: flex;
         align-items: center;
         gap: .6rem;
         flex-wrap: wrap;
      }

      .qv-breadcrumb a {
         color: #94a3b8;
         text-decoration: none;
         transition: color .15s;
      }

      .qv-breadcrumb a:hover { color: #1a2a6c; }
      .qv-breadcrumb i { font-size: 1.1rem; color: #cbd5e1; }

      /* ===== PRODUCT LAYOUT ===== */
      .qv-product-layout {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 5rem;
         align-items: start;
      }

      /* ===== LEFT: GALLERY ===== */
      .qv-gallery { display: flex; flex-direction: column; gap: 1.4rem; position: sticky; top: 10rem; }

      /* Main image */
      .qv-main-img-wrap {
         width: 100%;
         aspect-ratio: 1/1;
         border-radius: 1.6rem;
         overflow: hidden;
         background: #f1f0ec;
         position: relative;
         cursor: zoom-in;
      }

      .qv-main-img {
         width: 100%; height: 100%;
         object-fit: cover;
         display: block;
         transition: transform .4s cubic-bezier(.25,.46,.45,.94);
      }

      .qv-main-img-wrap:hover .qv-main-img { transform: scale(1.06); }

      /* Category badge on main image */
      .qv-cat-badge {
         position: absolute;
         top: 1.4rem; left: 1.4rem;
         padding: .4rem 1.1rem;
         border-radius: 2rem;
         font-size: 1.2rem;
         font-weight: 700;
         color: #fff;
         z-index: 2;
      }

      /* Thumbnails */
      .qv-thumbs {
         display: flex;
         gap: 1rem;
      }

      .qv-thumb {
         width: calc(33.333% - .7rem);
         aspect-ratio: 1/1;
         border-radius: .9rem;
         overflow: hidden;
         border: 2px solid transparent;
         cursor: pointer;
         transition: border-color .2s, transform .2s;
         background: #f1f0ec;
         flex-shrink: 0;
      }

      .qv-thumb.active { border-color: #1a2a6c; }
      .qv-thumb:hover  { transform: translateY(-2px); border-color: #c7d2fe; }

      .qv-thumb img {
         width: 100%; height: 100%;
         object-fit: cover; display: block;
      }

      /* ===== RIGHT: PRODUCT INFO ===== */
      .qv-info { display: flex; flex-direction: column; gap: 2rem; }

      /* Heading */
      .qv-product-name {
         font-size: 2.8rem;
         font-weight: 800;
         color: #0f172a;
         line-height: 1.3;
         letter-spacing: -.3px;
      }

      /* Price row */
      .qv-price-row {
         display: flex;
         align-items: center;
         gap: 1.6rem;
         flex-wrap: wrap;
      }

      .qv-price {
         font-size: 3.2rem;
         font-weight: 800;
         color: #e11d48;
         letter-spacing: -.5px;
      }

      /* Stock badge */
      .qv-stock-badge {
         display: inline-flex;
         align-items: center;
         gap: .5rem;
         padding: .5rem 1.2rem;
         border-radius: 2rem;
         font-size: 1.25rem;
         font-weight: 700;
         background: #ecfdf5;
         color: #059669;
         border: 1px solid #a7f3d0;
      }

      .qv-stock-dot {
         width: .7rem; height: .7rem;
         border-radius: 50%;
         background: #059669;
         animation: pulse 2s infinite;
      }

      @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

      /* Divider */
      .qv-divider { height: 1px; background: #f1f5f9; }

      /* Description */
      .qv-desc-title {
         font-size: 1.3rem;
         font-weight: 700;
         color: #64748b;
         text-transform: uppercase;
         letter-spacing: .08em;
         margin-bottom: .8rem;
      }

      .qv-desc-text {
         font-size: 1.5rem;
         color: #475569;
         line-height: 1.8;
         white-space: pre-wrap;
      }

      /* ===== VARIATION ===== */
      .variation-group {
         padding: 1.6rem;
         background: #f8fafc;
         border-radius: 1.2rem;
         border: 1px solid #f1f5f9;
      }

      .variation-title {
         font-size: 1.5rem;
         font-weight: 700;
         color: #0f172a;
         margin-bottom: 1.2rem;
         display: flex;
         align-items: center;
         gap: .6rem;
      }

      .variation-title i { color: #4f6ef7; font-size: 1.3rem; }

      /* Variation options grid */
      .variation-options {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
         gap: 1rem;
      }

      .variation-option { position: relative; }

      .variation-option input[type="radio"] { display: none; }

      .variation-option label {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: .7rem;
         padding: 1rem;
         border: 2px solid #e2e8f0;
         border-radius: 1rem;
         background: #fff;
         cursor: pointer;
         font-size: 1.35rem;
         font-weight: 500;
         color: #334155;
         text-align: center;
         transition: border-color .2s, background .2s, transform .15s, box-shadow .15s;
         min-height: 9rem;
         justify-content: center;
      }

      .variation-option label:hover {
         border-color: #c7d2fe;
         background: #f8fafc;
         transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(0,0,0,.06);
      }

      .variation-option input:checked + label {
         border-color: #1a2a6c;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         box-shadow: 0 6px 18px rgba(79,110,247,.3);
         transform: translateY(-2px);
      }

      .variation-option img {
         width: 5.6rem; height: 5.6rem;
         object-fit: cover;
         border-radius: .7rem;
         border: 2px solid rgba(255,255,255,.3);
      }

      .variation-option span { font-size: 1.3rem; line-height: 1.4; word-break: break-word; }

      .variation-note {
         font-size: 1.25rem;
         color: #94a3b8;
         margin-top: 1rem;
         display: flex;
         align-items: center;
         gap: .4rem;
      }

      /* Variation error */
      .variation-error {
         display: none;
         margin-top: .8rem;
         padding: 1.1rem 1.4rem;
         border-radius: .8rem;
         background: #fff1f2;
         border-left: 4px solid #e11d48;
         color: #be123c;
         font-size: 1.35rem;
         font-weight: 600;
         animation: shake .35s ease both;
      }

      @keyframes shake {
         0%,100%{transform:translateX(0)}
         20%{transform:translateX(-8px)}
         40%{transform:translateX(8px)}
         60%{transform:translateX(-4px)}
         80%{transform:translateX(4px)}
      }

      /* ===== QTY STEPPER ===== */
      .qv-qty-section { display: flex; flex-direction: column; gap: .8rem; }

      .qv-qty-label {
         font-size: 1.3rem;
         font-weight: 700;
         color: #64748b;
         text-transform: uppercase;
         letter-spacing: .06em;
      }

      .qv-qty-wrap {
         display: flex;
         align-items: center;
         gap: 0;
         border: 1.5px solid #e2e8f0;
         border-radius: .8rem;
         overflow: hidden;
         width: fit-content;
      }

      .qty-stepper-btn {
         width: 4rem; height: 4.4rem;
         background: #f8fafc;
         border: none;
         font-size: 1.8rem;
         color: #475569;
         cursor: pointer;
         display: flex; align-items: center; justify-content: center;
         transition: background .15s, color .15s;
         line-height: 1;
         font-family: inherit;
      }

      .qty-stepper-btn:hover { background: #eff2ff; color: #1a2a6c; }

      .qty-stepper-input {
         width: 5rem; height: 4.4rem;
         text-align: center;
         border: none;
         border-left: 1.5px solid #e2e8f0;
         border-right: 1.5px solid #e2e8f0;
         font-size: 1.6rem;
         color: #0f172a;
         font-weight: 700;
         font-family: inherit;
         outline: none;
         -moz-appearance: textfield;
      }

      .qty-stepper-input::-webkit-inner-spin-button,
      .qty-stepper-input::-webkit-outer-spin-button { -webkit-appearance: none; }

      /* ===== ACTION BUTTONS ===== */
      .qv-actions {
         display: flex;
         flex-direction: column;
         gap: 1rem;
      }

      .btn-add-cart {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: .8rem;
         width: 100%;
         padding: 1.6rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         border: none;
         border-radius: 1rem;
         font-size: 1.6rem;
         font-weight: 700;
         cursor: pointer;
         font-family: inherit;
         transition: transform .15s, box-shadow .2s;
         letter-spacing: .02em;
      }

      .btn-add-cart:hover  { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }
      .btn-add-cart:active { transform: scale(.97); }

      .btn-add-wish {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: .7rem;
         width: 100%;
         padding: 1.4rem;
         background: #fff;
         color: #e11d48;
         border: 2px solid #fecdd3;
         border-radius: 1rem;
         font-size: 1.5rem;
         font-weight: 700;
         cursor: pointer;
         font-family: inherit;
         transition: background .15s, border-color .15s, transform .15s;
      }

      .btn-add-wish:hover { background: #fff0f3; border-color: #e11d48; transform: translateY(-1px); }

      /* ===== TRUST STRIPS ===== */
      .qv-trust-strip {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 1.2rem;
         padding: 1.6rem;
         background: #f8fafc;
         border-radius: 1rem;
         border: 1px solid #f1f5f9;
      }

      .trust-item {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: .5rem;
         text-align: center;
      }

      .trust-item i { font-size: 2rem; color: #4f6ef7; }
      .trust-item strong { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
      .trust-item span   { font-size: 1.15rem; color: #64748b; }

      /* ===== NOTIFICATIONS ===== */
      .qv-notif {
         padding: 1.2rem 1.6rem;
         border-radius: .8rem;
         font-size: 1.35rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         animation: fadeSlideDown .35s ease both;
         margin-bottom: 1.6rem;
      }

      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-12px)} to{opacity:1;transform:translateY(0)} }

      .qv-notif.success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }
      .qv-notif.info    { background: #eff2ff; border-left: 4px solid #4f6ef7; color: #1e40af; }
      .qv-notif.warn    { background: #fffbeb; border-left: 4px solid #f59e0b; color: #78350f; }

      /* ===== EMPTY / ERROR STATE ===== */
      .qv-empty {
         text-align: center;
         padding: 6rem 2rem;
         color: #94a3b8;
      }

      .qv-empty i { font-size: 5rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .qv-empty p { font-size: 1.6rem; margin-bottom: 2rem; }

      .btn-back-shop {
         display: inline-flex;
         align-items: center;
         gap: .7rem;
         padding: 1.2rem 2.4rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border-radius: .8rem;
         font-size: 1.4rem;
         font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
      }

      .btn-back-shop:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79,110,247,.35); }

      /* ===== RESPONSIVE ===== */
      @media(max-width:900px){
         .qv-product-layout { grid-template-columns: 1fr; gap: 3rem; }
         .qv-gallery { position: static; }
         .qv-product-name { font-size: 2.2rem; }
      }

      @media(max-width:600px){
         .qv-page { padding: 2rem 1.4rem 4rem; }
         .qv-price { font-size: 2.6rem; }
         .variation-options { grid-template-columns: 1fr 1fr; }
         .qv-trust-strip { grid-template-columns: 1fr; }
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="qv-page">

   <?php
   $CAT_COLORS = [
      'Totebag'=>'#4f6ef7','Slingbag'=>'#059669','Dompet'=>'#f59e0b',
      'Heels'=>'#e11d48','Flat Shoes'=>'#0891b2','Top Handle'=>'#7c3aed',
      'Clutch'=>'#ea580c','Ransel'=>'#65a30d','Waistbag'=>'#db2777',
   ];

   $pid = $_GET['pid'] ?? '';

   if($pid != ''){
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_products->execute([$pid]);

      if($select_products->rowCount() > 0){
         $fp = $select_products->fetch(PDO::FETCH_ASSOC);

         // Fetch variations
         $select_variations = $conn->prepare("SELECT * FROM `product_variations` WHERE product_id = ?");
         $select_variations->execute([$fp['id']]);
         $variations = [];
         while($variation = $select_variations->fetch(PDO::FETCH_ASSOC)){
            $select_options = $conn->prepare("SELECT * FROM `product_variation_options` WHERE variation_id = ?");
            $select_options->execute([$variation['id']]);
            $variation['options'] = $select_options->fetchAll(PDO::FETCH_ASSOC);
            $variations[] = $variation;
         }
         $has_variations = count($variations) > 0;

         $cat    = $fp['category'] ?? '';
         $catCol = $CAT_COLORS[$cat] ?? '#1a2a6c';
   ?>

   <!-- Breadcrumb -->
   <nav class="qv-breadcrumb">
      <a href="home.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="shop.php">Toko</a>
      <?php if($cat): ?>
         <i class="fas fa-chevron-right"></i>
         <a href="shop.php?cat=<?= urlencode($cat); ?>"><?= htmlspecialchars($cat); ?></a>
      <?php endif; ?>
      <i class="fas fa-chevron-right"></i>
      <span style="color:#475569;font-weight:600;"><?= htmlspecialchars($fp['name']); ?></span>
   </nav>

   <!-- Product Form -->
   <form action="" method="post" id="productForm">
      <input type="hidden" name="pid"   value="<?= htmlspecialchars($fp['id']); ?>">
      <input type="hidden" name="name"  value="<?= htmlspecialchars($fp['name']); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fp['price']); ?>">
      <input type="hidden" name="image" id="selected_product_image" value="<?= htmlspecialchars($fp['image_01']); ?>">

      <div class="qv-product-layout">

         <!-- ===== LEFT: GALLERY ===== -->
         <div class="qv-gallery">
            <!-- Main image -->
            <div class="qv-main-img-wrap">
               <?php if($cat): ?>
                  <div class="qv-cat-badge" style="background:<?= $catCol; ?>;"><?= htmlspecialchars($cat); ?></div>
               <?php endif; ?>
               <img
                  id="main-product-image"
                  src="uploaded_img/<?= htmlspecialchars($fp['image_01']); ?>"
                  alt="<?= htmlspecialchars($fp['name']); ?>"
                  class="qv-main-img"
               >
            </div>

            <!-- Thumbnails -->
            <div class="qv-thumbs">
               <?php
               $images = [$fp['image_01'], $fp['image_02'], $fp['image_03']];
               foreach($images as $i => $img):
               ?>
               <div class="qv-thumb <?= $i===0?'active':''; ?>"
                  onclick="changeMainImage(this, 'uploaded_img/<?= htmlspecialchars($img); ?>', '<?= htmlspecialchars($img); ?>')">
                  <img src="uploaded_img/<?= htmlspecialchars($img); ?>" alt="">
               </div>
               <?php endforeach; ?>
            </div>
         </div>

         <!-- ===== RIGHT: INFO ===== -->
         <div class="qv-info">

            <!-- Name -->
            <div>
               <div class="qv-product-name"><?= htmlspecialchars($fp['name']); ?></div>
            </div>

            <!-- Price + Stock -->
            <div class="qv-price-row">
               <div class="qv-price">Rp <?= number_format($fp['price'], 0, ',', '.'); ?></div>
               <div class="qv-stock-badge">
                  <div class="qv-stock-dot"></div>
                  Tersedia
               </div>
            </div>

            <div class="qv-divider"></div>

            <!-- Description -->
            <div>
               <div class="qv-desc-title">Deskripsi Produk</div>
               <div class="qv-desc-text"><?= htmlspecialchars($fp['details']); ?></div>
            </div>

            <div class="qv-divider"></div>

            <!-- Variations -->
            <?php if($has_variations): ?>
               <div id="variationSection">
                  <?php foreach($variations as $vIndex => $variation):
                     $vNo = $vIndex + 1;
                  ?>
                  <div class="variation-group" style="margin-bottom:1.4rem;">
                     <div class="variation-title">
                        <i class="fas fa-tags"></i>
                        Pilih <?= htmlspecialchars($variation['variation_name']); ?>
                     </div>

                     <input type="hidden" name="variation_<?= $vNo; ?>_name" value="<?= htmlspecialchars($variation['variation_name']); ?>">

                     <div class="variation-options">
                        <?php foreach($variation['options'] as $oIdx => $option):
                           $radioName = 'variation_'.$vNo.'_value';
                           $radioId   = 'var_'.$vNo.'_opt_'.($oIdx+1);
                           $optImg    = $option['option_image'] ?? '';
                        ?>
                        <div class="variation-option">
                           <input
                              type="radio"
                              name="<?= $radioName; ?>"
                              id="<?= $radioId; ?>"
                              value="<?= htmlspecialchars($option['option_value']); ?>"
                              data-option-image="<?= htmlspecialchars($optImg); ?>"
                              onchange="handleVariationChange(this); hideVariationError();"
                           >
                           <label for="<?= $radioId; ?>">
                              <?php if(!empty($optImg) && file_exists('uploaded_img/'.$optImg)): ?>
                                 <img src="uploaded_img/<?= htmlspecialchars($optImg); ?>" alt="">
                              <?php endif; ?>
                              <span><?= htmlspecialchars($option['option_value']); ?></span>
                           </label>
                        </div>
                        <?php endforeach; ?>
                     </div>

                     <div class="variation-note">
                        <i class="fas fa-circle-info" style="color:#94a3b8;font-size:1.2rem"></i>
                        Silakan pilih <?= htmlspecialchars($variation['variation_name']); ?>
                     </div>
                  </div>
                  <?php endforeach; ?>

                  <div id="variationError" class="variation-error">
                     <i class="fas fa-circle-xmark"></i>
                     Silakan pilih semua variasi produk sebelum menambahkan ke keranjang.
                  </div>
               </div>
            <?php endif; ?>

            <!-- Qty stepper -->
            <div class="qv-qty-section">
               <div class="qv-qty-label">Jumlah</div>
               <div class="qv-qty-wrap">
                  <button type="button" class="qty-stepper-btn"
                     onclick="changeQty(-1)">−</button>
                  <input
                     type="number"
                     name="qty"
                     id="qtyInput"
                     class="qty-stepper-input"
                     min="1" max="99" value="1"
                  >
                  <button type="button" class="qty-stepper-btn"
                     onclick="changeQty(1)">+</button>
               </div>
            </div>

            <!-- Action buttons -->
            <div class="qv-actions">
               <button
                  type="submit"
                  name="add_to_cart"
                  class="btn-add-cart"
                  onclick="return validateCartVariations(event);"
               >
                  <i class="fas fa-cart-shopping"></i> Tambah ke Keranjang
               </button>
               <button type="submit" name="add_to_wishlist" class="btn-add-wish">
                  <i class="fas fa-heart"></i> Tambah ke Daftar Suka
               </button>
            </div>

            <!-- Trust strip -->
            <div class="qv-trust-strip">
               <div class="trust-item">
                  <i class="fas fa-truck-fast"></i>
                  <strong>Pengiriman Cepat</strong>
                  <span>Ke seluruh Indonesia</span>
               </div>
               <div class="trust-item">
                  <i class="fas fa-shield-halved"></i>
                  <strong>Produk Terjamin</strong>
                  <span>Kualitas terseleksi</span>
               </div>
               <div class="trust-item">
                  <i class="fas fa-headset"></i>
                  <strong>Support 24/7</strong>
                  <span>Siap membantu kamu</span>
               </div>
            </div>

         </div><!-- /qv-info -->
      </div><!-- /qv-product-layout -->
   </form>

   <?php
      } else {
   ?>
      <div class="qv-empty">
         <i class="fas fa-box-open"></i>
         <p>Produk tidak ditemukan.</p>
         <a href="shop.php" class="btn-back-shop"><i class="fas fa-store"></i> Kembali ke Toko</a>
      </div>
   <?php
      }
   } else {
   ?>
      <div class="qv-empty">
         <i class="fas fa-magnifying-glass"></i>
         <p>Tidak ada produk yang dipilih.</p>
         <a href="shop.php" class="btn-back-shop"><i class="fas fa-store"></i> Lihat Semua Produk</a>
      </div>
   <?php } ?>

</div>

<?php include 'components/footer.php'; ?>

<script>
   /* ─── Switch main image ─── */
   function changeMainImage(thumbEl, src, filename){
      document.getElementById('main-product-image').src = src;
      document.getElementById('selected_product_image').value = filename;
      document.querySelectorAll('.qv-thumb').forEach(t => t.classList.remove('active'));
      thumbEl.classList.add('active');
   }

   /* ─── Variation change: update main image if has image ─── */
   function handleVariationChange(radio){
      const optImg = radio.getAttribute('data-option-image');
      if(optImg && optImg.trim() !== ''){
         const src = 'uploaded_img/' + optImg;
         document.getElementById('main-product-image').src = src;
         document.getElementById('selected_product_image').value = optImg;
         // Update active thumb if matches
         document.querySelectorAll('.qv-thumb').forEach(t => {
            const ti = t.querySelector('img');
            if(ti && ti.src.includes(optImg)) t.classList.add('active');
            else t.classList.remove('active');
         });
      }
   }

   /* ─── Qty stepper ─── */
   function changeQty(delta){
      const inp = document.getElementById('qtyInput');
      let val = parseInt(inp.value) + delta;
      if(val < 1)  val = 1;
      if(val > 99) val = 99;
      inp.value = val;
   }

   /* ─── Variation validation ─── */
   function showVariationError(){
      const el = document.getElementById('variationError');
      if(el){
         el.style.display = 'flex';
         el.style.alignItems = 'center';
         el.style.gap = '.7rem';
      }
   }

   function hideVariationError(){
      const el = document.getElementById('variationError');
      if(el) el.style.display = 'none';
   }

   function validateCartVariations(event){
      const section = document.getElementById('variationSection');
      if(!section) return true;

      const groups = section.querySelectorAll('.variation-group');
      let allOk = true;

      groups.forEach(g => {
         if(!g.querySelector('input[type="radio"]:checked')) allOk = false;
      });

      if(!allOk){
         if(event) event.preventDefault();
         showVariationError();
         section.scrollIntoView({ behavior:'smooth', block:'center' });
         return false;
      }
      return true;
   }

</script>

<script src="js/script.js"></script>
</body>
</html>