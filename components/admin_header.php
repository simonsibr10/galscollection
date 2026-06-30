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
