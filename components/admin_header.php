<?php
if (isset($message)) {
   foreach ($message as $msg) {
      echo '
         <div class="message">
            <span>' . $msg . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
      ';
   }
}

$count_unread = $conn->prepare("SELECT COUNT(*) AS total FROM `chat_messages` WHERE sender = 'user' AND is_read = 0");
$count_unread->execute();
$fetch_unread = $count_unread->fetch(PDO::FETCH_ASSOC);
$total_unread = (int)($fetch_unread['total'] ?? 0);

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$admin_initial = $fetch_profile ? strtoupper(mb_substr($fetch_profile['name'], 0, 1)) : 'A';
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$app_dir = rtrim(dirname($script_dir), '/');
$base_url = ($app_dir === '/' || $app_dir === '.' || $app_dir === '\\') ? '' : $app_dir;
$base_url = htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8');
?>

<style>
   /* ===== ADMIN HEADER ===== */
   *, *::before, *::after { box-sizing: border-box; }

   .admin-header {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: #fff;
      border-bottom: 1px solid #f1f5f9;
      box-shadow: 0 2px 16px rgba(0,0,0,.06);
   }

   .admin-header-inner {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 2.4rem;
      height: 6.4rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 2rem;
   }

   /* ===== LOGO ===== */
   .admin-logo {
      display: flex;
      align-items: center;
      gap: .6rem;
      text-decoration: none;
      flex-shrink: 0;
   }

   .admin-logo-icon {
      width: 3.6rem; height: 3.6rem;
      border-radius: .8rem;
      background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 1.6rem;
   }

   .admin-logo-text {
      font-size: 1.7rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -.3px;
      line-height: 1;
   }

   .admin-logo-text span {
      background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: block;
      font-size: 1.1rem;
      font-weight: 600;
      margin-top: .1rem;
      letter-spacing: .02em;
   }

   /* ===== NAV ===== */
   .admin-nav {
      display: flex;
      align-items: center;
      gap: .4rem;
      flex: 1;
      justify-content: center;
   }

   .admin-nav a {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .7rem 1.3rem;
      border-radius: .7rem;
      font-size: 1.35rem;
      font-weight: 600;
      color: #64748b;
      text-decoration: none;
      transition: color .15s, background .15s;
      white-space: nowrap;
   }

   .admin-nav a i { font-size: 1.2rem; }

   .admin-nav a:hover { color: #1a2a6c; background: #eff2ff; }

   .admin-nav a.nav-active {
      color: #1a2a6c;
      background: #eff2ff;
   }

   /* ===== RIGHT ICONS ===== */
   .admin-header-right {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-shrink: 0;
   }

   /* Chat icon */
   .chat-icon-wrap {
      position: relative;
      width: 3.8rem; height: 3.8rem;
      border-radius: .8rem;
      background: #f8fafc;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      text-decoration: none;
      color: #475569;
      font-size: 1.8rem;
      transition: background .15s, color .15s;
      border: 1.5px solid #e2e8f0;
   }

   .chat-icon-wrap:hover { background: #eff2ff; color: #1a2a6c; border-color: #c7d2fe; }

   .chat-badge {
      position: absolute;
      top: -6px; right: -6px;
      min-width: 1.8rem; height: 1.8rem;
      padding: 0 .4rem;
      border-radius: 1rem;
      background: #e11d48;
      color: #fff;
      font-size: 1rem;
      font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 2px 6px rgba(225,29,72,.4);
      animation: badgePop .3s ease both;
   }

   @keyframes badgePop { from{transform:scale(0)} to{transform:scale(1)} }

   /* ===== PROFILE DROPDOWN ===== */
   .profile-dropdown-wrap {
      position: relative;
   }

   .profile-trigger {
      display: flex;
      align-items: center;
      gap: .8rem;
      padding: .5rem .8rem;
      border-radius: .8rem;
      cursor: pointer;
      border: 1.5px solid #e2e8f0;
      background: #f8fafc;
      transition: background .15s, border-color .15s;
   }

   .profile-trigger:hover { background: #eff2ff; border-color: #c7d2fe; }

   .profile-avatar {
      width: 3.2rem; height: 3.2rem;
      border-radius: 50%;
      background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem;
      font-weight: 800;
      color: #fff;
      flex-shrink: 0;
   }

   .profile-name {
      font-size: 1.35rem;
      font-weight: 700;
      color: #0f172a;
      max-width: 12rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
   }

   .profile-caret { color: #94a3b8; font-size: 1.1rem; transition: transform .2s; }
   .profile-trigger.open .profile-caret { transform: rotate(180deg); }

   /* Dropdown menu */
   .profile-dropdown {
      position: absolute;
      top: calc(100% + .8rem);
      right: 0;
      min-width: 22rem;
      background: #fff;
      border-radius: 1.2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,.12);
      border: 1.5px solid #f1f5f9;
      overflow: hidden;
      display: none;
      z-index: 200;
      animation: dropdownIn .2s ease both;
   }

   @keyframes dropdownIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

   .profile-dropdown.open { display: block; }

   .dropdown-header {
      padding: 1.4rem 1.6rem;
      background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
      display: flex;
      align-items: center;
      gap: 1rem;
   }

   .dropdown-avatar {
      width: 4rem; height: 4rem;
      border-radius: 50%;
      background: rgba(255,255,255,.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
      font-weight: 800;
      color: #fff;
   }

   .dropdown-name  { font-size: 1.4rem; font-weight: 700; color: #fff; }
   .dropdown-role  { font-size: 1.2rem; color: rgba(255,255,255,.65); margin-top: .2rem; }

   .dropdown-divider { height: 1px; background: #f1f5f9; }

   .dropdown-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.1rem 1.6rem;
      font-size: 1.35rem;
      font-weight: 600;
      color: #334155;
      text-decoration: none;
      transition: background .15s, color .15s;
      cursor: pointer;
   }

   .dropdown-item i {
      width: 2rem;
      text-align: center;
      color: #94a3b8;
      font-size: 1.3rem;
      transition: color .15s;
   }

   .dropdown-item:hover { background: #f8fafc; color: #1a2a6c; }
   .dropdown-item:hover i { color: #4f6ef7; }

   .dropdown-item.danger { color: #be123c; }
   .dropdown-item.danger i { color: #fca5a5; }
   .dropdown-item.danger:hover { background: #fff1f2; color: #9f1239; }
   .dropdown-item.danger:hover i { color: #e11d48; }

   /* ===== MOBILE HAMBURGER ===== */
   .hamburger-btn {
      display: none;
      flex-direction: column;
      gap: .5rem;
      cursor: pointer;
      padding: .6rem;
      border-radius: .6rem;
      transition: background .15s;
   }

   .hamburger-btn:hover { background: #f1f5f9; }
   .hamburger-btn span { display: block; width: 2.2rem; height: 2px; background: #475569; border-radius: 2px; transition: all .3s; }

   /* ===== MOBILE NAV ===== */
   .admin-mobile-nav {
      display: none;
      position: fixed;
      top: 6.4rem; left: 0; right: 0;
      background: #fff;
      border-bottom: 1px solid #f1f5f9;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      z-index: 999;
      padding: 1.2rem;
      flex-direction: column;
      gap: .4rem;
      animation: slideDown .2s ease both;
   }

   @keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }

   .admin-mobile-nav.open { display: flex; }

   .admin-mobile-nav a {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.1rem 1.4rem;
      border-radius: .8rem;
      font-size: 1.4rem;
      font-weight: 600;
      color: #475569;
      text-decoration: none;
      transition: background .15s, color .15s;
   }

   .admin-mobile-nav a i { color: #94a3b8; width: 1.6rem; text-align: center; }
   .admin-mobile-nav a:hover { background: #eff2ff; color: #1a2a6c; }
   .admin-mobile-nav a:hover i { color: #4f6ef7; }

   /* ===== MESSAGE TOAST ===== */
   .message {
      background: #ecfdf5 !important;
      border-left: 4px solid #059669 !important;
      border-radius: .8rem !important;
      padding: 1.2rem 1.6rem !important;
      font-size: 1.4rem !important;
      color: #065f46 !important;
      margin: 1rem 2.4rem !important;
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      gap: 1rem !important;
      animation: fadeSlideDown .3s ease both !important;
      max-width: 1400px !important;
      margin-left: auto !important;
      margin-right: auto !important;
   }

   @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }

   /* ===== RESPONSIVE ===== */
   @media (max-width: 960px) {
      .admin-nav { display: none; }
      .hamburger-btn { display: flex; }
      .profile-name { display: none; }
   }

   @media (max-width: 480px) {
      .admin-header-inner { padding: 0 1.4rem; }
   }
</style>

<header class="admin-header">
   <div class="admin-header-inner">

      <!-- LOGO -->
      <a href="<?= $base_url; ?>/admin/dashboard.php" class="admin-logo">
         <div class="admin-logo-icon"><i class="fas fa-store"></i></div>
         <div class="admin-logo-text">
            Admin
            <span>Gals Collection</span>
         </div>
      </a>

      <!-- DESKTOP NAV -->
      <nav class="admin-nav">
         <a href="<?= $base_url; ?>/admin/dashboard.php"><i class="fas fa-home"></i> Beranda</a>
         <a href="<?= $base_url; ?>/admin/products.php"><i class="fas fa-box-open"></i> Produk</a>
         <a href="<?= $base_url; ?>/admin/placed_orders.php"><i class="fas fa-clipboard-list"></i> Pesanan</a>
         <a href="<?= $base_url; ?>/admin/admin_accounts.php"><i class="fas fa-user-shield"></i> Admin</a>
         <a href="<?= $base_url; ?>/admin/users_accounts.php"><i class="fas fa-users"></i> Pengguna</a>
      </nav>

      <!-- RIGHT SECTION -->
      <div class="admin-header-right">

         <!-- Chat -->
         <a href="<?= $base_url; ?>/admin/chat_list.php" class="chat-icon-wrap" title="Chat Pengguna">
            <i class="fas fa-comment-dots"></i>
            <?php if($total_unread > 0): ?>
               <span class="chat-badge"><?= $total_unread; ?></span>
            <?php endif; ?>
         </a>

         <!-- Profile Dropdown -->
         <?php if($fetch_profile): ?>
         <div class="profile-dropdown-wrap">
            <div class="profile-trigger" id="profile-trigger" onclick="toggleDropdown()">
               <div class="profile-avatar"><?= htmlspecialchars($admin_initial); ?></div>
               <div class="profile-name"><?= htmlspecialchars($fetch_profile['name']); ?></div>
               <i class="fas fa-chevron-down profile-caret"></i>
            </div>

            <div class="profile-dropdown" id="profile-dropdown">
               <div class="dropdown-header">
                  <div class="dropdown-avatar"><?= htmlspecialchars($admin_initial); ?></div>
                  <div>
                     <div class="dropdown-name"><?= htmlspecialchars($fetch_profile['name']); ?></div>
                     <div class="dropdown-role">Administrator</div>
                  </div>
               </div>

               <div class="dropdown-divider"></div>

               <a href="<?= $base_url; ?>/admin/dashboard.php" class="dropdown-item">
                  <i class="fas fa-home"></i> Dashboard
               </a>
               <a href="<?= $base_url; ?>/admin/update_profile.php" class="dropdown-item">
                  <i class="fas fa-user-edit"></i> Update Profil
               </a>
               <a href="<?= $base_url; ?>/admin/register_admin.php" class="dropdown-item">
                  <i class="fas fa-user-plus"></i> Register Admin Baru
               </a>

               <div class="dropdown-divider"></div>

               <a href="<?= $base_url; ?>/components/admin_logout.php"
                  onclick="return confirm('Logout dari akun admin ini?');"
                  class="dropdown-item danger">
                  <i class="fas fa-sign-out-alt"></i> Logout
               </a>
            </div>
         </div>
         <?php endif; ?>

         <!-- Hamburger -->
         <div class="hamburger-btn" id="hamburger" onclick="toggleMobileNav()">
            <span></span>
            <span></span>
            <span></span>
         </div>

      </div>
   </div>
</header>

<!-- MOBILE NAV -->
<nav class="admin-mobile-nav" id="mobile-nav">
   <a href="<?= $base_url; ?>/admin/dashboard.php"><i class="fas fa-home"></i> Beranda</a>
   <a href="<?= $base_url; ?>/admin/products.php"><i class="fas fa-box-open"></i> Produk</a>
   <a href="<?= $base_url; ?>/admin/placed_orders.php"><i class="fas fa-clipboard-list"></i> Pesanan</a>
   <a href="<?= $base_url; ?>/admin/admin_accounts.php"><i class="fas fa-user-shield"></i> Admin</a>
   <a href="<?= $base_url; ?>/admin/users_accounts.php"><i class="fas fa-users"></i> Pengguna</a>
   <a href="<?= $base_url; ?>/admin/chat_list.php"><i class="fas fa-comment-dots"></i> Chat Pengguna</a>
</nav>

<script>
   // Active nav highlight
   (function(){
      const path = window.location.pathname;
      document.querySelectorAll('.admin-nav a, .admin-mobile-nav a').forEach(a => {
         if(path.includes(a.getAttribute('href').split('/').pop().split('.')[0])){
            a.classList.add('nav-active');
         }
      });
   })();

   // Profile dropdown
   function toggleDropdown(){
      const trigger  = document.getElementById('profile-trigger');
      const dropdown = document.getElementById('profile-dropdown');
      trigger.classList.toggle('open');
      dropdown.classList.toggle('open');
   }

   // Close dropdown on outside click
   document.addEventListener('click', function(e){
      const wrap = document.querySelector('.profile-dropdown-wrap');
      if(wrap && !wrap.contains(e.target)){
         document.getElementById('profile-trigger')?.classList.remove('open');
         document.getElementById('profile-dropdown')?.classList.remove('open');
      }
   });

   // Mobile nav toggle
   function toggleMobileNav(){
      document.getElementById('mobile-nav').classList.toggle('open');
   }

   // Close mobile nav on outside click
   document.addEventListener('click', function(e){
      const nav = document.getElementById('mobile-nav');
      const btn = document.getElementById('hamburger');
      if(nav && btn && !nav.contains(e.target) && !btn.contains(e.target)){
         nav.classList.remove('open');
      }
   });
</script>
