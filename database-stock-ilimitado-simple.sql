-- ============================================
-- MIGRACIÓN: Stock Ilimitado (Versión Simple)
-- ============================================
-- Ejecuta estos comandos UNO POR UNO en orden
-- ============================================

-- PASO 1: Actualizar el campo stock para permitir NULL (ilimitado)
ALTER TABLE `products` 
MODIFY COLUMN `stock` INT(11) DEFAULT NULL COMMENT 'Cantidad disponible en stock (NULL = ilimitado, 0 = sin stock, >0 = cantidad limitada)';

-- PASO 2: Agregar campo stock_minimo
-- Si ya existe, este comando dará error pero puedes ignorarlo
ALTER TABLE `products` 
ADD COLUMN `stock_minimo` INT(11) DEFAULT 5 COMMENT 'Cantidad mínima antes de alertar (solo para stock limitado)' AFTER `stock`;

-- PASO 3: Convertir productos existentes con stock = 1 a NULL (ilimitado)
UPDATE `products` 
SET `stock` = NULL 
WHERE `stock` = 1;

-- ============================================
-- VERIFICACIÓN (opcional)
-- ============================================
-- Ejecuta esto para verificar que funcionó:
-- SELECT COUNT(*) as productos_ilimitados FROM products WHERE stock IS NULL;
-- SELECT COUNT(*) as productos_limitados FROM products WHERE stock IS NOT NULL AND stock > 0;
-- SELECT COUNT(*) as productos_sin_stock FROM products WHERE stock = 0;

