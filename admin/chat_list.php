<?php

include '../components/connect.php';
include '../components/crypto.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

$select_users = $conn->prepare("
   SELECT
      cm.user_id,
      MAX(cm.created_at) AS last_time,
      SUM(CASE WHEN cm.sender = 'user' AND cm.is_read = 0 THEN 1 ELSE 0 END) AS unread_count,
      (SELECT message FROM chat_messages WHERE user_id = cm.user_id ORDER BY created_at DESC LIMIT 1) AS last_message,
      (SELECT sender  FROM chat_messages WHERE user_id = cm.user_id ORDER BY created_at DESC LIMIT 1) AS last_sender
   FROM chat_messages cm
   GROUP BY cm.user_id
   ORDER BY last_time DESC
");
$select_users->execute();

$total_unread_all = 0;
$rows = $select_users->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) $total_unread_all += $r['unread_count'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Daftar Chat</title>
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="php-admin-chat-list">

<?php include '../components/admin_header.php'; ?>

<div class="chat-list-page">

   <div class="page-header">
      <h1>Daftar <span>Chat</span></h1>
      <?php if($total_unread_all > 0): ?>
         <div class="total-unread-badge">
            <i class="fas fa-bell"></i>
            <?= $total_unread_all; ?> pesan belum dibaca
         </div>
      <?php endif; ?>
   </div>

   <?php if(count($rows) > 0): ?>
   <div class="chat-list">
      <?php foreach($rows as $row):
         $user_id   = $row['user_id'];
         $unread    = (int)$row['unread_count'];

         $select_user = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
         $select_user->execute([$user_id]);
         $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

         $user_name = 'User #'.$user_id;
         if($fetch_user){
            $decrypted = aes_decrypt($fetch_user['name']);
            $user_name = ($decrypted && $decrypted !== '') ? $decrypted : $fetch_user['name'];
         }

         $initial     = strtoupper(mb_substr($user_name, 0, 1));
         $last_msg    = htmlspecialchars($row['last_message'] ?? '');
         $last_sender = $row['last_sender'] ?? '';
         $last_time   = $row['last_time'] ?? '';

         // Format time
         $time_display = '';
         if($last_time){
            $ts = strtotime($last_time);
            $now = time();
            $diff = $now - $ts;
            if($diff < 60)           $time_display = 'Baru saja';
            elseif($diff < 3600)     $time_display = floor($diff/60).' mnt lalu';
            elseif($diff < 86400)    $time_display = floor($diff/3600).' jam lalu';
            elseif($diff < 604800)   $time_display = floor($diff/86400).' hari lalu';
            else                     $time_display = date('d M', $ts);
         }
      ?>
      <a href="chat.php?user_id=<?= $user_id; ?>" class="chat-card <?= $unread > 0 ? 'has-unread' : ''; ?>">

         <div class="chat-avatar">
            <?= htmlspecialchars($initial); ?>
         </div>

         <div class="chat-info">
            <div class="chat-info-top">
               <div class="chat-user-name"><?= htmlspecialchars($user_name); ?></div>
               <div class="chat-time"><?= $time_display; ?></div>
            </div>
            <div class="chat-last-msg">
               <?php if($last_sender === 'admin'): ?>
                  <i class="fas fa-reply"></i>
               <?php endif; ?>
               <?= mb_strlen($last_msg) > 55 ? mb_substr($last_msg,0,55).'…' : $last_msg; ?>
            </div>
         </div>

         <?php if($unread > 0): ?>
            <div class="unread-count"><?= $unread; ?></div>
         <?php endif; ?>

         <i class="fas fa-chevron-right chat-card-arrow"></i>
      </a>
      <?php endforeach; ?>
   </div>

   <?php else: ?>
      <div class="chat-empty">
         <i class="fas fa-comments"></i>
         <p>Belum ada chat dari pengguna.</p>
      </div>
   <?php endif; ?>

</div>

</body>
</html>