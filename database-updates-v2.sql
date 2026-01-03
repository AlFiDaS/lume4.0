-- ============================================
-- MIGRACIÓN V2: Nuevas Funcionalidades
-- ============================================
-- Este script agrega las tablas y campos necesarios para:
-- - Dashboard de estadísticas
-- - Control de stock avanzado
-- - Sistema de cupones/descuentos
-- - Reviews/reseñas
-- - Wishlist/favoritos
-- - Historial de pedidos para clientes
-- ============================================

-- ============================================
-- PASO 1: Actualizar tabla products para stock numérico
-- ============================================
-- Cambiar stock de TINYINT(1) a INT para permitir cantidades
-- NULL = stock ilimitado (productos hechos bajo pedido)
-- 0 = sin stock
-- > 0 = cantidad limitada
ALTER TABLE `products` 
MODIFY COLUMN `stock` INT(11) DEFAULT NULL COMMENT 'Cantidad disponible en stock (NULL = ilimitado, 0 = sin stock, >0 = cantidad limitada)';

-- Agregar campo stock_minimo para alertas (solo para productos con stock limitado)
ALTER TABLE `products` 
ADD COLUMN `stock_minimo` INT(11) DEFAULT 5 COMMENT 'Cantidad mínima antes de alertar (solo para stock limitado)' AFTER `stock`;

-- Actualizar productos existentes: si stock = 1 (antiguo booleano), poner NULL (ilimitado)
-- Si stock = 0, mantener 0 (sin stock)
UPDATE `products` SET `stock` = NULL WHERE `stock` = 1;

-- ============================================
-- PASO 2: Tabla de cupones/descuentos
-- ============================================
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL,
  `min_purchase` DECIMAL(10,2) DEFAULT 0,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `usage_limit` INT(11) DEFAULT NULL,
  `used_count` INT(11) DEFAULT 0,
  `valid_from` DATETIME DEFAULT NULL,
  `valid_until` DATETIME DEFAULT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `applicable_to` ENUM('all', 'category', 'product') DEFAULT 'all',
  `category_slug` VARCHAR(100) DEFAULT NULL,
  `product_id` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_active` (`active`),
  KEY `idx_valid_dates` (`valid_from`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 3: Tabla de reviews/reseñas
-- ============================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `rating` TINYINT(1) NOT NULL COMMENT 'Calificación de 1 a 5',
  `comment` TEXT DEFAULT NULL,
  `verified_purchase` TINYINT(1) DEFAULT 0,
  `order_id` INT(11) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_rating` (`rating`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 4: Tabla de wishlist/favoritos
-- ============================================
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(255) NOT NULL COMMENT 'ID de sesión o email del cliente',
  `product_id` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_product` (`session_id`, `product_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_session_id` (`session_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 5: Tabla de clientes (para historial de pedidos)
-- ============================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 6: Agregar customer_id a orders (opcional, para relacionar con customers)
-- ============================================
ALTER TABLE `orders` 
ADD COLUMN `customer_id` INT(11) DEFAULT NULL AFTER `id`,
ADD KEY `idx_customer_id` (`customer_id`),
ADD FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL;

-- ============================================
-- PASO 7: Agregar coupon_id a orders
-- ============================================
ALTER TABLE `orders` 
ADD COLUMN `coupon_code` VARCHAR(50) DEFAULT NULL AFTER `payment_method`,
ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0 AFTER `coupon_code`,
ADD KEY `idx_coupon_code` (`coupon_code`);

-- ============================================
-- PASO 8: Tabla de movimientos de stock (historial)
-- ============================================
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` VARCHAR(50) NOT NULL,
  `type` ENUM('sale', 'restock', 'adjustment', 'return') NOT NULL,
  `quantity` INT(11) NOT NULL COMMENT 'Cantidad positiva o negativa',
  `order_id` INT(11) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VERIFICACIÓN
-- ============================================
-- Verificar que las tablas fueron creadas:
-- SHOW TABLES LIKE 'coupons';
-- SHOW TABLES LIKE 'reviews';
-- SHOW TABLES LIKE 'wishlist';
-- SHOW TABLES LIKE 'customers';
-- SHOW TABLES LIKE 'stock_movements';

-- Verificar que los campos fueron agregados:
-- DESCRIBE products;
-- DESCRIBE orders;

