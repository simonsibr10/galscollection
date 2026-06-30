<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

if(!isset($_GET['update']) || empty($_GET['update'])){
   header('location:products.php');
   exit;
}

$update_id = (int)$_GET['update'];

$CATEGORIES = [
   'Totebag', 'Slingbag', 'Dompet', 'Heels',
   'Flat Shoes', 'Top Handle', 'Clutch', 'Ransel', 'Waistbag'
];

$alert_type = '';
$alert_msg  = '';

if(isset($_POST['update'])){

   $pid      = (int)$_POST['pid'];
   $name     = filter_var($_POST['name'],     FILTER_SANITIZE_STRING);
   $price    = filter_var($_POST['price'],    FILTER_SANITIZE_NUMBER_INT);
   $details  = filter_var($_POST['details'],  FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'] ?? '', FILTER_SANITIZE_STRING);

   if(!in_array($category, $CATEGORIES)) $category = $CATEGORIES[0];

   // ─── Update produk info + kategori ───
   $conn->prepare("UPDATE `products` SET name=?, price=?, details=?, category=? WHERE id=?")
        ->execute([$name, $price, $details, $category, $pid]);

   $alert_type = 'success';
   $alert_msg  = 'Produk berhasil diupdate!';

   // ─── Image 01 ───
   if(!empty($_FILES['image_01']['name'])){
      $img = filter_var($_FILES['image_01']['name'], FILTER_SANITIZE_STRING);
      $old = $_POST['old_image_01'];
      if($_FILES['image_01']['size'] > 2000000){
         $alert_msg .= ' Gambar 01 terlalu besar (maks 2MB).'; $alert_type='warning';
      }else{
         move_uploaded_file($_FILES['image_01']['tmp_name'], '../uploaded_img/'.$img);
         $conn->prepare("UPDATE `products` SET image_01=? WHERE id=?")->execute([$img, $pid]);
         if(!empty($old) && file_exists('../uploaded_img/'.$old)) unlink('../uploaded_img/'.$old);
         $alert_msg .= ' Gambar 01 diperbarui.';
      }
   }

   // ─── Image 02 ───
   if(!empty($_FILES['image_02']['name'])){
      $img = filter_var($_FILES['image_02']['name'], FILTER_SANITIZE_STRING);
      $old = $_POST['old_image_02'];
      if($_FILES['image_02']['size'] > 2000000){
         $alert_msg .= ' Gambar 02 terlalu besar (maks 2MB).'; $alert_type='warning';
      }else{
         move_uploaded_file($_FILES['image_02']['tmp_name'], '../uploaded_img/'.$img);
         $conn->prepare("UPDATE `products` SET image_02=? WHERE id=?")->execute([$img, $pid]);
         if(!empty($old) && file_exists('../uploaded_img/'.$old)) unlink('../uploaded_img/'.$old);
         $alert_msg .= ' Gambar 02 diperbarui.';
      }
   }

   // ─── Image 03 ───
   if(!empty($_FILES['image_03']['name'])){
      $img = filter_var($_FILES['image_03']['name'], FILTER_SANITIZE_STRING);
      $old = $_POST['old_image_03'];
      if($_FILES['image_03']['size'] > 2000000){
         $alert_msg .= ' Gambar 03 terlalu besar (maks 2MB).'; $alert_type='warning';
      }else{
         move_uploaded_file($_FILES['image_03']['tmp_name'], '../uploaded_img/'.$img);
         $conn->prepare("UPDATE `products` SET image_03=? WHERE id=?")->execute([$img, $pid]);
         if(!empty($old) && file_exists('../uploaded_img/'.$old)) unlink('../uploaded_img/'.$old);
         $alert_msg .= ' Gambar 03 diperbarui.';
      }
   }

   // ─── Update gambar opsi variasi ───
   // POST berisi option_id => upload file variation_option_image_{option_id}
   if(!empty($_POST['variation_option_ids'])){
      $opt_ids = $_POST['variation_option_ids']; // array of option IDs
      foreach($opt_ids as $opt_id){
         $opt_id = (int)$opt_id;
         $file_key = 'variation_opt_img_'.$opt_id;

         if(!empty($_FILES[$file_key]['name'])){
            $opt_img      = filter_var($_FILES[$file_key]['name'], FILTER_SANITIZE_STRING);
            $opt_img_size = $_FILES[$file_key]['size'];
            $opt_img_tmp  = $_FILES[$file_key]['tmp_name'];

            if($opt_img_size > 2000000){
               $alert_msg .= ' Gambar opsi variasi #'.$opt_id.' terlalu besar!';
               $alert_type = 'warning';
            } else {
               // Get old image
               $qo = $conn->prepare("SELECT option_image FROM `product_variation_options` WHERE id=? LIMIT 1");
               $qo->execute([$opt_id]);
               $old_opt = $qo->fetch(PDO::FETCH_ASSOC);

               move_uploaded_file($opt_img_tmp, '../uploaded_img/'.$opt_img);
               $conn->prepare("UPDATE `product_variation_options` SET option_image=? WHERE id=?")->execute([$opt_img, $opt_id]);

               if($old_opt && !empty($old_opt['option_image']) && file_exists('../uploaded_img/'.$old_opt['option_image'])){
                  unlink('../uploaded_img/'.$old_opt['option_image']);
               }
               $alert_msg .= ' Gambar variasi diperbarui.';
            }
         }
      }
   }

   // Re-fetch after update
   $sel = $conn->prepare("SELECT * FROM `products` WHERE id=?");
   $sel->execute([$update_id]);
   $fetch_products = $sel->fetch(PDO::FETCH_ASSOC);
}

// ─── Fetch product ───
if(empty($fetch_products)){
   $sel = $conn->prepare("SELECT * FROM `products` WHERE id=?");
   $sel->execute([$update_id]);
   $fetch_products = $sel->fetch(PDO::FETCH_ASSOC);
   if(!$fetch_products){ header('location:products.php'); exit; }
}

// ─── Fetch variations + options ───
$sel_var = $conn->prepare("SELECT * FROM `product_variations` WHERE product_id=?");
$sel_var->execute([$update_id]);
$variations = [];
while($v = $sel_var->fetch(PDO::FETCH_ASSOC)){
   $sel_opt = $conn->prepare("SELECT * FROM `product_variation_options` WHERE variation_id=?");
   $sel_opt->execute([$v['id']]);
   $v['options'] = $sel_opt->fetchAll(PDO::FETCH_ASSOC);
   $variations[] = $v;
}
$has_variations = count($variations) > 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Produk</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-update-product">

<?php include '../components/admin_header.php'; ?>

<div class="update-page">

   <!-- Page header -->
   <div class="page-header">
      <h1>Update <span>Produk</span></h1>
      <a href="products.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Produk</a>
   </div>

   <!-- Alert -->
   <?php if($alert_msg): ?>
      <div class="alert-bar <?= $alert_type; ?>">
         <i class="fas <?= $alert_type==='success'?'fa-circle-check':($alert_type==='warning'?'fa-triangle-exclamation':'fa-circle-xmark'); ?>"></i>
         <?= htmlspecialchars($alert_msg); ?>
      </div>
   <?php endif; ?>

   <div class="update-grid">

      <!-- ===== LEFT: IMAGE PANEL ===== -->
      <div class="image-panel">
         <div class="panel-title"><i class="fas fa-images"></i> Preview Gambar</div>

         <div class="img-preview-main">
            <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="" id="main-img">
            <div class="img-preview-label">Gambar Utama</div>
         </div>

         <div class="img-thumbs">
            <?php foreach([$fetch_products['image_01'],$fetch_products['image_02'],$fetch_products['image_03']] as $i=>$img): ?>
            <div class="img-thumb <?= $i===0?'active':''; ?>" onclick="switchImg(this,'../uploaded_img/<?= htmlspecialchars($img); ?>')">
               <img src="../uploaded_img/<?= htmlspecialchars($img); ?>" alt="" id="thumb-img-<?= $i; ?>">
               <div class="img-thumb-badge">0<?= $i+1; ?></div>
            </div>
            <?php endforeach; ?>
         </div>

         <div class="product-quick-info">
            <div class="qi-row"><span class="qi-key"><i class="fas fa-hashtag"></i> ID</span><span class="qi-val">#<?= $fetch_products['id']; ?></span></div>
            <div class="qi-row"><span class="qi-key"><i class="fas fa-tag"></i> Harga</span><span class="qi-val qi-price">Rp <?= number_format($fetch_products['price'],0,',','.'); ?></span></div>
            <?php if(!empty($fetch_products['category'])): ?>
            <div class="qi-row"><span class="qi-key"><i class="fas fa-folder"></i> Kategori</span><span class="qi-val"><?= htmlspecialchars($fetch_products['category']); ?></span></div>
            <?php endif; ?>
            <div class="qi-row u-inline-style-043">
               <span class="qi-key"><i class="fas fa-box"></i> Nama</span>
               <span class="qi-val u-inline-style-044"><?= htmlspecialchars($fetch_products['name']); ?></span>
            </div>
         </div>
      </div>

      <!-- ===== RIGHT: FORM ===== -->
      <div class="form-panel">
         <form action="" method="post" enctype="multipart/form-data" id="update-form">
            <input type="hidden" name="pid"          value="<?= $fetch_products['id']; ?>">
            <input type="hidden" name="old_image_01" value="<?= htmlspecialchars($fetch_products['image_01']); ?>">
            <input type="hidden" name="old_image_02" value="<?= htmlspecialchars($fetch_products['image_02']); ?>">
            <input type="hidden" name="old_image_03" value="<?= htmlspecialchars($fetch_products['image_03']); ?>">

            <!-- ─── INFO PRODUK ─── -->
            <div class="form-section">
               <div class="form-section-title"><i class="fas fa-info-circle u-inline-style-012"></i> Informasi Produk</div>

               <div class="form-field">
                  <label><i class="fas fa-pen"></i> Nama Produk <span class="u-inline-style-042">*</span></label>
                  <div class="input-wrap">
                     <input type="text" name="name" class="form-input" required maxlength="100"
                        value="<?= htmlspecialchars($fetch_products['name']); ?>" placeholder="Nama produk">
                     <i class="fas fa-pen fi"></i>
                  </div>
               </div>

               <div class="form-field">
                  <label><i class="fas fa-tag"></i> Harga Produk (Rp) <span class="u-inline-style-042">*</span></label>
                  <div class="input-wrap">
                     <input type="number" name="price" class="form-input" required min="0" max="9999999999"
                        value="<?= htmlspecialchars($fetch_products['price']); ?>"
                        oninput="updatePricePreview(this.value)">
                     <i class="fas fa-tag fi"></i>
                  </div>
                  <div class="u-inline-style-045" id="price-preview"></div>
               </div>

               <!-- ─── KATEGORI ─── -->
               <div class="form-field">
                  <label><i class="fas fa-folder"></i> Kategori Produk <span class="u-inline-style-042">*</span></label>
                  <div class="cat-select-wrap">
                     <select name="category" class="form-select" required id="cat-select" onchange="updateCatPill(this.value)">
                        <?php foreach($CATEGORIES as $cat): ?>
                           <option value="<?= $cat; ?>" <?= ($fetch_products['category']??'')===$cat?'selected':''; ?>><?= $cat; ?></option>
                        <?php endforeach; ?>
                     </select>
                  </div>
                  <?php
                  $cur_cat = $fetch_products['category'] ?? $CATEGORIES[0];
                  ?>
                  <div class="cat-pill-preview <?= category_theme_class($cur_cat); ?>" id="cat-pill">
                     <i class="fas fa-tag"></i>
                     <span id="cat-pill-text"><?= htmlspecialchars($cur_cat); ?></span>
                  </div>
               </div>

               <div class="form-field">
                  <label><i class="fas fa-align-left"></i> Detail Produk <span class="u-inline-style-042">*</span></label>
                  <div class="input-wrap">
                     <textarea name="details" class="form-input textarea-padded-left" required maxlength="500"
                        id="details-input" oninput="updateCharCount()"><?= htmlspecialchars($fetch_products['details']); ?></textarea>
                  </div>
                  <div class="u-inline-style-046"><span id="char-count">0</span>/500 karakter</div>
               </div>
            </div>

            <!-- ─── UPDATE GAMBAR PRODUK ─── -->
            <div class="form-section">
               <div class="form-section-title"><i class="fas fa-images u-inline-style-012"></i> Update Gambar Produk</div>

               <div class="img-upload-grid">
                  <?php
                  $img_labels = ['Gambar Utama 01','Gambar 02','Gambar 03'];
                  $img_keys   = ['image_01','image_02','image_03'];
                  $drop_ids   = ['drop01','drop02','drop03'];
                  for($i=0;$i<3;$i++):
                     $cur = $fetch_products[$img_keys[$i]];
                  ?>
                  <div class="img-upload-field">
                     <label><i class="fas fa-image"></i> <?= $img_labels[$i]; ?></label>
                     <div class="img-drop-area" id="<?= $drop_ids[$i]; ?>">
                        <?php if(!empty($cur)): ?>
                           <img src="../uploaded_img/<?= htmlspecialchars($cur); ?>" class="drop-current-img" id="cur-img-<?= $i+1; ?>" alt="">
                        <?php endif; ?>
                        <input type="file" name="<?= $img_keys[$i]; ?>" accept="image/jpg,image/jpeg,image/png,image/webp"
                           onchange="previewDropFile(this,'<?= $drop_ids[$i]; ?>',<?= $i+1; ?>)">
                        <div class="drop-area-content">
                           <i class="fas fa-cloud-upload-alt"></i>
                           <span>Klik untuk ganti<br>gambar</span>
                           <div class="fn" id="fn-<?= $i+1; ?>"></div>
                        </div>
                        <div class="preview-overlay">Ganti</div>
                     </div>
                  </div>
                  <?php endfor; ?>
               </div>

               <div class="tips-box">
                  <i class="fas fa-lightbulb"></i>
                  <span>Biarkan kosong jika tidak ingin mengganti gambar. Maksimal 2MB per gambar. Format: JPG, JPEG, PNG, WEBP.</span>
               </div>
            </div>

            <!-- ─── UPDATE GAMBAR VARIASI ─── -->
            <div class="form-section">
               <div class="form-section-title"><i class="fas fa-tags u-inline-style-012"></i> Update Gambar Variasi</div>

               <?php if($has_variations): ?>
                  <?php foreach($variations as $v): ?>
                  <div class="variation-card">
                     <div class="variation-card-title">
                        <i class="fas fa-layer-group"></i>
                        <?= htmlspecialchars($v['variation_name']); ?>
                     </div>

                     <div class="variation-options-grid">
                        <?php foreach($v['options'] as $opt):
                           $opt_id  = $opt['id'];
                           $opt_val = $opt['option_value'];
                           $opt_img = $opt['option_image'] ?? '';
                           $file_key = 'variation_opt_img_'.$opt_id;
                        ?>
                        <div class="variation-opt-item">
                           <!-- Hidden: track which option IDs we're submitting -->
                           <input type="hidden" name="variation_option_ids[]" value="<?= $opt_id; ?>">

                           <div class="opt-name"><?= htmlspecialchars($opt_val); ?></div>

                           <!-- Current image -->
                           <?php if(!empty($opt_img) && file_exists('../uploaded_img/'.$opt_img)): ?>
                              <div class="opt-current-img">
                                 <img src="../uploaded_img/<?= htmlspecialchars($opt_img); ?>" alt="" id="optimg-<?= $opt_id; ?>">
                              </div>
                           <?php else: ?>
                              <div class="opt-no-img" id="optimg-wrap-<?= $opt_id; ?>"><i class="fas fa-image"></i></div>
                           <?php endif; ?>

                           <!-- Mini drop area -->
                           <div class="mini-drop-area" id="mini-drop-<?= $opt_id; ?>">
                              <input type="file" name="<?= $file_key; ?>" accept="image/jpg,image/jpeg,image/png,image/webp"
                                 onchange="previewOptImg(this, <?= $opt_id; ?>)">
                              <div class="mini-drop-content">
                                 <i class="fas fa-cloud-upload-alt"></i>
                                 <span>Ganti foto</span>
                                 <div class="mfn" id="mfn-<?= $opt_id; ?>"></div>
                              </div>
                           </div>
                        </div>
                        <?php endforeach; ?>
                     </div>
                  </div>
                  <?php endforeach; ?>

                  <div class="tips-box">
                     <i class="fas fa-lightbulb"></i>
                     <span>Biarkan kosong jika tidak ingin mengganti foto variasi. Klik area upload untuk memilih foto baru per opsi.</span>
                  </div>

               <?php else: ?>
                  <div class="no-var-note">
                     <i class="fas fa-info-circle"></i>
                     Produk ini tidak memiliki variasi. Tambahkan variasi melalui halaman tambah produk.
                  </div>
               <?php endif; ?>
            </div>

            <!-- Submit row -->
            <div class="form-submit-row">
               <button type="submit" name="update" class="btn-submit">
                  <i class="fas fa-floppy-disk"></i> Simpan Perubahan
               </button>
               <a href="products.php" class="btn-cancel"><i class="fas fa-times"></i> Batal</a>
            </div>

         </form>
      </div>

   </div>
</div>

<script src="../js/admin_script.js"></script>
<script>
   /* Category pill */
   function updateCatPill(val){
      const pill = document.getElementById('cat-pill');
      const txt  = document.getElementById('cat-pill-text');
      txt.textContent = val;
      pill.className = `cat-pill-preview ${categoryClass(val)}`;
   }

   function categoryClass(cat){
      const slug = String(cat || 'default')
         .toLowerCase()
         .replace(/[^a-z0-9]+/g, '-')
         .replace(/^-|-$/g, '') || 'default';
      return `category-theme category-theme-${slug}`;
   }

   /* Switch main preview */
   function switchImg(el, src){
      document.getElementById('main-img').src = src;
      document.querySelectorAll('.img-thumb').forEach(t=>t.classList.remove('active'));
      el.classList.add('active');
   }

   /* Preview product image in drop area */
   function previewDropFile(input, areaId, idx){
      if(!input.files||!input.files[0]) return;
      const file = input.files[0];
      const reader = new FileReader();
      const fnEl = document.getElementById('fn-'+idx);

      reader.onload = e => {
         let ci = document.getElementById('cur-img-'+idx);
         if(!ci){
            ci = document.createElement('img');
            ci.className='drop-current-img';
            ci.id='cur-img-'+idx;
            document.getElementById(areaId).prepend(ci);
         }
         ci.src = e.target.result;
         if(idx===1) document.getElementById('main-img').src = e.target.result;
         const ti = document.querySelectorAll('.img-thumb img')[idx-1];
         if(ti) ti.src = e.target.result;
      };
      reader.readAsDataURL(file);

      if(fnEl){
         const n = file.name.length>18 ? file.name.substring(0,16)+'…' : file.name;
         fnEl.textContent = n;
      }
   }

   /* Preview variation option image */
   function previewOptImg(input, optId){
      if(!input.files||!input.files[0]) return;
      const file = input.files[0];
      const reader = new FileReader();
      const mfn = document.getElementById('mfn-'+optId);

      reader.onload = e => {
         // Update current image display
         let cur = document.getElementById('optimg-'+optId);
         if(cur){
            cur.src = e.target.result;
         } else {
            const wrap = document.getElementById('optimg-wrap-'+optId);
            if(wrap){
               wrap.className = 'opt-current-img';
               wrap.innerHTML = '';
               const img = document.createElement('img');
               img.src = e.target.result;
               img.id = 'optimg-'+optId;
               wrap.appendChild(img);
            }
         }
      };
      reader.readAsDataURL(file);

      if(mfn){
         const n = file.name.length>16 ? file.name.substring(0,14)+'…' : file.name;
         mfn.textContent = n;
      }
   }

   /* Price preview */
   function updatePricePreview(val){
      const el = document.getElementById('price-preview');
      if(!el) return;
      const n = parseInt(val);
      el.textContent = (!isNaN(n)&&n>0) ? '≈ Rp '+n.toLocaleString('id-ID') : '';
   }

   /* Char count */
   function updateCharCount(){
      const ta  = document.getElementById('details-input');
      const cnt = document.getElementById('char-count');
      if(!ta||!cnt) return;
      const len = ta.value.length;
      cnt.textContent = len;
      cnt.classList.toggle('status-danger', len>450);
   }

   /* Auto-dismiss alert */
   const alertBar = document.querySelector('.alert-bar');
   if(alertBar){
      setTimeout(()=>{
         alertBar.classList.add('is-fading');
         setTimeout(()=>alertBar.remove(),450);
      },5000);
   }

   /* Init */
   updatePricePreview(document.querySelector('[name="price"]')?.value||0);
   updateCharCount();
</script>
</body>
</html>
