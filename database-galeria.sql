-- ============================================
-- TABLA: galeria
-- ============================================
-- Descripción: Imágenes de la galería de ideas
-- Compatible: MySQL 5.7+ / MariaDB
-- ============================================

CREATE TABLE IF NOT EXISTS `galeria` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `imagen` VARCHAR(500) NOT NULL,
  `alt` VARCHAR(255) DEFAULT NULL,
  `orden` INT(11) DEFAULT 0,
  `visible` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visible` (`visible`),
  KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================
-- 1. El campo 'nombre' es el nombre del archivo sin extensión (ej: 'idea1', 'idea100')
-- 2. El campo 'imagen' contiene la ruta completa (ej: '/images/0_galeria/idea1.webp')
-- 3. 'visible' controla qué imágenes se muestran en la web
-- 4. 'orden' permite ordenar las imágenes manualmente

