<?php

include 'connect.php';
include 'crypto.php';
session_start();

if(!isset($_GET['viewer'])){
   exit;
}

$viewer = trim($_GET['viewer']);
$user_id = 0;

if($viewer === 'user'){
   if(!isset($_SESSION['user_id'])){
      exit;
   }

   $user_id = (int)$_SESSION['user_id'];

   // saat user buka chat, semua pesan dari admin dianggap sudah dibaca
   $mark_read = $conn->prepare("UPDATE `chat_messages` SET is_read = 1 WHERE user_id = ? AND sender = 'admin'");
   $mark_read->execute([$user_id]);
}
elseif($viewer === 'admin'){
   if(!isset($_SESSION['admin_id']) || !isset($_GET['user_id'])){
      exit;
   }

   $user_id = (int)$_GET['user_id'];

   // saat admin buka chat user tertentu, semua pesan dari user dianggap sudah dibaca
   $mark_read = $conn->prepare("UPDATE `chat_messages` SET is_read = 1 WHERE user_id = ? AND sender = 'user'");
   $mark_read->execute([$user_id]);
}
else{
   exit;
}

$user_name = 'User';

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

if($fetch_user){
   $decrypted_name = aes_decrypt($fetch_user['name']);
   if($decrypted_name !== false && $decrypted_name !== '' && $decrypted_name !== null){
      $user_name = $decrypted_name;
   }else{
      $user_name = $fetch_user['name'];
   }
}

$select_messages = $conn->prepare("SELECT * FROM `chat_messages` WHERE user_id = ? ORDER BY id ASC");
$select_messages->execute([$user_id]);

while($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)){

   $sender = $fetch_message['sender'];

   // posisi bubble berdasarkan siapa yang sedang melihat
   if($viewer === 'user'){
      $is_me = ($sender === 'user');
   }else{
      $is_me = ($sender === 'admin');
   }

   $row_class = $is_me ? 'msg-right' : 'msg-left';
   $sender_name = ($sender === 'admin') ? 'Gals_Collection' : $user_name;

   echo '<div class="chat-row '.$row_class.'">';
      echo '<div class="chat-bubble">';
         echo '<div class="chat-sender">'.htmlspecialchars($sender_name).'</div>';
         echo '<div class="chat-text">'.nl2br(htmlspecialchars($fetch_message['message'])).'</div>';
         echo '<div class="chat-time">'.htmlspecialchars($fetch_message['created_at']).'</div>';
      echo '</div>';
   echo '</div>';
}
?>