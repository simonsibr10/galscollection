<?php

if(isset($_POST['add_to_wishlist'])){

   if($user_id == ''){
      header('location:user_login.php');
      exit;
   }else{

      $pid   = htmlspecialchars(trim($_POST['pid']   ?? ''), ENT_QUOTES, 'UTF-8');
      $name  = htmlspecialchars(trim($_POST['name']  ?? ''), ENT_QUOTES, 'UTF-8');
      $price = htmlspecialchars(trim($_POST['price'] ?? ''), ENT_QUOTES, 'UTF-8');
      $image = htmlspecialchars(trim($_POST['image'] ?? ''), ENT_QUOTES, 'UTF-8');

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$name, $user_id]);

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $message[] = 'Sudah di tambahkan ke daftar suka!';
      }elseif($check_cart_numbers->rowCount() > 0){
         $message[] = 'Sudah di tambahkan ke keranjang!';
      }else{
         $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
         $message[] = 'Dimasukkan ke daftar suka!';
      }

   }

}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      header('location:user_login.php');
      exit;
   }else{

      $pid   = htmlspecialchars(trim($_POST['pid']   ?? ''), ENT_QUOTES, 'UTF-8');
      $name  = htmlspecialchars(trim($_POST['name']  ?? ''), ENT_QUOTES, 'UTF-8');
      $price = htmlspecialchars(trim($_POST['price'] ?? ''), ENT_QUOTES, 'UTF-8');
      $image = htmlspecialchars(trim($_POST['image'] ?? ''), ENT_QUOTES, 'UTF-8');
      $qty   = (int) ($_POST['qty'] ?? 1);
      if($qty < 1) $qty = 1;

      $variation_1_name  = htmlspecialchars(trim($_POST['variation_1_name']  ?? ''), ENT_QUOTES, 'UTF-8');
      $variation_1_value = htmlspecialchars(trim($_POST['variation_1_value'] ?? ''), ENT_QUOTES, 'UTF-8');
      $variation_2_name  = htmlspecialchars(trim($_POST['variation_2_name']  ?? ''), ENT_QUOTES, 'UTF-8');
      $variation_2_value = htmlspecialchars(trim($_POST['variation_2_value'] ?? ''), ENT_QUOTES, 'UTF-8');

      $select_variations = $conn->prepare("SELECT COUNT(*) AS total FROM `product_variations` WHERE product_id = ?");
      $select_variations->execute([$pid]);
      $fetch_variations = $select_variations->fetch(PDO::FETCH_ASSOC);
      $has_variations = (int)($fetch_variations['total'] ?? 0);

      if($has_variations > 0 && $variation_1_value == ''){
         $message[] = 'Silakan pilih variasi produk terlebih dahulu!';
      }else{

         $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ? AND selected_variation_1_value <=> ? AND selected_variation_2_value <=> ?");
         $check_cart_numbers->execute([$pid, $user_id, $variation_1_value, $variation_2_value]);

         if($check_cart_numbers->rowCount() > 0){
            $message[] = 'Produk dengan variasi tersebut sudah di tambahkan ke keranjang!';
         }else{

            $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE pid = ? AND user_id = ?");
            $check_wishlist_numbers->execute([$pid, $user_id]);

            if($check_wishlist_numbers->rowCount() > 0){
               $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ? AND user_id = ?");
               $delete_wishlist->execute([$pid, $user_id]);
            }

            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image, selected_variation_1_name, selected_variation_1_value, selected_variation_2_name, selected_variation_2_value) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $insert_cart->execute([
               $user_id, $pid, $name, $price, $qty, $image,
               $variation_1_name, $variation_1_value,
               $variation_2_name, $variation_2_value
            ]);

            $message[] = 'Ditambahkan ke keranjang!';
         }
      }
   }

}
?>
