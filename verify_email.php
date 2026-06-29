<?php

include 'components/connect.php';
include 'components/crypto.php';
session_start();

if(isset($_SESSION['user_id'])){
   header('location:home.php');
   exit;
}

$reg_error   = false;
$reg_success = false;

if(isset($_POST['submit'])){

   $name  = trim($_POST['name'] ?? '');
   $email = trim($_POST['email'] ?? '');
   $pass  = $_POST['pass'] ?? '';
   $cpass = $_POST['cpass'] ?? '';

   $name  = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);

   if($name === ''){
      $reg_error = 'Nama tidak boleh kosong.';
   }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $reg_error = 'Format email tidak valid.';
   }elseif(strlen($pass) < 8){
      $reg_error = 'Password minimal 8 karakter.';
   }elseif($pass !== $cpass){
      $reg_error = 'Konfirmasi password tidak cocok.';
   }else{

      $email_hash = email_lookup_hash($email);

      $select_user = $conn->prepare("SELECT id FROM users WHERE email_hash = ? LIMIT 1");
      $select_user->execute([$email_hash]);

      if($select_user->rowCount() > 0){
         $reg_error = 'Email sudah terdaftar. Silakan login.';
      }else{

         $name_enc  = aes_encrypt($name);
         $email_enc = aes_encrypt($email);
         $hashed    = password_hash($pass, PASSWORD_BCRYPT);

         $token  = bin2hex(random_bytes(32));
         $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

         $insert = $conn->prepare("
            INSERT INTO users
            (name, email_enc, email_hash, email, password, email_verified, verification_token, verification_expire)
            VALUES (?, ?, ?, NULL, ?, 0, ?, ?)
         ");

         $ok = $insert->execute([
            $name_enc,
            $email_enc,
            $email_hash,
            $hashed,
            $token,
            $expire
         ]);

         if($ok){
            $verify_link = "https://galscollection.pocari.id/ecommerce/verify_email.php?token=" . $token;

            $reg_success = '
               Pendaftaran berhasil. Untuk testing sementara, klik link ini untuk verifikasi akun:<br><br>
               <a href="'.$verify_link.'" style="color:#4f6ef7;font-weight:700;word-break:break-all;">'.$verify_link.'</a>
            ';
         }else{
            $reg_error = 'Terjadi kesalahan saat registrasi. Coba lagi.';
         }
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
   <title>Daftar — Gals Collection</title>
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

      /* ===== LEFT PANEL ===== */
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

      .auth-visual::before {
         content: '';
         position: absolute;
         top: -8rem; right: -8rem;
         width: 40rem; height: 40rem;
         border-radius: 50%;
         background: radial-gradient(circle, rgba(79,110,247,.28) 0%, transparent 65%);
         animation: floatBlob 8s ease-in-out infinite;
      }

      .auth-visual::after {
         content: '';
         position: absolute;
         bottom: -6rem; left: -4rem;
         width: 28rem; height: 28rem;
         border-radius: 50%;
         background: radial-gradient(circle, rgba(255,255,255,.05) 0%, transparent 65%);
         animation: floatBlob 10s ease-in-out infinite reverse;
      }

      @keyframes floatBlob {
         0%,100% { transform: translate(0,0) scale(1); }
         50%      { transform: translate(-15px,20px) scale(1.05); }
      }

      .visual-content { position: relative; z-index: 1; text-align: center; }

      /* Step progress visual */
      .visual-steps {
         display: flex;
         flex-direction: column;
         gap: 1.6rem;
         width: 100%;
         max-width: 32rem;
         margin: 0 auto 3rem;
      }

      .vstep {
         display: flex;
         align-items: center;
         gap: 1.4rem;
         animation: fadeSlideRight .5s ease both;
      }

      .vstep:nth-child(1){animation-delay:.1s}
      .vstep:nth-child(2){animation-delay:.2s}
      .vstep:nth-child(3){animation-delay:.3s}
      .vstep:nth-child(4){animation-delay:.4s}

      @keyframes fadeSlideRight {
         from{opacity:0;transform:translateX(-18px)}
         to  {opacity:1;transform:translateX(0)}
      }

      .vstep-num {
         width: 3.6rem; height: 3.6rem;
         border-radius: 50%;
         background: rgba(255,255,255,.18);
         border: 2px solid rgba(255,255,255,.3);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.4rem; font-weight: 800; color: #fff;
         flex-shrink: 0;
      }

      .vstep-info strong { display: block; font-size: 1.4rem; font-weight: 700; color: #fff; }
      .vstep-info span   { font-size: 1.25rem; color: rgba(255,255,255,.6); }

      .vstep-divider {
         width: 2px; height: 1.4rem;
         background: rgba(255,255,255,.2);
         margin-left: 1.7rem;
      }

      .visual-content h2 {
         font-size: 2.8rem;
         font-weight: 800;
         color: #fff;
         margin-bottom: 1rem;
         line-height: 1.2;
      }

      .visual-content p {
         font-size: 1.4rem;
         color: rgba(255,255,255,.65);
         line-height: 1.7;
         max-width: 32rem;
         margin: 0 auto 3rem;
      }

      /* Stats row */
      .visual-stats {
         display: flex;
         gap: 2rem;
         justify-content: center;
         margin-top: 2.4rem;
      }

      .vstat {
         text-align: center;
         background: rgba(255,255,255,.08);
         border: 1px solid rgba(255,255,255,.12);
         border-radius: 1rem;
         padding: 1.2rem 2rem;
      }

      .vstat-val { font-size: 2.2rem; font-weight: 800; color: #fff; line-height: 1; }
      .vstat-lbl { font-size: 1.2rem; color: rgba(255,255,255,.55); margin-top: .3rem; }

      /* ===== RIGHT PANEL — Form ===== */
      .auth-form-panel {
         background: #fff;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         padding: 4rem 6rem;
      }

      .auth-form-inner {
         width: 100%;
         max-width: 44rem;
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
         margin-bottom: 2.8rem;
      }

      .form-brand-icon {
         width: 4.4rem; height: 4.4rem;
         border-radius: 1rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 2rem; color: #fff;
         box-shadow: 0 4px 14px rgba(79,110,247,.35);
      }

      .form-brand-text strong { display: block; font-size: 2rem; font-weight: 800; color: #0f172a; line-height:1.2; }
      .form-brand-text span   { font-size: 1.3rem; color: #94a3b8; }

      /* Heading */
      .form-heading { margin-bottom: 2.4rem; }
      .form-heading h3 { font-size: 2.4rem; font-weight: 800; color: #0f172a; margin-bottom: .5rem; }
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
      .auth-form { display: flex; flex-direction: column; gap: 1.4rem; }

      .auth-field { display: flex; flex-direction: column; gap: .6rem; }

      .auth-field label {
         font-size: 1.3rem;
         font-weight: 700;
         color: #475569;
      }

      .auth-input-wrap { position: relative; }

      .auth-input-wrap i.ai {
         position: absolute;
         left: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8; font-size: 1.4rem;
         pointer-events: none;
         transition: color .2s;
      }

      .auth-input-wrap:focus-within i.ai { color: #4f6ef7; }

      .auth-input {
         width: 100%;
         padding: 1.2rem 1.4rem 1.2rem 4.2rem;
         border: 1.5px solid #e2e8f0;
         border-radius: 1rem;
         font-size: 1.4rem;
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

      /* 2-col grid for pass fields */
      .fields-row {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.2rem;
      }

      /* Password strength */
      .strength-wrap { margin-top: .6rem; }
      .strength-track { height: .4rem; background: #e2e8f0; border-radius: 2rem; overflow: hidden; }
      .strength-fill  { height: 100%; width: 0; border-radius: 2rem; transition: width .35s, background .35s; }
      .strength-lbl   { font-size: 1.2rem; margin-top: .3rem; font-weight: 600; }

      /* Terms */
      .terms-row {
         display: flex;
         align-items: flex-start;
         gap: .8rem;
         font-size: 1.3rem;
         color: #64748b;
         padding: 1rem 1.2rem;
         background: #f8fafc;
         border-radius: .8rem;
         border: 1px solid #f1f5f9;
      }

      .terms-row input[type="checkbox"] { width: 1.6rem; height: 1.6rem; accent-color: #4f6ef7; flex-shrink:0; margin-top:.15rem; }
      .terms-row a { color: #4f6ef7; font-weight: 600; text-decoration: none; }
      .terms-row a:hover { text-decoration: underline; }

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
      }

      .btn-auth:hover  { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(79,110,247,.35); }
      .btn-auth:active { transform: scale(.97); }

      /* Login link */
      .auth-switch {
         text-align: center;
         font-size: 1.4rem;
         color: #64748b;
         margin-top: 2rem;
      }

      .auth-switch a { color: #4f6ef7; font-weight: 700; text-decoration: none; }
      .auth-switch a:hover { text-decoration: underline; }

      /* Progress redirect indicator */
      .redirect-bar {
         background: #ecfdf5;
         border-left: 4px solid #059669;
         border-radius: .9rem;
         padding: 1.4rem 1.6rem;
         margin-bottom: 2rem;
         font-size: 1.35rem;
         color: #065f46;
      }

      .redirect-progress {
         margin-top: .8rem;
         height: .4rem;
         background: #d1fae5;
         border-radius: 2rem;
         overflow: hidden;
      }

      .redirect-fill {
         height: 100%;
         background: #059669;
         border-radius: 2rem;
         animation: fillProgress 3s linear both;
         width: 0%;
      }

      @keyframes fillProgress { from{width:0%} to{width:100%} }

      /* ===== RESPONSIVE ===== */
      @media(max-width:960px){
         .auth-layout { grid-template-columns: 1fr; }
         .auth-visual  { display: none; }
         .auth-form-panel { padding: 4rem 2.4rem; }
         .fields-row { grid-template-columns: 1fr; }
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
         <h2>Bergabung dengan Ribuan Pelanggan Kami</h2>
         <p>Daftarkan akun gratis dan nikmati kemudahan belanja koleksi tas & aksesori terbaik.</p>

         <div class="visual-steps">
            <div class="vstep">
               <div class="vstep-num">1</div>
               <div class="vstep-info">
                  <strong>Buat Akun Gratis</strong>
                  <span>Isi form pendaftaran dalam 1 menit</span>
               </div>
            </div>
            <div class="vstep-divider"></div>
            <div class="vstep">
               <div class="vstep-num">2</div>
               <div class="vstep-info">
                  <strong>Jelajahi Koleksi</strong>
                  <span>Temukan produk impianmu</span>
               </div>
            </div>
            <div class="vstep-divider"></div>
            <div class="vstep">
               <div class="vstep-num">3</div>
               <div class="vstep-info">
                  <strong>Tambahkan ke Keranjang</strong>
                  <span>Pilih produk yang kamu suka</span>
               </div>
            </div>
            <div class="vstep-divider"></div>
            <div class="vstep">
               <div class="vstep-num">4</div>
               <div class="vstep-info">
                  <strong>Nikmati Produknya!</strong>
                  <span>Produk dikirim ke pintu rumahmu</span>
               </div>
            </div>
         </div>

         <div class="visual-stats">
            <div class="vstat">
               <div class="vstat-val">500+</div>
               <div class="vstat-lbl">Produk</div>
            </div>
            <div class="vstat">
               <div class="vstat-val">1K+</div>
               <div class="vstat-lbl">Pelanggan</div>
            </div>
            <div class="vstat">
               <div class="vstat-val">4.9★</div>
               <div class="vstat-lbl">Rating</div>
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
            <h3>Buat Akun Baru</h3>
            <p>Sudah punya akun? <a href="user_login.php" style="color:#4f6ef7;font-weight:700;text-decoration:none;">Masuk di sini</a></p>
         </div>

         <!-- Redirect success bar -->
		<?php if($reg_success): ?>
		   <div class="redirect-bar">
			  <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.4rem;">
				 <i class="fas fa-circle-check"></i>
				 <strong>Pendaftaran berhasil!</strong>
			  </div>
			  <?= $reg_success; ?>
		   </div>
		<?php endif; ?>

         <!-- Error alert -->
         <?php if($reg_error): ?>
            <div class="auth-alert error">
               <i class="fas fa-circle-xmark"></i>
               <?= htmlspecialchars($reg_error); ?>
            </div>
         <?php endif; ?>

         <!-- Form -->
         <form action="" method="post" class="auth-form" id="reg-form">

            <div class="auth-field">
               <label>Username</label>
               <div class="auth-input-wrap">
                  <input type="text" name="name" class="auth-input" required maxlength="20"
                     placeholder="Nama lengkap atau username"
                     autocomplete="name">
                  <i class="fas fa-user ai"></i>
               </div>
            </div>

            <div class="auth-field">
               <label>Alamat Email</label>
               <div class="auth-input-wrap">
                  <input type="email" name="email" class="auth-input" required maxlength="50"
                     placeholder="nama@email.com"
                     oninput="this.value=this.value.replace(/\s/g,'')"
                     autocomplete="email">
                  <i class="fas fa-envelope ai"></i>
               </div>
            </div>

            <div class="fields-row">
               <div class="auth-field">
                  <label>Password</label>
                  <div class="auth-input-wrap">
                     <input type="password" id="pass" name="pass" class="auth-input" required maxlength="50"
                        placeholder="Min. 8 karakter"
                        oninput="this.value=this.value.replace(/\s/g,''); checkStrength(this.value)"
                        autocomplete="new-password">
                     <i class="fas fa-lock ai"></i>
                     <i class="fas fa-eye toggle-eye" onclick="toggleEye('pass',this)"></i>
                  </div>
                  <div class="strength-wrap">
                     <div class="strength-track"><div class="strength-fill" id="sfill"></div></div>
                     <div class="strength-lbl" id="slbl"></div>
                  </div>
               </div>

               <div class="auth-field">
                  <label>Konfirmasi Password</label>
                  <div class="auth-input-wrap">
                     <input type="password" id="cpass" name="cpass" class="auth-input" required maxlength="50"
                        placeholder="Ulangi password"
                        oninput="this.value=this.value.replace(/\s/g,''); checkMatch()"
                        autocomplete="new-password">
                     <i class="fas fa-lock ai"></i>
                     <i class="fas fa-eye toggle-eye" onclick="toggleEye('cpass',this)"></i>
                  </div>
                  <div id="match-lbl" style="font-size:1.2rem;margin-top:.3rem;"></div>
               </div>
            </div>

            <div class="terms-row">
               <input type="checkbox" id="agree-terms" required>
               <label for="agree-terms">
                  Saya menyetujui <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> Gals Collection.
               </label>
            </div>

            <button type="submit" name="submit" class="btn-auth">
               <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>

         </form>

         <div class="auth-switch">
            Sudah punya akun? <a href="user_login.php">Masuk Sekarang</a>
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

   function checkStrength(val){
      const fill = document.getElementById('sfill');
      const lbl  = document.getElementById('slbl');
      if(!fill) return;
      let s = 0;
      if(val.length >= 8)           s++;
      if(val.length >= 12)          s++;
      if(/[A-Z]/.test(val))         s++;
      if(/[0-9]/.test(val))         s++;
      if(/[^a-zA-Z0-9]/.test(val))  s++;
      const lvl = [
         {w:'0%',  c:'#e11d48',t:''},
         {w:'25%', c:'#e11d48',t:'Lemah'},
         {w:'50%', c:'#f59e0b',t:'Cukup'},
         {w:'75%', c:'#0891b2',t:'Kuat'},
         {w:'100%',c:'#059669',t:'Sangat Kuat'},
      ][Math.min(s,4)];
      fill.style.width = lvl.w;
      fill.style.background = lvl.c;
      lbl.style.color = lvl.c;
      lbl.textContent = lvl.t;
      checkMatch();
   }

   function checkMatch(){
      const p  = document.getElementById('pass').value;
      const cp = document.getElementById('cpass').value;
      const lbl = document.getElementById('match-lbl');
      if(!cp) { lbl.textContent = ''; return; }
      if(p === cp){
         lbl.style.color = '#059669';
         lbl.textContent = '✓ Password cocok';
      } else {
         lbl.style.color = '#e11d48';
         lbl.textContent = '✗ Belum cocok';
      }
   }
</script>
</body>
</html>