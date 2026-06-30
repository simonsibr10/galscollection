<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? '';

if($admin_id == ''){
   header('location:admin_login.php');
   exit;
}

$alert_type = '';
$alert_msg  = '';

if(isset($_POST['submit'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

   // Update username
   $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?")->execute([$name, $admin_id]);

   $empty_pass    = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass     = $_POST['prev_pass'];
   $old_pass      = sha1($_POST['old_pass']);
   $new_pass      = sha1($_POST['new_pass']);
   $confirm_pass  = sha1($_POST['confirm_pass']);

   if($old_pass == $empty_pass){
      $alert_type = 'info';
      $alert_msg  = 'Username berhasil diperbarui. Isi password lama untuk mengubah password.';
   } elseif($old_pass != $prev_pass){
      $alert_type = 'error';
      $alert_msg  = 'Password lama tidak cocok. Coba lagi.';
   } elseif($new_pass != $confirm_pass){
      $alert_type = 'error';
      $alert_msg  = 'Konfirmasi password baru tidak cocok.';
   } else {
      if($new_pass != $empty_pass){
         $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?")->execute([$confirm_pass, $admin_id]);
         $alert_type = 'success';
         $alert_msg  = 'Username dan password berhasil diperbarui!';
      } else {
         $alert_type = 'error';
         $alert_msg  = 'Silakan masukkan password baru.';
      }
   }

   // Re-fetch updated profile
   $sel = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
   $sel->execute([$admin_id]);
   $fetch_profile = $sel->fetch(PDO::FETCH_ASSOC);
}

$admin_initial = $fetch_profile ? strtoupper(mb_substr($fetch_profile['name'], 0, 1)) : 'A';

?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body class="php-admin-update-profile">

<?php include '../components/admin_header.php'; ?>

<div class="profile-page">

   <!-- PAGE HEADER -->
   <div class="page-header">
      <h1>Update <span>Profil</span></h1>
   </div>

   <!-- ALERT -->
   <?php if($alert_msg): ?>
      <div class="alert-bar <?= $alert_type; ?>">
         <i class="fas <?= $alert_type==='success'?'fa-circle-check':($alert_type==='error'?'fa-circle-xmark':'fa-circle-info'); ?>"></i>
         <?= htmlspecialchars($alert_msg); ?>
      </div>
   <?php endif; ?>

   <!-- PROFILE BANNER -->
   <div class="profile-banner">
      <div class="banner-avatar">
         <?= htmlspecialchars($admin_initial); ?>
         <div class="banner-avatar-ring"></div>
      </div>
      <div class="banner-info">
         <h2><?= htmlspecialchars($fetch_profile['name'] ?? 'Admin'); ?></h2>
         <p>Administrator &nbsp;·&nbsp; ID #<?= htmlspecialchars($admin_id); ?></p>
         <p class="u-inline-style-047">Perbarui username atau password akun kamu di bawah ini.</p>
      </div>
      <div class="banner-badges">
         <div class="banner-badge-item">
            <i class="fas fa-shield-halved"></i> Administrator
         </div>
         <div class="banner-badge-item">
            <i class="fas fa-circle u-inline-style-048"></i> Akun Aktif
         </div>
      </div>
   </div>

   <!-- MAIN GRID: two form panels side by side -->
   <div class="profile-main-grid">

      <!-- Panel 1: Update Username -->
      <div class="form-panel">
         <div class="panel-title"><i class="fas fa-user-pen"></i> Ubah Username</div>

         <form action="" method="post" id="form-username">
            <input type="hidden" name="prev_pass" value="<?= htmlspecialchars($fetch_profile['password'] ?? ''); ?>">
            <!-- Hidden password fields so submit still works -->
            <input type="hidden" name="old_pass"     value="">
            <input type="hidden" name="new_pass"     value="">
            <input type="hidden" name="confirm_pass" value="">

            <div class="form-field">
               <label><i class="fas fa-user"></i> Username</label>
               <div class="input-wrap">
                  <input
                     type="text"
                     name="name"
                     class="form-input"
                     required
                     maxlength="20"
                     placeholder="Masukkan username baru"
                     value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>"
                     oninput="this.value = this.value.replace(/\s/g, '')"
                  >
                  <i class="fas fa-user fi"></i>
               </div>
            </div>

            <div class="u-inline-style-049">
               <i class="fas fa-info-circle"></i>
               Username digunakan untuk login ke panel admin.
            </div>

            <button type="submit" name="submit" class="btn-submit">
               <i class="fas fa-floppy-disk"></i> Simpan Username
            </button>
         </form>
      </div>

      <!-- Panel 2: Update Password -->
      <div class="form-panel">
         <div class="panel-title"><i class="fas fa-lock"></i> Ubah Password</div>

         <form action="" method="post" id="form-password">
            <input type="hidden" name="prev_pass" value="<?= htmlspecialchars($fetch_profile['password'] ?? ''); ?>">
            <!-- Keep name same so both actions go through same PHP handler -->
            <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>">

            <div class="form-field">
               <label><i class="fas fa-key"></i> Password Lama</label>
               <div class="input-wrap">
                  <input type="password" id="old_pass" name="old_pass" class="form-input" maxlength="20"
                     placeholder="Masukkan password lama"
                     oninput="this.value = this.value.replace(/\s/g, '')">
                  <i class="fas fa-lock fi"></i>
                  <i class="fas fa-eye toggle-eye" onclick="toggleEye('old_pass',this)"></i>
               </div>
            </div>

            <div class="form-field">
               <label><i class="fas fa-lock-open"></i> Password Baru</label>
               <div class="input-wrap">
                  <input type="password" id="new_pass" name="new_pass" class="form-input" maxlength="20"
                     placeholder="Masukkan password baru"
                     oninput="this.value=this.value.replace(/\s/g,''); checkStrength(this.value)">
                  <i class="fas fa-lock fi"></i>
                  <i class="fas fa-eye toggle-eye" onclick="toggleEye('new_pass',this)"></i>
               </div>
               <div class="strength-wrap">
                  <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
                  <div class="strength-label" id="strength-label"></div>
               </div>
            </div>

            <div class="form-field">
               <label><i class="fas fa-check-double"></i> Konfirmasi Password</label>
               <div class="input-wrap">
                  <input type="password" id="confirm_pass" name="confirm_pass" class="form-input" maxlength="20"
                     placeholder="Ulangi password baru"
                     oninput="this.value = this.value.replace(/\s/g, '')">
                  <i class="fas fa-lock fi"></i>
                  <i class="fas fa-eye toggle-eye" onclick="toggleEye('confirm_pass',this)"></i>
               </div>
            </div>

            <button type="submit" name="submit" class="btn-submit">
               <i class="fas fa-shield-halved"></i> Perbarui Password
            </button>
         </form>
      </div>

   </div><!-- /profile-main-grid -->

   <!-- TIPS PANEL -->
   <div class="tips-panel">
      <div class="panel-title"><i class="fas fa-lightbulb"></i> Tips Keamanan Akun</div>
      <div class="tips-list">
         <div class="tip-row">
            <div class="tip-icon blue"><i class="fas fa-key"></i></div>
            <div class="tip-text">
               <strong>Gunakan password yang kuat</strong>
               <span>Kombinasikan huruf besar, huruf kecil, angka, dan simbol untuk password yang sulit ditebak.</span>
            </div>
         </div>
         <div class="tip-row">
            <div class="tip-icon amber"><i class="fas fa-rotate"></i></div>
            <div class="tip-text">
               <strong>Ganti password secara berkala</strong>
               <span>Disarankan mengganti password minimal setiap 3 bulan sekali untuk menjaga keamanan akun.</span>
            </div>
         </div>
         <div class="tip-row">
            <div class="tip-icon green"><i class="fas fa-user-secret"></i></div>
            <div class="tip-text">
               <strong>Jangan bagikan kredensial</strong>
               <span>Jangan pernah membagikan username dan password admin kepada pihak lain meskipun dipercaya.</span>
            </div>
         </div>
      </div>
   </div>

   <!-- ACTIVITY LOG (dekoratif) -->
   <div class="activity-panel">
      <div class="panel-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas Akun</div>
      <div class="activity-list">
         <div class="activity-row">
            <div class="activity-dot dot-green"></div>
            <span><strong>Login berhasil</strong> ke panel admin</span>
            <span class="activity-time">Sesi ini</span>
         </div>
         <div class="activity-row">
            <div class="activity-dot dot-blue"></div>
            <span>Mengakses halaman <strong>Update Profil</strong></span>
            <span class="activity-time">Baru saja</span>
         </div>
         <?php if($alert_type === 'success'): ?>
         <div class="activity-row">
            <div class="activity-dot dot-amber"></div>
            <span><strong>Profil diperbarui</strong> berhasil</span>
            <span class="activity-time">Baru saja</span>
         </div>
         <?php endif; ?>
         <div class="activity-row">
            <div class="activity-dot dot-green"></div>
            <span>Admin ID: <strong>#<?= htmlspecialchars($admin_id); ?></strong></span>
            <span class="activity-time">Aktif</span>
         </div>
      </div>
   </div>

</div><!-- /profile-page -->

<script src="../js/admin_script.js"></script>
<script>
   // Toggle show/hide password
   function toggleEye(id, icon){
      const inp = document.getElementById(id);
      if(inp.type === 'password'){
         inp.type = 'text';
         icon.classList.replace('fa-eye','fa-eye-slash');
      } else {
         inp.type = 'password';
         icon.classList.replace('fa-eye-slash','fa-eye');
      }
   }

   // Password strength meter
   function checkStrength(val){
      const fill  = document.getElementById('strength-fill');
      const label = document.getElementById('strength-label');
      if(!fill || !label) return;

      let score = 0;
      if(val.length >= 6)              score++;
      if(val.length >= 10)             score++;
      if(/[A-Z]/.test(val))            score++;
      if(/[0-9]/.test(val))            score++;
      if(/[^a-zA-Z0-9]/.test(val))     score++;

      const levels = [
         { w:'0%',   c:'#e11d48', t:'' },
         { w:'25%',  c:'#e11d48', t:'Lemah' },
         { w:'50%',  c:'#f59e0b', t:'Cukup' },
         { w:'75%',  c:'#0891b2', t:'Kuat' },
         { w:'100%', c:'#059669', t:'Sangat Kuat' },
      ];

      const lvl = levels[Math.min(score, 4)];
      fill.style.width      = lvl.w;
      fill.style.background = lvl.c;
      label.style.color     = lvl.c;
      label.textContent     = lvl.t;
   }

   // Auto-dismiss alert after 5 seconds
   const alertBar = document.querySelector('.alert-bar');
   if(alertBar){
      setTimeout(() => {
         alertBar.style.transition = 'opacity .4s ease';
         alertBar.style.opacity = '0';
         setTimeout(() => alertBar.remove(), 450);
      }, 5000);
   }

   // Confirm before leaving with unsaved changes
   let formDirty = false;
   document.querySelectorAll('.form-input').forEach(inp => {
      inp.addEventListener('input', () => { formDirty = true; });
   });
   document.querySelectorAll('form').forEach(f => {
      f.addEventListener('submit', () => { formDirty = false; });
   });
   window.addEventListener('beforeunload', e => {
      if(formDirty){ e.preventDefault(); e.returnValue = ''; }
   });
</script>
</body>
</html>