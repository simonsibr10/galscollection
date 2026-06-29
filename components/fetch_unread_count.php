<?php

include 'connect.php';
session_start();

$count = 0;

if(isset($_GET['role']) && $_GET['role'] == 'user' && isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];

   $q = $conn->prepare("SELECT COUNT(*) AS total FROM `chat_messages` WHERE user_id = ? AND sender = 'admin' AND is_read = 0");
   $q->execute([$user_id]);
   $row = $q->fetch(PDO::FETCH_ASSOC);
   $count = $row['total'] ?? 0;
}

if(isset($_GET['role']) && $_GET['role'] == 'admin' && isset($_SESSION['admin_id'])){
   $q = $conn->prepare("SELECT COUNT(*) AS total FROM `chat_messages` WHERE sender = 'user' AND is_read = 0");
   $q->execute();
   $row = $q->fetch(PDO::FETCH_ASSOC);
   $count = $row['total'] ?? 0;
}

header('Content-Type: application/json');
echo json_encode(['count' => (int)$count]);