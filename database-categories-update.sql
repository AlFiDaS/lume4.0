-- ============================================
-- MIGRACIÓN: Agregar campos catalog_title y min_quantity a categories
-- ============================================
-- Este script agrega los campos necesarios para:
-- - Título personalizado del catálogo (catalog_title)
-- - Cantidad mínima de compra por categoría (min_quantity)
-- ============================================

-- Agregar campo catalog_title (título personalizado del catálogo)
ALTER TABLE `categories` 
ADD COLUMN `catalog_title` VARCHAR(255) NULL DEFAULT NULL 
AFTER `name`;

-- Agregar campo min_quantity (cantidad mínima de compra)
ALTER TABLE `categories` 
ADD COLUMN `min_quantity` INT(11) NULL DEFAULT NULL 
AFTER `catalog_title`;

-- Actualizar la categoría souvenirs para que tenga min_quantity = 10
UPDATE `categories` 
SET `min_quantity` = 10 
WHERE `slug` = 'souvenirs';

-- Verificar cambios
-- SELECT id, slug, name, catalog_title, min_quantity FROM categories;

