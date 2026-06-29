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
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body {
         min-height: 100vh;
         background: #f0f2f8;
         font-family: 'Segoe UI', sans-serif;
         display: flex;
         align-items: center;
         justify-content: center;
         position: relative;
         overflow: hidden;
      }

      /* Animated background blobs */
      .bg-blob {
         position: fixed;
         border-radius: 50%;
         filter: blur(80px);
         opacity: .25;
         animation: blobFloat 8s ease-in-out infinite;
         pointer-events: none;
         z-index: 0;
      }

      .blob-1 { width: 50rem; height: 50rem; background: #4f6ef7; top: -15rem; left: -12rem; animation-delay: 0s; }
      .blob-2 { width: 40rem; height: 40rem; background: #7c3aed; bottom: -10rem; right: -10rem; animation-delay: 3s; }
      .blob-3 { width: 30rem; height: 30rem; background: #0891b2; bottom: 20%; left: 10%; animation-delay: 1.5s; }

      @keyframes blobFloat {
         0%,100% { transform: translateY(0) scale(1); }
         50%      { transform: translateY(-30px) scale(1.05); }
      }

      /* ===== CARD ===== */
      .login-card {
         background: #fff;
         border-radius: 2rem;
         box-shadow: 0 24px 64px rgba(0,0,0,.12);
         padding: 4rem 4rem 3.6rem;
         width: 100%;
         max-width: 44rem;
         position: relative;
         z-index: 1;
         animation: cardIn .55s cubic-bezier(.22,1,.36,1) both;
      }

      @keyframes cardIn {
         from { opacity: 0; transform: translateY(30px) scale(.97); }
         to   { opacity: 1; transform: translateY(0) scale(1); }
      }

      /* Top bar */
      .login-card::before {
         content: '';
         position: absolute;
         top: 0; left: 0; right: 0;
         height: 4px;
         border-radius: 2rem 2rem 0 0;
         background: linear-gradient(90deg, #1a2a6c, #4f6ef7, #0891b2);
      }

      /* Brand */
      .login-brand {
         display: flex;
         flex-direction: column;
         align-items: center;
         gap: 1.2rem;
         margin-bottom: 3rem;
         animation: fadeUp .5s .1s ease both;
      }

      @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

      .brand-icon {
         width: 6.4rem; height: 6.4rem;
         border-radius: 1.4rem;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 2.8rem;
         color: #fff;
         box-shadow: 0 8px 24px rgba(79,110,247,.35);
      }

      .brand-name {
         text-align: center;
      }

      .brand-name h1 {
         font-size: 2.2rem;
         font-weight: 800;
         color: #0f172a;
         letter-spacing: -.3px;
      }

      .brand-name p {
         font-size: 1.3rem;
         color: #94a3b8;
         margin-top: .3rem;
      }

      /* Error alert */
      .login-error {
         background: #fff1f2;
         border-left: 4px solid #e11d48;
         border-radius: .8rem;
         padding: 1.1rem 1.4rem;
         font-size: 1.35rem;
         color: #be123c;
         display: flex;
         align-items: center;
         gap: .8rem;
         margin-bottom: 2rem;
         animation: fadeUp .3s ease both;
      }

      /* Form */
      .login-form {
         display: flex;
         flex-direction: column;
         gap: 1.4rem;
         animation: fadeUp .5s .2s ease both;
      }

      .form-group {
         display: flex;
         flex-direction: column;
         gap: .6rem;
      }

      .form-group label {
         font-size: 1.3rem;
         font-weight: 700;
         color: #475569;
      }

      .input-wrap {
         position: relative;
      }

      .input-wrap i {
         position: absolute;
         left: 1.4rem;
         top: 50%;
         transform: translateY(-50%);
         color: #94a3b8;
         font-size: 1.4rem;
         pointer-events: none;
         transition: color .2s;
      }

      .login-input {
         width: 100%;
         padding: 1.2rem 1.4rem 1.2rem 4rem;
         border: 1.5px solid #e2e8f0;
         border-radius: .9rem;
         font-size: 1.45rem;
         color: #0f172a;
         background: #f8fafc;
         outline: none;
         font-family: inherit;
         transition: border-color .2s, box-shadow .2s, background .2s;
      }

      .login-input:focus {
         border-color: #4f6ef7;
         background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.13);
      }

      .login-input:focus + i,
      .input-wrap:focus-within i { color: #4f6ef7; }

      /* Toggle password */
      .toggle-pass {
         position: absolute;
         right: 1.4rem;
         top: 50%;
         transform: translateY(-50%);
         color: #94a3b8;
         cursor: pointer;
         font-size: 1.4rem;
         pointer-events: all;
         z-index: 2;
         transition: color .2s;
      }

      .toggle-pass:hover { color: #4f6ef7; }

      /* Submit */
      .btn-login {
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

      .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,110,247,.35); }
      .btn-login:active { transform: scale(.97); }

      /* Hint */
      .login-hint {
         margin-top: 2rem;
         padding: 1.2rem 1.4rem;
         background: #f8fafc;
         border-radius: .8rem;
         font-size: 1.25rem;
         color: #64748b;
         text-align: center;
         border: 1px dashed #e2e8f0;
         animation: fadeUp .5s .3s ease both;
      }

      .login-hint strong { color: #1a2a6c; }
   </style>
</head>
<body>

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