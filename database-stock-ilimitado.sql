-- ============================================
-- MIGRACIÓN: Stock Ilimitado
-- ============================================
-- Este script actualiza el sistema de stock para permitir
-- productos con stock ilimitado (NULL) por defecto
-- ============================================

-- ============================================
-- PASO 1: Actualizar tabla products para stock ilimitado
-- ============================================
-- Cambiar stock para permitir NULL (ilimitado)
-- NULL = stock ilimitado (productos hechos bajo pedido)
-- 0 = sin stock
-- > 0 = cantidad limitada
ALTER TABLE `products` 
MODIFY COLUMN `stock` INT(11) DEFAULT NULL COMMENT 'Cantidad disponible en stock (NULL = ilimitado, 0 = sin stock, >0 = cantidad limitada)';

-- ============================================
-- PASO 2: Agregar campo stock_minimo (si no existe)
-- ============================================
-- Verificar si la columna ya existe antes de agregarla
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'products' 
AND COLUMN_NAME = 'stock_minimo';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `products` ADD COLUMN `stock_minimo` INT(11) DEFAULT 5 COMMENT ''Cantidad mínima antes de alertar (solo para stock limitado)'' AFTER `stock`',
    'SELECT ''Columna stock_minimo ya existe'' AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- PASO 3: Actualizar productos existentes
-- ============================================
-- Convertir productos con stock = 1 (antiguo booleano "en stock") a NULL (ilimitado)
-- Mantener stock = 0 como "sin stock"
UPDATE `products` 
SET `stock` = NULL 
WHERE `stock` = 1;

-- ============================================
-- VERIFICACIÓN
-- ============================================
-- Verificar que los cambios se aplicaron correctamente:
-- SELECT COUNT(*) as productos_ilimitados FROM products WHERE stock IS NULL;
-- SELECT COUNT(*) as productos_limitados FROM products WHERE stock IS NOT NULL AND stock > 0;
-- SELECT COUNT(*) as productos_sin_stock FROM products WHERE stock = 0;
-- DESCRIBE products;

