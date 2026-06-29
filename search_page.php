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
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }

      /* ===== PAGE ===== */
      .sp-page { max-width: 1200px; margin: 0 auto; padding: 3rem 2.4rem 6rem; }

      /* Breadcrumb */
      .sp-bc {
         font-size: 1.4rem; color: #94a3b8;
         margin-bottom: 2rem; display: flex; align-items: center; gap: .6rem; flex-wrap: wrap;
         animation: fadeUp .4s ease both;
      }
      .sp-bc a { color: #94a3b8; text-decoration: none; transition: color .15s; }
      .sp-bc a:hover { color: #1a2a6c; }
      .sp-bc i { font-size: 1.1rem; color: #cbd5e1; }

      /* ===== SEARCH BAR ===== */
      .sp-search-section {
         margin-bottom: 3.2rem;
         animation: fadeUp .45s ease both;
      }

      .sp-search-title {
         font-size: 2.6rem; font-weight: 800; color: #1e293b;
         margin-bottom: .6rem; letter-spacing: -.4px;
      }

      .sp-search-title span {
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      }

      .sp-search-sub { font-size: 1.4rem; color: #64748b; margin-bottom: 2rem; }

      .sp-search-form {
         display: flex; align-items: center; gap: 0;
         background: #fff; border-radius: 1rem;
         box-shadow: 0 4px 20px rgba(0,0,0,.08);
         overflow: hidden;
         max-width: 68rem;
      }

      .sp-search-form i {
         padding: 0 1.6rem; font-size: 1.6rem; color: #94a3b8;
         flex-shrink: 0;
      }

      .sp-search-input {
         flex: 1; padding: 1.6rem 0; border: none; outline: none;
         font-size: 1.55rem; color: #0f172a; background: transparent;
         font-family: inherit;
      }
      .sp-search-input::placeholder { color: #94a3b8; }

      .sp-search-btn {
         padding: 1.4rem 2.8rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff; border: none; font-size: 1.5rem; font-weight: 700;
         cursor: pointer; font-family: inherit;
         transition: opacity .18s, transform .15s;
         display: flex; align-items: center; gap: .7rem; white-space: nowrap;
         flex-shrink: 0;
      }
      .sp-search-btn:hover { opacity: .88; }

      /* ===== RESULT INFO BAR ===== */
      .sp-result-bar {
         display: flex; align-items: center; justify-content: space-between;
         flex-wrap: wrap; gap: 1rem;
         margin-bottom: 2rem;
         animation: fadeUp .5s ease both;
      }

      .sp-result-count { font-size: 1.4rem; color: #475569; }
      .sp-result-count strong { color: #0f172a; }
      .sp-result-count .qhighlight {
         display: inline-flex; align-items: center;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff; padding: .3rem 1rem; border-radius: 2rem;
         font-size: 1.3rem; font-weight: 700; margin-left: .4rem;
      }

      .sp-sort-select {
         padding: .8rem 1.4rem; border: 1.5px solid #e2e8f0; border-radius: .8rem;
         font-size: 1.3rem; color: #0f172a; background: #fff; outline: none;
         font-family: inherit; cursor: pointer;
      }

      /* Fuzzy notice */
      .sp-fuzzy-notice {
         display: none;
         padding: 1rem 1.4rem; background: #fffbeb; border-left: 3px solid #f59e0b;
         border-radius: .8rem; font-size: 1.3rem; color: #78350f;
         margin-bottom: 1.6rem; align-items: center; gap: .7rem;
         animation: fadeUp .3s ease both;
      }
      .sp-fuzzy-notice.show { display: flex; }

      /* ===== PRODUCT GRID ===== */
      .sp-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(26rem, 1fr));
         gap: 2rem;
      }

      /* ─── Product card ─── */
      .sp-card {
         background: #fff; border-radius: 1.4rem;
         overflow: hidden; position: relative;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         transition: transform .25s, box-shadow .25s;
         display: flex; flex-direction: column;
         animation: fadeUp .5s ease both;
      }
      .sp-card:hover { transform: translateY(-6px); box-shadow: 0 14px 36px rgba(0,0,0,.11); }

      .sp-card:nth-child(1){animation-delay:.05s} .sp-card:nth-child(2){animation-delay:.10s}
      .sp-card:nth-child(3){animation-delay:.15s} .sp-card:nth-child(n+4){animation-delay:.20s}

      .sp-card-img {
         position: relative; aspect-ratio: 1/1;
         overflow: hidden; background: #f2f1ed;
      }
      .sp-card-img img {
         width: 100%; height: 100%; object-fit: cover; display: block;
         transition: transform .5s cubic-bezier(.25,.46,.45,.94);
      }
      .sp-card:hover .sp-card-img img { transform: scale(1.07); }

      /* Category ribbon */
      .sp-cat-ribbon {
         position: absolute; bottom: 1rem; left: 1rem;
         padding: .35rem .9rem; border-radius: 2rem;
         font-size: 1.15rem; font-weight: 700; color: #fff; z-index: 2;
      }

      /* Hover actions */
      .sp-card-actions {
         position: absolute; top: 1.2rem; right: 1.2rem;
         display: flex; flex-direction: column; gap: .8rem;
         opacity: 0; transform: translateX(1rem);
         transition: opacity .22s, transform .22s; z-index: 3;
      }
      .sp-card:hover .sp-card-actions { opacity: 1; transform: translateX(0); }

      .sp-act-btn {
         width: 4rem; height: 4rem; border-radius: 50%;
         background: #fff; border: none; color: #111; font-size: 1.5rem;
         display: flex; align-items: center; justify-content: center;
         box-shadow: 0 3px 10px rgba(0,0,0,.14);
         cursor: pointer; transition: background .18s, color .18s;
         text-decoration: none; font-family: inherit;
      }
      .sp-act-btn:hover { background: #111; color: #fff; }

      /* Card body */
      .sp-card-body {
         padding: 1.6rem 1.8rem 1.8rem;
         display: flex; flex-direction: column; gap: .8rem; flex: 1;
      }

      .sp-card-name {
         font-size: 1.5rem; font-weight: 600; color: #111;
         line-height: 1.4; text-decoration: none;
         display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
      }
      .sp-card-name:hover { color: #1a2a6c; }

      /* Highlight matched text */
      .sp-card-name mark {
         background: #fef08a; color: #0f172a; border-radius: .2rem; padding: 0 .1rem;
      }

      .sp-card-price { font-size: 1.8rem; font-weight: 800; color: #e11d48; }

      .sp-card-footer {
         display: flex; gap: .8rem; align-items: center; margin-top: auto;
      }
      .sp-qty {
         width: 5.5rem; height: 4rem; border: 1.5px solid #e2e8f0; border-radius: .7rem;
         text-align: center; font-size: 1.5rem; color: #111; font-family: inherit;
         background: #f8fafc; flex-shrink: 0; outline: none;
      }
      .sp-qty:focus { border-color: #4f6ef7; }
      .sp-cart-btn {
         flex: 1; height: 4rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff; border: none; border-radius: .7rem;
         font-size: 1.35rem; font-weight: 700; cursor: pointer;
         font-family: inherit; text-transform: uppercase; letter-spacing: .04em;
         transition: opacity .18s, transform .15s;
      }
      .sp-cart-btn:hover { opacity: .88; transform: scale(.98); }

      /* ===== EMPTY STATE ===== */
      .sp-empty {
         grid-column: 1/-1; text-align: center;
         padding: 6rem 2rem; background: #fff; border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.04); color: #94a3b8;
         animation: fadeUp .5s ease both;
      }
      .sp-empty i { font-size: 5.6rem; display: block; margin-bottom: 1.6rem; color: #cbd5e1; }
      .sp-empty h3 { font-size: 2rem; font-weight: 700; color: #475569; margin-bottom: .8rem; }
      .sp-empty p  { font-size: 1.4rem; margin-bottom: 2.4rem; line-height: 1.7; }
      .sp-empty-tip {
         font-size: 1.3rem; color: #94a3b8; display: flex; flex-wrap: wrap;
         justify-content: center; gap: .6rem; margin-bottom: 2.4rem;
      }
      .sp-empty-tip-item {
         background: #f1f5f9; border-radius: 2rem; padding: .4rem 1rem; cursor: pointer;
         transition: background .15s, color .15s;
      }
      .sp-empty-tip-item:hover { background: #eff2ff; color: #1a2a6c; }

      .btn-shop-all {
         display: inline-flex; align-items: center; gap: .7rem;
         padding: 1.2rem 2.8rem; background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff; border-radius: 1rem; font-size: 1.5rem; font-weight: 700;
         text-decoration: none; transition: transform .15s, box-shadow .2s;
      }
      .btn-shop-all:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }

      /* ===== INITIAL STATE (no query) ===== */
      .sp-initial {
         text-align: center; padding: 5rem 2rem;
         animation: fadeUp .5s ease both;
      }
      .sp-initial i { font-size: 5rem; color: #cbd5e1; display: block; margin-bottom: 1.6rem; }
      .sp-initial h3 { font-size: 2rem; font-weight: 700; color: #475569; margin-bottom: .8rem; }
      .sp-initial p  { font-size: 1.4rem; color: #94a3b8; margin-bottom: 2.4rem; }

      /* Category quick filter chips */
      .sp-cat-chips {
         display: flex; flex-wrap: wrap; gap: .8rem; justify-content: center;
         margin-bottom: .4rem;
      }
      .sp-cat-chip {
         display: inline-flex; align-items: center; gap: .5rem;
         padding: .6rem 1.4rem; border-radius: 3rem; font-size: 1.3rem; font-weight: 600;
         cursor: pointer; text-decoration: none; transition: transform .15s, box-shadow .15s;
         color: #fff;
      }
      .sp-cat-chip:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.15); }

      @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

      @media(max-width:900px){ .sp-grid{grid-template-columns:repeat(2,1fr)} }
      @media(max-width:520px){ .sp-page{padding:2rem 1.4rem 4rem} .sp-grid{grid-template-columns:1fr} }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="sp-page">

   <!-- Breadcrumb -->
   <nav class="sp-bc">
      <a href="home.php">Beranda</a>
      <i class="fas fa-chevron-right"></i>
      <a href="shop.php">Toko</a>
      <i class="fas fa-chevron-right"></i>
      <span style="color:#475569;font-weight:600;">Pencarian</span>
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

         <p style="font-size:1.4rem;color:#475569;font-weight:600;margin-bottom:1.2rem;">Atau cari berdasarkan kategori:</p>

         <div class="sp-cat-chips">
            <?php
            $CATS = [
               'Totebag'=>'#4f6ef7','Slingbag'=>'#059669','Dompet'=>'#f59e0b',
               'Heels'=>'#e11d48','Flat Shoes'=>'#0891b2','Top Handle'=>'#7c3aed',
               'Clutch'=>'#ea580c','Ransel'=>'#65a30d','Waistbag'=>'#db2777',
            ];
            foreach($CATS as $cat => $col):
            ?>
            <a href="search_page.php?search=<?= urlencode($cat); ?>" class="sp-cat-chip" style="background:<?= $col; ?>;">
               <?= htmlspecialchars($cat); ?>
            </a>
            <?php endforeach; ?>
         </div>
      </div>

   <?php else: ?>

   <!-- Fuzzy notice (shown by JS if needed) -->
   <div class="sp-fuzzy-notice" id="spFuzzyNotice">
      <i class="fas fa-wand-magic-sparkles" style="color:#f59e0b;flex-shrink:0"></i>
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

const CAT_COLORS = {
   'Totebag':'#4f6ef7','Slingbag':'#059669','Dompet':'#f59e0b',
   'Heels':'#e11d48','Flat Shoes':'#0891b2','Top Handle':'#7c3aed',
   'Clutch':'#ea580c','Ransel':'#65a30d','Waistbag':'#db2777',
};

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
               ${Object.keys(CAT_COLORS).map(c=>`<span class="sp-empty-tip-item" onclick="doSearch('${c}')">${escHtml(c)}</span>`).join('')}
            </div>
            <a href="shop.php" class="btn-shop-all"><i class="fas fa-store"></i> Lihat Semua Produk</a>
         </div>`;
      return;
   }

   grid.innerHTML = data.map(p => {
      const col   = CAT_COLORS[p.category] || 'transparent';
      const price = Number(p.price).toLocaleString('id-ID');
      const hname = highlightText(p.name, SP_QUERY);

      return `
      <form action="" method="post" class="sp-card">
         <input type="hidden" name="pid"   value="${p.id}">
         <input type="hidden" name="name"  value="${escHtml(p.name)}">
         <input type="hidden" name="price" value="${escHtml(p.price)}">
         <input type="hidden" name="image" value="${escHtml(p.image)}">

         <div class="sp-card-img">
            <img src="uploaded_img/${escHtml(p.image)}" alt="${escHtml(p.name)}" loading="lazy" onerror="this.style.opacity='.3'">
            ${p.category ? `<div class="sp-cat-ribbon" style="background:${col};">${escHtml(p.category)}</div>` : ''}
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

function escHtml(str){
   return String(str??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
<?php endif; ?>

</body>
</html>