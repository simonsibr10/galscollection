<?php
include '../components/connect.php';
session_start();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit;
}

$admin_id = $_SESSION['admin_id'];
$user_id  = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$chat_user_name = '';
if($user_id > 0){
   include_once '../components/crypto.php';
   $q = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
   $q->execute([$user_id]);
   $u = $q->fetch(PDO::FETCH_ASSOC);
   if($u){
      $dec = aes_decrypt($u['name']);
      $chat_user_name = ($dec && $dec !== '') ? $dec : $u['name'];
   } else {
      $chat_user_name = 'User #'.$user_id;
   }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Chat Admin</title>
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
      body { background: #f0f2f8 !important; font-family: 'Segoe UI', sans-serif !important; }
      .header { background: #fff !important; }

      .chat-page {
         max-width: 860px;
         margin: 0 auto;
         padding: 2rem 1.6rem 3rem;
         animation: fadeUp .45s ease both;
      }

      @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

      /* ===== WINDOW ===== */
      .chat-window {
         background: #fff;
         border-radius: 1.6rem;
         box-shadow: 0 4px 24px rgba(0,0,0,.08);
         overflow: hidden;
         display: flex;
         flex-direction: column;
         height: calc(100vh - 14rem);
         min-height: 52rem;
      }

      /* ===== HEADER ===== */
      .chat-win-header {
         background: linear-gradient(135deg, #0f2027 0%, #1a2a6c 55%, #2c3e8f 100%);
         padding: 1.6rem 2rem;
         display: flex;
         align-items: center;
         gap: 1.4rem;
         position: relative;
         overflow: hidden;
         flex-shrink: 0;
      }

      .chat-win-header::before {
         content: '';
         position: absolute;
         top: -50%; right: -5%;
         width: 20rem; height: 20rem;
         background: radial-gradient(circle, rgba(79,110,247,.3) 0%, transparent 70%);
         pointer-events: none;
      }

      .hdr-back {
         color: rgba(255,255,255,.8);
         font-size: 1.8rem;
         text-decoration: none;
         z-index: 1;
         flex-shrink: 0;
         transition: color .15s;
      }
      .hdr-back:hover { color: #fff; }

      .hdr-avatar {
         width: 4.4rem; height: 4.4rem;
         border-radius: 50%;
         background: rgba(255,255,255,.2);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.8rem; font-weight: 800; color: #fff;
         flex-shrink: 0; z-index: 1;
      }

      .hdr-info { flex: 1; z-index: 1; }
      .hdr-info h3 { font-size: 1.6rem; font-weight: 700; color: #fff; }
      .hdr-info p  { font-size: 1.2rem; color: rgba(255,255,255,.6); margin-top: .2rem; }

      .hdr-status {
         display: flex; align-items: center; gap: .5rem;
         font-size: 1.2rem; color: rgba(255,255,255,.7); z-index: 1;
      }

      .status-dot {
         width: .8rem; height: .8rem;
         border-radius: 50%;
         background: #34d399;
         box-shadow: 0 0 6px #34d399;
         animation: pulse 2s infinite;
      }
      @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

      /* ===== MESSAGES ===== */
      .chat-msgs {
         flex: 1;
         overflow-y: auto;
         padding: 2rem;
         background: #f8fafc;
         display: flex;
         flex-direction: column;
         gap: .8rem;
         scroll-behavior: smooth;
      }

      .chat-msgs::-webkit-scrollbar { width: 4px; }
      .chat-msgs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

      /* Loading / empty */
      .msg-placeholder {
         display: flex; flex-direction: column;
         align-items: center; justify-content: center;
         flex: 1; gap: 1rem;
         color: #94a3b8; padding: 3rem; text-align: center;
      }
      .msg-placeholder i { font-size: 4rem; color: #cbd5e1; }
      .msg-placeholder p  { font-size: 1.4rem; }

      /* ─── ROW: flex container ─── */
      .msg-row {
         display: flex;
         width: 100%;
         align-items: flex-end;
         gap: .8rem;
      }

      /*
       * PESAN USER  → justify flex-start  → bubble muncul di KIRI
       * PESAN ADMIN → justify flex-end    → bubble muncul di KANAN
       */
      .msg-row.row-user  { justify-content: flex-start; }
      .msg-row.row-admin { justify-content: flex-end; }

      /* Small avatar for user messages */
      .row-avatar {
         width: 3.2rem; height: 3.2rem;
         border-radius: 50%;
         background: linear-gradient(135deg,#64748b,#94a3b8);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.3rem; font-weight: 700; color: #fff;
         flex-shrink: 0;
         margin-bottom: .2rem;
      }

      .row-admin .row-avatar { display: none; } /* no avatar on right side */

      /* ─── BUBBLE ─── */
      .msg-bubble {
         max-width: 65%;
         padding: 1rem 1.4rem;
         border-radius: 1.4rem;
         font-size: 1.4rem;
         line-height: 1.65;
         word-break: break-word;
         box-shadow: 0 2px 8px rgba(0,0,0,.06);
         animation: bubbleIn .18s ease both;
      }

      @keyframes bubbleIn {
         from { opacity: 0; transform: scale(.94) translateY(6px); }
         to   { opacity: 1; transform: scale(1) translateY(0); }
      }

      /* User bubble — kiri — putih */
      .row-user .msg-bubble {
         background: #fff;
         color: #1e293b;
         border: 1px solid #f1f5f9;
         border-bottom-left-radius: .3rem;
      }

      /* Admin bubble — kanan — gradasi biru gelap */
      .row-admin .msg-bubble {
         background: linear-gradient(135deg, #1a2a6c 0%, #4f6ef7 100%);
         color: #fff;
         border-bottom-right-radius: .3rem;
      }

      /* Sender label inside bubble */
      .bubble-sender {
         font-size: 1.1rem;
         font-weight: 700;
         margin-bottom: .35rem;
         opacity: .75;
      }

      .row-user  .bubble-sender { color: #1a2a6c; }
      .row-admin .bubble-sender { color: rgba(255,255,255,.8); }

      /* Message text */
      .bubble-text { font-size: 1.4rem; }

      /* Time */
      .bubble-time {
         font-size: 1.05rem;
         margin-top: .5rem;
         opacity: .55;
         display: flex;
         align-items: center;
         justify-content: flex-end;
         gap: .4rem;
      }

      /* Double check for sent messages */
      .row-admin .bubble-time i {
         color: rgba(255,255,255,.6);
         font-size: 1rem;
      }

      /* Date separator */
      .date-separator {
         display: flex;
         align-items: center;
         gap: 1rem;
         margin: .8rem 0;
         font-size: 1.2rem;
         color: #94a3b8;
         font-weight: 600;
      }

      .date-separator::before,
      .date-separator::after {
         content: '';
         flex: 1;
         height: 1px;
         background: #e2e8f0;
      }

      /* ===== INPUT AREA ===== */
      .chat-input-row {
         padding: 1.4rem 1.8rem;
         border-top: 1px solid #f1f5f9;
         background: #fff;
         display: flex;
         align-items: center;
         gap: 1rem;
         flex-shrink: 0;
      }

      .chat-input-row input {
         flex: 1;
         padding: 1.2rem 1.6rem;
         border: 1.5px solid #e2e8f0;
         border-radius: 3rem;
         font-size: 1.4rem;
         color: #0f172a;
         background: #f8fafc;
         outline: none;
         font-family: inherit;
         transition: border-color .2s, box-shadow .2s, background .2s;
      }

      .chat-input-row input:focus {
         border-color: #4f6ef7;
         background: #fff;
         box-shadow: 0 0 0 3px rgba(79,110,247,.12);
      }

      .chat-input-row input::placeholder { color: #94a3b8; }

      .btn-send {
         width: 4.8rem; height: 4.8rem;
         border-radius: 50%;
         background: linear-gradient(135deg, #1a2a6c, #4f6ef7);
         color: #fff;
         border: none;
         cursor: pointer;
         display: flex; align-items: center; justify-content: center;
         font-size: 1.8rem;
         flex-shrink: 0;
         box-shadow: 0 4px 12px rgba(79,110,247,.35);
         transition: transform .15s, box-shadow .15s;
      }

      .btn-send:hover  { transform: scale(1.08); box-shadow: 0 6px 18px rgba(79,110,247,.4); }
      .btn-send:active { transform: scale(.94); }

      /* ===== NO USER PANEL ===== */
      .no-user-panel {
         background: #fff;
         border-radius: 1.6rem;
         box-shadow: 0 4px 24px rgba(0,0,0,.07);
         padding: 6rem 3rem;
         text-align: center;
      }

      .no-user-panel i  { font-size: 6rem; color: #cbd5e1; display: block; margin-bottom: 1.6rem; }
      .no-user-panel h3 { font-size: 2rem; font-weight: 700; color: #64748b; margin-bottom: .8rem; }
      .no-user-panel p  { font-size: 1.4rem; color: #94a3b8; }

      .btn-go-list {
         display: inline-flex; align-items: center; gap: .6rem;
         margin-top: 2rem; padding: 1.1rem 2.2rem;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         color: #fff; border-radius: .8rem;
         font-size: 1.4rem; font-weight: 700;
         text-decoration: none;
         transition: transform .15s, box-shadow .15s;
      }
      .btn-go-list:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79,110,247,.35); }

      @media(max-width:600px){
         .chat-page   { padding: 1rem .8rem 2rem; }
         .chat-window { height: calc(100vh - 10rem); }
         .msg-bubble  { max-width: 82%; }
         .chat-msgs   { padding: 1.2rem; }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="chat-page">

<?php if($user_id == 0): ?>

   <div class="no-user-panel">
      <i class="fas fa-comments"></i>
      <h3>Pilih Percakapan</h3>
      <p>Buka daftar chat dan pilih pengguna untuk mulai membalas pesan.</p>
      <a href="chat_list.php" class="btn-go-list"><i class="fas fa-list"></i> Lihat Daftar Chat</a>
   </div>

<?php else: ?>

   <div class="chat-window">

      <div class="chat-win-header">
         <a href="chat_list.php" class="hdr-back"><i class="fas fa-arrow-left"></i></a>
         <div class="hdr-avatar"><?= strtoupper(mb_substr($chat_user_name, 0, 1)); ?></div>
         <div class="hdr-info">
            <h3><?= htmlspecialchars($chat_user_name); ?></h3>
            <p>User ID #<?= $user_id; ?></p>
         </div>
         <div class="hdr-status">
            <div class="status-dot"></div> Online
         </div>
      </div>

      <div id="chat-msgs" class="chat-msgs">
         <div class="msg-placeholder">
            <i class="fas fa-circle-notch fa-spin"></i>
            <p>Memuat pesan...</p>
         </div>
      </div>

      <div class="chat-input-row">
         <input type="text" id="msg-input" placeholder="Tulis balasan..." autocomplete="off">
         <button class="btn-send" id="btn-send">
            <i class="fas fa-paper-plane"></i>
         </button>
      </div>

   </div>

<?php endif; ?>
</div>

<?php if($user_id != 0): ?>
<script>
   const USER_ID  = <?= (int)$user_id; ?>;
   const chatBox  = document.getElementById('chat-msgs');
   const msgInput = document.getElementById('msg-input');
   const ADMIN_INITIAL = '<?= addslashes(strtoupper(mb_substr($chat_user_name, 0, 1))); ?>';

   /* ─────────────────────────────────────────────────────────────
      Format a timestamp string into a human-readable time label
   ───────────────────────────────────────────────────────────── */
   function fmtTime(ts) {
      if (!ts) return '';
      const d = new Date(ts.replace && ts.replace(' ', 'T') || ts);
      if (isNaN(d)) return ts;
      return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
   }

   function fmtDate(ts) {
      if (!ts) return '';
      const d = new Date(ts.replace && ts.replace(' ', 'T') || ts);
      if (isNaN(d)) return '';
      const today = new Date();
      const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
      if (d.toDateString() === today.toDateString())     return 'Hari ini';
      if (d.toDateString() === yesterday.toDateString()) return 'Kemarin';
      return d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long' });
   }

   /* ─────────────────────────────────────────────────────────────
      Build bubble DOM from a messages array.
      Each item must have: { sender: 'admin'|'user', message: '...', created_at: '...' }
   ───────────────────────────────────────────────────────────── */
   function buildBubbles(messages) {
      if (!messages || messages.length === 0) {
         chatBox.innerHTML = '<div class="msg-placeholder"><i class="fas fa-comment-dots" style="color:#cbd5e1"></i><p>Belum ada pesan.</p></div>';
         return;
      }

      chatBox.innerHTML = '';
      let lastDate = '';

      messages.forEach(msg => {
         const isAdmin  = (String(msg.sender).toLowerCase() === 'admin');
         const msgDate  = fmtDate(msg.created_at || msg.time || '');
         const msgTime  = fmtTime(msg.created_at || msg.time || '');
         const msgText  = msg.message || msg.msg || msg.text || '';

         // Date separator
         if (msgDate && msgDate !== lastDate) {
            lastDate = msgDate;
            const sep = document.createElement('div');
            sep.className = 'date-separator';
            sep.textContent = msgDate;
            chatBox.appendChild(sep);
         }

         /* ── ROW ── */
         const row = document.createElement('div');
         // isAdmin  → row-admin → justify-content: flex-end  → KANAN
         // !isAdmin → row-user  → justify-content: flex-start → KIRI
         row.className = 'msg-row ' + (isAdmin ? 'row-admin' : 'row-user');

         /* User avatar (only left-side) */
         if (!isAdmin) {
            const av = document.createElement('div');
            av.className = 'row-avatar';
            av.textContent = ADMIN_INITIAL || 'U';
            row.appendChild(av);
         }

         /* Bubble */
         const bubble = document.createElement('div');
         bubble.className = 'msg-bubble';

         /* Sender label */
         const senderEl = document.createElement('div');
         senderEl.className = 'bubble-sender';
         senderEl.textContent = isAdmin ? 'Admin' : 'User';

         /* Text */
         const textEl = document.createElement('div');
         textEl.className = 'bubble-text';
         textEl.textContent = msgText;

         /* Time */
         const timeEl = document.createElement('div');
         timeEl.className = 'bubble-time';
         timeEl.textContent = msgTime;

         // Double check icon for admin messages
         if (isAdmin) {
            const checkIcon = document.createElement('i');
            checkIcon.className = 'fas fa-check-double';
            timeEl.appendChild(checkIcon);
         }

         bubble.appendChild(senderEl);
         bubble.appendChild(textEl);
         bubble.appendChild(timeEl);
         row.appendChild(bubble);
         chatBox.appendChild(row);
      });
   }

   /* ─────────────────────────────────────────────────────────────
      Load messages — tries JSON endpoint first, falls back to
      reading directly from the database via a simple PHP call.

      IMPORTANT: This script queries chat_messages table directly
      via the inline PHP endpoint below (injected as a data-attr).
      We parse the response as JSON so we control rendering 100%.
   ───────────────────────────────────────────────────────────── */
   function loadMessages(forceScroll = false) {
      const wasNearBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 150;

      // Call the inline JSON endpoint (see PHP below, emitted as a hidden element)
      fetch('?ajax=messages&user_id=' + USER_ID, {
         headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
         buildBubbles(data);
         if (forceScroll || wasNearBottom) chatBox.scrollTop = chatBox.scrollHeight;
         refreshBadge();
      })
      .catch(() => {
         // Last resort: try the old HTML endpoint
         fetch('../components/fetch_messages.php?viewer=admin&user_id=' + USER_ID)
            .then(r => r.text())
            .then(html => {
               // Parse HTML and rebuild with proper alignment
               parseAndRenderHTML(html);
               if (forceScroll || wasNearBottom) chatBox.scrollTop = chatBox.scrollHeight;
            });
      });
   }

   /* ─────────────────────────────────────────────────────────────
      HTML fallback: scan the raw HTML from fetch_messages.php
      and rebuild bubbles with correct left/right alignment.
   ───────────────────────────────────────────────────────────── */
   function parseAndRenderHTML(html) {
      const tmp = document.createElement('div');
      tmp.innerHTML = html.trim();

      // Try common patterns your fetch_messages.php might output
      const allEls = tmp.querySelectorAll('[class]');
      const messages = [];

      allEls.forEach(el => {
         const cls = el.className || '';
         // Detect sender from class name patterns
         let sender = null;
         if (/admin|right|sent/i.test(cls))  sender = 'admin';
         if (/user|left|received/i.test(cls)) sender = 'user';
         if (!sender) return;

         // Get text — try inner text elements, fallback to textContent
         const textEl = el.querySelector('.chat-text, .message-text, .text, p, span');
         const text   = textEl ? textEl.textContent.trim() : el.textContent.trim();

         const timeEl = el.querySelector('.chat-time, .time, .timestamp, small');
         const time   = timeEl ? timeEl.textContent.trim() : '';

         if (text) messages.push({ sender, message: text, created_at: time, time });
      });

      if (messages.length > 0) {
         buildBubbles(messages);
      } else {
         // Absolute last resort — raw inject with basic styling
         if (tmp.textContent.trim() === '') {
            chatBox.innerHTML = '<div class="msg-placeholder"><i class="fas fa-comment-dots" style="color:#cbd5e1"></i><p>Belum ada pesan.</p></div>';
         } else {
            chatBox.innerHTML = '<div class="msg-placeholder"><i class="fas fa-exclamation-circle"></i><p>Tidak dapat menampilkan pesan.</p></div>';
         }
      }
   }

   /* ─── Send ─── */
   function sendMessage() {
      const msg = msgInput.value.trim();
      if (msg === '') return;

      const btn = document.getElementById('btn-send');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      fetch('../components/send_message.php', {
         method: 'POST',
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
         body: 'message=' + encodeURIComponent(msg) + '&sender=admin&user_id=' + USER_ID
      })
      .then(r => r.text())
      .then(() => {
         msgInput.value = '';
         loadMessages(true);
      })
      .finally(() => {
         btn.disabled = false;
         btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
      });
   }

   /* ─── Badge ─── */
   function refreshBadge() {
      fetch('../components/fetch_unread_count.php?role=admin')
         .then(r => r.json())
         .then(data => {
            const badge = document.querySelector('.chat-badge');
            if (badge) {
               badge.textContent  = data.count > 0 ? data.count : '';
               badge.style.display = data.count > 0 ? 'inline-flex' : 'none';
            }
         }).catch(() => {});
   }

   document.getElementById('btn-send').addEventListener('click', sendMessage);
   msgInput.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
   });

   setInterval(() => loadMessages(false), 2500);
   loadMessages(true);
</script>
<?php endif; ?>

</body>
</html>

<?php
/* ─────────────────────────────────────────────────────────────
   INLINE AJAX ENDPOINT
   If this page is called with ?ajax=messages&user_id=X
   it returns JSON array of messages directly.
   This way we don't need a separate file.
───────────────────────────────────────────────────────────── */
if(isset($_GET['ajax']) && $_GET['ajax'] === 'messages' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
   ob_clean();
   header('Content-Type: application/json');

   $uid = (int)($_GET['user_id'] ?? 0);
   if($uid > 0){
      // Mark user messages as read
      $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE user_id = ? AND sender = 'user'")->execute([$uid]);

      $q = $conn->prepare("SELECT sender, message, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
      $q->execute([$uid]);
      $rows = $q->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode($rows);
   } else {
      echo json_encode([]);
   }
   exit;
}
?>