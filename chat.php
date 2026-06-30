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
</head>
<body class="php-chat">

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
            <i class="fas fa-circle-notch fa-spin u-inline-style-002"></i>
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
               <i class="fas fa-comments u-inline-style-003"></i>
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
            av.innerHTML = '<i class="fas fa-headset u-inline-style-004"></i>';
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
      else chatBox.innerHTML = `<div class="msg-placeholder"><i class="fas fa-comment-dots u-inline-style-005"></i><h4>Mulai Percakapan</h4><p>Kirim pesan ke admin.</p></div>`;
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