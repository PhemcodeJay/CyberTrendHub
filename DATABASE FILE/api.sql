SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Digital Products Table
CREATE TABLE `digital_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `file_url` VARCHAR(255) NOT NULL, -- path to digital product file
    `is_digital` BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- General Products Table for Dropshipping Items
CREATE TABLE `tbl_product` (
  `p_id` INT AUTO_INCREMENT PRIMARY KEY,
  `p_name` VARCHAR(255) NOT NULL,
  `p_old_price` DECIMAL(10,2) NOT NULL,
  `p_current_price` DECIMAL(10,2) NOT NULL,
  `p_qty` INT(10) NOT NULL,
  `p_featured_photo` VARCHAR(255) NOT NULL,
  `p_description` TEXT NOT NULL,
  `p_short_description` TEXT NOT NULL,
  `p_feature` TEXT NOT NULL,
  `p_condition` TEXT NOT NULL,
  `p_return_policy` TEXT NOT NULL,
  `p_total_view` INT(11) NOT NULL,
  `p_is_featured` INT(1) NOT NULL,
  `p_is_active` INT(1) NOT NULL,
  `ecat_id` INT(11) NOT NULL,
  `aliexpress_product_id` VARCHAR(100) DEFAULT NULL,
  `cj_product_id` VARCHAR(100) DEFAULT NULL,
  `source_url` VARCHAR(255) DEFAULT NULL,
  `vendor_name` VARCHAR(50) DEFAULT NULL,
  `inventory_sync` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Orders Table for Both Digital and Dropshipping Products
CREATE TABLE `tbl_order` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT(11) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `size` VARCHAR(100) DEFAULT NULL,
  `color` VARCHAR(100) DEFAULT NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `payment_id` VARCHAR(255) NOT NULL,
  `aliexpress_order_id` VARCHAR(100) DEFAULT NULL,
  `cj_order_id` VARCHAR(100) DEFAULT NULL,
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `carrier` VARCHAR(100) DEFAULT NULL,
  `order_status` VARCHAR(50) DEFAULT 'Pending',
  `shipping_status` VARCHAR(50) DEFAULT 'Processing',
  `estimated_delivery` DATE DEFAULT NULL,
  `download_code` VARCHAR(32) UNIQUE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `tbl_product`(`p_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inventory Table for Dropshipping Products
CREATE TABLE `tbl_inventory` (
  `inventory_id` INT AUTO_INCREMENT PRIMARY KEY,
  `p_id` INT(11) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `stock` INT(11) NOT NULL,
  `cost_price` DECIMAL(10,2) NOT NULL,
  `retail_price` DECIMAL(10,2) NOT NULL,
  `source` ENUM('AliExpress', 'CJ') NOT NULL,
  FOREIGN KEY (`p_id`) REFERENCES `tbl_product`(`p_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Shipping Information Table for Tracking
CREATE TABLE `tbl_shipping_info` (
  `shipping_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT(11) NOT NULL,
  `tracking_number` VARCHAR(100) NOT NULL,
  `carrier` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'In Transit',
  `estimated_arrival` DATE DEFAULT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `tbl_order`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional Shipping Cost Table for Additional Costs
CREATE TABLE `tbl_shipping_cost_all` (
  `sca_id` INT AUTO_INCREMENT PRIMARY KEY,
  `amount` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
