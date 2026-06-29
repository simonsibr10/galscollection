<?php

include 'components/connect.php';
include 'components/crypto.php';
session_start();

if(isset($_SESSION['user_id'])){
   header('location:home.php');
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
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body {
         min-height: 100vh;
         background: #fafafa;
         font-family: 'Segoe UI', sans-serif;
         display: flex;
         flex-direction: column;
      }

      /* ===== AUTH LAYOUT ===== */
      .auth-layout {
         flex: 1;
         display: grid;
         grid-template-columns: 1fr 1fr;
         min-height: calc(100vh - 8rem);
      }

      /* ===== LEFT PANEL — Decorative ===== */
      .auth-visual {
         background: linear-gradient(160deg, #1a2a6c 0%, #0f2027 40%, #2c3e8f 100%);
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         padding: 5rem 4rem;
         position: relative;
         overflow: hidden;
      }

      /* Floating decoration circles */
      .auth-visual::before {
         content: '';
         position: absolute;
         top: -8rem; left: -8rem;
         width: 40rem; height: 40rem;
         border-radius: 50%;
         background: radial-gradient(circle, rgba(79,110,247,.3) 0%, transparent 65%);
         animation: floatBlob 7s ease-in-out infinite;
      }

      .auth-visual::after {
         content: '';
         position: absolute;
         bottom: -6rem; right: -6rem;
         width: 32rem; height: 32rem;
         border-radius: 50%;
         background: radial-gradient(circle, rgba(255,255,255,.06) 0%, transparent 65%);
         animation: floatBlob 9s ease-in-out infinite reverse;
      }

      @keyframes floatBlob {
         0%,100% { transform: translate(0,0) scale(1); }
         50%      { transform: translate(20px,-20px) scale(1.06); }
      }

      .visual-content {
         position: relative;
         z-index: 1;
         text-align: center;
      }

      .visual-icon-wrap {
         width: 10rem; height: 10rem;
         border-radius: 50%;
         background: rgba(255,255,255,.1);
         border: 2px solid rgba(255,255,255,.2);
         display: flex; align-items: center; justify-content: center;
         font-size: 4.4rem; color: #fff;
         margin: 0 auto 2.4rem;
         animation: iconPulse 3s ease-in-out infinite;
      }

      @keyframes iconPulse {
         0%,100% { box-shadow: 0 0 0 0 rgba(255,255,255,.15); }
         50%      { box-shadow: 0 0 0 20px rgba(255,255,255,0); }
      }

      .visual-content h2 {
         font-size: 3.2rem;
         font-weight: 800;
         color: #fff;
         margin-bottom: 1rem;
         letter-spacing: -.5px;
         line-height: 1.2;
      }

      .visual-content p {
         font-size: 1.5rem;
         color: rgba(255,255,255,.65);
         line-height: 1.7;
         max-width: 34rem;
         margin: 0 auto 3rem;
      }

      /* Feature pills */
      .feature-pills {
         display: flex;
         flex-direction: column;
         gap: 1.2rem;
         width: 100%;
         max-width: 32rem;
         margin: 0 auto;
      }

      .feature-pill {
         display: flex;
         align-items: center;
         gap: 1.2rem;
         background: rgba(255,255,255,.1);
         border: 1px solid rgba(255,255,255,.15);
         border-radius: 1rem;
         padding: 1.2rem 1.6rem;
         color: rgba(255,255,255,.9);
         font-size: 1.35rem;
         font-weight: 500;
         animation: fadeSlideRight .6s ease both;
      }

      .feature-pill:nth-child(1){animation-delay:.1s}
      .feature-pill:nth-child(2){animation-delay:.2s}
      .feature-pill:nth-child(3){animation-delay:.3s}

      @keyframes fadeSlideRight {
         from{opacity:0;transform:translateX(-20px)}
         to  {opacity:1;transform:translateX(0)}
      }

      .feature-pill i {
         width: 3.2rem; height: 3.2rem;
         border-radius: .7rem;
         background: rgba(255,255,255,.18);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.5rem;
         flex-shrink: 0;
      }

      /* ===== RIGHT PANEL — Form ===== */
      .auth-form-panel {
         background: #fff;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         padding: 5rem 6rem;
         position: relative;
      }

      .auth-form-inner {
         width: 100%;
         max-width: 42rem;
         animation: fadeSlideUp .55s cubic-bezier(.22,1,.36,1) both;
      }

      @keyframes fadeSlideUp {
         from{opacity:0;transform:translateY(24px)}
         to  {opacity:1;transform:translateY(0)}
      }

      /* Brand */
      .form-brand {
         display: flex;
         align-items: center;
         gap: 1rem;
         margin-bottom: 3.2rem;
      }

      .form-brand-icon {
         width: 4.4rem; height: 4.4rem;
         border-radius: 1rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 2rem; color: #fff;
         box-shadow: 0 4px 14px rgba(79,110,247,.35);
      }

      .form-brand-text { line-height: 1.2; }
      .form-brand-text strong { display: block; font-size: 2rem; font-weight: 800; color: #0f172a; }
      .form-brand-text span   { font-size: 1.3rem; color: #94a3b8; font-weight: 500; }

      /* Heading */
      .form-heading { margin-bottom: 2.8rem; }
      .form-heading h3 { font-size: 2.6rem; font-weight: 800; color: #0f172a; margin-bottom: .5rem; }
      .form-heading p  { font-size: 1.4rem; color: #64748b; }

      /* Alert */
      .auth-alert {
         padding: 1.2rem 1.4rem;
         border-radius: .9rem;
         font-size: 1.35rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         margin-bottom: 2rem;
      }
      .auth-alert.error   { background: #fff1f2; border-left: 4px solid #e11d48; color: #be123c; }
      .auth-alert.success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }

      /* Form fields */
      .auth-form { display: flex; flex-direction: column; gap: 1.6rem; }

      .auth-field { display: flex; flex-direction: column; gap: .6rem; }

      .auth-field label {
         font-size: 1.3rem;
         font-weight: 700;
         color: #475569;
         display: flex;
         align-items: center;
         gap: .5rem;
      }

      .auth-input-wrap { position: relative; }

      .auth-input-wrap i.ai {
         position: absolute;
         left: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8; font-size: 1.5rem;
         pointer-events: none;
         transition: color .2s;
      }

      .auth-input-wrap:focus-within i.ai { color: #4f6ef7; }

      .auth-input {
         width: 100%;
         padding: 1.3rem 1.4rem 1.3rem 4.2rem;
         border: 1.5px solid #e2e8f0;
         border-radius: 1rem;
         font-size: 1.45rem;
         color: #0f172a;
         background: #f8fafc;
         outline: none;
         font-family: inherit;
         transition: border-color .2s, box-shadow .2s, background .2s;
      }

      .auth-input:focus {
         border-color: #4f6ef7;
         background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.12);
      }

      .auth-input::placeholder { color: #94a3b8; }

      .toggle-eye {
         position: absolute;
         right: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8; font-size: 1.4rem;
         cursor: pointer;
         transition: color .2s;
         z-index: 2;
      }
      .toggle-eye:hover { color: #4f6ef7; }

      /* Submit */
      .btn-auth {
         width: 100%;
         padding: 1.5rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff;
         border: none;
         border-radius: 1rem;
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

      .btn-auth:hover  { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }
      .btn-auth:active { transform: scale(.97); }

      /* Divider */
      .auth-divider {
         display: flex;
         align-items: center;
         gap: 1.2rem;
         margin: 2.4rem 0 1.6rem;
         font-size: 1.3rem;
         color: #94a3b8;
      }

      .auth-divider::before,
      .auth-divider::after {
         content: '';
         flex: 1;
         height: 1px;
         background: #e2e8f0;
      }

      /* Register link */
      .auth-switch {
         text-align: center;
         font-size: 1.4rem;
         color: #64748b;
      }

      .auth-switch a {
         color: #4f6ef7;
         font-weight: 700;
         text-decoration: none;
         transition: color .15s;
      }

      .auth-switch a:hover { color: #1a2a6c; text-decoration: underline; }

      /* Trust badges */
      .trust-badges {
         display: flex;
         justify-content: center;
         gap: 2rem;
         margin-top: 3.2rem;
         padding-top: 2.4rem;
         border-top: 1px solid #f1f5f9;
      }

      .trust-badge {
         display: flex;
         align-items: center;
         gap: .5rem;
         font-size: 1.2rem;
         color: #94a3b8;
         font-weight: 600;
      }

      .trust-badge i { color: #cbd5e1; }

      /* ===== RESPONSIVE ===== */
      @media(max-width:900px){
         .auth-layout { grid-template-columns: 1fr; }
         .auth-visual  { display: none; }
         .auth-form-panel { padding: 4rem 2.4rem; min-height: auto; }
      }

      @media(max-width:480px){
         .auth-form-panel { padding: 3rem 1.6rem; }
      }
   </style>
</head>
<body>

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
            <p>Belum punya akun? <a href="user_register.php" style="color:#4f6ef7;font-weight:700;text-decoration:none;">Daftar sekarang</a></p>
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
               <label for="email"><i class="fas fa-envelope" style="color:#94a3b8;font-size:1.2rem"></i> Email</label>
               <div class="auth-input-wrap">
                  <input type="email" id="email" name="email" class="auth-input" required maxlength="50"
                     placeholder="nama@email.com"
                     oninput="this.value = this.value.replace(/\s/g, '')"
                     autocomplete="email">
                  <i class="fas fa-envelope ai"></i>
               </div>
            </div>

            <div class="auth-field">
               <label for="pass"><i class="fas fa-lock" style="color:#94a3b8;font-size:1.2rem"></i> Password</label>
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