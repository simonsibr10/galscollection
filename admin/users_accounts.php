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
   $conn->prepare("DELETE FROM `users`    WHERE id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `orders`   WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `messages` WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `cart`     WHERE user_id = ?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?")->execute([$delete_id]);
   header('location:users_accounts.php');
   exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Akun Pengguna</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      section, .accounts, .dashboard { background: transparent !important; }
      .header { background: #fff !important; }
      .footer { background: #fff !important; }

      .users-page {
         max-width: 1200px;
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

      .page-header h1 { font-size: 2.6rem; font-weight: 800; color: #0f172a; letter-spacing: -.5px; }
      .page-header h1 span {
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      /* ===== STATS STRIP ===== */
      .stats-strip {
         display: grid;
         grid-template-columns: repeat(4, 1fr);
         gap: 1.2rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideUp .5s ease both;
      }

      .stat-chip {
         background: #fff;
         border-radius: 1.2rem;
         padding: 1.6rem 1.8rem;
         box-shadow: 0 2px 10px rgba(0,0,0,.05);
         display: flex;
         align-items: center;
         gap: 1.2rem;
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
      .stat-chip.cyan::before   { background: linear-gradient(90deg,#0891b2,#22d3ee); }
      .stat-chip.green::before  { background: linear-gradient(90deg,#059669,#34d399); }
      .stat-chip.rose::before   { background: linear-gradient(90deg,#e11d48,#fb7185); }

      .stat-icon {
         width: 4.4rem; height: 4.4rem;
         border-radius: .9rem;
         display: flex; align-items: center; justify-content: center;
         font-size: 1.8rem;
         flex-shrink: 0;
      }

      .bg-blue  { background: #eff2ff; color: #4f6ef7; }
      .bg-cyan  { background: #ecfeff; color: #0891b2; }
      .bg-green { background: #ecfdf5; color: #059669; }
      .bg-rose  { background: #fff1f2; color: #e11d48; }

      .stat-info-val { font-size: 2.4rem; font-weight: 800; color: #0f172a; line-height: 1; }
      .stat-info-lbl { font-size: 1.2rem; font-weight: 600; color: #64748b; margin-top: .3rem; }

      /* ===== TOOLBAR ===== */
      .users-toolbar {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 1rem;
         background: #fff;
         border-radius: 1.2rem;
         padding: 1.2rem 1.6rem;
         box-shadow: 0 2px 10px rgba(0,0,0,.05);
         margin-bottom: 2rem;
         animation: fadeSlideUp .5s .05s ease both;
      }

      .toolbar-left { font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: .8rem; }
      .toolbar-left i { color: #4f6ef7; }

      .search-wrap { position: relative; }
      .search-wrap i { position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.3rem; pointer-events: none; }

      #user-search {
         padding: .8rem 1.4rem .8rem 3.4rem;
         border: 1.5px solid #e2e8f0;
         border-radius: 3rem;
         font-size: 1.3rem;
         color: #0f172a;
         background: #f8fafc;
         outline: none;
         width: 24rem;
         transition: border-color .2s, box-shadow .2s;
         font-family: inherit;
      }

      #user-search:focus { border-color: #4f6ef7; box-shadow: 0 0 0 3px rgba(79,110,247,.12); background: #fff; }

      /* ===== USER TABLE ===== */
      .users-table-wrap {
         background: #fff;
         border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         overflow: hidden;
         animation: fadeSlideUp .55s ease both;
      }

      .users-table {
         width: 100%;
         border-collapse: collapse;
      }

      .users-table thead tr {
         background: #f8fafc;
         border-bottom: 2px solid #f1f5f9;
      }

      .users-table th {
         padding: 1.4rem 1.8rem;
         text-align: left;
         font-size: 1.2rem;
         font-weight: 700;
         color: #64748b;
         text-transform: uppercase;
         letter-spacing: .07em;
         white-space: nowrap;
      }

      .users-table tbody tr {
         border-bottom: 1px solid #f8fafc;
         transition: background .15s;
         animation: fadeSlideUp .4s ease both;
      }

      .users-table tbody tr:last-child { border-bottom: none; }
      .users-table tbody tr:hover { background: #f8fafc; }

      .users-table td { padding: 1.4rem 1.8rem; font-size: 1.35rem; color: #334155; vertical-align: middle; }

      /* Avatar cell */
      .user-avatar-cell { display: flex; align-items: center; gap: 1.2rem; }

      .user-avatar-circle {
         width: 4rem; height: 4rem;
         border-radius: 50%;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.5rem;
         font-weight: 800;
         color: #fff;
         flex-shrink: 0;
      }

      .user-name-text { font-weight: 600; color: #0f172a; }
      .user-id-text { font-size: 1.2rem; color: #94a3b8; margin-top: .2rem; }

      .user-email-text { color: #475569; }

      .id-badge {
         display: inline-flex;
         background: #f1f5f9;
         color: #475569;
         border-radius: 2rem;
         padding: .3rem .9rem;
         font-size: 1.2rem;
         font-weight: 600;
      }

      /* Action btn */
      .btn-del-user {
         display: inline-flex;
         align-items: center;
         gap: .5rem;
         padding: .7rem 1.4rem;
         border-radius: .6rem;
         background: #fff1f2;
         color: #be123c;
         font-size: 1.3rem;
         font-weight: 600;
         text-decoration: none;
         border: none;
         cursor: pointer;
         transition: background .15s, transform .15s;
         font-family: inherit;
         white-space: nowrap;
      }

      .btn-del-user:hover { background: #ffe4e6; transform: translateY(-1px); }

      /* Empty */
      .users-empty { text-align: center; padding: 5rem 2rem; color: #94a3b8; }
      .users-empty i { font-size: 4.8rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .users-empty p { font-size: 1.6rem; }

      /* Hidden row */
      .user-row-hidden { display: none !important; }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      .users-table tbody tr:nth-child(1){animation-delay:.04s}
      .users-table tbody tr:nth-child(2){animation-delay:.08s}
      .users-table tbody tr:nth-child(3){animation-delay:.12s}
      .users-table tbody tr:nth-child(4){animation-delay:.16s}
      .users-table tbody tr:nth-child(n+5){animation-delay:.20s}

      @media(max-width:900px){
         .users-page{padding:1.6rem}
         .stats-strip{grid-template-columns:1fr 1fr}
         .users-table th:nth-child(1),
         .users-table td:nth-child(1){display:none}
         #user-search{width:100%}
      }

      @media(max-width:600px){
         .stats-strip{grid-template-columns:1fr 1fr}
         .users-table{display:block;overflow-x:auto}
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="users-page">

   <?php
      $q = $conn->prepare("SELECT u.*, (SELECT COUNT(*) FROM `orders` WHERE user_id=u.id) as order_count FROM `users` u ORDER BY u.id DESC");
      $q->execute();
      $all_users = $q->fetchAll(PDO::FETCH_ASSOC);
      $total_users = count($all_users);

      $q_orders = $conn->prepare("SELECT COUNT(*) FROM `orders`");
      $q_orders->execute();
      $total_orders = $q_orders->fetchColumn();

      $q_carts = $conn->prepare("SELECT COUNT(*) FROM `cart`");
      $q_carts->execute();
      $total_carts = $q_carts->fetchColumn();
   ?>

   <!-- PAGE HEADER -->
   <div class="page-header">
      <h1>Kelola <span>Pengguna</span></h1>
   </div>

   <!-- STATS -->
   <div class="stats-strip">
      <div class="stat-chip blue">
         <div class="stat-icon bg-blue"><i class="fas fa-users"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_users; ?></div>
            <div class="stat-info-lbl">Total Pengguna</div>
         </div>
      </div>
      <div class="stat-chip cyan">
         <div class="stat-icon bg-cyan"><i class="fas fa-shopping-bag"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_orders; ?></div>
            <div class="stat-info-lbl">Total Pesanan</div>
         </div>
      </div>
      <div class="stat-chip green">
         <div class="stat-icon bg-green"><i class="fas fa-cart-shopping"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_carts; ?></div>
            <div class="stat-info-lbl">Item di Keranjang</div>
         </div>
      </div>
      <div class="stat-chip rose">
         <div class="stat-icon bg-rose"><i class="fas fa-user-check"></i></div>
         <div>
            <div class="stat-info-val"><?= $total_users > 0 ? round($total_orders / $total_users, 1) : 0; ?></div>
            <div class="stat-info-lbl">Rata Pesanan/User</div>
         </div>
      </div>
   </div>

   <!-- TOOLBAR -->
   <div class="users-toolbar">
      <div class="toolbar-left">
         <i class="fas fa-table"></i>
         <?= $total_users; ?> Pengguna Terdaftar
      </div>
      <div class="search-wrap">
         <i class="fas fa-search"></i>
         <input type="text" id="user-search" placeholder="Cari nama atau email..." oninput="filterUsers(this.value)">
      </div>
   </div>

   <!-- TABLE -->
   <?php if($total_users > 0): ?>
   <div class="users-table-wrap">
      <table class="users-table">
         <thead>
            <tr>
               <th>ID</th>
               <th>Pengguna</th>
               <th>Email</th>
               <th>Pesanan</th>
               <th>Aksi</th>
            </tr>
         </thead>
         <tbody id="users-tbody">
         <?php foreach($all_users as $idx => $acc):
            $initial = strtoupper(mb_substr($acc['name'], 0, 1));
         ?>
         <tr class="user-row" data-search="<?= strtolower(htmlspecialchars($acc['name'].' '.$acc['email'])); ?>">
            <td><span class="id-badge">#<?= htmlspecialchars($acc['id']); ?></span></td>
            <td>
               <div class="user-avatar-cell">
                  <div class="user-avatar-circle"><?= htmlspecialchars($initial); ?></div>
                  <div>
                     <div class="user-name-text"><?= htmlspecialchars($acc['name']); ?></div>
                  </div>
               </div>
            </td>
            <td class="user-email-text"><?= htmlspecialchars($acc['email']); ?></td>
            <td>
               <span style="font-weight:700;color:<?= $acc['order_count']>0?'#059669':'#94a3b8'; ?>">
                  <?= $acc['order_count']; ?> pesanan
               </span>
            </td>
            <td>
               <a
                  href="users_accounts.php?delete=<?= $acc['id']; ?>"
                  onclick="return confirm('Hapus akun \'<?= addslashes(htmlspecialchars($acc['name'])); ?>\'? Semua data terkait pengguna juga akan dihapus!');"
                  class="btn-del-user"
               >
                  <i class="fas fa-trash"></i> Hapus
               </a>
            </td>
         </tr>
         <?php endforeach; ?>
         </tbody>
      </table>
   </div>
   <?php else: ?>
      <div class="users-empty">
         <i class="fas fa-users-slash"></i>
         <p>Belum ada pengguna terdaftar.</p>
      </div>
   <?php endif; ?>

</div>

<script src="../js/admin_script.js"></script>
<script>
   function filterUsers(q){
      const rows = document.querySelectorAll('#users-tbody .user-row');
      const val = q.toLowerCase().trim();
      rows.forEach(r => {
         r.classList.toggle('user-row-hidden', val !== '' && !r.dataset.search.includes(val));
      });
   }
</script>
</body>
</html>