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

// Icon per kategori
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
</head>
<body class="shop-page php-shop">

<?php include 'components/user_header.php'; ?>

<div class="shop-page-wrap">

   <div class="shop-breadcrumb">
      <a href="index.php">Beranda</a><span>/</span><span>Toko</span>
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
                  <button class="sidebar-cat-btn active category-theme category-theme-all" data-cat="all"
                     onclick="filterCategory('all',this)">
                     <div class="sidebar-cat-icon">
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
                     $icon = $CAT_ICONS[$cat]   ?? 'fa-tag';
                  ?>
                  <button class="sidebar-cat-btn <?= category_theme_class($cat); ?>" data-cat="<?= htmlspecialchars($cat); ?>"
                     onclick="filterCategory('<?= htmlspecialchars($cat); ?>',this)">
                     <div class="sidebar-cat-icon">
                        <i class="fas <?= $icon; ?>"></i>
                     </div>
                     <?= htmlspecialchars($cat); ?>
                     <span class="sidebar-cat-count"><?= $cnt; ?></span>
                  </button>
                  <?php endforeach; ?>

                  <?php if(empty($all_cats)): ?>
                     <p class="u-inline-style-029">
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
                  <span class="u-inline-style-030">—</span>
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
         badgeEl.innerHTML = `<span class="active-cat-badge ${categoryClass(activeCat)}">${escHtml(activeCat)}</span>`;
      } else {
         badgeEl.innerHTML = '';
      }

      if(slice.length===0){
         document.getElementById('shopGrid').innerHTML=
            '<div class="shop-empty"><i class="fas fa-box-open"></i><p>Produk tidak ditemukan.</p></div>';
         return;
      }

      document.getElementById('shopGrid').innerHTML = slice.map(p=>{
         const price = Number(p.price).toLocaleString('id-ID');

         return `
         <form action="" method="post" class="shop-card">
            <input type="hidden" name="pid"   value="${p.id}">
            <input type="hidden" name="name"  value="${escHtml(p.name)}">
            <input type="hidden" name="price" value="${escHtml(p.price)}">
            <input type="hidden" name="image" value="${escHtml(p.image_01)}">

            <div class="shop-card-img">
               <img src="uploaded_img/${escHtml(p.image_01)}" alt="${escHtml(p.name)}" loading="lazy">

               ${p.category ? `<div class="shop-card-cat-ribbon ${categoryClass(p.category)}">${escHtml(p.category)}</div>` : ''}

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

   function categoryClass(cat){
      const slug = String(cat || 'default')
         .toLowerCase()
         .replace(/[^a-z0-9]+/g, '-')
         .replace(/^-|-$/g, '') || 'default';
      return `category-theme category-theme-${slug}`;
   }

   /* ── Init ── */
   renderGrid();
</script>
</body>
</html>
