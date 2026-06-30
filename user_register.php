<?php

include 'components/connect.php';
include 'components/crypto.php';
session_start();

if(isset($_SESSION['user_id'])){
   header('location:index.php');
   exit;
}

$reg_error   = false;
$reg_success = false;

if(isset($_POST['submit'])){

   $name  = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass  = $_POST['pass'];
   $cpass = $_POST['cpass'];

   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $reg_error = 'Format email tidak valid.';
   } elseif(strlen($pass) < 8){
      $reg_error = 'Password minimal 8 karakter.';
   } elseif($pass !== $cpass){
      $reg_error = 'Konfirmasi password tidak cocok.';
   } else {
      $name_enc   = aes_encrypt($name);
      $email_enc  = aes_encrypt($email);
      $email_hash = email_lookup_hash($email);

      $select_user = $conn->prepare("SELECT id FROM users WHERE email_hash = ? LIMIT 1");
      $select_user->execute([$email_hash]);

      if($select_user->rowCount() > 0){
         $reg_error = 'Email sudah terdaftar. Silakan login.';
      } else {
         $hashed = password_hash($pass, PASSWORD_BCRYPT);
         $insert = $conn->prepare("INSERT INTO users(name, email_enc, email_hash, password) VALUES(?,?,?,?)");
         $ok     = $insert->execute([$name_enc, $email_enc, $email_hash, $hashed]);

         if($ok){
            $reg_success = true;
            header("refresh:3;url=user_login.php");
         } else {
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
</head>
<body class="php-user-register">

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
            <p>Sudah punya akun? <a class="u-inline-style-031" href="user_login.php">Masuk di sini</a></p>
         </div>

         <!-- Redirect success bar -->
         <?php if($reg_success): ?>
            <div class="redirect-bar">
               <div class="u-inline-style-032">
                  <i class="fas fa-circle-check"></i>
                  <strong>Pendaftaran berhasil!</strong>
               </div>
               Anda akan diarahkan ke halaman login dalam 3 detik...
               <div class="redirect-progress"><div class="redirect-fill"></div></div>
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
                  <div class="u-inline-style-033" id="match-lbl"></div>
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