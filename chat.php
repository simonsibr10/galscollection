<?php
include 'components/connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:user_login.php');
   exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Chat Admin — Gals Collection</title>
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

      body {
         background: #f0f2f8;
         font-family: 'Segoe UI', sans-serif;
         min-height: 100vh;
         display: flex;
         flex-direction: column;
      }

      /* ===== CHAT PAGE ===== */
      .chat-page {
         flex: 1;
         display: flex;
         align-items: stretch;
         justify-content: center;
         padding: 2rem 1.6rem 3rem;
         animation: fadeUp .45s ease both;
      }

      @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

      /* ===== CHAT WINDOW ===== */
      .chat-window {
         background: #fff;
         border-radius: 1.6rem;
         box-shadow: 0 4px 28px rgba(0,0,0,.09);
         overflow: hidden;
         display: flex;
         flex-direction: column;
         width: 100%;
         max-width: 860px;
         height: calc(100vh - 16rem);
         min-height: 50rem;
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
         width: 22rem; height: 22rem;
         background: radial-gradient(circle, rgba(79,110,247,.3) 0%, transparent 70%);
         pointer-events: none;
      }

      .hdr-avatar {
         width: 4.6rem; height: 4.6rem;
         border-radius: 50%;
         background: rgba(255,255,255,.18);
         border: 2px solid rgba(255,255,255,.3);
         display: flex; align-items: center; justify-content: center;
         font-size: 2rem; font-weight: 800; color: #fff;
         flex-shrink: 0; z-index: 1;
      }

      .hdr-info { flex: 1; z-index: 1; }
      .hdr-info h3 { font-size: 1.6rem; font-weight: 700; color: #fff; }
      .hdr-info p  { font-size: 1.2rem; color: rgba(255,255,255,.6); margin-top: .2rem; }

      .hdr-status {
         display: flex; align-items: center; gap: .5rem;
         font-size: 1.2rem; color: rgba(255,255,255,.75); z-index: 1;
      }

      .hdr-badge {
         display: flex;
         align-items: center;
         gap: .5rem;
         background: rgba(255,255,255,.12);
         border: 1px solid rgba(255,255,255,.2);
         border-radius: 2rem;
         padding: .5rem 1.1rem;
         font-size: 1.2rem;
         color: rgba(255,255,255,.85);
         z-index: 1;
      }

      .status-dot {
         width: .8rem; height: .8rem;
         border-radius: 50%;
         background: #34d399;
         box-shadow: 0 0 6px #34d399;
         animation: statusPulse 2s infinite;
      }

      @keyframes statusPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

      /* ===== MESSAGES AREA ===== */
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
         color: #94a3b8; padding: 4rem; text-align: center;
         animation: fadeUp .4s ease both;
      }
      .msg-placeholder i { font-size: 4.8rem; color: #dbeafe; }
      .msg-placeholder h4 { font-size: 1.6rem; font-weight: 700; color: #64748b; }
      .msg-placeholder p  { font-size: 1.35rem; }

      /* ─── MESSAGE ROW ─── */
      .msg-row {
         display: flex;
         width: 100%;
         align-items: flex-end;
         gap: .8rem;
      }

      /*
       * Pesan dari USER  → justify flex-end   → bubble KANAN (biru)
       * Pesan dari ADMIN → justify flex-start  → bubble KIRI  (putih)
       */
      .msg-row.row-user  { justify-content: flex-end; }
      .msg-row.row-admin { justify-content: flex-start; }

      /* Admin avatar — only on left side */
      .row-avatar {
         width: 3.2rem; height: 3.2rem;
         border-radius: 50%;
         background: linear-gradient(135deg,#1a2a6c,#4f6ef7);
         display: flex; align-items: center; justify-content: center;
         font-size: 1.3rem; font-weight: 700; color: #fff;
         flex-shrink: 0;
         margin-bottom: .2rem;
      }

      /* No avatar on right (user) side */
      .row-user .row-avatar { display: none; }

      /* ─── BUBBLE ─── */
      .msg-bubble {
         max-width: 65%;
         padding: 1.1rem 1.4rem;
         border-radius: 1.4rem;
         font-size: 1.4rem;
         line-height: 1.65;
         word-break: break-word;
         box-shadow: 0 2px 8px rgba(0,0,0,.06);
         animation: bubbleIn .18s ease both;
      }

      @keyframes bubbleIn {
         from { opacity:0; transform:scale(.94) translateY(6px); }
         to   { opacity:1; transform:scale(1) translateY(0); }
      }

      /* Admin bubble — kiri — putih */
      .row-admin .msg-bubble {
         background: #fff;
         color: #1e293b;
         border: 1px solid #f1f5f9;
         border-bottom-left-radius: .3rem;
      }

      /* User bubble — kanan — gradasi biru */
      .row-user .msg-bubble {
         background: linear-gradient(135deg, #1a2a6c 0%, #4f6ef7 100%);
         color: #fff;
         border-bottom-right-radius: .3rem;
      }

      .bubble-sender {
         font-size: 1.1rem; font-weight: 700;
         margin-bottom: .35rem; opacity: .75;
      }

      .row-admin .bubble-sender { color: #1a2a6c; }
      .row-user  .bubble-sender { color: rgba(255,255,255,.8); }

      .bubble-text { font-size: 1.4rem; }

      .bubble-time {
         font-size: 1.05rem;
         margin-top: .5rem;
         opacity: .55;
         display: flex;
         align-items: center;
         justify-content: flex-end;
         gap: .4rem;
      }

      .row-user .bubble-time i { color: rgba(255,255,255,.6); font-size: 1rem; }

      /* Date separator */
      .date-sep {
         display: flex; align-items: center; gap: 1rem;
         margin: .8rem 0;
         font-size: 1.2rem; color: #94a3b8; font-weight: 600;
      }

      .date-sep::before, .date-sep::after {
         content: ''; flex: 1; height: 1px; background: #e2e8f0;
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

      /* ===== INFO STRIP below header (tip) ===== */
      .chat-info-strip {
         background: #eff2ff;
         border-bottom: 1px solid #c7d2fe;
         padding: .8rem 2rem;
         display: flex;
         align-items: center;
         gap: .7rem;
         font-size: 1.25rem;
         color: #1e40af;
         flex-shrink: 0;
      }

      .chat-info-strip i { font-size: 1.2rem; }

      @media(max-width:600px){
         .chat-page    { padding: .8rem .8rem 2rem; }
         .chat-window  { height: calc(100vh - 12rem); min-height: 42rem; border-radius: 1rem; }
         .msg-bubble   { max-width: 85%; }
         .chat-msgs    { padding: 1.2rem; }
         .chat-input-row { padding: 1rem 1.2rem; }
         .hdr-badge    { display: none; }
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="chat-page">
   <div class="chat-window">

      <!-- Header -->
      <div class="chat-win-header">
         <div class="hdr-avatar"><i class="fas fa-headset"></i></div>
         <div class="hdr-info">
            <h3>Admin Gals Collection</h3>
            <p>Customer Support · Siap membantu kamu</p>
         </div>
         <div class="hdr-badge">
            <div class="status-dot"></div> Online
         </div>
      </div>

      <!-- Info strip -->
      <div class="chat-info-strip">
         <i class="fas fa-circle-info"></i>
         Pesan kamu akan segera dibalas oleh tim kami. Biasanya dalam beberapa menit.
      </div>

      <!-- Messages -->
      <div id="chat-msgs" class="chat-msgs">
         <div class="msg-placeholder">
            <i class="fas fa-circle-notch fa-spin" style="color:#4f6ef7;font-size:3.2rem"></i>
            <p>Memuat percakapan...</p>
         </div>
      </div>

      <!-- Input -->
      <div class="chat-input-row">
         <input type="text" id="msg-input" placeholder="Tulis pesan ke admin..." autocomplete="off">
         <button class="btn-send" id="btn-send">
            <i class="fas fa-paper-plane"></i>
         </button>
      </div>

   </div>
</div>

<?php include 'components/footer.php'; ?>

<script>
   const chatBox  = document.getElementById('chat-msgs');
   const msgInput = document.getElementById('msg-input');

   /* ─── Format time ─── */
   function fmtTime(ts) {
      if (!ts) return '';
      const d = new Date((ts + '').replace(' ', 'T'));
      if (isNaN(d)) return ts;
      return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
   }

   function fmtDate(ts) {
      if (!ts) return '';
      const d = new Date((ts + '').replace(' ', 'T'));
      if (isNaN(d)) return '';
      const today     = new Date();
      const yesterday = new Date(today);
      yesterday.setDate(today.getDate() - 1);
      if (d.toDateString() === today.toDateString())     return 'Hari ini';
      if (d.toDateString() === yesterday.toDateString()) return 'Kemarin';
      return d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long' });
   }

   /* ─── Render bubbles from JSON ─── */
   function buildBubbles(messages) {
      if (!messages || messages.length === 0) {
         chatBox.innerHTML = `
            <div class="msg-placeholder">
               <i class="fas fa-comments" style="color:#dbeafe;font-size:4.8rem"></i>
               <h4>Mulai Percakapan</h4>
               <p>Kirim pesan ke admin untuk mendapatkan bantuan.</p>
            </div>`;
         return;
      }

      chatBox.innerHTML = '';
      let lastDate = '';

      messages.forEach(msg => {
         const isUser  = (String(msg.sender).toLowerCase() === 'user');
         const msgDate = fmtDate(msg.created_at || '');
         const msgTime = fmtTime(msg.created_at || '');
         const msgText = msg.message || msg.msg || '';

         /* Date separator */
         if (msgDate && msgDate !== lastDate) {
            lastDate = msgDate;
            const sep = document.createElement('div');
            sep.className   = 'date-sep';
            sep.textContent = msgDate;
            chatBox.appendChild(sep);
         }

         /* Row */
         const row = document.createElement('div');
         /*
          * isUser  = pesan dari USER  → row-user  → justify flex-end  → KANAN (biru)
          * !isUser = pesan dari ADMIN → row-admin → justify flex-start → KIRI  (putih)
          */
         row.className = 'msg-row ' + (isUser ? 'row-user' : 'row-admin');

         /* Admin avatar (left side only) */
         if (!isUser) {
            const av = document.createElement('div');
            av.className = 'row-avatar';
            av.innerHTML = '<i class="fas fa-headset" style="font-size:1.4rem"></i>';
            row.appendChild(av);
         }

         /* Bubble */
         const bubble = document.createElement('div');
         bubble.className = 'msg-bubble';

         const senderEl = document.createElement('div');
         senderEl.className  = 'bubble-sender';
         senderEl.textContent = isUser ? 'Kamu' : 'Admin';

         const textEl = document.createElement('div');
         textEl.className  = 'bubble-text';
         textEl.textContent = msgText;

         const timeEl = document.createElement('div');
         timeEl.className  = 'bubble-time';
         timeEl.textContent = msgTime;

         if (isUser) {
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

   /* ─── Load messages via inline AJAX or fallback ─── */
   function loadMessages(forceScroll = false) {
      const wasNearBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 150;

      fetch('user_chat.php?ajax=messages', {
         headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
         buildBubbles(data);
         if (forceScroll || wasNearBottom) chatBox.scrollTop = chatBox.scrollHeight;
         refreshBadge();
      })
      .catch(() => {
         /* Fallback: old fetch_messages endpoint */
         fetch('components/fetch_messages.php?viewer=user')
            .then(r => r.text())
            .then(html => {
               parseAndRenderHTML(html);
               if (forceScroll || wasNearBottom) chatBox.scrollTop = chatBox.scrollHeight;
            });
      });
   }

   /* ─── HTML fallback parser ─── */
   function parseAndRenderHTML(html) {
      const tmp = document.createElement('div');
      tmp.innerHTML = html.trim();
      const allEls = tmp.querySelectorAll('[class]');
      const messages = [];

      allEls.forEach(el => {
         const cls = el.className || '';
         let sender = null;
         if (/admin|left|received/i.test(cls))  sender = 'admin';
         if (/user|right|sent/i.test(cls))       sender = 'user';
         if (!sender) return;
         const textEl = el.querySelector('.chat-text, .message-text, .text, p, span');
         const text   = textEl ? textEl.textContent.trim() : el.textContent.trim();
         const timeEl = el.querySelector('.chat-time, .time, .timestamp, small');
         const time   = timeEl ? timeEl.textContent.trim() : '';
         if (text) messages.push({ sender, message: text, created_at: time });
      });

      if (messages.length > 0) buildBubbles(messages);
      else chatBox.innerHTML = `<div class="msg-placeholder"><i class="fas fa-comment-dots" style="color:#dbeafe"></i><h4>Mulai Percakapan</h4><p>Kirim pesan ke admin.</p></div>`;
   }

   /* ─── Send message ─── */
   function sendMessage() {
      const msg = msgInput.value.trim();
      if (msg === '') return;

      const btn = document.getElementById('btn-send');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      fetch('components/send_message.php', {
         method : 'POST',
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
         body   : 'message=' + encodeURIComponent(msg) + '&sender=user'
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

   /* ─── Refresh unread badge in header ─── */
   function refreshBadge() {
      fetch('components/fetch_unread_count.php?role=user')
         .then(r => r.json())
         .then(data => {
            const badge = document.querySelector('#chat-badge-user');
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

</body>
</html>

<?php
/* ─────────────────────────────────────────────────────────────
   INLINE AJAX ENDPOINT
   Dipanggil dengan ?ajax=messages — return JSON array pesan
   sekaligus tandai pesan admin sebagai sudah dibaca
───────────────────────────────────────────────────────────── */
if(isset($_GET['ajax']) && $_GET['ajax'] === 'messages' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
   ob_clean();
   header('Content-Type: application/json');

   // Mark admin messages as read
   $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE user_id = ? AND sender = 'admin'")
        ->execute([$user_id]);

   $q = $conn->prepare("SELECT sender, message, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC");
   $q->execute([$user_id]);
   echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
   exit;
}
?>