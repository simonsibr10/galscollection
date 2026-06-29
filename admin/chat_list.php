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
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      section, .dashboard { background: transparent !important; }
      .header { background: #fff !important; }

      .chat-list-page {
         max-width: 860px;
         margin: 0 auto;
         padding: 2.4rem 2rem 5rem;
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

      .total-unread-badge {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         background: #fff1f2;
         color: #be123c;
         border-radius: 3rem;
         padding: .7rem 1.4rem;
         font-size: 1.3rem;
         font-weight: 700;
         box-shadow: 0 2px 8px rgba(0,0,0,.06);
      }

      /* ===== CHAT LIST ===== */
      .chat-list { display: flex; flex-direction: column; gap: 1rem; }

      /* ===== CHAT CARD ===== */
      .chat-card {
         background: #fff;
         border-radius: 1.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         display: flex;
         align-items: center;
         gap: 1.6rem;
         padding: 1.6rem 2rem;
         text-decoration: none;
         transition: transform .2s, box-shadow .2s;
         animation: fadeSlideUp .5s ease both;
         position: relative;
         overflow: hidden;
      }

      .chat-card::before {
         content: '';
         position: absolute;
         left: 0; top: 0; bottom: 0;
         width: 4px;
         background: #e2e8f0;
         border-radius: 4px 0 0 4px;
         transition: background .2s;
      }

      .chat-card.has-unread::before { background: linear-gradient(180deg,#e11d48,#fb7185); }
      .chat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.1); }

      /* Avatar */
      .chat-avatar {
         width: 5.2rem; height: 5.2rem;
         border-radius: 50%;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 2rem;
         font-weight: 800;
         color: #fff;
         flex-shrink: 0;
         position: relative;
      }

      .avatar-online-dot {
         position: absolute;
         bottom: .2rem; right: .2rem;
         width: 1.2rem; height: 1.2rem;
         background: #059669;
         border-radius: 50%;
         border: 2px solid #fff;
      }

      /* Info */
      .chat-info { flex: 1; min-width: 0; }

      .chat-info-top {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: .8rem;
         margin-bottom: .4rem;
      }

      .chat-user-name {
         font-size: 1.5rem;
         font-weight: 700;
         color: #0f172a;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .chat-time {
         font-size: 1.2rem;
         color: #94a3b8;
         white-space: nowrap;
         flex-shrink: 0;
      }

      .chat-last-msg {
         font-size: 1.3rem;
         color: #64748b;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
         display: flex;
         align-items: center;
         gap: .5rem;
      }

      .chat-last-msg i { font-size: 1.1rem; color: #94a3b8; }

      /* Unread badge */
      .unread-count {
         background: #e11d48;
         color: #fff;
         border-radius: 1.2rem;
         padding: .3rem .8rem;
         font-size: 1.2rem;
         font-weight: 800;
         flex-shrink: 0;
         min-width: 2.4rem;
         text-align: center;
         box-shadow: 0 2px 6px rgba(225,29,72,.35);
         animation: badgePop .3s ease both;
      }

      @keyframes badgePop { from{transform:scale(0)} to{transform:scale(1)} }

      /* Open chat arrow */
      .chat-card-arrow { color: #cbd5e1; font-size: 1.6rem; flex-shrink: 0; transition: color .2s, transform .2s; }
      .chat-card:hover .chat-card-arrow { color: #4f6ef7; transform: translateX(3px); }

      /* Empty */
      .chat-empty { text-align: center; padding: 6rem 2rem; color: #94a3b8; }
      .chat-empty i { font-size: 5rem; display: block; margin-bottom: 1.4rem; color: #cbd5e1; }
      .chat-empty p { font-size: 1.6rem; }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      .chat-card:nth-child(1){animation-delay:.04s}
      .chat-card:nth-child(2){animation-delay:.08s}
      .chat-card:nth-child(3){animation-delay:.12s}
      .chat-card:nth-child(4){animation-delay:.16s}
      .chat-card:nth-child(n+5){animation-delay:.20s}

      @media(max-width:600px){
         .chat-list-page{padding:1.6rem 1.2rem 4rem}
         .chat-card{padding:1.2rem 1.4rem; gap:1.2rem}
      }
   </style>
</head>
<body>

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