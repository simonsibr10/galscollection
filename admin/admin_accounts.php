<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   if($delete_id != $admin_id){
      $conn->prepare("DELETE FROM `admins` WHERE id = ?")->execute([$delete_id]);
   }
   header('location:admin_accounts.php');
   exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Akun Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-admin-accounts">

<?php include '../components/admin_header.php'; ?>

<div class="accounts-page">

   <?php
      $q_all = $conn->prepare("SELECT * FROM `admins`");
      $q_all->execute();
      $all_admins = $q_all->fetchAll(PDO::FETCH_ASSOC);
      $total_admins = count($all_admins);
   ?>

   <!-- ===== PAGE HEADER ===== -->
   <div class="page-header">
      <h1>Kelola <span>Admin</span></h1>
   </div>

   <!-- ===== STATS STRIP ===== -->
   <div class="stats-strip">
      <div class="stat-chip blue">
         <div class="stat-icon bg-blue"><i class="fas fa-user-shield"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_admins; ?></div>
            <div class="stat-info-lbl">Total Admin</div>
         </div>
      </div>
      <div class="stat-chip green">
         <div class="stat-icon bg-green"><i class="fas fa-circle"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_admins; ?></div>
            <div class="stat-info-lbl">Admin Aktif</div>
         </div>
      </div>
      <div class="stat-chip amber">
         <div class="stat-icon bg-amber"><i class="fas fa-user-plus"></i></div>
         <div>
            <div class="stat-info-val">1</div>
            <div class="stat-info-lbl">Slot Tersedia</div>
         </div>
      </div>
   </div>

   <!-- ===== ADD ADMIN BANNER ===== -->
   <div class="add-admin-banner">
      <div class="banner-text">
         <h3>Tambah Admin Baru</h3>
         <p>Daftarkan akun admin baru untuk mengelola toko bersama.</p>
      </div>
      <a href="register_admin.php" class="btn-register-admin">
         <i class="fas fa-user-plus"></i> Register Admin
      </a>
   </div>

   <!-- ===== ADMIN LIST ===== -->
   <div class="section-label">Daftar Admin (<?= $total_admins; ?>)</div>

   <?php if($total_admins > 0): ?>
   <div class="admin-grid">
      <?php foreach($all_admins as $acc):
         $is_you = ($acc['id'] == $admin_id);
         $initial = strtoupper(mb_substr($acc['name'], 0, 1));
      ?>
      <div class="admin-card">
         <div class="admin-card-bar <?= $is_you ? 'bar-you' : 'bar-other'; ?>"></div>
         <div class="admin-card-inner">
            <div class="admin-avatar-row">
               <div class="admin-avatar <?= $is_you ? 'avatar-you' : 'avatar-other'; ?>">
                  <?= htmlspecialchars($initial); ?>
               </div>
               <div class="admin-name-block">
                  <div class="admin-name"><?= htmlspecialchars($acc['name']); ?></div>
                  <div class="admin-id-tag"><i class="fas fa-hashtag"></i> ID <?= htmlspecialchars($acc['id']); ?></div>
               </div>
               <?php if($is_you): ?>
                  <div class="you-badge"><i class="fas fa-star"></i> Anda</div>
               <?php endif; ?>
            </div>

            <div class="admin-card-divider"></div>

            <div class="admin-card-actions">
               <?php if($is_you): ?>
                  <a href="update_profile.php" class="card-btn btn-update">
                     <i class="fas fa-pen"></i> Update Profil
                  </a>
               <?php else: ?>
                  <a
                     href="admin_accounts.php?delete=<?= $acc['id']; ?>"
                     onclick="return confirm('Hapus akun admin \'<?= addslashes(htmlspecialchars($acc['name'])); ?>\'?');"
                     class="card-btn btn-delete"
                  >
                     <i class="fas fa-trash"></i> Hapus Admin
                  </a>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <?php endforeach; ?>
   </div>
   <?php else: ?>
      <div class="accounts-empty">
         <i class="fas fa-user-slash"></i>
         <p>Tidak ada akun admin tersedia.</p>
      </div>
   <?php endif; ?>

</div>

<script src="../js/admin_script.js"></script>
</body>
</html>