<?php
/**
 * ============================================
 * CONFIGURACIÓN: LUME - Catálogo de Velas
 * ============================================
 * Archivo de configuración central
 * Compatible: PHP 7.4+
 * ============================================
 */

// Prevenir acceso directo
if (!defined('LUME_ADMIN')) {
    define('LUME_ADMIN', true);
}

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');          // Cambia si tu DB está en otro servidor

// Detectar si estamos en local
// Por defecto asumimos local si no estamos en producción (Hostinger)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;

// Detectar producción: si el host contiene un dominio (no localhost) o está en htdocs
$isProduction = (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false) ||
                (strpos($scriptFile, 'htdocs') !== false && strpos($host, 'localhost') === false);

if (!$isProduction) {
    // Configuración para LOCAL
    define('DB_NAME', 'lume_catalogo');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Configuración para PRODUCCIÓN (Hostinger)
    define('DB_NAME', 'u161673556_lume');
    define('DB_USER', 'u161673556_lume');
    define('DB_PASS', 'Luky2428');
}
define('DB_CHARSET', 'utf8mb4');

// ============================================
// RUTAS DEL SISTEMA
// ============================================
// Directorio raíz del proyecto (ajusta según tu estructura)
define('BASE_PATH', dirname(__FILE__));

// Ruta base de la URL (ajusta según tu dominio)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Detectar host correctamente
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
} elseif (isset($_SERVER['SERVER_NAME'])) {
    $host = $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
        $host .= ':' . $_SERVER['SERVER_PORT'];
    }
} else {
    $host = 'localhost:8080';
}

// BASE_URL siempre debe ser la raíz del dominio, sin subdirectorios
// Independientemente de dónde esté el script actual (admin/, api/, etc.)
define('BASE_URL', $protocol . '://' . $host);

// Rutas de administración
define('ADMIN_PATH', BASE_PATH . '/admin');
define('ADMIN_URL', BASE_URL . '/admin');

// Ruta de imágenes
define('IMAGES_PATH', BASE_PATH . '/public/images');
define('IMAGES_URL', BASE_URL . '/images');

// Ruta de API
define('API_PATH', BASE_PATH . '/api');
define('API_URL', BASE_URL . '/api');

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
// Clave secreta para tokens CSRF (cambia esto)
define('CSRF_SECRET', 'cambia-esta-clave-secreta-por-una-aleatoria-muy-larga-123456789');

// Configuración de sesiones
define('SESSION_NAME', 'LUME_ADMIN_SESSION');
define('SESSION_LIFETIME', 3600 * 24); // 24 horas

// Intentos máximos de login
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// ============================================
// CONFIGURACIÓN DE SUBIDA DE ARCHIVOS
// ============================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// ============================================
// CONFIGURACIÓN GENERAL
// ============================================
define('SITE_NAME', 'LUME - Velas Artesanales');
define('TIMEZONE', 'America/Argentina/Buenos_Aires');

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// ============================================
// CONFIGURACIÓN DE ERRORES
// ============================================
// En producción, cambiar a 0
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// AUTOLOAD DE HELPERS
// ============================================
$helpersPath = BASE_PATH . '/helpers';
if (is_dir($helpersPath)) {
    require_once $helpersPath . '/db.php';
    require_once $helpersPath . '/auth.php';
}

