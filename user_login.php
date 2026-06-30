<?php

include 'components/connect.php';
include 'components/crypto.php';
session_start();

if(isset($_SESSION['user_id'])){
   header('location:index.php');
   exit;
}

$login_error = false;
$login_success = false;

if(isset($_POST['submit'])){

   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass  = $_POST['pass'];

   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $login_error = 'Format email tidak valid.';
   } else {
      $email_hash = email_lookup_hash($email);

      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email_hash = ? LIMIT 1");
      $select_user->execute([$email_hash]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if(!$row){
         $select_old = $conn->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
         $select_old->execute([$email]);
         $row = $select_old->fetch(PDO::FETCH_ASSOC);
      }

      if($row && password_verify($pass, $row['password'])){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
         exit;
      } else {
         $login_error = 'Email atau password salah. Coba lagi.';
      }
   }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="php-user-login">

<?php include 'components/user_header.php'; ?>

<div class="auth-layout">

   <!-- ===== LEFT VISUAL ===== -->
   <div class="auth-visual">
      <div class="visual-content">
         <div class="visual-icon-wrap">
            <i class="fas fa-bag-shopping"></i>
         </div>
         <h2>Selamat Datang di Gals Collection</h2>
         <p>Temukan koleksi tas dan aksesori terbaru dengan kualitas premium dan harga terjangkau.</p>

         <div class="feature-pills">
            <div class="feature-pill">
               <i class="fas fa-tags"></i>
               Ribuan koleksi tas pilihan terbaik
            </div>
            <div class="feature-pill">
               <i class="fas fa-truck-fast"></i>
               Pengiriman cepat ke seluruh Indonesia
            </div>
            <div class="feature-pill">
               <i class="fas fa-shield-halved"></i>
               Belanja aman dengan enkripsi data
            </div>
         </div>
      </div>
   </div>

   <!-- ===== RIGHT FORM ===== -->
   <div class="auth-form-panel">
      <div class="auth-form-inner">

         <!-- Brand -->
         <div class="form-brand">
            <div class="form-brand-icon"><i class="fas fa-bag-shopping"></i></div>
            <div class="form-brand-text">
               <strong>Gals Collection</strong>
               <span>Fashion & Accessories</span>
            </div>
         </div>

         <!-- Heading -->
         <div class="form-heading">
            <h3>Masuk ke Akun</h3>
            <p>Belum punya akun? <a class="u-inline-style-031" href="user_register.php">Daftar sekarang</a></p>
         </div>

         <!-- Alert -->
         <?php if($login_error): ?>
            <div class="auth-alert error">
               <i class="fas fa-circle-xmark"></i>
               <?= htmlspecialchars($login_error); ?>
            </div>
         <?php endif; ?>

         <!-- Form -->
         <form action="" method="post" class="auth-form">

            <div class="auth-field">
               <label for="email"><i class="fas fa-envelope u-inline-style-025"></i> Email</label>
               <div class="auth-input-wrap">
                  <input type="email" id="email" name="email" class="auth-input" required maxlength="50"
                     placeholder="nama@email.com"
                     oninput="this.value = this.value.replace(/\s/g, '')"
                     autocomplete="email">
                  <i class="fas fa-envelope ai"></i>
               </div>
            </div>

            <div class="auth-field">
               <label for="pass"><i class="fas fa-lock u-inline-style-025"></i> Password</label>
               <div class="auth-input-wrap">
                  <input type="password" id="pass" name="pass" class="auth-input" required maxlength="50"
                     placeholder="Masukkan password"
                     oninput="this.value = this.value.replace(/\s/g, '')"
                     autocomplete="current-password">
                  <i class="fas fa-lock ai"></i>
                  <i class="fas fa-eye toggle-eye" onclick="toggleEye('pass',this)"></i>
               </div>
            </div>

            <button type="submit" name="submit" class="btn-auth">
               <i class="fas fa-right-to-bracket"></i> Masuk Sekarang
            </button>

         </form>

         <div class="auth-divider">atau</div>

         <div class="auth-switch">
            Belum punya akun?
            <a href="user_register.php">Daftar Gratis</a>
         </div>

         <!-- Trust badges -->
         <div class="trust-badges">
            <div class="trust-badge"><i class="fas fa-lock"></i> Aman & Terenkripsi</div>
            <div class="trust-badge"><i class="fas fa-shield-halved"></i> Data Terlindungi</div>
            <div class="trust-badge"><i class="fas fa-star"></i> Terpercaya</div>
         </div>

      </div>
   </div>

</div>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
   function toggleEye(id, icon){
      const inp = document.getElementById(id);
      inp.type  = inp.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
   }
</script>
</body>
</html>