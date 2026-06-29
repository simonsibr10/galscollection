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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      section, .accounts, .dashboard { background: transparent !important; }
      .header { background: #fff !important; }
      .footer { background: #fff !important; }

      .accounts-page {
         max-width: 1100px;
         margin: 0 auto;
         padding: 2.4rem 2.8rem 5rem;
      }

      /* ===== PAGE HEADER ===== */
      .page-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 1rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideDown .45s ease both;
      }

      .page-header h1 {
         font-size: 2.6rem;
         font-weight: 800;
         color: #0f172a;
         letter-spacing: -.5px;
      }

      .page-header h1 span {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      /* ===== STATS STRIP ===== */
      .stats-strip {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 1.2rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideUp .5s ease both;
      }

      .stat-chip {
         background: #fff;
         border-radius: 1.2rem;
         padding: 1.6rem 2rem;
         box-shadow: 0 2px 10px rgba(0,0,0,.05);
         display: flex;
         align-items: center;
         gap: 1.4rem;
         position: relative;
         overflow: hidden;
         transition: transform .2s, box-shadow .2s;
      }

      .stat-chip:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }

      .stat-chip::before {
         content: '';
         position: absolute;
         top: 0; left: 0; right: 0;
         height: 3px;
         border-radius: 1.2rem 1.2rem 0 0;
      }

      .stat-chip.blue::before   { background: linear-gradient(90deg,#1a2a6c,#4f6ef7); }
      .stat-chip.green::before  { background: linear-gradient(90deg,#059669,#34d399); }
      .stat-chip.amber::before  { background: linear-gradient(90deg,#f59e0b,#fbbf24); }

      .stat-icon {
         width: 4.8rem; height: 4.8rem;
         border-radius: 1rem;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 2rem;
         flex-shrink: 0;
      }

      .stat-icon.bg-blue   { background: #eff2ff; color: #4f6ef7; }
      .stat-icon.bg-green  { background: #ecfdf5; color: #059669; }
      .stat-icon.bg-amber  { background: #fffbeb; color: #f59e0b; }

      .stat-info-val { font-size: 2.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
      .stat-info-lbl { font-size: 1.25rem; font-weight: 600; color: #64748b; margin-top: .3rem; }

      /* ===== ADD ADMIN BANNER ===== */
      .add-admin-banner {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 55%, #2c3e8f 100%);
         border-radius: 1.4rem;
         padding: 2.2rem 2.6rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1.6rem;
         flex-wrap: wrap;
         margin-bottom: 2.4rem;
         position: relative;
         overflow: hidden;
         animation: fadeSlideUp .5s .05s ease both;
      }

      .add-admin-banner::before {
         content: '';
         position: absolute;
         top: -50%; right: -5%;
         width: 28rem; height: 28rem;
         background: radial-gradient(circle, rgba(79,110,247,.3) 0%, transparent 70%);
         pointer-events: none;
      }

      .banner-text h3 { font-size: 1.8rem; font-weight: 700; color: #fff; margin-bottom: .4rem; }
      .banner-text p  { font-size: 1.3rem; color: rgba(255,255,255,.6); }

      .btn-register-admin {
         display: inline-flex;
         align-items: center;
         gap: .7rem;
         padding: 1.1rem 2.2rem;
         background: #fff;
         color: #1a2a6c;
         border-radius: .8rem;
         font-size: 1.4rem;
         font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
         white-space: nowrap;
         position: relative;
         z-index: 1;
      }

      .btn-register-admin:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }

      /* ===== SECTION LABEL ===== */
      .section-label {
         font-size: 1.1rem;
         font-weight: 700;
         letter-spacing: .12em;
         text-transform: uppercase;
         color: #94a3b8;
         margin-bottom: 1.2rem;
      }

      /* ===== ADMIN CARDS GRID ===== */
      .admin-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(28rem, 1fr));
         gap: 1.6rem;
      }

      /* ===== ADMIN CARD ===== */
      .admin-card {
         background: #fff;
         border-radius: 1.4rem;
         overflow: hidden;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         display: flex;
         flex-direction: column;
         transition: transform .22s, box-shadow .22s;
         animation: fadeSlideUp .5s ease both;
         position: relative;
      }

      .admin-card:hover { transform: translateY(-4px); box-shadow: 0 14px 36px rgba(0,0,0,.1); }

      .admin-card-bar { height: 4px; }
      .bar-you   { background: linear-gradient(90deg,#1a2a6c,#4f6ef7); }
      .bar-other { background: linear-gradient(90deg,#64748b,#94a3b8); }

      .admin-card-inner { padding: 2rem; display: flex; flex-direction: column; gap: 1.4rem; flex: 1; }

      /* Avatar */
      .admin-avatar-row {
         display: flex;
         align-items: center;
         gap: 1.4rem;
      }

      .admin-avatar {
         width: 5.2rem; height: 5.2rem;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 2rem;
         font-weight: 800;
         color: #fff;
         flex-shrink: 0;
      }

      .avatar-you   { background: linear-gradient(135deg,#1a2a6c,#4f6ef7); }
      .avatar-other { background: linear-gradient(135deg,#475569,#94a3b8); }

      .admin-name-block { flex: 1; min-width: 0; }

      .admin-name {
         font-size: 1.6rem;
         font-weight: 700;
         color: #0f172a;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .admin-id-tag {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         font-size: 1.2rem;
         color: #94a3b8;
         margin-top: .3rem;
         font-weight: 500;
      }

      /* You badge */
      .you-badge {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         padding: .4rem 1rem;
         background: #eff2ff;
         color: #1a2a6c;
         border-radius: 2rem;
         font-size: 1.15rem;
         font-weight: 700;
         flex-shrink: 0;
      }

      /* Divider */
      .admin-card-divider { height: 1px; background: #f1f5f9; }

      /* Actions */
      .admin-card-actions {
         display: flex;
         gap: .8rem;
         margin-top: auto;
      }

      .card-btn {
         display: inline-flex;
         align-items: center;
         justify-content: center;
         gap: .5rem;
         padding: .9rem 1.4rem;
         border-radius: .7rem;
         font-size: 1.3rem;
         font-weight: 600;
         text-decoration: none;
         border: none;
         cursor: pointer;
         transition: transform .15s, box-shadow .15s;
         font-family: inherit;
         flex: 1;
         white-space: nowrap;
      }

      .card-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.12); }
      .card-btn:active { transform: scale(.96); }

      .btn-update { background: #eff2ff; color: #1a2a6c; }
      .btn-update:hover { background: #e0e7ff; }

      .btn-delete { background: #fff1f2; color: #be123c; }
      .btn-delete:hover { background: #ffe4e6; }

      /* Empty */
      .accounts-empty { text-align: center; padding: 5rem 2rem; color: #94a3b8; }
      .accounts-empty i { font-size: 4.8rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .accounts-empty p { font-size: 1.6rem; }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      .admin-card:nth-child(1){animation-delay:.05s}
      .admin-card:nth-child(2){animation-delay:.10s}
      .admin-card:nth-child(3){animation-delay:.15s}
      .admin-card:nth-child(n+4){animation-delay:.20s}

      @media(max-width:700px){
         .accounts-page{padding:1.6rem}
         .stats-strip{grid-template-columns:1fr 1fr}
         .admin-grid{grid-template-columns:1fr}
      }
   </style>
</head>
<body>

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