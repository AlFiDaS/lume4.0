-- ============================================
-- MIGRACIÓN: Sistema de Categorías Dinámicas
-- ============================================
-- Este script crea la tabla de categorías y migra las existentes
-- Ejecutar en orden:
-- 1. Crear tabla categories
-- 2. Insertar categorías iniciales
-- 3. Modificar tabla products (cambiar ENUM a VARCHAR)
-- ============================================

-- ============================================
-- PASO 1: Crear tabla categories
-- ============================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `visible` TINYINT(1) DEFAULT 1,
  `orden` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_slug` (`slug`),
  KEY `idx_visible` (`visible`),
  KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 2: Insertar categorías iniciales
-- ============================================
INSERT IGNORE INTO `categories` (`slug`, `name`, `visible`, `orden`) VALUES
('productos', 'Productos', 1, 1),
('souvenirs', 'Souvenirs', 1, 2),
('navidad', 'Navidad', 1, 3);

-- ============================================
-- PASO 3: Modificar tabla products
-- ============================================
-- Cambiar el campo categoria de ENUM a VARCHAR para permitir categorías dinámicas
-- IMPORTANTE: Esto requiere que no haya datos o que todos los valores sean válidos

-- Primero, verificar que todos los valores actuales sean válidos
-- Si hay valores fuera de 'productos', 'souvenirs', 'navidad', ajustarlos primero

-- Modificar la columna categoria
ALTER TABLE `products` 
MODIFY COLUMN `categoria` VARCHAR(100) NOT NULL;

-- Actualizar el índice
DROP INDEX IF EXISTS `idx_categoria` ON `products`;
CREATE INDEX `idx_categoria` ON `products` (`categoria`);

-- ============================================
-- VERIFICACIÓN
-- ============================================
-- Verificar que las categorías fueron creadas:
-- SELECT * FROM categories;

-- Verificar que los productos tienen categorías válidas:
-- SELECT DISTINCT categoria FROM products;

