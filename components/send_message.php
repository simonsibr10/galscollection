<?php

include 'connect.php';
session_start();

if(!isset($_POST['sender']) || !isset($_POST['message'])){
   exit;
}

$sender = trim($_POST['sender']);
$message = trim($_POST['message']);

if($message === ''){
   exit;
}

$message = filter_var($message, FILTER_SANITIZE_STRING);

if($sender === 'user'){
   if(!isset($_SESSION['user_id'])){
      exit;
   }

   $user_id = (int)$_SESSION['user_id'];

   $insert_message = $conn->prepare("INSERT INTO `chat_messages` (user_id, sender, message, is_read) VALUES (?, 'user', ?, 0)");
   $insert_message->execute([$user_id, $message]);

   echo 'success';
   exit;
}

if($sender === 'admin'){
   if(!isset($_SESSION['admin_id']) || !isset($_POST['user_id'])){
      exit;
   }

   $user_id = (int)$_POST['user_id'];

   $insert_message = $conn->prepare("INSERT INTO `chat_messages` (user_id, sender, message, is_read) VALUES (?, 'admin', ?, 0)");
   $insert_message->execute([$user_id, $message]);

   echo 'success';
   exit;
}

exit;
?>