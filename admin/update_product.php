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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
      body{background:#f0f2f8!important;font-family:'Segoe UI',sans-serif!important}
      section,.update-product,.dashboard{background:transparent!important}
      .header{background:#fff!important}.footer{background:#fff!important}

      .update-page{max-width:1200px;margin:0 auto;padding:2.4rem 2.8rem 5rem}

      /* Page header */
      .page-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2.4rem;animation:fadeSlideDown .45s ease both}
      .page-header h1{font-size:2.6rem;font-weight:800;color:#0f172a;letter-spacing:-.5px}
      .page-header h1 span{background:linear-gradient(135deg,#1a2a6c,#4f6ef7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}

      .btn-back{display:inline-flex;align-items:center;gap:.7rem;padding:.9rem 1.8rem;background:#fff;border:1.5px solid #e2e8f0;border-radius:.8rem;font-size:1.35rem;font-weight:600;color:#475569;text-decoration:none;transition:all .2s;box-shadow:0 2px 8px rgba(0,0,0,.05)}
      .btn-back:hover{background:#f8fafc;color:#1a2a6c;border-color:#c7d2fe;transform:translateY(-1px)}

      /* Alert */
      .alert-bar{padding:1.2rem 1.6rem;border-radius:.9rem;font-size:1.35rem;display:flex;align-items:center;gap:.8rem;margin-bottom:2rem;animation:fadeSlideDown .35s ease both}
      .alert-bar.success{background:#ecfdf5;border-left:4px solid #059669;color:#065f46}
      .alert-bar.warning{background:#fffbeb;border-left:4px solid #f59e0b;color:#78350f}
      .alert-bar.error{background:#fff1f2;border-left:4px solid #e11d48;color:#be123c}

      /* Main grid */
      .update-grid{display:grid;grid-template-columns:34rem 1fr;gap:2rem;align-items:start}

      /* Image panel */
      .image-panel{background:#fff;border-radius:1.4rem;padding:2rem;box-shadow:0 2px 12px rgba(0,0,0,.05);animation:fadeSlideUp .5s ease both;position:sticky;top:8rem}
      .panel-title{font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:1.6rem;display:flex;align-items:center;gap:.7rem;padding-bottom:1.2rem;border-bottom:1px solid #f1f5f9}
      .panel-title i{color:#4f6ef7}

      .img-preview-main{width:100%;aspect-ratio:1/1;border-radius:1rem;overflow:hidden;background:#f8fafc;border:2px solid #f1f5f9;margin-bottom:1.2rem;position:relative;cursor:pointer;transition:border-color .2s}
      .img-preview-main:hover{border-color:#c7d2fe}
      .img-preview-main img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s}
      .img-preview-main:hover img{transform:scale(1.04)}
      .img-preview-label{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);color:#fff;font-size:1.2rem;font-weight:600;padding:1.2rem 1rem .8rem;text-align:center}

      .img-thumbs{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem}
      .img-thumb{aspect-ratio:1/1;border-radius:.8rem;overflow:hidden;background:#f8fafc;border:2px solid #f1f5f9;cursor:pointer;transition:border-color .2s,transform .2s;position:relative}
      .img-thumb.active{border-color:#4f6ef7}
      .img-thumb:hover{border-color:#c7d2fe;transform:translateY(-2px)}
      .img-thumb img{width:100%;height:100%;object-fit:cover;display:block}
      .img-thumb-badge{position:absolute;bottom:.3rem;right:.3rem;background:rgba(0,0,0,.55);color:#fff;font-size:1rem;font-weight:700;padding:.15rem .5rem;border-radius:.4rem}

      .product-quick-info{margin-top:1.6rem;padding-top:1.6rem;border-top:1px solid #f1f5f9}
      .qi-row{display:flex;align-items:center;justify-content:space-between;padding:.8rem 0;border-bottom:1px solid #f8fafc;font-size:1.3rem}
      .qi-row:last-child{border-bottom:none}
      .qi-key{color:#64748b;font-weight:500}
      .qi-val{color:#0f172a;font-weight:700}
      .qi-price{color:#e11d48;font-size:1.5rem}

      /* Form panel */
      .form-panel{background:#fff;border-radius:1.4rem;padding:2.4rem;box-shadow:0 2px 12px rgba(0,0,0,.05);animation:fadeSlideUp .5s .06s ease both}

      .form-section{margin-bottom:2.8rem}
      .form-section-title{font-size:1.35rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1.6rem;display:flex;align-items:center;gap:.7rem}
      .form-section-title::after{content:'';flex:1;height:1px;background:#f1f5f9}

      .form-field{display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.6rem}
      .form-field:last-child{margin-bottom:0}
      .form-field label{font-size:1.3rem;font-weight:700;color:#475569;display:flex;align-items:center;gap:.5rem}
      .form-field label i{color:#94a3b8;font-size:1.2rem}

      .input-wrap{position:relative}
      .input-wrap i.fi{position:absolute;left:1.4rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.4rem;pointer-events:none;transition:color .2s}
      .input-wrap:focus-within i.fi{color:#4f6ef7}

      .form-input{width:100%;padding:1.2rem 1.4rem 1.2rem 4rem;border:1.5px solid #e2e8f0;border-radius:.9rem;font-size:1.4rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .2s,box-shadow .2s,background .2s}
      .form-input:focus{border-color:#4f6ef7;background:#fff;box-shadow:0 0 0 3px rgba(79,110,247,.12)}
      .form-input::placeholder{color:#94a3b8}
      textarea.form-input{resize:vertical;min-height:14rem;padding-top:1.2rem;line-height:1.6}

      /* Category select */
      .cat-select-wrap{position:relative}
      .cat-select-wrap::after{content:'\f078';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:1.4rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.2rem;pointer-events:none}
      .form-select{width:100%;padding:1.2rem 4rem 1.2rem 1.4rem;border:1.5px solid #e2e8f0;border-radius:.9rem;font-size:1.4rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;appearance:none;cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s}
      .form-select:focus{border-color:#4f6ef7;background:#fff;box-shadow:0 0 0 3px rgba(79,110,247,.12)}

      /* Category pill preview */
      .cat-pill-preview{display:inline-flex;align-items:center;gap:.5rem;margin-top:.6rem;padding:.5rem 1.2rem;border-radius:2rem;font-size:1.25rem;font-weight:700;width:fit-content;transition:background .25s,color .25s}

      /* Image upload grid */
      .img-upload-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.2rem}
      .img-upload-field{display:flex;flex-direction:column;gap:.6rem}
      .img-upload-field label{font-size:1.25rem;font-weight:700;color:#475569;display:flex;align-items:center;gap:.4rem}

      .img-drop-area{border:2px dashed #c7d2fe;border-radius:.9rem;padding:1.6rem 1rem;text-align:center;cursor:pointer;background:#f8fafc;position:relative;transition:border-color .2s,background .2s;aspect-ratio:1/1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;overflow:hidden}
      .img-drop-area input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
      .img-drop-area:hover{border-color:#4f6ef7;background:#eff2ff}
      .img-drop-area i{font-size:2.2rem;color:#94a3b8;pointer-events:none}
      .img-drop-area span{font-size:1.2rem;color:#64748b;pointer-events:none;line-height:1.4}
      .img-drop-area .fn{font-size:1.1rem;color:#4f6ef7;font-weight:600;margin-top:.2rem}
      .drop-current-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;border-radius:.7rem;pointer-events:none;z-index:0}
      .drop-area-content{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:.4rem}
      .preview-overlay{position:absolute;inset:0;background:#000;opacity:0;transition:opacity .2s;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;font-weight:700;pointer-events:none}
      .img-drop-area:hover .preview-overlay{opacity:.35}

      /* ── VARIATION SECTION ── */
      .variation-section{margin-bottom:2.8rem}

      .variation-card{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:1.2rem;padding:1.8rem;margin-bottom:1.4rem}
      .variation-card-title{font-size:1.5rem;font-weight:700;color:#1a2a6c;margin-bottom:1.4rem;display:flex;align-items:center;gap:.7rem}
      .variation-card-title i{color:#4f6ef7}

      /* Options grid per variation */
      .variation-options-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(16rem,1fr));gap:1.2rem}

      .variation-opt-item{background:#fff;border:1.5px solid #e2e8f0;border-radius:1rem;padding:1.2rem;display:flex;flex-direction:column;gap:.8rem;transition:border-color .2s,box-shadow .2s}
      .variation-opt-item:hover{border-color:#c7d2fe;box-shadow:0 4px 12px rgba(0,0,0,.06)}

      .opt-name{font-size:1.3rem;font-weight:700;color:#0f172a;text-align:center;padding:.4rem .8rem;background:#eff2ff;border-radius:.6rem;color:#1a2a6c}

      /* Current option image */
      .opt-current-img{width:100%;aspect-ratio:1/1;border-radius:.8rem;overflow:hidden;background:#f1f5f9;margin-bottom:.4rem;position:relative}
      .opt-current-img img{width:100%;height:100%;object-fit:cover;display:block}
      .opt-no-img{width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:.8rem;color:#cbd5e1;font-size:2rem;margin-bottom:.4rem}

      /* Mini drop area for variation option image */
      .mini-drop-area{border:2px dashed #c7d2fe;border-radius:.8rem;padding:1.1rem .8rem;text-align:center;cursor:pointer;background:#f8fafc;position:relative;transition:border-color .2s,background .2s;display:flex;flex-direction:column;align-items:center;gap:.4rem;overflow:hidden}
      .mini-drop-area input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
      .mini-drop-area:hover{border-color:#4f6ef7;background:#eff2ff}
      .mini-drop-area i{font-size:1.6rem;color:#94a3b8;pointer-events:none}
      .mini-drop-area span{font-size:1.1rem;color:#64748b;pointer-events:none}
      .mini-drop-area .mfn{font-size:1.1rem;color:#4f6ef7;font-weight:600}
      .mini-preview-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;pointer-events:none;z-index:0}
      .mini-drop-content{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:.3rem}

      .no-var-note{padding:1.4rem 1.6rem;background:#fffbeb;border-left:3px solid #f59e0b;border-radius:.8rem;font-size:1.35rem;color:#78350f;display:flex;align-items:center;gap:.7rem}

      /* Tips box */
      .tips-box{background:#fffbeb;border:1px solid #fde68a;border-radius:.9rem;padding:1.2rem 1.4rem;font-size:1.3rem;color:#78350f;display:flex;align-items:flex-start;gap:.7rem;margin-top:1.2rem;line-height:1.6}

      /* Submit row */
      .form-submit-row{display:flex;gap:1.2rem;margin-top:2.8rem;padding-top:2rem;border-top:1px solid #f1f5f9;flex-wrap:wrap}
      .btn-submit{flex:1;padding:1.4rem;background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;border:none;border-radius:.9rem;font-size:1.5rem;font-weight:700;cursor:pointer;transition:transform .15s,box-shadow .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.7rem}
      .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,110,247,.35)}
      .btn-submit:active{transform:scale(.97)}
      .btn-cancel{padding:1.4rem 2.4rem;background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;border-radius:.9rem;font-size:1.45rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.6rem;font-family:inherit;transition:background .15s,border-color .15s}
      .btn-cancel:hover{background:#fff1f2;border-color:#fca5a5;color:#be123c}

      @keyframes fadeSlideDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
      @keyframes fadeSlideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

      @media(max-width:1000px){.update-page{padding:1.6rem}.update-grid{grid-template-columns:1fr}.image-panel{position:static}.img-upload-grid{grid-template-columns:1fr 1fr 1fr}}
      @media(max-width:600px){.img-upload-grid{grid-template-columns:1fr}.form-submit-row{flex-direction:column}.variation-options-grid{grid-template-columns:1fr 1fr}}
   </style>
</head>
<body>

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
            <div class="qi-row" style="align-items:flex-start;padding-top:1rem;">
               <span class="qi-key"><i class="fas fa-box"></i> Nama</span>
               <span class="qi-val" style="text-align:right;max-width:15rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($fetch_products['name']); ?></span>
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
               <div class="form-section-title"><i class="fas fa-info-circle" style="color:#4f6ef7"></i> Informasi Produk</div>

               <div class="form-field">
                  <label><i class="fas fa-pen"></i> Nama Produk <span style="color:#e11d48">*</span></label>
                  <div class="input-wrap">
                     <input type="text" name="name" class="form-input" required maxlength="100"
                        value="<?= htmlspecialchars($fetch_products['name']); ?>" placeholder="Nama produk">
                     <i class="fas fa-pen fi"></i>
                  </div>
               </div>

               <div class="form-field">
                  <label><i class="fas fa-tag"></i> Harga Produk (Rp) <span style="color:#e11d48">*</span></label>
                  <div class="input-wrap">
                     <input type="number" name="price" class="form-input" required min="0" max="9999999999"
                        value="<?= htmlspecialchars($fetch_products['price']); ?>"
                        oninput="updatePricePreview(this.value)">
                     <i class="fas fa-tag fi"></i>
                  </div>
                  <div id="price-preview" style="font-size:1.3rem;color:#059669;font-weight:700;margin-top:.4rem;"></div>
               </div>

               <!-- ─── KATEGORI ─── -->
               <div class="form-field">
                  <label><i class="fas fa-folder"></i> Kategori Produk <span style="color:#e11d48">*</span></label>
                  <div class="cat-select-wrap">
                     <select name="category" class="form-select" required id="cat-select" onchange="updateCatPill(this.value)">
                        <?php foreach($CATEGORIES as $cat): ?>
                           <option value="<?= $cat; ?>" <?= ($fetch_products['category']??'')===$cat?'selected':''; ?>><?= $cat; ?></option>
                        <?php endforeach; ?>
                     </select>
                  </div>
                  <?php
                  $CAT_COLORS = ['Totebag'=>'#4f6ef7','Slingbag'=>'#059669','Dompet'=>'#f59e0b','Heels'=>'#e11d48','Flat Shoes'=>'#0891b2','Top Handle'=>'#7c3aed','Clutch'=>'#ea580c','Ransel'=>'#65a30d','Waistbag'=>'#db2777'];
                  $cur_cat = $fetch_products['category'] ?? $CATEGORIES[0];
                  $cur_col = $CAT_COLORS[$cur_cat] ?? '#4f6ef7';
                  ?>
                  <div class="cat-pill-preview" id="cat-pill" style="background:<?= $cur_col; ?>22;color:<?= $cur_col; ?>">
                     <i class="fas fa-tag"></i>
                     <span id="cat-pill-text"><?= htmlspecialchars($cur_cat); ?></span>
                  </div>
               </div>

               <div class="form-field">
                  <label><i class="fas fa-align-left"></i> Detail Produk <span style="color:#e11d48">*</span></label>
                  <div class="input-wrap">
                     <textarea name="details" class="form-input" required maxlength="500"
                        id="details-input" oninput="updateCharCount()"
                        style="padding-left:1.4rem;"><?= htmlspecialchars($fetch_products['details']); ?></textarea>
                  </div>
                  <div style="font-size:1.2rem;color:#94a3b8;text-align:right;"><span id="char-count">0</span>/500 karakter</div>
               </div>
            </div>

            <!-- ─── UPDATE GAMBAR PRODUK ─── -->
            <div class="form-section">
               <div class="form-section-title"><i class="fas fa-images" style="color:#4f6ef7"></i> Update Gambar Produk</div>

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
               <div class="form-section-title"><i class="fas fa-tags" style="color:#4f6ef7"></i> Update Gambar Variasi</div>

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
   const CAT_COLORS = <?= json_encode($CAT_COLORS); ?>;

   function updateCatPill(val){
      const pill = document.getElementById('cat-pill');
      const txt  = document.getElementById('cat-pill-text');
      const col  = CAT_COLORS[val] || '#4f6ef7';
      txt.textContent = val;
      pill.style.background = col+'22';
      pill.style.color = col;
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
            // Replace no-img div with img
            const wrap = document.getElementById('optimg-wrap-'+optId);
            if(wrap){
               const img = document.createElement('img');
               img.src = e.target.result;
               img.style.cssText='width:100%;height:100%;object-fit:cover;border-radius:.8rem;';
               img.id = 'optimg-'+optId;
               wrap.replaceWith(img);
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
      cnt.style.color = len>450?'#e11d48':'#94a3b8';
   }

   /* Auto-dismiss alert */
   const alertBar = document.querySelector('.alert-bar');
   if(alertBar){
      setTimeout(()=>{
         alertBar.style.transition='opacity .4s';
         alertBar.style.opacity='0';
         setTimeout(()=>alertBar.remove(),450);
      },5000);
   }

   /* Init */
   updatePricePreview(document.querySelector('[name="price"]')?.value||0);
   updateCharCount();
</script>
</body>
</html>