<?php

define('CRYPTO_VERSION', '2.1');
define('AES_128_KEY', 'GalsKey123456789'); // 16 karakter = AES-128

/* =========================
   HELPER
========================= */
function crypto_is_encrypted($value){
   $value = trim((string)$value);
   return strpos($value, 'ENC:') === 0;
}

function crypto_clean_plaintext($value){
   $value = trim((string)$value);

   if($value === ''){
      return '';
   }

   // Jangan pernah tampilkan ciphertext sebagai nama/data biasa
   if(strpos($value, 'ENC:') === 0){
      return '';
   }

   return $value;
}

/* =========================
   AES ENCRYPT
========================= */
function aes_encrypt($plaintext){
   if($plaintext === null || $plaintext === ''){
      return '';
   }

   $plaintext = (string)$plaintext;

   // Kalau sudah terenkripsi, jangan dienkripsi ulang
   if(strpos($plaintext, 'ENC:') === 0){
      return $plaintext;
   }

   $cipher = 'AES-128-CBC';
   $iv_length = openssl_cipher_iv_length($cipher);
   $iv = random_bytes($iv_length);

   $ciphertext = openssl_encrypt(
      $plaintext,
      $cipher,
      AES_128_KEY,
      OPENSSL_RAW_DATA,
      $iv
   );

   if($ciphertext === false){
      return '';
   }

   return 'ENC:' . base64_encode($iv . $ciphertext);
}

/* =========================
   AES DECRYPT
   Support:
   1. Format baru: ENC:base64(iv+ciphertext)
   2. Format lama: base64(iv+ciphertext)
   3. Data lama plaintext
========================= */
function aes_decrypt($encrypted){
   if($encrypted === null || $encrypted === ''){
      return '';
   }

   $encrypted = trim((string)$encrypted);

   $cipher = 'AES-128-CBC';
   $iv_length = openssl_cipher_iv_length($cipher);

   // =========================
   // Format baru: ENC:
   // =========================
   if(strpos($encrypted, 'ENC:') === 0){
      $payload = substr($encrypted, 4);
      $raw = base64_decode($payload, true);

      if($raw === false || strlen($raw) <= $iv_length){
         return '';
      }

      $iv = substr($raw, 0, $iv_length);
      $ciphertext = substr($raw, $iv_length);

      $decrypted = openssl_decrypt(
         $ciphertext,
         $cipher,
         AES_128_KEY,
         OPENSSL_RAW_DATA,
         $iv
      );

      if($decrypted !== false && trim($decrypted) !== ''){
         return $decrypted;
      }

      return '';
   }

   // =========================
   // Format lama: base64 tanpa ENC:
   // =========================
   $raw = base64_decode($encrypted, true);

   if($raw !== false && strlen($raw) > $iv_length){
      $iv = substr($raw, 0, $iv_length);
      $ciphertext = substr($raw, $iv_length);

      $decrypted = openssl_decrypt(
         $ciphertext,
         $cipher,
         AES_128_KEY,
         OPENSSL_RAW_DATA,
         $iv
      );

      if($decrypted !== false && trim($decrypted) !== ''){
         return $decrypted;
      }
   }

   // =========================
   // Data lama plaintext
   // =========================
   return $encrypted;
}

/* =========================
   SAFE DISPLAY DECRYPT
   Untuk tampilan nama/email/alamat
========================= */
function aes_decrypt_display($value, $fallback = 'Pengguna'){
   if($value === null || $value === ''){
      return $fallback;
   }

   $value = trim((string)$value);

   $decrypted = aes_decrypt($value);
   $decrypted = trim((string)$decrypted);

   if($decrypted !== '' && strpos($decrypted, 'ENC:') !== 0){
      return $decrypted;
   }

   if(strpos($value, 'ENC:') !== 0 && $value !== ''){
      return $value;
   }

   return $fallback;
}

/* =========================
   EMAIL HASH UNTUK LOGIN
========================= */
function email_lookup_hash($email){
   return hash('sha256', strtolower(trim((string)$email)));
}

/* =========================
   MASKING DATA TAMPILAN ADMIN
========================= */
function mask_name($name){
   $name = aes_decrypt_display($name, '');
   $name = trim((string)$name);

   $len = mb_strlen($name, 'UTF-8');

   if($len <= 1){
      return $name;
   }

   if($len == 2){
      return mb_substr($name, 0, 1, 'UTF-8') . '*';
   }

   return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $len - 2) . mb_substr($name, -1, null, 'UTF-8');
}

function mask_phone($phone){
   $phone = aes_decrypt_display($phone, '');
   $phone = preg_replace('/\s+/', '', (string)$phone);
   $len = strlen($phone);

   if($len <= 6){
      return $phone;
   }

   return substr($phone, 0, 3) . str_repeat('*', $len - 6) . substr($phone, -3);
}

function mask_email($email){
   $email = aes_decrypt_display($email, '');
   $email = trim((string)$email);
   $parts = explode('@', $email);

   if(count($parts) != 2){
      return $email;
   }

   $name = $parts[0];
   $domain = $parts[1];
   $len = strlen($name);

   if($len <= 1){
      $masked_name = '*';
   }elseif($len == 2){
      $masked_name = substr($name, 0, 1) . '*';
   }else{
      $masked_name = substr($name, 0, 1) . str_repeat('*', $len - 2) . substr($name, -1);
   }

   return $masked_name . '@' . $domain;
}

function mask_address($address){
   $address = aes_decrypt_display($address, '');
   $address = trim((string)$address);

   if($address === ''){
      return '';
   }

   $len = mb_strlen($address, 'UTF-8');

   if($len <= 20){
      return mb_substr($address, 0, 6, 'UTF-8') . ' *****';
   }

   $start = mb_substr($address, 0, 12, 'UTF-8');
   $end = mb_substr($address, -12, null, 'UTF-8');

   return $start . ' ********** ' . $end;
}
?>