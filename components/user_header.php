<?php
/*
 * =============================================
 *  user_header.php — GALS COLLECTION
 * =============================================
 */


include_once 'components/crypto.php';

if (isset($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="gc-alert-success">
         <span><i class="fas fa-circle-check"></i> ' . htmlspecialchars($msg) . '</span>
         <i class="fas fa-times gc-alert-close" onclick="this.parentElement.remove();"></i>
      </div>';
   }
}

$uid = (!empty($user_id)) ? $user_id : 0;

$count_wishlist = $conn->prepare("SELECT id FROM `wishlist` WHERE user_id = ?");
$count_wishlist->execute([$uid]);
$total_wishlist = $count_wishlist->rowCount();

$count_cart = $conn->prepare("SELECT id FROM `cart` WHERE user_id = ?");
$count_cart->execute([$uid]);
$total_cart = $count_cart->rowCount();

$unread_chat = 0;
if (!empty($user_id)) {
   $q_unread = $conn->prepare("SELECT COUNT(*) AS total FROM `chat_messages` WHERE user_id = ? AND sender = 'admin' AND is_read = 0");
   $q_unread->execute([$user_id]);
   $r_unread  = $q_unread->fetch(PDO::FETCH_ASSOC);
   $unread_chat = (int)($r_unread['total'] ?? 0);
}

// ─── PERBAIKAN: Ambil & dekripsi nama dengan benar ───
// ─── PERBAIKAN: Ambil & dekripsi nama dengan benar ───
// ─── AMBIL NAMA USER UNTUK DROPDOWN HEADER ───
// ─── AMBIL & DEKRIPSI NAMA USER UNTUK HEADER ───
// ─── AMBIL NAMA USER UNTUK HEADER ───
$header_profile_name = 'Pengguna';

if(!empty($_SESSION['user_name']) && $_SESSION['user_name'] !== 'Pengguna'){
   $header_profile_name = trim($_SESSION['user_name']);
}else if(!empty($user_id)){
   $q_name = $conn->prepare("SELECT name FROM `users` WHERE id = ? LIMIT 1");
   $q_name->execute([$user_id]);
   $r_name = $q_name->fetch(PDO::FETCH_ASSOC);

   if($r_name && !empty($r_name['name'])){
      $name_from_db = aes_decrypt_display($r_name['name'], 'Pengguna');

      if($name_from_db !== '' && $name_from_db !== 'Pengguna'){
         $header_profile_name = $name_from_db;
         $_SESSION['user_name'] = $name_from_db;
      }
   }
}

$header_first_name_raw = explode(' ', $header_profile_name)[0];
$header_first_name = htmlspecialchars($header_first_name_raw, ENT_QUOTES, 'UTF-8');

$header_initial = strtoupper(mb_substr($header_profile_name, 0, 1, 'UTF-8'));
$header_initial = htmlspecialchars($header_initial, ENT_QUOTES, 'UTF-8');

$current_page = basename($_SERVER['PHP_SELF']);
$is_home      = ($current_page === 'index.php');
$gc_class     = $is_home ? 'gc-home' : 'gc-other';
$script_dir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_url     = ($script_dir === '/' || $script_dir === '.' || $script_dir === '\\') ? '' : rtrim($script_dir, '/');
$base_url     = htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:opsz,wght@9..40,300;400;500;600&display=swap" rel="stylesheet">
<?php /* Add class to html element */ ?>
<script>document.documentElement.classList.add('<?= $gc_class; ?>');</script>

<header class="gc-header" id="gcHeader">
   <div class="gc-inner">

      <!-- ── LEFT: NAV ── -->
      <nav class="gc-nav" aria-label="Navigasi Utama">
         <a href="<?= $base_url; ?>/index.php"   class="<?= $current_page==='index.php'   ? 'gc-active':''; ?>">Beranda</a>

         <div class="gc-nav-item">
            <a href="<?= $base_url; ?>/shop.php" class="<?= $current_page==='shop.php' ? 'gc-active':''; ?>">
               Produk <i class="fas fa-chevron-down u-inline-style-050"></i>
            </a>
            <div class="gc-dropdown">
               <?php
               $CATS = [
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
               foreach($CATS as $cat => $icon):
               ?>
               <a href="<?= $base_url; ?>/shop.php?cat=<?= urlencode($cat); ?>" class="gc-drop-link">
                  <div class="gc-drop-icon <?= category_theme_class($cat); ?>">
                     <i class="fas <?= $icon; ?>"></i>
                  </div>
                  <?= htmlspecialchars($cat); ?>
               </a>
               <?php endforeach; ?>
            </div>
         </div>

         <a href="<?= $base_url; ?>/orders.php"  class="<?= in_array($current_page,['orders.php','order_detail.php']) ? 'gc-active':''; ?>">Pesanan</a>
         <a href="<?= $base_url; ?>/about.php"   class="<?= $current_page==='about.php'   ? 'gc-active':''; ?>">Tentang</a>
      </nav>

      <!-- ── CENTER: LOGO ── -->
      <a href="<?= $base_url; ?>/index.php" class="gc-logo" aria-label="Gals Collection">
         <img src="images/LogoGals/LogoGalsTerbaru.png" alt="Gals Collection" class="gc-logo__img">
         <span class="gc-logo__sub">Est. 2020 · Pekanbaru</span>
      </a>

      <!-- ── RIGHT: ACTIONS ── -->
      <div class="gc-actions">

         <!-- Search -->
         <a href="<?= $base_url; ?>/search_page.php" class="gc-icon-btn" aria-label="Cari">
            <i class="fas fa-search"></i>
         </a>

         <!-- Wishlist -->
         <a href="<?= $base_url; ?>/wishlist.php" class="gc-icon-btn" aria-label="Wishlist">
            <i class="fas fa-heart"></i>
            <?php if($total_wishlist > 0): ?>
               <span class="gc-badge"><?= $total_wishlist; ?></span>
            <?php endif; ?>
         </a>

         <!-- Cart -->
         <a href="<?= $base_url; ?>/cart.php" class="gc-icon-btn" aria-label="Keranjang">
            <i class="fas fa-shopping-bag"></i>
            <?php if($total_cart > 0): ?>
               <span class="gc-badge"><?= $total_cart; ?></span>
            <?php endif; ?>
         </a>

         <!-- Chat -->
         <a href="<?= $base_url; ?>/chat.php" class="gc-icon-btn" aria-label="Chat">
            <i class="fas fa-comment-dots"></i>
            <?php if($unread_chat > 0): ?>
               <span class="gc-badge"><?= $unread_chat; ?></span>
            <?php endif; ?>
         </a>

         <div class="gc-divider"></div>

         <!-- User: logged in -->
         <?php if(!empty($user_id)): ?>
         <div class="gc-user-wrap" id="gcUserWrap">
            <button type="button" class="gc-user-chip" onclick="gcToggleUser(event)">
			<div class="gc-user-chip__av"><?= $header_initial; ?></div>
			<?= $header_first_name; ?>
               <i class="fas fa-chevron-down gc-user-chip__caret"></i>
            </button>

            <div class="gc-user-popup" id="gcUserPopup">
               <div class="gc-popup-top">
                  <div class="gc-popup-av"><?= $header_initial; ?></div>
                  <div>
                     <div class="gc-popup-label">Akun Saya</div>
                     <div class="gc-popup-name"><?= htmlspecialchars($header_profile_name); ?></div>
                  </div>
               </div>
               <div class="gc-popup-hr"></div>
               <div class="gc-popup-actions">
                  <a href="<?= $base_url; ?>/update_user.php" class="gc-popup-btn gc-popup-btn--primary">
                     <i class="fas fa-user-edit"></i> Update Profil
                  </a>
                  <a href="<?= $base_url; ?>/components/user_logout.php" class="gc-popup-btn gc-popup-btn--ghost"
                     onclick="return confirm('Yakin mau keluar?');">
                     <i class="fas fa-right-from-bracket"></i> Logout
                  </a>
               </div>
            </div>
         </div>

         <!-- User: guest -->
         <?php else: ?>
         <a href="<?= $base_url; ?>/user_login.php" class="gc-user-chip">
            <div class="gc-user-chip__av"><i class="fas fa-user u-inline-style-051"></i></div>
            Masuk
         </a>
         <?php endif; ?>

         <!-- Hamburger -->
         <button class="gc-hamburger" id="gcHamburger" type="button" onclick="gcToggleDrawer()">
            <span></span><span></span><span></span>
         </button>

      </div>
   </div>
</header>

<!-- MOBILE DRAWER -->
<div class="gc-drawer" id="gcDrawer">
   <div class="gc-drawer__overlay" onclick="gcCloseDrawer()"></div>
   <div class="gc-drawer__panel">

      <a href="<?= $base_url; ?>/index.php" class="gc-drawer-logo">
         <img src="images/LogoGals/LogoGalsTerbaru.png" alt="Gals Collection">
      </a>

      <nav class="gc-drawer-nav">
         <a href="<?= $base_url; ?>/index.php"   class="<?= $current_page==='index.php'   ? 'gc-active':''; ?>"><span>Beranda</span><i class="fas fa-chevron-right u-inline-style-052"></i></a>
         <a href="<?= $base_url; ?>/shop.php"   class="<?= $current_page==='shop.php'   ? 'gc-active':''; ?>"><span>Toko</span><i class="fas fa-chevron-right u-inline-style-052"></i></a>
         <a href="<?= $base_url; ?>/orders.php" class="<?= in_array($current_page,['orders.php','order_detail.php']) ? 'gc-active':''; ?>"><span>Pesanan Saya</span><i class="fas fa-chevron-right u-inline-style-052"></i></a>
         <a href="<?= $base_url; ?>/about.php"  class="<?= $current_page==='about.php'  ? 'gc-active':''; ?>"><span>Tentang Kami</span><i class="fas fa-chevron-right u-inline-style-052"></i></a>
      </nav>

      <div class="gc-drawer-hr"></div>

      <div class="gc-drawer-extras">
         <a href="<?= $base_url; ?>/wishlist.php">
            <i class="fas fa-heart u-inline-style-053"></i> Wishlist
            <?php if($total_wishlist > 0): ?><span class="gc-drawer-count"><?= $total_wishlist; ?></span><?php endif; ?>
         </a>
         <a href="<?= $base_url; ?>/cart.php">
            <i class="fas fa-shopping-bag u-inline-style-053"></i> Keranjang
            <?php if($total_cart > 0): ?><span class="gc-drawer-count"><?= $total_cart; ?></span><?php endif; ?>
         </a>
         <a href="<?= $base_url; ?>/chat.php">
            <i class="fas fa-comment-dots u-inline-style-053"></i> Chat Admin
            <?php if($unread_chat > 0): ?><span class="gc-drawer-count"><?= $unread_chat; ?></span><?php endif; ?>
         </a>

         <div class="gc-drawer-hr"></div>

         <?php if(!empty($user_id)): ?>
            <a href="<?= $base_url; ?>/update_user.php"><i class="fas fa-user-circle u-inline-style-054"></i><?= htmlspecialchars($header_first_name); ?> — Profil</a>
            <a href="<?= $base_url; ?>/components/user_logout.php" onclick="return confirm('Yakin mau keluar?');"><i class="fas fa-sign-out-alt u-inline-style-054"></i>Logout</a>
         <?php else: ?>
            <a href="<?= $base_url; ?>/user_login.php"><i class="fas fa-sign-in-alt u-inline-style-053"></i>Login</a>
            <a href="<?= $base_url; ?>/user_register.php"><i class="fas fa-user-plus u-inline-style-053"></i>Register</a>
         <?php endif; ?>
      </div>

   </div>
</div>

<script>
   /* ── Scroll: add class when scrolled ── */
   const gcHeader = document.getElementById('gcHeader');

   window.addEventListener('scroll', () => {
      gcHeader.classList.toggle('scrolled', window.scrollY > 8);
   }, { passive: true });

   /* ── Search ── */
   function gcToggleSearch(){
      const wrap = document.getElementById('gcSearchWrap');
      const inp  = document.getElementById('gcSearchInput');
      const icon = document.getElementById('gcSearchIcon');
      const open = wrap.classList.toggle('open');
      icon.className = open ? 'fas fa-times' : 'fas fa-search';
      if(open) inp.focus(); else inp.value = '';
   }

   function gcCloseSearch(){
      document.getElementById('gcSearchWrap').classList.remove('open');
      document.getElementById('gcSearchIcon').className = 'fas fa-search';
      document.getElementById('gcSearchInput').value = '';
   }

   /* ── User popup ── */
   function gcToggleUser(e){
      e.stopPropagation();
      document.getElementById('gcUserWrap')?.classList.toggle('open');
   }

   /* ── Mobile drawer ── */
   function gcToggleDrawer(){
      const d = document.getElementById('gcDrawer');
      const h = document.getElementById('gcHamburger');
      const open = d.classList.toggle('open');
      h.classList.toggle('open', open);
      document.body.style.overflow = open ? 'hidden' : '';
   }

   function gcCloseDrawer(){
      document.getElementById('gcDrawer').classList.remove('open');
      document.getElementById('gcHamburger').classList.remove('open');
      document.body.style.overflow = '';
   }

   /* ── Close on outside click / Escape ── */
   document.addEventListener('click', e => {
      const uw = document.getElementById('gcUserWrap');
      if(uw && !uw.contains(e.target)) uw.classList.remove('open');
   });

   document.addEventListener('keydown', e => {
      if(e.key === 'Escape'){
         gcCloseDrawer();
         gcCloseSearch();
         document.getElementById('gcUserWrap')?.classList.remove('open');
      }
   });
</script>
