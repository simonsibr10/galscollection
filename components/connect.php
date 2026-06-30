<?php

$db_name = 'mysql:host=localhost;dbname=shop_db';
$user_name = 'galscollection';
$user_password = 'Vnx?8^BBkwoif7i1';

$conn = new PDO($db_name, $user_name, $user_password);

if(!function_exists('category_theme_class')){
   function category_theme_class($category){
      $slug = strtolower(trim((string)$category));
      $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
      $slug = trim($slug, '-');
      return 'category-theme category-theme-'.($slug ?: 'default');
   }
}

?>
