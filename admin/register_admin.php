<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

if(isset($_POST['submit'])){

   $name  = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $pass  = sha1($_POST['pass']);
   $cpass = sha1($_POST['cpass']);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $select_admin->execute([$name]);

   if($select_admin->rowCount() > 0){
      $reg_error = 'Username sudah digunakan. Pilih username lain.';
   } elseif($pass != $cpass) {
      $reg_error = 'Konfirmasi password tidak cocok.';
   } else {
      $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?,?)")->execute([$name, $cpass]);
      $reg_success = 'Admin baru berhasil didaftarkan!';
   }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-register-admin">

<?php include '../components/admin_header.php'; ?>

<div class="register-page">
   <div class="register-card">

      <!-- Banner -->
      <div class="register-banner">
         <div class="banner-icon"><i class="fas fa-user-plus"></i></div>
         <h2>Register Admin Baru</h2>
         <p>Tambahkan akun admin untuk mengelola toko bersama</p>
      </div>

      <!-- Body -->
      <div class="register-body">

         <?php if(isset($reg_error)): ?>
            <div class="alert alert-error"><i class="fas fa-circle-xmark"></i> <?= htmlspecialchars($reg_error); ?></div>
         <?php endif; ?>

         <?php if(isset($reg_success)): ?>
            <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($reg_success); ?></div>
         <?php endif; ?>

         <form action="" method="post" class="register-form">

            <div class="form-group">
               <label>Username <span class="u-inline-style-042">*</span></label>
               <div class="input-wrap">
                  <input type="text" name="name" class="reg-input" required maxlength="20"
                     placeholder="Masukkan username admin"
                     oninput="this.value = this.value.replace(/\s/g, '')">
                  <i class="fas fa-user field-icon"></i>
               </div>
            </div>

            <div class="form-group">
               <label>Password <span class="u-inline-style-042">*</span></label>
               <div class="input-wrap">
                  <input type="password" id="reg-pass" name="pass" class="reg-input" required maxlength="20"
                     placeholder="Masukkan password"
                     oninput="this.value = this.value.replace(/\s/g, ''); checkStrength(this.value)">
                  <i class="fas fa-lock field-icon"></i>
                  <i class="fas fa-eye toggle-pass" onclick="togglePass('reg-pass',this)"></i>
               </div>
               <div class="strength-bar-wrap"><div class="strength-bar" id="strength-bar"></div></div>
               <div class="strength-text" id="strength-text"></div>
            </div>

            <div class="form-group">
               <label>Konfirmasi Password <span class="u-inline-style-042">*</span></label>
               <div class="input-wrap">
                  <input type="password" id="reg-cpass" name="cpass" class="reg-input" required maxlength="20"
                     placeholder="Ulangi password"
                     oninput="this.value = this.value.replace(/\s/g, '')">
                  <i class="fas fa-lock field-icon"></i>
                  <i class="fas fa-eye toggle-pass" onclick="togglePass('reg-cpass',this)"></i>
               </div>
            </div>

            <button type="submit" name="submit" class="btn-register">
               <i class="fas fa-user-plus"></i> Daftarkan Admin
            </button>

         </form>

         <a href="admin_accounts.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Akun Admin
         </a>
      </div>
   </div>
</div>

<script src="../js/admin_script.js"></script>
<script>
   function togglePass(id, icon){
      const input = document.getElementById(id);
      if(input.type === 'password'){
         input.type = 'text';
         icon.classList.replace('fa-eye','fa-eye-slash');
      }else{
         input.type = 'password';
         icon.classList.replace('fa-eye-slash','fa-eye');
      }
   }

   function checkStrength(val){
      const bar  = document.getElementById('strength-bar');
      const text = document.getElementById('strength-text');
      let score = 0;
      if(val.length >= 6) score++;
      if(val.length >= 10) score++;
      if(/[A-Z]/.test(val)) score++;
      if(/[0-9]/.test(val)) score++;
      if(/[^a-zA-Z0-9]/.test(val)) score++;

      const levels = [
         { w:'0%',   c:'#e11d48', t:'' },
         { w:'25%',  c:'#e11d48', t:'Lemah' },
         { w:'50%',  c:'#f59e0b', t:'Cukup' },
         { w:'75%',  c:'#0891b2', t:'Kuat' },
         { w:'100%', c:'#059669', t:'Sangat Kuat' },
      ];
      const lvl = levels[Math.min(score, 4)];
      bar.style.width = lvl.w;
      bar.style.background = lvl.c;
      text.style.color = lvl.c;
      text.textContent = lvl.t;
   }
</script>
</body>
</html>