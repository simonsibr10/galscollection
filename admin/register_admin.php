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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      .header { background: #fff !important; }

      /* ===== PAGE ===== */
      .register-page {
         min-height: calc(100vh - 6.4rem);
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 3rem 1.6rem;
      }

      /* ===== CARD ===== */
      .register-card {
         background: #fff;
         border-radius: 2rem;
         box-shadow: 0 16px 48px rgba(0,0,0,.09);
         width: 100%;
         max-width: 48rem;
         overflow: hidden;
         animation: cardIn .55s cubic-bezier(.22,1,.36,1) both;
      }

      @keyframes cardIn {
         from { opacity:0; transform:translateY(28px) scale(.97); }
         to   { opacity:1; transform:translateY(0) scale(1); }
      }

      /* Banner top */
      .register-banner {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 55%, #2c3e8f 100%);
         padding: 2.8rem 3.2rem;
         position: relative;
         overflow: hidden;
      }

      .register-banner::before {
         content: '';
         position: absolute;
         top: -40%; right: -8%;
         width: 28rem; height: 28rem;
         background: radial-gradient(circle, rgba(79,110,247,.35) 0%, transparent 70%);
         pointer-events: none;
      }

      .banner-icon {
         width: 5.6rem; height: 5.6rem;
         border-radius: 1.2rem;
         background: rgba(255,255,255,.15);
         display: flex; align-items: center; justify-content: center;
         font-size: 2.4rem;
         color: #fff;
         margin-bottom: 1.2rem;
         position: relative; z-index: 1;
      }

      .register-banner h2 {
         font-size: 2.2rem;
         font-weight: 800;
         color: #fff;
         margin-bottom: .4rem;
         position: relative; z-index: 1;
      }

      .register-banner p { font-size: 1.3rem; color: rgba(255,255,255,.65); position: relative; z-index: 1; }

      /* Form body */
      .register-body { padding: 3rem 3.2rem 3.2rem; }

      /* Alerts */
      .alert {
         padding: 1.2rem 1.4rem;
         border-radius: .8rem;
         font-size: 1.35rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         margin-bottom: 2rem;
         animation: fadeUp .3s ease both;
      }

      @keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

      .alert-error   { background: #fff1f2; border-left: 4px solid #e11d48; color: #be123c; }
      .alert-success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }

      /* Form */
      .register-form { display: flex; flex-direction: column; gap: 1.6rem; }

      .form-group { display: flex; flex-direction: column; gap: .6rem; }

      .form-group label { font-size: 1.3rem; font-weight: 700; color: #475569; }

      .input-wrap { position: relative; }

      .input-wrap i.field-icon {
         position: absolute;
         left: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8;
         font-size: 1.4rem;
         pointer-events: none;
         transition: color .2s;
      }

      .input-wrap:focus-within i.field-icon { color: #4f6ef7; }

      .reg-input {
         width: 100%;
         padding: 1.2rem 1.4rem 1.2rem 4rem;
         border: 1.5px solid #e2e8f0;
         border-radius: .9rem;
         font-size: 1.4rem;
         color: #0f172a;
         background: #f8fafc;
         outline: none;
         font-family: inherit;
         transition: border-color .2s, box-shadow .2s, background .2s;
      }

      .reg-input:focus {
         border-color: #4f6ef7;
         background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.12);
      }

      .toggle-pass {
         position: absolute;
         right: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8;
         cursor: pointer;
         font-size: 1.4rem;
         transition: color .2s;
      }

      .toggle-pass:hover { color: #4f6ef7; }

      /* Password strength */
      .strength-bar-wrap { margin-top: .6rem; height: .4rem; background: #e2e8f0; border-radius: 2rem; overflow: hidden; }
      .strength-bar { height: 100%; width: 0; border-radius: 2rem; transition: width .3s, background .3s; }
      .strength-text { font-size: 1.2rem; color: #94a3b8; margin-top: .4rem; }

      /* Submit */
      .btn-register {
         width: 100%;
         padding: 1.4rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         border: none;
         border-radius: .9rem;
         font-size: 1.5rem;
         font-weight: 700;
         cursor: pointer;
         transition: transform .15s, box-shadow .2s;
         font-family: inherit;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: .7rem;
         margin-top: .4rem;
      }

      .btn-register:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,110,247,.35); }
      .btn-register:active { transform: scale(.97); }

      .back-link {
         display: inline-flex;
         align-items: center;
         gap: .6rem;
         font-size: 1.35rem;
         color: #64748b;
         text-decoration: none;
         margin-top: 1.8rem;
         transition: color .15s;
      }

      .back-link:hover { color: #1a2a6c; }
   </style>
</head>
<body>

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
               <label>Username <span style="color:#e11d48">*</span></label>
               <div class="input-wrap">
                  <input type="text" name="name" class="reg-input" required maxlength="20"
                     placeholder="Masukkan username admin"
                     oninput="this.value = this.value.replace(/\s/g, '')">
                  <i class="fas fa-user field-icon"></i>
               </div>
            </div>

            <div class="form-group">
               <label>Password <span style="color:#e11d48">*</span></label>
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
               <label>Konfirmasi Password <span style="color:#e11d48">*</span></label>
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