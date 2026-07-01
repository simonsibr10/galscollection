<?php

include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:dashboard.php');
      exit;
   }else{
      $login_error = true;
   }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login — Gals Collection</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-admin-login">

   <div class="bg-blob blob-1"></div>
   <div class="bg-blob blob-2"></div>
   <div class="bg-blob blob-3"></div>

   <div class="login-card">

      <div class="login-brand">
         <div class="brand-icon"><i class="fas fa-store"></i></div>
         <div class="brand-name">
            <h1>Gals Collection</h1>
            <p>Admin Panel — Masuk untuk melanjutkan</p>
         </div>
      </div>

      <?php if(isset($login_error)): ?>
         <div class="login-error">
            <i class="fas fa-circle-xmark"></i>
            Username atau password salah. Coba lagi.
         </div>
      <?php endif; ?>

      <form action="" method="post" class="login-form">

         <div class="form-group">
            <label for="name">Username</label>
            <div class="input-wrap">
               <input type="text" id="name" name="name" class="login-input" required maxlength="20"
                  placeholder="Masukkan username"
                  oninput="this.value = this.value.replace(/\s/g, '')"
                  autocomplete="username">
               <i class="fas fa-user"></i>
            </div>
         </div>

         <div class="form-group">
            <label for="pass">Password</label>
            <div class="input-wrap">
               <input type="password" id="pass" name="pass" class="login-input" required maxlength="20"
                  placeholder="Masukkan password"
                  oninput="this.value = this.value.replace(/\s/g, '')"
                  autocomplete="current-password">
               <i class="fas fa-lock"></i>
               <i class="fas fa-eye toggle-pass" onclick="togglePassword()"></i>
            </div>
         </div>

         <button type="submit" name="submit" class="btn-login">
            <i class="fas fa-right-to-bracket"></i> Login Sekarang
         </button>

      </form>

      <div class="login-hint">
         Default: username <strong>admin</strong> &nbsp;|&nbsp; password <strong>111</strong>
      </div>

   </div>

   <script>
      function togglePassword(){
         const input = document.getElementById('pass');
         const icon  = document.querySelector('.toggle-pass');
         if(input.type === 'password'){
            input.type = 'text';
            icon.classList.replace('fa-eye','fa-eye-slash');
         }else{
            input.type = 'password';
            icon.classList.replace('fa-eye-slash','fa-eye');
         }
      }
   </script>
</body>
</html>
