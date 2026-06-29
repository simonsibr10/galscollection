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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      section, .dashboard { background: transparent !important; }
      .header { background: #fff !important; }
      .footer { background: #fff !important; }

      /* ===== PAGE ===== */
      .profile-page {
         max-width: 900px;
         margin: 0 auto;
         padding: 2.4rem 2rem 5rem;
      }

      /* ===== PAGE HEADER ===== */
      .page-header {
         display: flex;
         align-items: center;
         justify-content: space-between;
         flex-wrap: wrap;
         gap: 1rem;
         margin-bottom: 2.4rem;
         animation: fadeSlideDown .45s ease both;
      }

      .page-header h1 { font-size: 2.6rem; font-weight: 800; color: #0f172a; letter-spacing: -.5px; }
      .page-header h1 span {
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }

      /* ===== PROFILE BANNER ===== */
      .profile-banner {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 55%, #2c3e8f 100%);
         border-radius: 1.6rem;
         padding: 2.8rem 3.2rem;
         display: flex;
         align-items: center;
         gap: 2.4rem;
         flex-wrap: wrap;
         margin-bottom: 2.4rem;
         position: relative;
         overflow: hidden;
         animation: fadeSlideDown .5s ease both;
      }

      .profile-banner::before {
         content: '';
         position: absolute;
         top: -40%; right: -5%;
         width: 32rem; height: 32rem;
         background: radial-gradient(circle, rgba(79,110,247,.3) 0%, transparent 70%);
         pointer-events: none;
      }

      .profile-banner::after {
         content: '';
         position: absolute;
         bottom: -50%; left: 15%;
         width: 24rem; height: 24rem;
         background: radial-gradient(circle, rgba(255,255,255,.05) 0%, transparent 70%);
         pointer-events: none;
      }

      /* Big avatar */
      .banner-avatar {
         width: 8rem; height: 8rem;
         border-radius: 50%;
         background: rgba(255,255,255,.18);
         border: 3px solid rgba(255,255,255,.3);
         display: flex; align-items: center; justify-content: center;
         font-size: 3.2rem; font-weight: 800; color: #fff;
         flex-shrink: 0;
         position: relative; z-index: 1;
         box-shadow: 0 8px 24px rgba(0,0,0,.2);
      }

      .banner-avatar-ring {
         position: absolute;
         inset: -6px;
         border-radius: 50%;
         border: 2px dashed rgba(255,255,255,.25);
         animation: ringRotate 12s linear infinite;
      }

      @keyframes ringRotate { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

      .banner-info { flex: 1; position: relative; z-index: 1; }
      .banner-info h2 { font-size: 2.4rem; font-weight: 800; color: #fff; margin-bottom: .5rem; }
      .banner-info p  { font-size: 1.35rem; color: rgba(255,255,255,.65); }

      .banner-badges {
         display: flex;
         flex-direction: column;
         gap: .8rem;
         position: relative;
         z-index: 1;
      }

      .banner-badge-item {
         display: flex;
         align-items: center;
         gap: .7rem;
         background: rgba(255,255,255,.1);
         border: 1px solid rgba(255,255,255,.15);
         border-radius: .8rem;
         padding: .7rem 1.2rem;
         font-size: 1.3rem;
         color: rgba(255,255,255,.85);
      }

      .banner-badge-item i { color: rgba(255,255,255,.6); font-size: 1.2rem; }

      /* ===== MAIN GRID ===== */
      .profile-main-grid {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.6rem;
      }

      /* ===== FORM PANELS ===== */
      .form-panel {
         background: #fff;
         border-radius: 1.4rem;
         padding: 2.2rem 2.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         animation: fadeSlideUp .5s ease both;
      }

      .form-panel:nth-child(2) { animation-delay: .08s; }

      .panel-title {
         font-size: 1.5rem;
         font-weight: 700;
         color: #0f172a;
         margin-bottom: 2rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         padding-bottom: 1.2rem;
         border-bottom: 1px solid #f1f5f9;
      }

      .panel-title i { color: #4f6ef7; }

      /* Form field */
      .form-field { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.6rem; }
      .form-field:last-of-type { margin-bottom: 0; }

      .form-field label {
         font-size: 1.3rem;
         font-weight: 700;
         color: #475569;
         display: flex;
         align-items: center;
         gap: .5rem;
      }

      .form-field label i { color: #94a3b8; font-size: 1.2rem; }

      .input-wrap { position: relative; }

      .input-wrap i.fi {
         position: absolute;
         left: 1.4rem; top: 50%;
         transform: translateY(-50%);
         color: #94a3b8; font-size: 1.4rem;
         pointer-events: none;
         transition: color .2s;
      }

      .input-wrap:focus-within i.fi { color: #4f6ef7; }

      .form-input {
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

      .form-input:focus {
         border-color: #4f6ef7;
         background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.12);
      }

      .form-input::placeholder { color: #94a3b8; }

      /* Toggle password eye */
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

      /* Password strength */
      .strength-wrap { margin-top: .6rem; }
      .strength-track { height: .4rem; background: #e2e8f0; border-radius: 2rem; overflow: hidden; }
      .strength-fill  { height: 100%; width: 0; border-radius: 2rem; transition: width .35s, background .35s; }
      .strength-label { font-size: 1.2rem; margin-top: .4rem; font-weight: 600; }

      /* Submit button */
      .btn-submit {
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
         margin-top: 2rem;
      }

      .btn-submit:hover  { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,110,247,.35); }
      .btn-submit:active { transform: scale(.97); }

      /* ===== ALERT ===== */
      .alert-bar {
         border-radius: .9rem;
         padding: 1.2rem 1.6rem;
         font-size: 1.35rem;
         display: flex;
         align-items: center;
         gap: .8rem;
         margin-bottom: 2rem;
         animation: fadeSlideDown .35s ease both;
      }

      .alert-bar.success { background: #ecfdf5; border-left: 4px solid #059669; color: #065f46; }
      .alert-bar.error   { background: #fff1f2; border-left: 4px solid #e11d48; color: #be123c; }
      .alert-bar.info    { background: #eff2ff; border-left: 4px solid #4f6ef7; color: #1e40af; }

      /* ===== TIPS PANEL ===== */
      .tips-panel {
         background: #fff;
         border-radius: 1.4rem;
         padding: 2.2rem 2.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         margin-top: 1.6rem;
         animation: fadeSlideUp .55s ease both;
      }

      .tips-list { display: flex; flex-direction: column; gap: 1rem; margin-top: .4rem; }

      .tip-row {
         display: flex;
         align-items: flex-start;
         gap: 1.2rem;
         padding: 1.2rem;
         border-radius: .8rem;
         background: #f8fafc;
      }

      .tip-icon {
         width: 3.6rem; height: 3.6rem;
         border-radius: .8rem;
         display: flex; align-items: center; justify-content: center;
         font-size: 1.5rem;
         flex-shrink: 0;
      }

      .tip-icon.blue   { background: #eff2ff; color: #4f6ef7; }
      .tip-icon.amber  { background: #fffbeb; color: #f59e0b; }
      .tip-icon.green  { background: #ecfdf5; color: #059669; }

      .tip-text strong { display: block; font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .2rem; }
      .tip-text span   { font-size: 1.25rem; color: #64748b; line-height: 1.5; }

      /* ===== ACTIVITY LOG (dekoratif) ===== */
      .activity-panel {
         background: #fff;
         border-radius: 1.4rem;
         padding: 2.2rem 2.4rem;
         box-shadow: 0 2px 12px rgba(0,0,0,.05);
         margin-top: 1.6rem;
         animation: fadeSlideUp .6s ease both;
      }

      .activity-list { display: flex; flex-direction: column; gap: .6rem; margin-top: .4rem; }

      .activity-row {
         display: flex;
         align-items: center;
         gap: 1.2rem;
         padding: 1rem 1.2rem;
         border-radius: .8rem;
         background: #f8fafc;
         font-size: 1.3rem;
         color: #475569;
      }

      .activity-dot {
         width: .8rem; height: .8rem;
         border-radius: 50%;
         flex-shrink: 0;
      }

      .dot-green  { background: #059669; }
      .dot-blue   { background: #4f6ef7; }
      .dot-amber  { background: #f59e0b; }

      .activity-row strong { color: #0f172a; font-weight: 600; }
      .activity-time { margin-left: auto; font-size: 1.2rem; color: #94a3b8; white-space: nowrap; }

      /* ===== ANIMATIONS ===== */
      @keyframes fadeSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:translateY(0)} }
      @keyframes fadeSlideUp   { from{opacity:0;transform:translateY(18px)}  to{opacity:1;transform:translateY(0)} }

      @media(max-width:800px){
         .profile-page        { padding: 1.6rem; }
         .profile-main-grid   { grid-template-columns: 1fr; }
         .banner-badges       { flex-direction: row; flex-wrap: wrap; }
         .profile-banner      { flex-direction: column; align-items: flex-start; gap: 1.6rem; }
      }
   </style>
</head>
<body>

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
         <p style="margin-top:.6rem; font-size:1.2rem;">Perbarui username atau password akun kamu di bawah ini.</p>
      </div>
      <div class="banner-badges">
         <div class="banner-badge-item">
            <i class="fas fa-shield-halved"></i> Administrator
         </div>
         <div class="banner-badge-item">
            <i class="fas fa-circle" style="color:#34d399;font-size:.8rem;"></i> Akun Aktif
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

            <div style="padding:1.2rem;background:#eff2ff;border-radius:.8rem;font-size:1.3rem;color:#1e40af;display:flex;align-items:center;gap:.7rem;margin-top:.4rem;">
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