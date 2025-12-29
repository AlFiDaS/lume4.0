-- ============================================
-- TABLA: orders
-- ============================================
-- Tabla para guardar los pedidos realizados
-- ============================================

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `mercadopago_id` VARCHAR(255) DEFAULT NULL,
  `preference_id` VARCHAR(255) DEFAULT NULL,
  `external_reference` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT NULL,
  `status_detail` VARCHAR(100) DEFAULT NULL,
  `payer_name` VARCHAR(255) DEFAULT NULL,
  `payer_email` VARCHAR(255) DEFAULT NULL,
  `payer_phone` VARCHAR(50) DEFAULT NULL,
  `payer_document` VARCHAR(50) DEFAULT NULL,
  `items` TEXT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) DEFAULT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_type` VARCHAR(50) DEFAULT NULL,
  `shipping_type` VARCHAR(255) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `metadata` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mercadopago_id` (`mercadopago_id`),
  KEY `idx_preference_id` (`preference_id`),
  KEY `idx_external_reference` (`external_reference`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

