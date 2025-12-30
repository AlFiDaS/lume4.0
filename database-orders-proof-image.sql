-- ============================================
-- AGREGAR COLUMNA: proof_image a orders
-- ============================================
-- Agrega la columna para almacenar la ruta de la imagen del comprobante de pago
-- ============================================

ALTER TABLE `orders` 
ADD COLUMN `proof_image` VARCHAR(500) DEFAULT NULL AFTER `payer_document`;

