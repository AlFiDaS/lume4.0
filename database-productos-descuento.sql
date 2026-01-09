-- ============================================
-- AGREGAR SOPORTE PARA PRECIOS EN DESCUENTO
-- ============================================
-- Descripción: Agregar campos para gestionar precios en descuento en productos
-- Compatible: MySQL 5.7+ / MariaDB
-- ============================================

-- Agregar columna para indicar si el producto está en descuento
ALTER TABLE `products` 
ADD COLUMN `en_descuento` TINYINT(1) DEFAULT 0 AFTER `price`;

-- Agregar columna para el precio en descuento
ALTER TABLE `products` 
ADD COLUMN `precio_descuento` VARCHAR(50) DEFAULT NULL AFTER `en_descuento`;

-- Agregar índice para búsquedas rápidas de productos en descuento
ALTER TABLE `products` 
ADD KEY `idx_en_descuento` (`en_descuento`);
