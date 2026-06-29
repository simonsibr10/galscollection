-- Run this on an existing old `shop_db` database to match the current codebase
-- without dropping existing data. This syntax targets MariaDB in XAMPP.

USE `shop_db`;

START TRANSACTION;

ALTER TABLE `admins`
  MODIFY COLUMN `name` varchar(100) NOT NULL,
  MODIFY COLUMN `password` varchar(255) NOT NULL;

ALTER TABLE `users`
  MODIFY COLUMN `name` varchar(255) NOT NULL,
  MODIFY COLUMN `email` varchar(255) DEFAULT NULL,
  MODIFY COLUMN `password` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `email_enc` varchar(255) DEFAULT NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `email_hash` char(64) DEFAULT NULL AFTER `email_enc`,
  ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `password`,
  ADD COLUMN IF NOT EXISTS `verification_token` varchar(255) DEFAULT NULL AFTER `email_verified`,
  ADD COLUMN IF NOT EXISTS `verification_expire` datetime DEFAULT NULL AFTER `verification_token`;

ALTER TABLE `products`
  MODIFY COLUMN `name` varchar(255) NOT NULL,
  MODIFY COLUMN `details` text NOT NULL,
  MODIFY COLUMN `image_01` varchar(255) NOT NULL,
  MODIFY COLUMN `image_02` varchar(255) NOT NULL,
  MODIFY COLUMN `image_03` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `category` varchar(100) NOT NULL DEFAULT 'Totebag' AFTER `image_03`;

UPDATE `products`
SET `category` = 'Totebag'
WHERE `category` IS NULL OR `category` = '';

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Totebag', 'Kategori produk Totebag'),
(2, 'Slingbag', 'Kategori produk Slingbag'),
(3, 'Dompet', 'Kategori produk Dompet'),
(4, 'Heels', 'Kategori produk Heels'),
(5, 'Flat Shoes', 'Kategori produk Flat Shoes'),
(6, 'Top Handle', 'Kategori produk Top Handle'),
(7, 'Clutch', 'Kategori produk Clutch'),
(8, 'Ransel', 'Kategori produk Ransel'),
(9, 'Waistbag', 'Kategori produk Waistbag');

CREATE TABLE IF NOT EXISTS `product_variations` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `product_id` int(100) NOT NULL,
  `variation_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_variations_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_variation_options` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `variation_id` int(100) NOT NULL,
  `option_value` varchar(100) NOT NULL,
  `option_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_variation_options_variation_id` (`variation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cart`
  MODIFY COLUMN `name` varchar(255) NOT NULL,
  MODIFY COLUMN `image` varchar(255) NOT NULL,
  ADD COLUMN IF NOT EXISTS `selected` tinyint(1) NOT NULL DEFAULT 1 AFTER `image`,
  ADD COLUMN IF NOT EXISTS `selected_variation_1_name` varchar(100) DEFAULT NULL AFTER `selected`,
  ADD COLUMN IF NOT EXISTS `selected_variation_1_value` varchar(100) DEFAULT NULL AFTER `selected_variation_1_name`,
  ADD COLUMN IF NOT EXISTS `selected_variation_2_name` varchar(100) DEFAULT NULL AFTER `selected_variation_1_value`,
  ADD COLUMN IF NOT EXISTS `selected_variation_2_value` varchar(100) DEFAULT NULL AFTER `selected_variation_2_name`;

ALTER TABLE `cart`
  MODIFY COLUMN `selected` tinyint(1) NOT NULL DEFAULT 1;

UPDATE `cart`
SET `selected` = 1
WHERE `selected` IS NULL;

ALTER TABLE `wishlist`
  MODIFY COLUMN `name` varchar(255) NOT NULL,
  MODIFY COLUMN `image` varchar(255) NOT NULL;

ALTER TABLE `orders`
  MODIFY COLUMN `name` varchar(255) NOT NULL,
  MODIFY COLUMN `number` varchar(255) NOT NULL,
  MODIFY COLUMN `email` varchar(255) NOT NULL,
  MODIFY COLUMN `address` text NOT NULL,
  MODIFY COLUMN `total_products` text NOT NULL,
  ADD COLUMN IF NOT EXISTS `order_number` varchar(50) DEFAULT NULL AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `tracking_number` varchar(100) NOT NULL DEFAULT '' AFTER `order_number`,
  ADD COLUMN IF NOT EXISTS `shipping_status` varchar(30) NOT NULL DEFAULT 'diproses' AFTER `tracking_number`,
  ADD COLUMN IF NOT EXISTS `payment_proof` varchar(255) NOT NULL DEFAULT '' AFTER `shipping_status`;

UPDATE `orders`
SET `shipping_status` = 'diproses'
WHERE `shipping_status` IS NULL OR `shipping_status` = '';

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `order_id` int(100) NOT NULL,
  `metode_pembayaran` varchar(20) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `tanggal_pembayaran` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payments_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `order_id` int(100) NOT NULL,
  `produk_id` int(100) NOT NULL,
  `jumlah` int(10) NOT NULL,
  `harga_satuan` int(10) NOT NULL,
  `subtotal` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_details_order_id` (`order_id`),
  KEY `idx_order_details_produk_id` (`produk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `messages`
  MODIFY COLUMN `user_id` int(100) NOT NULL DEFAULT 0,
  MODIFY COLUMN `email` varchar(255) NOT NULL,
  MODIFY COLUMN `number` varchar(30) NOT NULL,
  MODIFY COLUMN `message` text NOT NULL,
  ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `message`;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_user_id` (`user_id`),
  KEY `idx_chat_unread` (`sender`,`is_read`),
  KEY `idx_chat_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
