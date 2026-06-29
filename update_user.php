<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'components/connect.php';
include 'components/crypto.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   header('location:user_login.php');
   exit;
}

/* =========================
   AMBIL DATA USER
========================= */
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$profile_name = aes_decrypt($fetch_profile['name']);
$profile_email = aes_decrypt($fetch_profile['email_enc']);

/* =========================
   UPDATE PROFILE
========================= */
if(isset($_POST['submit'])){

   $name = trim($_POST['name'] ?? '');
   $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

   // ENKRIPSI
   $name_enc  = aes_encrypt($name);
   $email_enc = aes_encrypt($email);
   $email_hash = email_lookup_hash($email);

   // UPDATE DATA
   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email_enc = ?, email_hash = ? WHERE id = ?");
   $update_profile->execute([$name_enc, $email_enc, $email_hash, $user_id]);

   $message[] = 'Profile berhasil diupdate!';

   /* =========================
      UPDATE PASSWORD
   ========================= */

   $old_pass = $_POST['old_pass'];
   $new_pass = $_POST['new_pass'];
   $cpass    = $_POST['cpass'];

   if(!empty($old_pass) || !empty($new_pass) || !empty($cpass)){

      if(!password_verify($old_pass, $fetch_profile['password'])){
         $message[] = 'Password lama salah!';
      }
      elseif(strlen($new_pass) < 8){
         $message[] = 'Password minimal 8 karakter!';
      }
      elseif($new_pass != $cpass){
         $message[] = 'Konfirmasi password tidak cocok!';
      }
      else{
         $hashed = password_hash($new_pass, PASSWORD_BCRYPT);

         $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass->execute([$hashed, $user_id]);

         $message[] = 'Password berhasil diupdate!';
      }
   }

   // refresh data
   $select_profile->execute([$user_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

   $profile_name = aes_decrypt($fetch_profile['name']);
   $profile_email = aes_decrypt($fetch_profile['email_enc']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Update Profile</h3>

      <input type="text" name="name" required 
         placeholder="Masukkan username" 
         class="box" 
         value="<?= htmlspecialchars($profile_name); ?>">

      <input type="email" name="email" required 
         placeholder="Masukkan email" 
         class="box"
         value="<?= htmlspecialchars($profile_email); ?>">

      <input type="password" name="old_pass" 
         placeholder="Password lama" 
         class="box">

      <input type="password" name="new_pass" 
         placeholder="Password baru (min 8 karakter)" 
         class="box">

      <input type="password" name="cpass" 
         placeholder="Konfirmasi password" 
         class="box">

      <input type="submit" value="Update Sekarang" class="btn" name="submit">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>