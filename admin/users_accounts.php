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
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-users-accounts">

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
               <span class="order-count-status <?= $acc['order_count']>0?'status-positive':'status-muted'; ?>">
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
