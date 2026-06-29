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
$is_home      = ($current_page === 'home.php');
$gc_class     = $is_home ? 'gc-home' : 'gc-other';
$script_dir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_url     = ($script_dir === '/' || $script_dir === '.' || $script_dir === '\\') ? '' : rtrim($script_dir, '/');
$base_url     = htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:opsz,wght@9..40,300;400;500;600&display=swap" rel="stylesheet">

<style>
/* ══════════════════════════════════════════════
   GALS COLLECTION — USER HEADER
   Fixed: header always sticks to top on scroll
   ══════════════════════════════════════════════ */

/* Push body content below header height */
body { padding-top: 72px !important; }

/* ─── HEADER: FIXED at top ─── */
.gc-header {
   position: fixed !important;
   top: 0 !important;
   left: 0 !important;
   right: 0 !important;
   z-index: 10000 !important;
   height: 72px !important;
   transition: box-shadow .35s ease, background .35s ease !important;
}

/* HOME page = dark gradient */
.gc-home .gc-header {
   background: radial-gradient(circle at 30% 50%, #1a2a6c, #0f2027, #000) !important;
   border-bottom: 1px solid rgba(255,255,255,.10) !important;
}
.gc-home .gc-header.scrolled {
   box-shadow: 0 4px 32px rgba(0,0,0,.45) !important;
}

/* Other pages = white */
.gc-other .gc-header {
   background: rgba(255,255,255,.97) !important;
   border-bottom: 1px solid rgba(0,0,0,.08) !important;
   backdrop-filter: blur(12px);
}
.gc-other .gc-header.scrolled {
   box-shadow: 0 4px 24px rgba(0,0,0,.09) !important;
}

/* ─── INNER ─── */
.gc-inner {
   max-width: 1440px;
   margin: 0 auto;
   padding: 0 4rem;
   height: 72px;
   display: grid;
   grid-template-columns: 1fr auto 1fr;
   align-items: center;
   gap: 2rem;
}

/* ─── NAV ─── */
.gc-nav {
   display: flex;
   align-items: center;
   gap: .4rem;
}

.gc-nav a {
   position: relative;
   font-size: 1.4rem;
   font-weight: 500;
   letter-spacing: .06em;
   text-transform: uppercase;
   text-decoration: none;
   padding: .6rem 1.1rem;
   border-radius: .4rem;
   transition: color .2s;
   white-space: nowrap;
   font-family: 'DM Sans', sans-serif;
}

.gc-nav a::after {
   content: '';
   position: absolute;
   bottom: .2rem; left: 1.1rem; right: 1.1rem;
   height: 1.5px;
   transform: scaleX(0);
   transform-origin: right;
   transition: transform .3s ease;
}

.gc-nav a:hover::after,
.gc-nav a.gc-active::after {
   transform: scaleX(1);
   transform-origin: left;
}

.gc-home .gc-nav a { color: rgba(255,255,255,.82); }
.gc-home .gc-nav a::after { background: #fff; }
.gc-home .gc-nav a:hover,
.gc-home .gc-nav a.gc-active { color: #fff; font-weight: 600; }

.gc-other .gc-nav a { color: #0d0d0d; }
.gc-other .gc-nav a::after { background: #1a2a6c; }
.gc-other .gc-nav a:hover,
.gc-other .gc-nav a.gc-active { color: #1a2a6c; font-weight: 600; }

/* ─── DROPDOWN ─── */
.gc-nav-item { position: relative; }

.gc-dropdown {
   position: absolute;
   top: calc(100% + 1rem);
   left: 50%;
   transform: translateX(-50%) translateY(8px);
   border-radius: 1.2rem;
   padding: .8rem;
   min-width: 20rem;
   opacity: 0;
   pointer-events: none;
   transition: opacity .22s ease, transform .28s ease;
   z-index: 999;
   display: grid;
   grid-template-columns: 1fr 1fr;
   gap: .3rem;
}

.gc-dropdown::before {
   content: '';
   position: absolute;
   top: -6px; left: 50%;
   transform: translateX(-50%) rotate(45deg);
   width: 12px; height: 12px;
   border-top: 1px solid; border-left: 1px solid;
}

.gc-nav-item:hover .gc-dropdown {
   opacity: 1;
   pointer-events: auto;
   transform: translateX(-50%) translateY(0);
}

.gc-home .gc-dropdown {
   background: #0f2027;
   border: 1px solid rgba(255,255,255,.12);
   box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.gc-home .gc-dropdown::before {
   background: #0f2027;
   border-color: rgba(255,255,255,.12);
}

.gc-other .gc-dropdown {
   background: #fff;
   border: 1px solid rgba(0,0,0,.08);
   box-shadow: 0 20px 60px rgba(0,0,0,.12);
}
.gc-other .gc-dropdown::before {
   background: #fff;
   border-color: rgba(0,0,0,.08);
}

.gc-drop-link {
   display: flex !important;
   align-items: center !important;
   gap: .8rem !important;
   padding: .8rem 1rem !important;
   border-radius: .8rem !important;
   font-size: 1.3rem !important;
   text-decoration: none !important;
   transition: background .15s, color .15s !important;
   font-weight: 400 !important;
   text-transform: none !important;
   letter-spacing: 0 !important;
   white-space: nowrap;
}

.gc-drop-link::after { display: none !important; }

.gc-home .gc-drop-link { color: rgba(255,255,255,.8) !important; }
.gc-home .gc-drop-link:hover { background: rgba(255,255,255,.1) !important; color: #fff !important; }
.gc-other .gc-drop-link { color: #0d0d0d !important; }
.gc-other .gc-drop-link:hover { background: #eff2ff !important; color: #1a2a6c !important; }

.gc-drop-icon {
   width: 2.8rem; height: 2.8rem;
   border-radius: .5rem;
   display: flex; align-items: center; justify-content: center;
   font-size: 1.1rem;
   flex-shrink: 0;
}

/* ─── LOGO ─── */
.gc-logo {
   text-align: center;
   text-decoration: none !important;
   display: flex;
   flex-direction: column;
   align-items: center;
   line-height: 1;
}

.gc-logo__img {
   height: 46px;
   width: auto;
   max-width: 170px;
   object-fit: contain;
   display: block;
   transition: opacity .2s;
}
.gc-logo:hover .gc-logo__img { opacity: .8; }
.gc-home .gc-logo__img  { filter: brightness(0) invert(1); }
.gc-other .gc-logo__img { filter: none; }

.gc-logo__sub {
   font-size: .9rem;
   letter-spacing: .22em;
   text-transform: uppercase;
   font-weight: 500;
   margin-top: .3rem;
   font-family: 'DM Sans', sans-serif;
}
.gc-home .gc-logo__sub  { color: rgba(255,255,255,.4); }
.gc-other .gc-logo__sub { color: #b89b6a; }

/* ─── ACTIONS (right side) ─── */
.gc-actions {
   display: flex;
   align-items: center;
   justify-content: flex-end;
   gap: .3rem;
}

/* Icon button */
.gc-icon-btn {
   position: relative;
   width: 4rem; height: 4rem;
   border-radius: .7rem;
   background: transparent;
   border: none;
   font-size: 1.65rem;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   cursor: pointer;
   transition: background .18s, color .18s, transform .18s;
   text-decoration: none !important;
   flex-shrink: 0;
   line-height: 1;
}
.gc-icon-btn:hover { transform: scale(1.07); }

.gc-home .gc-icon-btn { color: rgba(255,255,255,.8); }
.gc-home .gc-icon-btn:hover { background: rgba(255,255,255,.1); color: #fff; }
.gc-other .gc-icon-btn { color: #0d0d0d; }
.gc-other .gc-icon-btn:hover { background: #eff2ff; color: #1a2a6c; }

/* Badge */
.gc-badge {
   position: absolute;
   top: .4rem; right: .4rem;
   min-width: 1.7rem; height: 1.7rem;
   background: #4f7cff;
   color: #fff;
   font-size: .95rem;
   font-weight: 700;
   border-radius: 10rem;
   display: flex; align-items: center; justify-content: center;
   padding: 0 .35rem;
   line-height: 1;
   pointer-events: none;
   animation: badgePop .3s ease;
}
.gc-home .gc-badge  { border: 2px solid #0f2027; }
.gc-other .gc-badge { border: 2px solid #fff; }

@keyframes badgePop {
   0% { transform: scale(0); }
   70% { transform: scale(1.2); }
   100% { transform: scale(1); }
}

/* Divider */
.gc-divider {
   width: 1px; height: 2rem;
   margin: 0 .4rem;
   flex-shrink: 0;
}
.gc-home .gc-divider  { background: rgba(255,255,255,.15); }
.gc-other .gc-divider { background: rgba(0,0,0,.09); }

/* ─── SEARCH ─── */
.gc-search-wrap { position: relative; display: flex; align-items: center; }

.gc-search-input {
   width: 0;
   overflow: hidden;
   padding: 0;
   border: none !important;
   border-bottom: 1.5px solid transparent !important;
   background: transparent !important;
   border-radius: 0 !important;
   font-family: 'DM Sans', sans-serif;
   font-size: 1.3rem;
   outline: none !important;
   transition: width .4s ease, padding .4s, border-color .3s;
}
.gc-search-wrap.open .gc-search-input {
   width: 17rem;
   padding: .4rem .6rem .4rem 0 !important;
}
.gc-home .gc-search-input { color: #fff; }
.gc-home .gc-search-input::placeholder { color: rgba(255,255,255,.4); }
.gc-home .gc-search-wrap.open .gc-search-input { border-bottom-color: rgba(255,255,255,.5) !important; }
.gc-other .gc-search-input { color: #0d0d0d; }
.gc-other .gc-search-input::placeholder { color: #94a3b8; }
.gc-other .gc-search-wrap.open .gc-search-input { border-bottom-color: #0d0d0d !important; }

/* ─── USER CHIP ─── */
.gc-user-wrap { position: relative; display: inline-flex; align-items: center; }

.gc-user-chip {
   display: inline-flex !important;
   align-items: center !important;
   gap: .6rem !important;
   padding: .5rem 1.1rem .5rem .7rem !important;
   border-radius: 10rem !important;
   text-decoration: none !important;
   font-size: 1.25rem !important;
   font-weight: 500 !important;
   font-family: 'DM Sans', sans-serif !important;
   cursor: pointer;
   border: 1px solid transparent !important;
   background: none;
   transition: background .18s, border-color .18s, color .18s !important;
   white-space: nowrap;
}

.gc-user-chip__av {
   width: 2.5rem; height: 2.5rem;
   border-radius: 50%;
   color: #fff;
   display: flex; align-items: center; justify-content: center;
   font-size: 1.1rem; font-weight: 700;
   flex-shrink: 0;
}

.gc-user-chip__caret {
   font-size: 1rem;
   opacity: .65;
   transition: transform .22s ease;
}
.gc-user-wrap.open .gc-user-chip__caret { transform: rotate(180deg); }

.gc-home .gc-user-chip { background: rgba(255,255,255,.1) !important; border-color: rgba(255,255,255,.18) !important; color: rgba(255,255,255,.9) !important; }
.gc-home .gc-user-chip:hover { background: rgba(255,255,255,.18) !important; }
.gc-home .gc-user-chip__av { background: #4f7cff; }

.gc-other .gc-user-chip { background: #eff2ff !important; border-color: rgba(0,0,0,.08) !important; color: #0d0d0d !important; }
.gc-other .gc-user-chip:hover { background: #e0e7ff !important; color: #1a2a6c !important; }
.gc-other .gc-user-chip__av { background: #1a2a6c; }

/* ─── USER POPUP ─── */
.gc-user-popup {
   position: absolute;
   top: calc(100% + 1.2rem);
   right: 0;
   width: 28rem;
   border-radius: 1.4rem;
   opacity: 0;
   visibility: hidden;
   pointer-events: none;
   transform: translateY(10px) scale(.98);
   transition: opacity .22s ease, transform .26s ease, visibility .22s ease;
   z-index: 10050;
   overflow: hidden;
}

.gc-user-wrap.open .gc-user-popup {
   opacity: 1;
   visibility: visible;
   pointer-events: auto;
   transform: translateY(0) scale(1);
}

.gc-home .gc-user-popup  { background: #0f2027; border: 1px solid rgba(255,255,255,.12); box-shadow: 0 24px 70px rgba(0,0,0,.45); }
.gc-other .gc-user-popup { background: #fff; border: 1px solid rgba(0,0,0,.08); box-shadow: 0 24px 70px rgba(0,0,0,.14); }

.gc-popup-top {
   padding: 1.6rem 1.6rem 1.2rem;
   display: flex; align-items: center; gap: 1.2rem;
}

.gc-popup-av {
   width: 4.8rem; height: 4.8rem;
   border-radius: 50%;
   display: flex; align-items: center; justify-content: center;
   font-size: 1.6rem; font-weight: 700; color: #fff;
   flex-shrink: 0;
   background: linear-gradient(135deg,#1a2a6c,#4f7cff);
}

.gc-popup-label { font-size: 1.05rem; letter-spacing: .12em; text-transform: uppercase; opacity: .5; font-family: 'DM Sans', sans-serif; }
.gc-popup-name  { font-size: 1.6rem; font-weight: 700; font-family: 'DM Sans', sans-serif; word-break: break-word; }
.gc-home .gc-popup-label, .gc-home .gc-popup-name  { color: #fff; }
.gc-other .gc-popup-label { color: #64748b; }
.gc-other .gc-popup-name  { color: #0f172a; }

.gc-popup-hr {
   height: 1px; margin: 0 1.6rem;
}
.gc-home .gc-popup-hr  { background: rgba(255,255,255,.1); }
.gc-other .gc-popup-hr { background: rgba(0,0,0,.07); }

.gc-popup-actions {
   padding: 1.2rem 1.4rem;
   display: flex; flex-direction: column; gap: .7rem;
}

.gc-popup-btn {
   display: flex !important;
   align-items: center !important;
   gap: .9rem !important;
   padding: 1.1rem 1.2rem !important;
   border-radius: .9rem !important;
   text-decoration: none !important;
   font-size: 1.35rem !important;
   font-weight: 600 !important;
   font-family: 'DM Sans', sans-serif !important;
   transition: background .18s, color .18s, transform .15s !important;
   border: 1px solid transparent !important;
}
.gc-popup-btn:hover { transform: translateY(-1px); }
.gc-popup-btn i { width: 1.6rem; text-align: center; font-size: 1.3rem; }

.gc-popup-btn--primary { background: linear-gradient(135deg,#1a2a6c,#4f7cff) !important; color: #fff !important; box-shadow: 0 8px 20px rgba(79,124,255,.2); }
.gc-popup-btn--primary:hover { filter: brightness(1.05); }

.gc-home .gc-popup-btn--ghost  { background: rgba(255,255,255,.07) !important; color: #fff !important; border-color: rgba(255,255,255,.1) !important; }
.gc-home .gc-popup-btn--ghost:hover  { background: rgba(255,255,255,.14) !important; }
.gc-other .gc-popup-btn--ghost { background: #f8fafc !important; color: #0f172a !important; border-color: rgba(0,0,0,.07) !important; }
.gc-other .gc-popup-btn--ghost:hover { background: #eff2ff !important; color: #1d4ed8 !important; }

/* ─── HAMBURGER ─── */
.gc-hamburger {
   display: none;
   flex-direction: column;
   justify-content: center;
   gap: .5rem;
   width: 4rem; height: 4rem;
   background: none; border: none;
   cursor: pointer;
   padding: .8rem;
   border-radius: .6rem;
   transition: background .18s;
}
.gc-home .gc-hamburger:hover  { background: rgba(255,255,255,.1); }
.gc-other .gc-hamburger:hover { background: #eff2ff; }

.gc-hamburger span {
   display: block;
   width: 100%; height: 1.5px;
   border-radius: 2px;
   transition: transform .32s ease, opacity .22s;
}
.gc-home .gc-hamburger span  { background: rgba(255,255,255,.85); }
.gc-other .gc-hamburger span { background: #0d0d0d; }

.gc-hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
.gc-hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.gc-hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

/* ─── MOBILE DRAWER ─── */
.gc-drawer {
   display: none;
   position: fixed;
   inset: 0;
   z-index: 9998;
}
.gc-drawer.open { display: block; }

.gc-drawer__overlay {
   position: absolute; inset: 0;
   background: rgba(0,0,0,.55);
   backdrop-filter: blur(4px);
   animation: gcFadeIn .28s ease;
}

.gc-drawer__panel {
   position: absolute;
   top: 0; left: 0; bottom: 0;
   width: min(85vw, 34rem);
   padding: 2.4rem 1.8rem;
   overflow-y: auto;
   animation: gcSlideIn .35s cubic-bezier(.19,1,.22,1);
}

.gc-home .gc-drawer__panel  { background: #0f2027; }
.gc-other .gc-drawer__panel { background: #fff; }

@keyframes gcFadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes gcSlideIn { from { transform: translateX(-100%); } to { transform: translateX(0); } }

.gc-drawer-logo {
   display: block;
   margin-bottom: 2.2rem;
   text-decoration: none;
}
.gc-drawer-logo img {
   height: 38px; width: auto;
}
.gc-home .gc-drawer-logo img  { filter: brightness(0) invert(1); }
.gc-other .gc-drawer-logo img { filter: none; }

.gc-drawer-nav {
   display: flex; flex-direction: column; gap: .3rem;
   margin-bottom: 1.8rem;
}
.gc-drawer-nav a {
   display: flex !important;
   align-items: center !important;
   justify-content: space-between !important;
   padding: 1.1rem 1.2rem !important;
   border-radius: .8rem !important;
   font-size: 1.5rem !important;
   font-weight: 500 !important;
   text-decoration: none !important;
   font-family: 'DM Sans', sans-serif !important;
   transition: background .15s, color .15s !important;
}
.gc-home .gc-drawer-nav a { color: rgba(255,255,255,.82) !important; }
.gc-home .gc-drawer-nav a:hover,
.gc-home .gc-drawer-nav a.gc-active { background: rgba(255,255,255,.08) !important; color: #fff !important; }
.gc-other .gc-drawer-nav a { color: #0d0d0d !important; }
.gc-other .gc-drawer-nav a:hover,
.gc-other .gc-drawer-nav a.gc-active { background: #eff2ff !important; color: #1a2a6c !important; }

.gc-drawer-hr {
   height: 1px; margin: 1.4rem 0;
}
.gc-home .gc-drawer-hr  { background: rgba(255,255,255,.1); }
.gc-other .gc-drawer-hr { background: rgba(0,0,0,.08); }

.gc-drawer-extras {
   display: flex; flex-direction: column; gap: .6rem;
}
.gc-drawer-extras a {
   display: flex !important;
   align-items: center !important;
   gap: 1rem !important;
   padding: 1rem 1.2rem !important;
   border-radius: .8rem !important;
   font-size: 1.4rem !important;
   text-decoration: none !important;
   font-family: 'DM Sans', sans-serif !important;
   transition: background .15s, color .15s !important;
   font-weight: 400 !important;
}
.gc-home .gc-drawer-extras a { color: rgba(255,255,255,.6) !important; }
.gc-home .gc-drawer-extras a:hover { background: rgba(255,255,255,.08) !important; color: #fff !important; }
.gc-other .gc-drawer-extras a { color: #64748b !important; }
.gc-other .gc-drawer-extras a:hover { background: #eff2ff !important; color: #0d0d0d !important; }

.gc-drawer-count {
   margin-left: auto;
   background: #4f7cff; color: #fff;
   border-radius: 10rem; padding: .15rem .65rem;
   font-size: 1.1rem; font-weight: 700;
}
	
	.gc-alert-success{
   max-width: 1250px;
   margin: 1.5rem auto;
   padding: 1.5rem 2rem;
   background: #ecfdf5 !important;
   color: #047857 !important;
   border-left: 4px solid #059669 !important;
   border-radius: .8rem !important;
   font-size: 1.55rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 1.5rem;
   box-shadow: none !important;
}

.gc-alert-success span{
   color: #047857 !important;
   display: flex;
   align-items: center;
   gap: .8rem;
}

.gc-alert-success span i{
   color: #059669 !important;
}

.gc-alert-close{
   color: #ef4444 !important;
   cursor: pointer;
   font-size: 1.8rem;
}

/* ─── Hide old header elements ─── */
.header .flex .navbar,
.header .flex .profile,
#user-btn,
#menu-btn {
   display: none !important;
}

/* ─── RESPONSIVE ─── */
@media (max-width: 1024px) {
   .gc-nav      { display: none !important; }
   .gc-hamburger { display: flex !important; }
   .gc-inner { grid-template-columns: auto 1fr auto !important; }
   .gc-logo { text-align: left; align-items: flex-start; }
}

@media (max-width: 640px) {
   .gc-inner { padding: 0 1.6rem !important; }
   .gc-search-wrap.open .gc-search-input { width: 12rem; }
   .gc-user-popup { width: 26rem; }
}
</style>

<?php /* Add class to html element */ ?>
<script>document.documentElement.classList.add('<?= $gc_class; ?>');</script>

<header class="gc-header" id="gcHeader">
   <div class="gc-inner">

      <!-- ── LEFT: NAV ── -->
      <nav class="gc-nav" aria-label="Navigasi Utama">
         <a href="<?= $base_url; ?>/home.php"   class="<?= $current_page==='home.php'   ? 'gc-active':''; ?>">Beranda</a>

         <div class="gc-nav-item">
            <a href="<?= $base_url; ?>/shop.php" class="<?= $current_page==='shop.php' ? 'gc-active':''; ?>">
               Produk <i class="fas fa-chevron-down" style="font-size:.85rem;margin-left:.2rem;"></i>
            </a>
            <div class="gc-dropdown">
               <?php
               $CATS = [
                  'Totebag'    => ['fa-bag-shopping',  '#4f6ef7'],
                  'Slingbag'   => ['fa-person-walking','#059669'],
                  'Dompet'     => ['fa-wallet',        '#f59e0b'],
                  'Heels'      => ['fa-shoe-prints',   '#e11d48'],
                  'Flat Shoes' => ['fa-shoe-prints',   '#0891b2'],
                  'Top Handle' => ['fa-hand-holding',  '#7c3aed'],
                  'Clutch'     => ['fa-grip',          '#ea580c'],
                  'Ransel'     => ['fa-backpack',      '#65a30d'],
                  'Waistbag'   => ['fa-vest-patches',  '#db2777'],
               ];
               foreach($CATS as $cat => [$icon, $color]):
               ?>
               <a href="<?= $base_url; ?>/shop.php?cat=<?= urlencode($cat); ?>" class="gc-drop-link">
                  <div class="gc-drop-icon" style="background:<?= $color; ?>22;color:<?= $color; ?>;">
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
      <a href="<?= $base_url; ?>/home.php" class="gc-logo" aria-label="Gals Collection">
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
            <div class="gc-user-chip__av"><i class="fas fa-user" style="font-size:1rem"></i></div>
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

      <a href="<?= $base_url; ?>/home.php" class="gc-drawer-logo">
         <img src="images/LogoGals/LogoGalsTerbaru.png" alt="Gals Collection">
      </a>

      <nav class="gc-drawer-nav">
         <a href="<?= $base_url; ?>/home.php"   class="<?= $current_page==='home.php'   ? 'gc-active':''; ?>"><span>Beranda</span><i class="fas fa-chevron-right" style="font-size:1rem;opacity:.35"></i></a>
         <a href="<?= $base_url; ?>/shop.php"   class="<?= $current_page==='shop.php'   ? 'gc-active':''; ?>"><span>Toko</span><i class="fas fa-chevron-right" style="font-size:1rem;opacity:.35"></i></a>
         <a href="<?= $base_url; ?>/orders.php" class="<?= in_array($current_page,['orders.php','order_detail.php']) ? 'gc-active':''; ?>"><span>Pesanan Saya</span><i class="fas fa-chevron-right" style="font-size:1rem;opacity:.35"></i></a>
         <a href="<?= $base_url; ?>/about.php"  class="<?= $current_page==='about.php'  ? 'gc-active':''; ?>"><span>Tentang Kami</span><i class="fas fa-chevron-right" style="font-size:1rem;opacity:.35"></i></a>
      </nav>

      <div class="gc-drawer-hr"></div>

      <div class="gc-drawer-extras">
         <a href="<?= $base_url; ?>/wishlist.php">
            <i class="fas fa-heart" style="width:1.6rem;color:#4f7cff"></i> Wishlist
            <?php if($total_wishlist > 0): ?><span class="gc-drawer-count"><?= $total_wishlist; ?></span><?php endif; ?>
         </a>
         <a href="<?= $base_url; ?>/cart.php">
            <i class="fas fa-shopping-bag" style="width:1.6rem;color:#4f7cff"></i> Keranjang
            <?php if($total_cart > 0): ?><span class="gc-drawer-count"><?= $total_cart; ?></span><?php endif; ?>
         </a>
         <a href="<?= $base_url; ?>/chat.php">
            <i class="fas fa-comment-dots" style="width:1.6rem;color:#4f7cff"></i> Chat Admin
            <?php if($unread_chat > 0): ?><span class="gc-drawer-count"><?= $unread_chat; ?></span><?php endif; ?>
         </a>

         <div class="gc-drawer-hr"></div>

         <?php if(!empty($user_id)): ?>
            <a href="<?= $base_url; ?>/update_user.php"><i class="fas fa-user-circle" style="width:1.6rem;color:#94a3b8"></i><?= htmlspecialchars($header_first_name); ?> — Profil</a>
            <a href="<?= $base_url; ?>/components/user_logout.php" onclick="return confirm('Yakin mau keluar?');"><i class="fas fa-sign-out-alt" style="width:1.6rem;color:#94a3b8"></i>Logout</a>
         <?php else: ?>
            <a href="<?= $base_url; ?>/user_login.php"><i class="fas fa-sign-in-alt" style="width:1.6rem;color:#4f7cff"></i>Login</a>
            <a href="<?= $base_url; ?>/user_register.php"><i class="fas fa-user-plus" style="width:1.6rem;color:#4f7cff"></i>Register</a>
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
