-- ============================================
-- ACTUALIZACIÓN: Agregar campo payer_document
-- ============================================
-- Si ya tienes la tabla orders, ejecuta solo este script
-- ============================================

-- Agregar campo payer_document si no existe
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `payer_document` VARCHAR(50) DEFAULT NULL AFTER `payer_phone`;

-- Si tu versión de MySQL no soporta IF NOT EXISTS, usa esto:
-- ALTER TABLE `orders` 
-- ADD COLUMN `payer_document` VARCHAR(50) DEFAULT NULL AFTER `payer_phone`;

