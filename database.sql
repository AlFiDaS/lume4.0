-- ============================================
-- BASE DE DATOS: LUME - Catálogo de Velas
-- ============================================
-- Descripción: Estructura de base de datos para catálogo dinámico de productos
-- Compatible: MySQL 5.7+ / MariaDB
-- ============================================

-- Crear base de datos (opcional, comenta si ya existe)
-- CREATE DATABASE IF NOT EXISTS lume_catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE lume_catalogo;

-- ============================================
-- TABLA: products
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `price` VARCHAR(50) DEFAULT NULL,
  `image` VARCHAR(500) DEFAULT NULL,
  `hoverImage` VARCHAR(500) DEFAULT NULL,
  `stock` TINYINT(1) DEFAULT 1,
  `destacado` TINYINT(1) DEFAULT 0,
  `categoria` ENUM('productos', 'souvenirs', 'navidad') NOT NULL,
  `visible` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_slug` (`slug`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_destacado` (`destacado`),
  KEY `idx_visible` (`visible`),
  KEY `idx_stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: admin_users (Opcional - para sistema de login)
-- ============================================
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE EJEMPLO (Opcional)
-- ============================================
-- INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
-- ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@lume.com');
-- Contraseña por defecto: "password" (CAMBIA ESTO EN PRODUCCIÓN)

-- ============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================
-- Los índices ya están creados en la definición de la tabla
-- Para consultas comunes como:
-- - Productos por categoría y visibles
-- - Productos destacados
-- - Productos con stock

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================
-- 1. El campo 'id' usa VARCHAR(50) para mayor flexibilidad
-- 2. El campo 'slug' debe ser único para URLs amigables
-- 3. 'visible' controla qué productos se muestran en la web
-- 4. 'destacado' marca productos para homepage
-- 5. Todos los timestamps usan zona horaria del servidor

