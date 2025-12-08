<?php
/**
 * ============================================
 * Crear tabla de galería si no existe
 * ============================================
 */

require_once 'config.php';

if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}

require_once 'helpers/db.php';

echo "🔧 Verificando tabla 'galeria'...\n\n";

// Verificar si la tabla existe
$tableExists = fetchOne("SHOW TABLES LIKE 'galeria'");

if ($tableExists) {
    echo "✅ La tabla 'galeria' ya existe.\n";
    exit(0);
}

echo "📝 Creando tabla 'galeria'...\n\n";

// SQL para crear la tabla
$sql = "CREATE TABLE IF NOT EXISTS `galeria` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (executeQuery($sql)) {
    echo "✅ Tabla 'galeria' creada exitosamente.\n";
} else {
    echo "❌ Error al crear la tabla 'galeria'.\n";
    echo "Verifica los logs de error de PHP.\n";
    exit(1);
}

