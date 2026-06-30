<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';

// ─── Get search query ───
// Support GET (dari header search redirect) dan POST (dari form di halaman ini)
$search_query = '';
if(!empty($_GET['search'])){
   $search_query = trim($_GET['search']);
} elseif(!empty($_POST['search_box'])){
   $search_query = trim($_POST['search_box']);
}

// ─── Fetch ALL products for fuzzy match ───
$all_products_raw = [];
$select_all = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC");
$select_all->execute();
$all_products_raw = $select_all->fetchAll(PDO::FETCH_ASSOC);

// ─── PHP-side simple fuzzy filter ───
// We pass everything to JS for full fuzzy, but do a PHP pre-filter for SQL-based exact/like results first
$exact_results = [];
if($search_query !== ''){
   $like_q = '%' . $search_query . '%';
   $q_sql = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ? OR details LIKE ? OR category LIKE ? ORDER BY id DESC");
   $q_sql->execute([$like_q, $like_q, $like_q]);
   $exact_results = $q_sql->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $search_query ? 'Hasil "'.htmlspecialchars($search_query).'"' : 'Pencarian'; ?> — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="php-search-page">

<?php include 'components/user_header.php'; ?>

<div class="sp-page">

   <!-- Breadcrumb -->
   <nav class="sp-bc">
      <a href="index.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="shop.php">Toko</a>
      <i class="fas fa-chevron-right"></i>
      <span class="u-inline-style-006">Pencarian</span>
   </nav>

   <!-- Search bar -->
   <div class="sp-search-section">
      <h1 class="sp-search-title">
         <?php if($search_query): ?>
            Hasil untuk <span>"<?= htmlspecialchars($search_query); ?>"</span>
         <?php else: ?>
            Cari <span>Produk</span>
         <?php endif; ?>
      </h1>
      <p class="sp-search-sub">Temukan produk fashion favoritmu di Gals Collection</p>

      <form action="search_page.php" method="GET" class="sp-search-form">
         <i class="fas fa-search"></i>
         <input
            type="text"
            name="search"
            id="spSearchInput"
            class="sp-search-input"
            placeholder="Cari produk, kategori, brand…"
            value="<?= htmlspecialchars($search_query); ?>"
            maxlength="100"
            autocomplete="off"
         >
         <button type="submit" class="sp-search-btn">
            <i class="fas fa-search"></i> Cari
         </button>
      </form>
   </div>

   <!-- Main content area -->
   <?php if($search_query === ''): ?>

      <!-- No query: show initial state -->
      <div class="sp-initial">
         <i class="fas fa-magnifying-glass"></i>
         <h3>Mau cari apa hari ini?</h3>
         <p>Ketik nama produk, kategori, atau kata kunci di kolom pencarian di atas.<br>Kami juga mengerti jika ada sedikit salah ketik 😉</p>

         <p class="u-inline-style-026">Atau cari berdasarkan kategori:</p>

         <div class="sp-cat-chips">
            <?php
            $CATS = ['Totebag','Slingbag','Dompet','Heels','Flat Shoes','Top Handle','Clutch','Ransel','Waistbag'];
            foreach($CATS as $cat):
            ?>
            <a href="search_page.php?search=<?= urlencode($cat); ?>" class="sp-cat-chip <?= category_theme_class($cat); ?>">
               <?= htmlspecialchars($cat); ?>
            </a>
            <?php endforeach; ?>
         </div>
      </div>

   <?php else: ?>

   <!-- Fuzzy notice (shown by JS if needed) -->
   <div class="sp-fuzzy-notice" id="spFuzzyNotice">
      <i class="fas fa-wand-magic-sparkles u-inline-style-027"></i>
      <span id="spFuzzyText">Menampilkan hasil terbaik yang cocok dengan pencarianmu.</span>
   </div>

   <!-- Result bar -->
   <div class="sp-result-bar" id="spResultBar">
      <div class="sp-result-count">
         Menampilkan <strong id="spCountNum">0</strong> produk untuk
         <span class="qhighlight"><?= htmlspecialchars($search_query); ?></span>
      </div>
      <select class="sp-sort-select" id="spSort" onchange="spRender()">
         <option value="relevance">Relevansi</option>
         <option value="newest">Terbaru</option>
         <option value="price_asc">Harga: Terendah</option>
         <option value="price_desc">Harga: Tertinggi</option>
         <option value="name_asc">Nama: A–Z</option>
      </select>
   </div>

   <!-- Product grid -->
   <div class="sp-grid" id="spGrid">
      <!-- Filled by JS -->
   </div>

   <?php endif; ?>

</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

<?php if($search_query !== ''): ?>
<script>
/* ════════════════════════════════════════════════
   ALL PRODUCTS DATA
   ════════════════════════════════════════════════ */
const SP_PRODUCTS = <?= json_encode(array_map(function($p){
   return [
      'id'       => (int)$p['id'],
      'name'     => $p['name'],
      'price'    => (int)$p['price'],
      'image'    => $p['image_01'],
      'category' => $p['category'] ?? '',
      'details'  => substr($p['details'] ?? '', 0, 100),
   ];
}, $all_products_raw)); ?>;

const SP_EXACT = <?= json_encode(array_map(function($p){
   return (int)$p['id'];
}, $exact_results)); ?>;

const SP_QUERY = <?= json_encode($search_query); ?>;

const CAT_NAMES = ['Totebag','Slingbag','Dompet','Heels','Flat Shoes','Top Handle','Clutch','Ransel','Waistbag'];

/* ════════════════════════════════════════════════
   JARO-WINKLER FUZZY MATCH
   ════════════════════════════════════════════════ */
function jaroSim(s1, s2){
   if(s1===s2) return 1;
   const l1=s1.length, l2=s2.length;
   if(!l1||!l2) return 0;
   const md = Math.max(Math.floor(Math.max(l1,l2)/2)-1, 0);
   const m1 = new Array(l1).fill(false), m2 = new Array(l2).fill(false);
   let matches=0, trans=0;
   for(let i=0;i<l1;i++){
      const lo=Math.max(0,i-md), hi=Math.min(i+md+1,l2);
      for(let j=lo;j<hi;j++){
         if(m2[j]||s1[i]!==s2[j]) continue;
         m1[i]=true; m2[j]=true; matches++; break;
      }
   }
   if(!matches) return 0;
   let k=0;
   for(let i=0;i<l1;i++){
      if(!m1[i]) continue;
      while(!m2[k]) k++;
      if(s1[i]!==s2[k]) trans++;
      k++;
   }
   return (matches/l1 + matches/l2 + (matches-trans/2)/matches)/3;
}

function jaroWinkler(s1, s2, p=0.1){
   const jaro = jaroSim(s1, s2);
   let pfx=0;
   for(let i=0;i<Math.min(4,Math.min(s1.length,s2.length));i++){
      if(s1[i]===s2[i]) pfx++; else break;
   }
   return jaro + pfx*p*(1-jaro);
}

function scoreProduct(p, q){
   const name = p.name.toLowerCase();
   const cat  = (p.category||'').toLowerCase();
   const det  = (p.details||'').toLowerCase();
   const full = name + ' ' + cat + ' ' + det;
   q = q.toLowerCase().trim();
   if(!q) return 0;

   // Exact substring → highest
   if(name.includes(q)) return 1.0;
   if(cat.includes(q))  return 0.95;

   // Word-by-word fuzzy
   const qWords    = q.split(/\s+/);
   const nameWords = full.split(/\s+/);
   let total = 0;
   for(const qw of qWords){
      let best = 0;
      for(const nw of nameWords){
         const sim = jaroWinkler(qw, nw);
         if(sim > best) best = sim;
         if(nw.startsWith(qw) && qw.length >= 3) best = Math.max(best, 0.94);
      }
      total += best;
   }
   return total / qWords.length;
}

const THRESHOLD = 0.72;

/* ════════════════════════════════════════════════
   HIGHLIGHT matched text in product name
   ════════════════════════════════════════════════ */
function highlightText(text, query){
   if(!query) return escHtml(text);
   const words = query.toLowerCase().split(/\s+/).filter(Boolean);
   let result  = escHtml(text);
   for(const w of words){
      if(w.length < 2) continue;
      const regex = new RegExp(`(${w.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
      result = result.replace(regex, '<mark>$1</mark>');
   }
   return result;
}

/* ════════════════════════════════════════════════
   RENDER
   ════════════════════════════════════════════════ */
let spSorted = [];

function spRender(){
   const sort  = document.getElementById('spSort')?.value || 'relevance';
   let data    = [...spSorted];

   if(sort === 'price_asc')  data.sort((a,b) => a.price - b.price);
   else if(sort === 'price_desc') data.sort((a,b) => b.price - a.price);
   else if(sort === 'name_asc')   data.sort((a,b) => a.name.localeCompare(b.name));
   else if(sort === 'newest')     data.sort((a,b) => b.id - a.id);
   // relevance: keep spSorted order

   document.getElementById('spCountNum').textContent = data.length;

   const grid = document.getElementById('spGrid');

   if(!data.length){
      grid.innerHTML = `
         <div class="sp-empty">
            <i class="fas fa-box-open"></i>
            <h3>Produk tidak ditemukan</h3>
            <p>Kami tidak menemukan produk yang cocok dengan <strong>"${escHtml(SP_QUERY)}"</strong>.<br>Coba kata kunci lain atau pilih kategori berikut:</p>
            <div class="sp-empty-tip">
               ${CAT_NAMES.map(c=>`<span class="sp-empty-tip-item" onclick="doSearch('${c}')">${escHtml(c)}</span>`).join('')}
            </div>
            <a href="shop.php" class="btn-shop-all"><i class="fas fa-store"></i> Lihat Semua Produk</a>
         </div>`;
      return;
   }

   grid.innerHTML = data.map(p => {
      const price = Number(p.price).toLocaleString('id-ID');
      const hname = highlightText(p.name, SP_QUERY);

      return `
      <form action="" method="post" class="sp-card">
         <input type="hidden" name="pid"   value="${p.id}">
         <input type="hidden" name="name"  value="${escHtml(p.name)}">
         <input type="hidden" name="price" value="${escHtml(p.price)}">
         <input type="hidden" name="image" value="${escHtml(p.image)}">

         <div class="sp-card-img">
            <img src="uploaded_img/${escHtml(p.image)}" alt="${escHtml(p.name)}" loading="lazy" onerror="markImageError(this)">
            ${p.category ? `<div class="sp-cat-ribbon ${categoryClass(p.category)}">${escHtml(p.category)}</div>` : ''}
            <div class="sp-card-actions">
               <button type="submit" name="add_to_wishlist" class="sp-act-btn" title="Wishlist"><i class="fas fa-heart"></i></button>
               <a href="quick_view.php?pid=${p.id}" class="sp-act-btn" title="Lihat Detail"><i class="fas fa-eye"></i></a>
            </div>
         </div>

         <div class="sp-card-body">
            <a href="quick_view.php?pid=${p.id}" class="sp-card-name">${hname}</a>
            <div class="sp-card-price">Rp ${price}</div>
            <div class="sp-card-footer">
               <input type="number" name="qty" class="sp-qty" min="1" max="99" value="1" onkeypress="if(this.value.length==2)return false;">
               <button type="submit" name="add_to_cart" class="sp-cart-btn">+ Keranjang</button>
            </div>
         </div>
      </form>`;
   }).join('');
}

/* ════════════════════════════════════════════════
   INIT — run fuzzy search
   ════════════════════════════════════════════════ */
(function init(){
   const q = SP_QUERY.trim();

   // Score all products
   let scored = SP_PRODUCTS.map(p => ({
      ...p,
      score: scoreProduct(p, q),
      isExact: SP_EXACT.includes(p.id)
   }));

   // Filter by threshold OR exact SQL match
   scored = scored.filter(p => p.isExact || p.score >= THRESHOLD);

   // Sort by relevance (exact first, then by score)
   scored.sort((a,b) => {
      if(a.isExact && !b.isExact) return -1;
      if(!a.isExact && b.isExact) return 1;
      return b.score - a.score;
   });

   spSorted = scored;

   // Show fuzzy notice if any result came from fuzzy (not exact SQL)
   const hasFuzzyOnly = scored.some(p => !p.isExact && p.score >= THRESHOLD);
   if(hasFuzzyOnly && scored.length > 0){
      const notice = document.getElementById('spFuzzyNotice');
      const txt    = document.getElementById('spFuzzyText');
      const fuzzyCount = scored.filter(p => !p.isExact).length;
      if(fuzzyCount > 0){
         txt.innerHTML = `Kami juga menampilkan <strong>${fuzzyCount} produk serupa</strong> yang mungkin kamu maksud meski ada perbedaan penulisan.`;
         notice.classList.add('show');
      }
   }

   spRender();
})();

function doSearch(q){
   window.location.href = 'search_page.php?search=' + encodeURIComponent(q);
}

function categoryClass(cat){
   const slug = String(cat || 'default')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '') || 'default';
   return `category-theme category-theme-${slug}`;
}

function markImageError(img){
   img.classList.add('img-load-error');
}

function escHtml(str){
   return String(str??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
<?php endif; ?>

</body>
</html>
