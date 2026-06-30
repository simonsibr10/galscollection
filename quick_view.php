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
</head>
<body class="php-quick-view">

<?php include 'components/user_header.php'; ?>

<div class="qv-page">

   <?php
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
   ?>

   <!-- Breadcrumb -->
   <nav class="qv-breadcrumb">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="shop.php">Toko</a>
      <?php if($cat): ?>
         <i class="fas fa-chevron-right"></i>
         <a href="shop.php?cat=<?= urlencode($cat); ?>"><?= htmlspecialchars($cat); ?></a>
      <?php endif; ?>
      <i class="fas fa-chevron-right"></i>
      <span class="u-inline-style-006"><?= htmlspecialchars($fp['name']); ?></span>
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
                  <div class="qv-cat-badge <?= category_theme_class($cat); ?>"><?= htmlspecialchars($cat); ?></div>
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
                  <div class="variation-group u-inline-style-024">
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
                        <i class="fas fa-circle-info u-inline-style-025"></i>
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
