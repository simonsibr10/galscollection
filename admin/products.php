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

$CAT_COLORS = [
   'Totebag'    => '#4f6ef7',
   'Slingbag'   => '#059669',
   'Dompet'     => '#f59e0b',
   'Heels'      => '#e11d48',
   'Flat Shoes' => '#0891b2',
   'Top Handle' => '#7c3aed',
   'Clutch'     => '#ea580c',
   'Ransel'     => '#65a30d',
   'Waistbag'   => '#db2777',
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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
      body{background:#f0f2f8!important;font-family:'Segoe UI',sans-serif!important}
      section,.add-products,.show-products,.dashboard{background:transparent!important}
      .header{background:#fff!important}.footer{background:#fff!important}

      .products-page{max-width:1400px;margin:0 auto;padding:2.4rem 2.8rem 5rem}

      .products-page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2.4rem;flex-wrap:wrap;gap:1rem;animation:fadeSlideDown .45s ease both}
      .products-page-header h1{font-size:2.6rem;font-weight:800;color:#0f172a;letter-spacing:-.5px}
      .products-page-header h1 span{background:linear-gradient(135deg,#1a2a6c,#4f6ef7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}

      .notif-bar{background:#ecfdf5;border-left:4px solid #059669;border-radius:.8rem;padding:1.2rem 1.6rem;font-size:1.4rem;color:#065f46;margin-bottom:1.6rem;display:flex;align-items:center;gap:.8rem;animation:fadeSlideDown .3s ease both}
      .notif-bar.error{background:#fff1f2;border-left-color:#e11d48;color:#9f1239}

      .form-panel{background:#fff;border-radius:1.6rem;padding:2.8rem 3rem;box-shadow:0 2px 16px rgba(0,0,0,.06);margin-bottom:3.2rem;animation:fadeSlideUp .5s ease both}
      .form-panel-title{font-size:1.8rem;font-weight:700;color:#0f172a;margin-bottom:2.4rem;display:flex;align-items:center;gap:.8rem;padding-bottom:1.4rem;border-bottom:1px solid #f1f5f9}
      .form-panel-title i{color:#4f6ef7}

      .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.8rem 2.4rem}
      .form-grid .full-width{grid-column:1/-1}
      .form-field{display:flex;flex-direction:column;gap:.6rem}
      .form-field label{font-size:1.3rem;font-weight:600;color:#475569}
      .form-field label .req{color:#e11d48;margin-left:.2rem}

      .form-input{width:100%;padding:1.1rem 1.4rem;border:1.5px solid #e2e8f0;border-radius:.8rem;font-size:1.4rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .2s,box-shadow .2s,background .2s}
      .form-input:focus{border-color:#4f6ef7;background:#fff;box-shadow:0 0 0 3px rgba(79,110,247,.12)}
      .form-input::placeholder{color:#94a3b8}
      textarea.form-input{resize:vertical;min-height:12rem;line-height:1.6}

      /* ── Category dropdown ── */
      .cat-select-wrap{position:relative}
      .cat-select-wrap::after{content:'\f078';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:1.4rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.2rem;pointer-events:none}
      .form-select{width:100%;padding:1.1rem 4rem 1.1rem 1.4rem;border:1.5px solid #e2e8f0;border-radius:.8rem;font-size:1.4rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;appearance:none;cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s}
      .form-select:focus{border-color:#4f6ef7;background:#fff;box-shadow:0 0 0 3px rgba(79,110,247,.12)}

      /* Category pill preview */
      .cat-pill-preview{display:inline-flex;align-items:center;gap:.5rem;margin-top:.6rem;padding:.5rem 1.2rem;border-radius:2rem;font-size:1.25rem;font-weight:700;width:fit-content;transition:background .25s,color .25s}

      /* File upload */
      .file-upload-area{border:2px dashed #c7d2fe;border-radius:.8rem;padding:1.8rem;text-align:center;cursor:pointer;background:#f8fafc;position:relative;transition:border-color .2s,background .2s}
      .file-upload-area:hover{border-color:#4f6ef7;background:#eff2ff}
      .file-upload-area input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
      .file-upload-area i{font-size:2.4rem;color:#94a3b8;display:block;margin-bottom:.6rem;pointer-events:none}
      .file-upload-area span{font-size:1.3rem;color:#64748b;pointer-events:none}
      .file-upload-area .file-name{font-size:1.2rem;color:#4f6ef7;margin-top:.4rem;font-weight:600}

      /* Variation */
      .variation-toggle-row{display:flex;align-items:center;gap:1rem;padding:1.4rem 1.6rem;background:#eff2ff;border-radius:.8rem;border:1.5px solid #c7d2fe;cursor:pointer;transition:background .2s}
      .variation-toggle-row:hover{background:#e0e7ff}
      .variation-toggle-row input[type="checkbox"]{width:1.8rem;height:1.8rem;accent-color:#4f6ef7;cursor:pointer}
      .variation-toggle-row label{font-size:1.4rem;font-weight:600;color:#1a2a6c;cursor:pointer}
      .variation-panel{display:none;margin-top:1.6rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:1.2rem;padding:2rem}
      .variation-group-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:1rem;padding:1.8rem;margin-bottom:1.4rem}
      .variation-group-card h3{font-size:1.5rem;font-weight:700;color:#1a2a6c;margin-bottom:1.4rem;display:flex;align-items:center;gap:.6rem}
      .variation-group-card h3 i{color:#4f6ef7}
      .variation-option-row{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:.8rem;padding:1.2rem;margin-bottom:1rem}
      .variation-option-row-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
      .variation-note-box{margin-top:1.2rem;padding:1rem 1.4rem;background:#fffbeb;border-left:3px solid #f59e0b;border-radius:.6rem;font-size:1.3rem;color:#78350f;line-height:1.6}

      .btn-submit{display:inline-flex;align-items:center;justify-content:center;gap:.8rem;width:100%;padding:1.4rem;background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;border:none;border-radius:.8rem;font-size:1.5rem;font-weight:700;cursor:pointer;margin-top:.8rem;font-family:inherit;transition:transform .15s,box-shadow .2s}
      .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,110,247,.3)}
      .btn-submit:active{transform:scale(.98)}

      /* Category stats */
      .cat-stats-strip{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:2rem}
      .cat-stat-chip{background:#fff;border-radius:1rem;padding:1.2rem 1.6rem;box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;gap:1rem;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;min-width:12rem}
      .cat-stat-chip:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.09)}
      .cat-stat-chip::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:1rem 1rem 0 0}
      .cat-stat-num{font-size:2.2rem;font-weight:800;color:#0f172a;line-height:1}
      .cat-stat-lbl{font-size:1.2rem;font-weight:600;color:#64748b;margin-top:.2rem}

      /* Category filter tabs */
      .cat-filter-row{display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:1.6rem}
      .cat-tab{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.3rem;border-radius:3rem;font-size:1.25rem;font-weight:600;background:#f1f5f9;color:#475569;border:1.5px solid transparent;cursor:pointer;transition:all .18s}
      .cat-tab:hover{background:#eff2ff;color:#1a2a6c}
      .cat-tab.active{background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff}

      /* Section header */
      .products-section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:1rem}
      .section-label{font-size:1.1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8}
      .search-bar-wrap{position:relative}
      .search-bar-wrap i{position:absolute;left:1.2rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.3rem;pointer-events:none}
      #product-search{padding:.8rem 1.4rem .8rem 3.4rem;border:1.5px solid #e2e8f0;border-radius:3rem;font-size:1.3rem;color:#0f172a;background:#fff;outline:none;width:24rem;transition:border-color .2s,box-shadow .2s;font-family:inherit}
      #product-search:focus{border-color:#4f6ef7;box-shadow:0 0 0 3px rgba(79,110,247,.12)}

      /* Product grid */
      .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(28rem,1fr));gap:2rem}
      .product-card{background:#fff;border-radius:1.4rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05);display:flex;flex-direction:column;position:relative;transition:transform .25s,box-shadow .25s;animation:fadeSlideUp .5s ease both}
      .product-card:hover{transform:translateY(-5px);box-shadow:0 14px 36px rgba(0,0,0,.1)}
      .product-variation-badge{position:absolute;top:1rem;left:1rem;background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;font-size:1.1rem;font-weight:700;padding:.3rem .8rem;border-radius:2rem;z-index:2}
      .product-category-badge{position:absolute;top:1rem;right:1rem;color:#fff;font-size:1.1rem;font-weight:700;padding:.3rem .9rem;border-radius:2rem;z-index:2}
      .product-card-img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;background:#f1f5f9}
      .product-card-body{padding:1.6rem;display:flex;flex-direction:column;flex:1;gap:.8rem}
      .product-card-name{font-size:1.5rem;font-weight:700;color:#0f172a;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
      .product-card-price{font-size:1.8rem;font-weight:800;color:#e11d48}
      .product-card-desc{font-size:1.3rem;color:#64748b;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
      .product-card-variations{display:flex;flex-direction:column;gap:.6rem;padding:1rem;background:#f8fafc;border-radius:.8rem;border:1px solid #f1f5f9}
      .variation-label-tag{font-size:1.15rem;font-weight:700;color:#475569;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em}
      .variation-chips{display:flex;flex-wrap:wrap;gap:.5rem}
      .variation-chip{display:inline-flex;align-items:center;gap:.4rem;background:#fff;border:1px solid #e2e8f0;border-radius:2rem;padding:.3rem .9rem;font-size:1.2rem;color:#334155;font-weight:500}
      .variation-chip img{width:1.8rem;height:1.8rem;object-fit:cover;border-radius:50%}
      .product-card-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.8rem;margin-top:auto;padding-top:1.2rem;border-top:1px solid #f1f5f9}
      .card-action-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.9rem .6rem;border-radius:.7rem;font-size:1.25rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s;white-space:nowrap;font-family:inherit}
      .card-action-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.12)}
      .card-action-btn:active{transform:scale(.96)}
      .btn-view-product{background:#eff2ff;color:#1a2a6c}.btn-view-product:hover{background:#e0e7ff}
      .btn-update-product{background:#fffbeb;color:#b45309}.btn-update-product:hover{background:#fef3c7}
      .btn-delete-product{background:#fff1f2;color:#be123c}.btn-delete-product:hover{background:#ffe4e6}
      .products-empty{grid-column:1/-1;text-align:center;padding:6rem 2rem;color:#94a3b8}
      .products-empty i{font-size:5rem;display:block;margin-bottom:1.4rem;color:#cbd5e1}
      .products-empty p{font-size:1.6rem}

      @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
      @keyframes fadeSlideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
      .product-card:nth-child(1){animation-delay:.05s}.product-card:nth-child(2){animation-delay:.10s}
      .product-card:nth-child(3){animation-delay:.15s}.product-card:nth-child(4){animation-delay:.20s}
      .product-card:nth-child(n+5){animation-delay:.25s}

      @media(max-width:900px){.products-page{padding:1.6rem}.form-grid{grid-template-columns:1fr}.products-grid{grid-template-columns:1fr 1fr}}
      @media(max-width:580px){.products-grid{grid-template-columns:1fr}#product-search{width:100%}.variation-option-row-grid{grid-template-columns:1fr}}
   </style>
</head>
<body>

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
               <label><i class="fas fa-tag" style="color:#94a3b8;font-size:1.2rem"></i> Kategori Produk <span class="req">*</span></label>
               <div class="cat-select-wrap">
                  <select name="category" class="form-select" required id="cat-select" onchange="updateCatPill(this.value)">
                     <?php foreach($CATEGORIES as $cat): ?>
                        <option value="<?= $cat; ?>"><?= $cat; ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="cat-pill-preview" id="cat-pill" style="background:#eff2ff;color:#1a2a6c;">
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
                     <div class="form-field" style="margin-bottom:1.2rem;">
                        <label>Nama Variasi 1</label>
                        <input type="text" name="variation_1_name" class="form-input" placeholder="Contoh: Warna">
                     </div>
                     <?php for($i=1;$i<=5;$i++): ?>
                     <div class="variation-option-row">
                        <div style="font-size:1.2rem;font-weight:700;color:#64748b;margin-bottom:.8rem;">Opsi <?= $i; ?></div>
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
                     <div style="margin-top:1.4rem;">
                        <div class="variation-toggle-row" onclick="document.getElementById('has_variation_2').click()">
                           <input type="checkbox" name="has_variation_2" id="has_variation_2" onchange="toggleVariation2Box()" onclick="event.stopPropagation()">
                           <label for="has_variation_2"><i class="fas fa-plus"></i> &nbsp;Tambah Variasi 2</label>
                        </div>
                     </div>
                  </div>
                  <div id="variation_2_box" class="variation-group-card" style="display:none;">
                     <h3><i class="fas fa-layer-group"></i> Variasi 2</h3>
                     <div class="form-field" style="margin-bottom:1.2rem;">
                        <label>Nama Variasi 2</label>
                        <input type="text" name="variation_2_name" class="form-input" placeholder="Contoh: Ukuran">
                     </div>
                     <?php for($i=1;$i<=5;$i++): ?>
                     <div class="variation-option-row">
                        <div style="font-size:1.2rem;font-weight:700;color:#64748b;margin-bottom:.8rem;">Opsi <?= $i; ?></div>
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
      <div class="cat-stat-chip" style="--c:#4f6ef7;">
         <div style="background:#eff2ff;color:#4f6ef7;width:3.6rem;height:3.6rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">
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
         $col = $CAT_COLORS[$c] ?? '#94a3b8';
         $colLight = $col.'22';
      ?>
      <div class="cat-stat-chip" style="border-left:3px solid <?= $col; ?>;">
         <div style="background:<?= $colLight; ?>;color:<?= $col; ?>;width:3.6rem;height:3.6rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">
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
         <div style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.2rem;"><?= $total_products; ?> Produk Terdaftar</div>
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
      $catCol  = $CAT_COLORS[$cat] ?? '#64748b';
   ?>
   <div class="product-card"
      data-name="<?= strtolower(htmlspecialchars($fp['name'])); ?>"
      data-category="<?= htmlspecialchars($cat); ?>">

      <?php if($has_var): ?>
         <div class="product-variation-badge"><i class="fas fa-tags"></i> Variasi</div>
      <?php endif; ?>

      <?php if($cat): ?>
         <div class="product-category-badge" style="background:<?= $catCol; ?>;">
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
   const CAT_COLORS = <?= json_encode($CAT_COLORS); ?>;

   function updateCatPill(val){
      const pill = document.getElementById('cat-pill');
      const txt  = document.getElementById('cat-pill-text');
      const col  = CAT_COLORS[val] || '#4f6ef7';
      txt.textContent = val;
      pill.style.background = col + '22';
      pill.style.color = col;
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