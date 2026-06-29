<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';

// Ambil semua produk (termasuk category)
$select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC");
$select_products->execute();
$all_products = $select_products->fetchAll(PDO::FETCH_ASSOC);

// Ambil SEMUA kategori yang ada datanya di database (tidak NULL, tidak kosong)
$select_cats = $conn->prepare(
   "SELECT DISTINCT category FROM `products`
    WHERE category IS NOT NULL AND category != ''
    ORDER BY category ASC"
);
$select_cats->execute();
$all_cats = $select_cats->fetchAll(PDO::FETCH_COLUMN);

// Hitung produk per kategori
$cat_counts = [];
foreach($all_products as $p){
   $c = $p['category'] ?? '';
   if($c !== '') $cat_counts[$c] = ($cat_counts[$c] ?? 0) + 1;
}

// Icon & color per kategori
$CAT_ICONS = [
   'Totebag'    => 'fa-bag-shopping',
   'Slingbag'   => 'fa-person-walking',
   'Dompet'     => 'fa-wallet',
   'Heels'      => 'fa-shoe-prints',
   'Flat Shoes' => 'fa-shoe-prints',
   'Top Handle' => 'fa-hand-holding',
   'Clutch'     => 'fa-grip',
   'Ransel'     => 'fa-backpack',
   'Waistbag'   => 'fa-vest-patches',
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Toko — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      *,*::before,*::after{box-sizing:border-box;}

      body.shop-page{background:#fafaf8;font-family:'Segoe UI',sans-serif}

      body.shop-page .header{
         background:rgba(255,255,255,.92)!important;
         backdrop-filter:blur(12px);
         box-shadow:0 1px 12px rgba(0,0,0,.06)!important;
         position:sticky!important;top:0!important;z-index:9999!important;
      }

      .shop-page-wrap{max-width:1380px;margin:0 auto;padding:0 3.2rem 6rem}

      .shop-breadcrumb{padding:2.4rem 0 0;font-size:1.4rem;color:#999}
      .shop-breadcrumb a{color:#999;text-decoration:none}
      .shop-breadcrumb a:hover{color:#111}
      .shop-breadcrumb span{margin:0 .6rem}

      .shop-title{font-size:clamp(2.6rem,4vw,4.4rem);font-weight:800;letter-spacing:-.04em;color:#111;margin:1.4rem 0 3rem;text-transform:uppercase}

      /* Layout */
      .shop-layout{display:grid;grid-template-columns:22rem 1fr;gap:4rem;align-items:start}

      /* Sidebar */
      .shop-sidebar{position:sticky;top:9rem}
      .sidebar-section{margin-bottom:3rem}
      .sidebar-section h4{font-size:1.2rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#111;margin-bottom:1.4rem;padding-bottom:.8rem;border-bottom:1.5px solid #e8e8e4}

      .sidebar-cat-list{display:flex;flex-direction:column;gap:.4rem}

      .sidebar-cat-btn{display:flex;align-items:center;gap:1rem;padding:.9rem 1.2rem;border-radius:.8rem;font-size:1.4rem;font-weight:500;color:#555;cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:background .18s,color .18s;position:relative}
      .sidebar-cat-btn:hover{background:#f1f5f9;color:#111}
      .sidebar-cat-btn.active{background:#f1f5f9;color:#111;font-weight:700}
      .sidebar-cat-btn.active::before{content:'';position:absolute;left:0;top:.6rem;bottom:.6rem;width:3px;border-radius:2px;background:var(--cat-color,#1a2a6c)}

      .sidebar-cat-icon{width:3rem;height:3rem;border-radius:.5rem;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;transition:background .18s}

      .sidebar-cat-count{margin-left:auto;font-size:1.2rem;background:#e2e8f0;color:#475569;border-radius:2rem;padding:.2rem .7rem;font-weight:700}
      .sidebar-cat-btn.active .sidebar-cat-count{background:var(--cat-color,#1a2a6c);color:#fff;opacity:.9}

      /* Price filter */
      .price-range-row{display:flex;align-items:center;gap:.8rem}
      .price-input{flex:1;padding:.9rem 1rem;border:1.5px solid #e2e8f0;border-radius:.6rem;font-size:1.3rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;width:100%;transition:border-color .2s}
      .price-input:focus{border-color:#1a2a6c}
      .btn-apply-filter{width:100%;margin-top:1rem;padding:1rem;background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;border:none;border-radius:.7rem;font-size:1.4rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .2s}
      .btn-apply-filter:hover{opacity:.88}
      .btn-reset-price{width:100%;margin-top:.6rem;padding:.8rem;background:none;border:1.5px solid #e2e8f0;border-radius:.7rem;font-size:1.3rem;color:#64748b;cursor:pointer;font-family:inherit;transition:border-color .2s,color .2s}
      .btn-reset-price:hover{border-color:#94a3b8;color:#334155}

      /* Shop main */
      .shop-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;gap:1.2rem;flex-wrap:wrap}
      .shop-count{font-size:1.4rem;color:#888}
      .shop-count strong{color:#111;font-weight:700}

      .active-cat-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1.1rem;border-radius:3rem;font-size:1.3rem;font-weight:700;color:#fff;margin-top:.5rem}

      .sort-select{padding:.7rem 1.2rem;border:1.5px solid #e2e8f0;border-radius:.6rem;font-size:1.3rem;color:#0f172a;background:#fff;outline:none;font-family:inherit;cursor:pointer}

      /* Product grid */
      .shop-products-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2.4rem 2rem}

      /* Product card */
      .shop-card{background:#fff;border-radius:1.2rem;overflow:hidden;position:relative;border:1px solid #eeede8;transition:box-shadow .28s,transform .28s;display:flex;flex-direction:column}
      .shop-card:hover{box-shadow:0 1.2rem 3rem rgba(0,0,0,.10);transform:translateY(-6px)}

      .shop-card-img{position:relative;overflow:hidden;aspect-ratio:1/1;background:#f2f1ed}
      .shop-card-img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s cubic-bezier(.25,.46,.45,.94)}
      .shop-card:hover .shop-card-img img{transform:scale(1.07)}

      /* Category ribbon */
      .shop-card-cat-ribbon{position:absolute;bottom:1rem;left:1rem;padding:.35rem .9rem;border-radius:2rem;font-size:1.15rem;font-weight:700;color:#fff;z-index:3}

      /* Hover actions */
      .shop-card-hover-actions{position:absolute;top:1.2rem;right:1.2rem;display:flex;flex-direction:column;gap:.8rem;opacity:0;transform:translateX(1rem);transition:opacity .25s,transform .25s;z-index:3}
      .shop-card:hover .shop-card-hover-actions{opacity:1;transform:translateX(0)}
      .shop-card-hover-actions button,.shop-card-hover-actions a{width:4rem;height:4rem;border-radius:50%;background:#fff;color:#111;font-size:1.5rem;display:flex;align-items:center;justify-content:center;box-shadow:0 .3rem .8rem rgba(0,0,0,.14);cursor:pointer;transition:background .2s,color .2s;text-decoration:none;border:none;font-family:inherit}
      .shop-card-hover-actions button:hover,.shop-card-hover-actions a:hover{background:#111;color:#fff}

      /* Card body */
      .shop-card-body{padding:1.6rem 1.8rem 1.8rem;display:flex;flex-direction:column;flex:1;gap:.8rem}
      .shop-card-name{font-size:1.55rem;font-weight:600;color:#111;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:4.4rem;text-decoration:none}
      .shop-card-price{font-size:1.7rem;font-weight:800;color:#e74c3c}
      .shop-card-footer{display:flex;gap:1rem;align-items:center;margin-top:auto}
      .shop-card-qty{width:5.5rem;height:4rem;border:1.5px solid #ddd;border-radius:.6rem;text-align:center;font-size:1.5rem;color:#111;background:#fafaf8;flex-shrink:0;font-family:inherit}
      .shop-card-btn{flex:1;height:4rem;background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;font-size:1.4rem;font-weight:700;letter-spacing:.04rem;border-radius:.6rem;cursor:pointer;border:none;transition:opacity .2s,transform .15s;text-transform:uppercase;font-family:inherit}
      .shop-card-btn:hover{opacity:.88;transform:scale(.98)}
	   .shop-card-link-btn{
   display:flex;
   align-items:center;
   justify-content:center;
   text-decoration:none;
   color:#fff;
}

      /* Pagination */
      .shop-pagination{display:flex;align-items:center;justify-content:center;gap:1.2rem;margin-top:5rem;flex-wrap:wrap}
      .page-nav-btn{display:inline-flex;align-items:center;gap:.8rem;padding:1.1rem 2.4rem;font-size:1.45rem;font-weight:700;letter-spacing:.04rem;text-transform:uppercase;border-radius:.7rem;cursor:pointer;border:2px solid #111;background:transparent;color:#111;transition:.2s;font-family:inherit}
      .page-nav-btn:hover:not(:disabled){background:linear-gradient(135deg,#1a2a6c,#4f6ef7);color:#fff;border-color:transparent}
      .page-nav-btn:disabled{opacity:.3;cursor:not-allowed}
      .page-indicator{font-size:1.4rem;color:#666;min-width:8rem;text-align:center}

      /* Empty */
      .shop-empty{grid-column:1/-1;text-align:center;padding:6rem 2rem;color:#aaa}
      .shop-empty i{font-size:4rem;display:block;margin-bottom:1.5rem;opacity:.35}
      .shop-empty p{font-size:1.7rem}

      /* Mobile */
      .mobile-cat-toggle{display:none}

      @media(max-width:1100px){.shop-layout{grid-template-columns:19rem 1fr;gap:3rem}.shop-products-grid{grid-template-columns:repeat(2,1fr)}}
      @media(max-width:768px){
         .shop-layout{grid-template-columns:1fr}
         .shop-sidebar{position:static}
         .shop-sidebar-inner{display:none}
         .shop-sidebar-inner.open{display:block}
         .mobile-cat-toggle{display:flex;align-items:center;gap:.8rem;padding:1rem 1.4rem;background:#111;color:#fff;border:none;border-radius:.8rem;font-size:1.4rem;font-weight:700;cursor:pointer;margin-bottom:1.2rem;font-family:inherit}
         .shop-products-grid{grid-template-columns:repeat(2,1fr);gap:1.4rem}
         .shop-page-wrap{padding:0 1.6rem 4rem}
      }
      @media(max-width:480px){.shop-products-grid{grid-template-columns:1fr}}
   </style>
</head>
<body class="shop-page">

<?php include 'components/user_header.php'; ?>

<div class="shop-page-wrap">

   <div class="shop-breadcrumb">
      <a href="home.php">Beranda</a><span>/</span><span>Toko</span>
   </div>

   <h1 class="shop-title">Semua Produk</h1>

   <div class="shop-layout">

      <!-- SIDEBAR -->
      <aside class="shop-sidebar">
         <button class="mobile-cat-toggle" onclick="document.querySelector('.shop-sidebar-inner').classList.toggle('open')">
            <i class="fas fa-filter"></i> Filter Kategori
         </button>

         <div class="shop-sidebar-inner">

            <div class="sidebar-section">
               <h4>Kategori</h4>
               <div class="sidebar-cat-list">

                  <!-- Semua -->
                  <button class="sidebar-cat-btn active" data-cat="all"
                     onclick="filterCategory('all',this)" style="--cat-color:#1a2a6c">
                     <div class="sidebar-cat-icon" style="background:#eff2ff;color:#4f6ef7">
                        <i class="fas fa-th"></i>
                     </div>
                     Semua
                     <span class="sidebar-cat-count"><?= count($all_products); ?></span>
                  </button>

                  <?php
                  // Tampilkan semua kategori yang ada di database
                  foreach($all_cats as $cat):
                     $cnt  = $cat_counts[$cat] ?? 0;
                     if($cnt === 0) continue;
                     $col  = $CAT_COLORS[$cat]  ?? '#64748b';
                     $icon = $CAT_ICONS[$cat]   ?? 'fa-tag';
                     $light = $col.'22';
                  ?>
                  <button class="sidebar-cat-btn" data-cat="<?= htmlspecialchars($cat); ?>"
                     onclick="filterCategory('<?= htmlspecialchars($cat); ?>',this)"
                     style="--cat-color:<?= $col; ?>">
                     <div class="sidebar-cat-icon" style="background:<?= $light; ?>;color:<?= $col; ?>">
                        <i class="fas <?= $icon; ?>"></i>
                     </div>
                     <?= htmlspecialchars($cat); ?>
                     <span class="sidebar-cat-count"><?= $cnt; ?></span>
                  </button>
                  <?php endforeach; ?>

                  <?php if(empty($all_cats)): ?>
                     <p style="font-size:1.3rem;color:#94a3b8;padding:.8rem 0;">
                        Belum ada kategori. Tambahkan kategori saat upload produk.
                     </p>
                  <?php endif; ?>

               </div>
            </div>

            <!-- Filter Harga -->
            <div class="sidebar-section">
               <h4>Filter Harga</h4>
               <div class="price-range-row">
                  <input type="number" id="price-min" class="price-input" placeholder="Min" min="0">
                  <span style="font-size:1.4rem;color:#94a3b8">—</span>
                  <input type="number" id="price-max" class="price-input" placeholder="Max" min="0">
               </div>
               <button class="btn-apply-filter" onclick="applyPriceFilter()">
                  <i class="fas fa-filter"></i> Terapkan Filter
               </button>
               <button class="btn-reset-price" onclick="resetPriceFilter()">Reset Harga</button>
            </div>

         </div>
      </aside>

      <!-- MAIN GRID -->
      <div class="shop-grid-wrap">

         <div class="shop-toolbar">
            <div>
               <p class="shop-count">
                  Menampilkan <strong id="count-shown">0</strong> dari
                  <strong id="count-total">0</strong> produk
               </p>
               <div id="active-cat-badge"></div>
            </div>
            <select class="sort-select" onchange="applySort(this.value)">
               <option value="newest">Terbaru</option>
               <option value="price_asc">Harga: Terendah</option>
               <option value="price_desc">Harga: Tertinggi</option>
               <option value="name_asc">Nama: A–Z</option>
            </select>
         </div>

         <div class="shop-products-grid" id="shopGrid"></div>

         <div class="shop-pagination">
            <button class="page-nav-btn" id="btnPrev" onclick="changePage(-1)" disabled>
               <i class="fas fa-arrow-left"></i> Sebelumnya
            </button>
            <span class="page-indicator" id="pageIndicator">1 / 1</span>
            <button class="page-nav-btn" id="btnNext" onclick="changePage(1)">
               Selanjutnya <i class="fas fa-arrow-right"></i>
            </button>
         </div>

      </div>
   </div>
</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
   /* ── Data dari PHP ── */
   const allProducts = <?= json_encode($all_products); ?>;
   const CAT_COLORS  = <?= json_encode($CAT_COLORS); ?>;

   const PER_PAGE  = 9;
   let currentPage = 1;
   let activeCat   = 'all';
   let sortMode    = 'newest';
   let priceMin    = 0;
   let priceMax    = Infinity;
   let filteredData = [...allProducts];

   /* ── Sort ── */
   function sortData(data){
      const d = [...data];
      if(sortMode==='price_asc')  return d.sort((a,b)=>a.price-b.price);
      if(sortMode==='price_desc') return d.sort((a,b)=>b.price-a.price);
      if(sortMode==='name_asc')   return d.sort((a,b)=>a.name.localeCompare(b.name));
      return d;
   }

   /* ── Render ── */
   function renderGrid(){
      const sorted = sortData(filteredData);
      const total  = sorted.length;
      const pages  = Math.ceil(total/PER_PAGE)||1;
      if(currentPage>pages) currentPage=1;

      const start = (currentPage-1)*PER_PAGE;
      const slice = sorted.slice(start, start+PER_PAGE);

      document.getElementById('count-shown').textContent = slice.length + (total>PER_PAGE ? ` (hlm ${currentPage})` : '');
      document.getElementById('count-total').textContent  = total;
      document.getElementById('pageIndicator').textContent = currentPage+' / '+pages;
      document.getElementById('btnPrev').disabled = currentPage<=1;
      document.getElementById('btnNext').disabled = currentPage>=pages;

      /* Active badge */
      const badgeEl = document.getElementById('active-cat-badge');
      if(activeCat!=='all'){
         const col = CAT_COLORS[activeCat]||'#1a2a6c';
         badgeEl.innerHTML = `<span class="active-cat-badge" style="background:${col};">${escHtml(activeCat)}</span>`;
      } else {
         badgeEl.innerHTML = '';
      }

      if(slice.length===0){
         document.getElementById('shopGrid').innerHTML=
            '<div class="shop-empty"><i class="fas fa-box-open"></i><p>Produk tidak ditemukan.</p></div>';
         return;
      }

      document.getElementById('shopGrid').innerHTML = slice.map(p=>{
         const col   = CAT_COLORS[p.category]||'transparent';
         const price = Number(p.price).toLocaleString('id-ID');

         return `
         <form action="" method="post" class="shop-card">
            <input type="hidden" name="pid"   value="${p.id}">
            <input type="hidden" name="name"  value="${escHtml(p.name)}">
            <input type="hidden" name="price" value="${escHtml(p.price)}">
            <input type="hidden" name="image" value="${escHtml(p.image_01)}">

            <div class="shop-card-img">
               <img src="uploaded_img/${escHtml(p.image_01)}" alt="${escHtml(p.name)}" loading="lazy">

               ${p.category ? `<div class="shop-card-cat-ribbon" style="background:${col};">${escHtml(p.category)}</div>` : ''}

               <div class="shop-card-hover-actions">
                  <button type="submit" name="add_to_wishlist" title="Tambah Wishlist"><i class="fas fa-heart"></i></button>
                  <a href="quick_view.php?pid=${p.id}" title="Lihat Detail"><i class="fas fa-eye"></i></a>
               </div>
            </div>

            <div class="shop-card-body">
               <a href="quick_view.php?pid=${p.id}" class="shop-card-name">${escHtml(p.name)}</a>
               <div class="shop-card-price">Rp ${price}</div>
               <div class="shop-card-footer">
                  <input type="number" name="qty" class="shop-card-qty" min="1" max="99" value="1"
                         onkeypress="if(this.value.length==2)return false;">
                  <a href="quick_view.php?pid=${p.id}" class="shop-card-btn shop-card-link-btn">
					 + Keranjang
				  </a>
               </div>
            </div>
         </form>`;
      }).join('');

      if(currentPage>1){
         document.querySelector('.shop-grid-wrap').scrollIntoView({behavior:'smooth',block:'start'});
      }
   }

   /* ── Apply all filters ── */
   function applyFilters(){
      filteredData = allProducts.filter(p=>{
         const matchCat   = activeCat==='all' || (p.category||'')=== activeCat;
         const price      = Number(p.price);
         const matchPrice = price>=priceMin && price<=priceMax;
         return matchCat && matchPrice;
      });
      currentPage=1;
      renderGrid();
   }

   /* ── Category ── */
   function filterCategory(cat, el){
      activeCat = cat;
      document.querySelectorAll('.sidebar-cat-btn').forEach(b=>b.classList.remove('active'));
      el.classList.add('active');
      applyFilters();
   }

   /* ── Price ── */
   function applyPriceFilter(){
      priceMin = parseFloat(document.getElementById('price-min').value)||0;
      priceMax = parseFloat(document.getElementById('price-max').value)||Infinity;
      applyFilters();
   }

   function resetPriceFilter(){
      priceMin=0; priceMax=Infinity;
      document.getElementById('price-min').value='';
      document.getElementById('price-max').value='';
      applyFilters();
   }

   /* ── Sort ── */
   function applySort(val){ sortMode=val; renderGrid(); }

   /* ── Pagination ── */
   function changePage(dir){
      const pages=Math.ceil(filteredData.length/PER_PAGE)||1;
      currentPage=Math.min(Math.max(currentPage+dir,1),pages);
      renderGrid();
   }

   /* ── Escape ── */
   function escHtml(str){
      return String(str??'')
         .replace(/&/g,'&amp;').replace(/</g,'&lt;')
         .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
   }

   /* ── Init ── */
   renderGrid();
</script>
</body>
</html>