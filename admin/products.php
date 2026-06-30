<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

$CATEGORIES = [
   'Totebag', 'Slingbag', 'Dompet', 'Heels',
   'Flat Shoes', 'Top Handle', 'Clutch', 'Ransel', 'Waistbag'
];

if(isset($_POST['add_product'])){

   $name     = filter_var($_POST['name'],    FILTER_SANITIZE_STRING);
   $price    = filter_var($_POST['price'],   FILTER_SANITIZE_NUMBER_INT);
   $details  = filter_var($_POST['details'], FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'] ?? $CATEGORIES[0], FILTER_SANITIZE_STRING);

   if(!in_array($category, $CATEGORIES)) $category = $CATEGORIES[0];

   $image_01 = filter_var($_FILES['image_01']['name'], FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_02 = filter_var($_FILES['image_02']['name'], FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_03 = filter_var($_FILES['image_03']['name'], FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];

   $check = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $check->execute([$name]);

   if($check->rowCount() > 0){
      $message[] = 'Nama produk sudah ada!';
   } else {
      if($image_size_01 > 2000000 || $image_size_02 > 2000000 || $image_size_03 > 2000000){
         $message[] = 'Ukuran gambar terlalu besar! Maksimal 2MB.';
      } else {
         $ins = $conn->prepare("INSERT INTO `products`(name,details,price,image_01,image_02,image_03,category) VALUES(?,?,?,?,?,?,?)");
         $ins->execute([$name,$details,$price,$image_01,$image_02,$image_03,$category]);

         if($ins){
            $product_id = $conn->lastInsertId();
            move_uploaded_file($_FILES['image_01']['tmp_name'], '../uploaded_img/'.$image_01);
            move_uploaded_file($_FILES['image_02']['tmp_name'], '../uploaded_img/'.$image_02);
            move_uploaded_file($_FILES['image_03']['tmp_name'], '../uploaded_img/'.$image_03);

            if(isset($_POST['has_variation'])){
               $v1name = filter_var(trim($_POST['variation_1_name']??''), FILTER_SANITIZE_STRING);
               if($v1name != ''){
                  $ins_v1 = $conn->prepare("INSERT INTO `product_variations`(product_id,variation_name) VALUES(?,?)");
                  $ins_v1->execute([$product_id,$v1name]);
                  $v1id = $conn->lastInsertId();
                  for($i=1;$i<=5;$i++){
                     $ov = filter_var(trim($_POST["variation_1_option_$i"]??''), FILTER_SANITIZE_STRING);
                     $on = $_FILES["variation_1_option_image_$i"]['name']??'';
                     $os = $_FILES["variation_1_option_image_$i"]['size']??0;
                     $ot = $_FILES["variation_1_option_image_$i"]['tmp_name']??'';
                     $si = null;
                     if($ov!=''){
                        if(!empty($on)){
                           $on = filter_var($on,FILTER_SANITIZE_STRING);
                           if($os>2000000){ $message[]='Gambar variasi 1 opsi '.$i.' terlalu besar!'; }
                           else { $si=$on; move_uploaded_file($ot,'../uploaded_img/'.$si); }
                        }
                        $conn->prepare("INSERT INTO `product_variation_options`(variation_id,option_value,option_image) VALUES(?,?,?)")->execute([$v1id,$ov,$si]);
                     }
                  }
               }
               if(isset($_POST['has_variation_2'])){
                  $v2name = filter_var(trim($_POST['variation_2_name']??''), FILTER_SANITIZE_STRING);
                  if($v2name!=''){
                     $ins_v2=$conn->prepare("INSERT INTO `product_variations`(product_id,variation_name) VALUES(?,?)");
                     $ins_v2->execute([$product_id,$v2name]);
                     $v2id=$conn->lastInsertId();
                     for($i=1;$i<=5;$i++){
                        $ov=filter_var(trim($_POST["variation_2_option_$i"]??''),FILTER_SANITIZE_STRING);
                        $on=$_FILES["variation_2_option_image_$i"]['name']??'';
                        $os=$_FILES["variation_2_option_image_$i"]['size']??0;
                        $ot=$_FILES["variation_2_option_image_$i"]['tmp_name']??'';
                        $si=null;
                        if($ov!=''){
                           if(!empty($on)){
                              $on=filter_var($on,FILTER_SANITIZE_STRING);
                              if($os>2000000){$message[]='Gambar variasi 2 opsi '.$i.' terlalu besar!';}
                              else{$si=$on;move_uploaded_file($ot,'../uploaded_img/'.$si);}
                           }
                           $conn->prepare("INSERT INTO `product_variation_options`(variation_id,option_value,option_image) VALUES(?,?,?)")->execute([$v2id,$ov,$si]);
                        }
                     }
                  }
               }
            }
            $message[] = 'Produk baru berhasil ditambahkan!';
         } else {
            $message[] = 'Gagal menambahkan produk!';
         }
      }
   }
}

if(isset($_GET['delete'])){
   $del_id = $_GET['delete'];
   $qd = $conn->prepare("SELECT * FROM `products` WHERE id=?");
   $qd->execute([$del_id]);
   $fd = $qd->fetch(PDO::FETCH_ASSOC);
   if($fd){
      foreach(['image_01','image_02','image_03'] as $c){
         if(!empty($fd[$c])&&file_exists('../uploaded_img/'.$fd[$c])) unlink('../uploaded_img/'.$fd[$c]);
      }
   }
   $sv=$conn->prepare("SELECT id FROM `product_variations` WHERE product_id=?"); $sv->execute([$del_id]);
   while($fv=$sv->fetch(PDO::FETCH_ASSOC)){
      $so=$conn->prepare("SELECT * FROM `product_variation_options` WHERE variation_id=?"); $so->execute([$fv['id']]);
      while($fo=$so->fetch(PDO::FETCH_ASSOC)){
         if(!empty($fo['option_image'])&&file_exists('../uploaded_img/'.$fo['option_image'])) unlink('../uploaded_img/'.$fo['option_image']);
      }
      $conn->prepare("DELETE FROM `product_variation_options` WHERE variation_id=?")->execute([$fv['id']]);
   }
   $conn->prepare("DELETE FROM `product_variations` WHERE product_id=?")->execute([$del_id]);
   $conn->prepare("DELETE FROM `products` WHERE id=?")->execute([$del_id]);
   $conn->prepare("DELETE FROM `cart` WHERE pid=?")->execute([$del_id]);
   $conn->prepare("DELETE FROM `wishlist` WHERE pid=?")->execute([$del_id]);
   header('location:products.php'); exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kelola Produk</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-products">

<?php include '../components/admin_header.php'; ?>

<div class="products-page">

   <div class="products-page-header">
      <h1>Kelola <span>Produk</span></h1>
   </div>

   <?php if(isset($message)){ foreach($message as $msg){
      $is_err = str_contains(strtolower($msg),'gagal')||str_contains(strtolower($msg),'besar')||str_contains(strtolower($msg),'sudah');
   ?>
      <div class="notif-bar <?= $is_err?'error':''; ?>">
         <i class="fas <?= $is_err?'fa-circle-xmark':'fa-circle-check'; ?>"></i>
         <?= htmlspecialchars($msg); ?>
      </div>
   <?php }} ?>

   <!-- ===== FORM TAMBAH PRODUK ===== -->
   <div class="form-panel">
      <div class="form-panel-title"><i class="fas fa-plus-circle"></i> Tambahkan Produk Baru</div>
      <form action="" method="post" enctype="multipart/form-data">
         <div class="form-grid">

            <div class="form-field">
               <label>Nama Produk <span class="req">*</span></label>
               <input type="text" name="name" class="form-input" required maxlength="100" placeholder="Masukkan nama produk...">
            </div>

            <div class="form-field">
               <label>Harga Produk <span class="req">*</span></label>
               <input type="number" name="price" class="form-input" required min="0" max="9999999999" placeholder="Contoh: 150000">
            </div>

            <!-- ─── KATEGORI ─── -->
            <div class="form-field full-width">
               <label><i class="fas fa-tag u-inline-style-025"></i> Kategori Produk <span class="req">*</span></label>
               <div class="cat-select-wrap">
                  <select name="category" class="form-select" required id="cat-select" onchange="updateCatPill(this.value)">
                     <?php foreach($CATEGORIES as $cat): ?>
                        <option value="<?= $cat; ?>"><?= $cat; ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="cat-pill-preview <?= category_theme_class($CATEGORIES[0]); ?>" id="cat-pill">
                  <i class="fas fa-tag"></i>
                  <span id="cat-pill-text"><?= $CATEGORIES[0]; ?></span>
               </div>
            </div>

            <div class="form-field">
               <label>Gambar Utama (01) <span class="req">*</span></label>
               <div class="file-upload-area" id="area01">
                  <input type="file" name="image_01" accept="image/jpg,image/jpeg,image/png,image/webp" required onchange="showFileName(this,'area01')">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <span>Klik untuk upload gambar</span>
                  <div class="file-name" id="fn01"></div>
               </div>
            </div>

            <div class="form-field">
               <label>Gambar 02 <span class="req">*</span></label>
               <div class="file-upload-area" id="area02">
                  <input type="file" name="image_02" accept="image/jpg,image/jpeg,image/png,image/webp" required onchange="showFileName(this,'area02')">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <span>Klik untuk upload gambar</span>
                  <div class="file-name" id="fn02"></div>
               </div>
            </div>

            <div class="form-field">
               <label>Gambar 03 <span class="req">*</span></label>
               <div class="file-upload-area" id="area03">
                  <input type="file" name="image_03" accept="image/jpg,image/jpeg,image/png,image/webp" required onchange="showFileName(this,'area03')">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <span>Klik untuk upload gambar</span>
                  <div class="file-name" id="fn03"></div>
               </div>
            </div>

            <div class="form-field">
               <label>Detail Produk <span class="req">*</span></label>
               <textarea name="details" class="form-input" required maxlength="500" placeholder="Masukkan deskripsi produk..."></textarea>
            </div>

            <!-- Variation -->
            <div class="form-field full-width">
               <div class="variation-toggle-row" onclick="document.getElementById('has_variation').click()">
                  <input type="checkbox" name="has_variation" id="has_variation" onchange="toggleVariationBox()" onclick="event.stopPropagation()">
                  <label for="has_variation"><i class="fas fa-tags"></i> &nbsp;Aktifkan Variasi Produk (opsional)</label>
               </div>
               <div id="variation_box" class="variation-panel">
                  <div class="variation-group-card">
                     <h3><i class="fas fa-layer-group"></i> Variasi 1</h3>
                     <div class="form-field u-inline-style-035">
                        <label>Nama Variasi 1</label>
                        <input type="text" name="variation_1_name" class="form-input" placeholder="Contoh: Warna">
                     </div>
                     <?php for($i=1;$i<=5;$i++): ?>
                     <div class="variation-option-row">
                        <div class="u-inline-style-036">Opsi <?= $i; ?></div>
                        <div class="variation-option-row-grid">
                           <div class="form-field">
                              <label>Nilai Opsi</label>
                              <input type="text" name="variation_1_option_<?= $i; ?>" class="form-input" placeholder="Contoh: Hitam">
                           </div>
                           <div class="form-field">
                              <label>Foto Opsi (opsional)</label>
                              <div class="file-upload-area">
                                 <input type="file" name="variation_1_option_image_<?= $i; ?>" accept="image/jpg,image/jpeg,image/png,image/webp" onchange="showFileName(this,null,this.parentElement.querySelector('.file-name'))">
                                 <i class="fas fa-image"></i><span>Upload foto</span>
                                 <div class="file-name"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php endfor; ?>
                     <div class="u-inline-style-037">
                        <div class="variation-toggle-row" onclick="document.getElementById('has_variation_2').click()">
                           <input type="checkbox" name="has_variation_2" id="has_variation_2" onchange="toggleVariation2Box()" onclick="event.stopPropagation()">
                           <label for="has_variation_2"><i class="fas fa-plus"></i> &nbsp;Tambah Variasi 2</label>
                        </div>
                     </div>
                  </div>
                  <div id="variation_2_box" class="variation-group-card u-inline-style-038">
                     <h3><i class="fas fa-layer-group"></i> Variasi 2</h3>
                     <div class="form-field u-inline-style-035">
                        <label>Nama Variasi 2</label>
                        <input type="text" name="variation_2_name" class="form-input" placeholder="Contoh: Ukuran">
                     </div>
                     <?php for($i=1;$i<=5;$i++): ?>
                     <div class="variation-option-row">
                        <div class="u-inline-style-036">Opsi <?= $i; ?></div>
                        <div class="variation-option-row-grid">
                           <div class="form-field">
                              <label>Nilai Opsi</label>
                              <input type="text" name="variation_2_option_<?= $i; ?>" class="form-input" placeholder="Contoh: S, M, L">
                           </div>
                           <div class="form-field">
                              <label>Foto Opsi (opsional)</label>
                              <div class="file-upload-area">
                                 <input type="file" name="variation_2_option_image_<?= $i; ?>" accept="image/jpg,image/jpeg,image/png,image/webp" onchange="showFileName(this,null,this.parentElement.querySelector('.file-name'))">
                                 <i class="fas fa-image"></i><span>Upload foto</span>
                                 <div class="file-name"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php endfor; ?>
                  </div>
                  <div class="variation-note-box">
                     <i class="fas fa-lightbulb"></i>
                     Setiap opsi bisa diberi foto. Untuk variasi seperti Ukuran, foto bisa dikosongkan.
                  </div>
               </div>
            </div>

         </div>
         <button type="submit" name="add_product" class="btn-submit">
            <i class="fas fa-plus-circle"></i> Masukkan Produk
         </button>
      </form>
   </div>

   <!-- ===== PRODUCT LIST ===== -->
   <?php
      $qp = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC");
      $qp->execute();
      $total_products = $qp->rowCount();
      $all_prods = $qp->fetchAll(PDO::FETCH_ASSOC);

      // Count per category
      $cat_counts = ['all' => $total_products];
      foreach($all_prods as $p){
         $c = $p['category'] ?? '';
         if($c) $cat_counts[$c] = ($cat_counts[$c] ?? 0) + 1;
      }
   ?>

   <!-- Category stats strip -->
   <?php if($total_products > 0): ?>
   <div class="cat-stats-strip">
      <div class="cat-stat-chip category-theme category-theme-all">
         <div class="cat-stat-icon category-theme category-theme-all">
            <i class="fas fa-boxes-stacked"></i>
         </div>
         <div>
            <div class="cat-stat-num"><?= $total_products; ?></div>
            <div class="cat-stat-lbl">Semua Produk</div>
         </div>
      </div>
      <?php
      foreach($CATEGORIES as $c):
         $cnt = $cat_counts[$c] ?? 0;
         if($cnt === 0) continue;
      ?>
      <div class="cat-stat-chip <?= category_theme_class($c); ?>">
         <div class="cat-stat-icon <?= category_theme_class($c); ?>">
            <i class="fas fa-tag"></i>
         </div>
         <div>
            <div class="cat-stat-num"><?= $cnt; ?></div>
            <div class="cat-stat-lbl"><?= $c; ?></div>
         </div>
      </div>
      <?php endforeach; ?>
   </div>
   <?php endif; ?>

   <div class="products-section-header">
      <div>
         <div class="section-label">Daftar Produk</div>
         <div class="u-inline-style-041"><?= $total_products; ?> Produk Terdaftar</div>
      </div>
      <div class="search-bar-wrap">
         <i class="fas fa-search"></i>
         <input type="text" id="product-search" placeholder="Cari produk..." oninput="filterProducts()">
      </div>
   </div>

   <!-- Category filter tabs -->
   <div class="cat-filter-row">
      <span class="cat-tab active" onclick="filterByCategory('all',this)">
         <i class="fas fa-th"></i> Semua (<?= $total_products; ?>)
      </span>
      <?php foreach($CATEGORIES as $c):
         $cnt = $cat_counts[$c] ?? 0;
         if($cnt === 0) continue;
      ?>
      <span class="cat-tab" onclick="filterByCategory('<?= htmlspecialchars($c); ?>',this)">
         <?= $c; ?> (<?= $cnt; ?>)
      </span>
      <?php endforeach; ?>
   </div>

   <!-- Products grid -->
   <div class="products-grid" id="products-grid">

   <?php if($total_products > 0): foreach($all_prods as $fp):
      $sv = $conn->prepare("SELECT * FROM `product_variations` WHERE product_id = ?");
      $sv->execute([$fp['id']]);
      $has_var = $sv->rowCount() > 0;
      $cat     = $fp['category'] ?? '';
   ?>
   <div class="product-card"
      data-name="<?= strtolower(htmlspecialchars($fp['name'])); ?>"
      data-category="<?= htmlspecialchars($cat); ?>">

      <?php if($has_var): ?>
         <div class="product-variation-badge"><i class="fas fa-tags"></i> Variasi</div>
      <?php endif; ?>

      <?php if($cat): ?>
         <div class="product-category-badge <?= category_theme_class($cat); ?>">
            <?= htmlspecialchars($cat); ?>
         </div>
      <?php endif; ?>

      <img src="../uploaded_img/<?= htmlspecialchars($fp['image_01']); ?>"
           alt="<?= htmlspecialchars($fp['name']); ?>"
           class="product-card-img" loading="lazy">

      <div class="product-card-body">
         <div class="product-card-name"><?= htmlspecialchars($fp['name']); ?></div>
         <div class="product-card-price">Rp <?= number_format($fp['price'],0,',','.'); ?></div>
         <div class="product-card-desc"><?= htmlspecialchars($fp['details']); ?></div>

         <?php
            $sv->execute([$fp['id']]);
            if($sv->rowCount() > 0):
         ?>
         <div class="product-card-variations">
            <?php while($variation = $sv->fetch(PDO::FETCH_ASSOC)): ?>
               <div class="variation-label-tag"><?= htmlspecialchars($variation['variation_name']); ?></div>
               <div class="variation-chips">
                  <?php
                     $so = $conn->prepare("SELECT * FROM `product_variation_options` WHERE variation_id = ?");
                     $so->execute([$variation['id']]);
                     while($opt = $so->fetch(PDO::FETCH_ASSOC)):
                  ?>
                  <div class="variation-chip">
                     <?php if(!empty($opt['option_image']) && file_exists('../uploaded_img/'.$opt['option_image'])): ?>
                        <img src="../uploaded_img/<?= htmlspecialchars($opt['option_image']); ?>" alt="">
                     <?php endif; ?>
                     <?= htmlspecialchars($opt['option_value']); ?>
                  </div>
                  <?php endwhile; ?>
               </div>
            <?php endwhile; ?>
         </div>
         <?php endif; ?>

         <div class="product-card-actions">
            <a href="../quick_view.php?pid=<?= $fp['id']; ?>" target="_blank" class="card-action-btn btn-view-product">
               <i class="fas fa-eye"></i> Lihat
            </a>
            <a href="update_product.php?update=<?= $fp['id']; ?>" class="card-action-btn btn-update-product">
               <i class="fas fa-pen"></i> Update
            </a>
            <a href="products.php?delete=<?= $fp['id']; ?>"
               class="card-action-btn btn-delete-product"
               onclick="return confirm('Hapus produk \'<?= addslashes(htmlspecialchars($fp['name'])); ?>\'?');">
               <i class="fas fa-trash"></i> Hapus
            </a>
         </div>
      </div>
   </div>
   <?php endforeach; else: ?>
      <div class="products-empty">
         <i class="fas fa-box-open"></i>
         <p>Belum ada produk. Tambahkan produk pertamamu!</p>
      </div>
   <?php endif; ?>

   </div>
</div>

<script>
   /* ── Category pill preview ── */
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

   /* ── Filter ── */
   let activeCat = 'all';

   function filterByCategory(cat, el){
      activeCat = cat;
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
      el.classList.add('active');
      filterProducts();
   }

   function filterProducts(){
      const q = (document.getElementById('product-search').value || '').toLowerCase().trim();
      document.querySelectorAll('#products-grid .product-card').forEach(card => {
         const name = card.dataset.name || '';
         const cat  = card.dataset.category || '';
         const mq   = q==='' || name.includes(q);
         const mc   = activeCat==='all' || cat===activeCat;
         card.style.display = (mq&&mc) ? '' : 'none';
      });
   }

   /* ── Variation toggle ── */
   function toggleVariationBox(){
      const cb=document.getElementById('has_variation'), box=document.getElementById('variation_box');
      const cb2=document.getElementById('has_variation_2'), b2=document.getElementById('variation_2_box');
      box.style.display = cb.checked?'block':'none';
      if(!cb.checked){cb2.checked=false;b2.style.display='none';}
   }

   function toggleVariation2Box(){
      const cb2=document.getElementById('has_variation_2'), b2=document.getElementById('variation_2_box');
      b2.style.display = cb2.checked?'block':'none';
   }

   /* ── File name ── */
   function showFileName(input, areaId, nameEl){
      const el = nameEl||(areaId?document.getElementById('fn'+areaId.replace('area','')):null);
      if(el&&input.files.length>0) el.textContent=input.files[0].name;
   }
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>
