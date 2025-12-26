<?php
/**
 * ============================================
 * EXPORTAR PRODUCTOS PARA IMPORTAR EN HOSTINGER
 * ============================================
 * Exporta productos de la base de datos local a un archivo SQL
 * que puedes importar directamente en Hostinger usando phpMyAdmin
 * 
 * USO: php exportar-productos-para-hostinger.php
 * ============================================
 */

define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

echo "============================================\n";
echo "  EXPORTAR PRODUCTOS PARA HOSTINGER\n";
echo "============================================\n\n";

// Verificar si la tabla existe
$tableExists = fetchOne("SHOW TABLES LIKE 'products'");
if (!$tableExists) {
    echo "❌ ERROR: La tabla 'products' no existe.\n";
    exit(1);
}

echo "✅ Tabla 'products' encontrada.\n\n";

// Obtener todos los productos
$products = fetchAll("SELECT * FROM products ORDER BY categoria, name");

if (empty($products)) {
    echo "❌ No hay productos en la base de datos para exportar.\n";
    exit(1);
}

echo "📦 Productos encontrados: " . count($products) . "\n\n";

// Generar nombre de archivo
$filename = 'productos-export-' . date('Y-m-d-His') . '.sql';
$filepath = __DIR__ . '/' . $filename;

// Abrir archivo para escritura
$file = fopen($filepath, 'w');

if (!$file) {
    echo "❌ Error: No se pudo crear el archivo de exportación.\n";
    exit(1);
}

// Escribir encabezado
fwrite($file, "-- ============================================\n");
fwrite($file, "-- EXPORTACIÓN DE PRODUCTOS PARA HOSTINGER\n");
fwrite($file, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
fwrite($file, "-- Total productos: " . count($products) . "\n");
fwrite($file, "-- ============================================\n\n");

// Desactivar checks de claves foráneas temporalmente
fwrite($file, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

// Escribir INSERT statements
foreach ($products as $product) {
    $sql = "INSERT INTO `products` (`id`, `slug`, `name`, `descripcion`, `price`, `image`, `hoverImage`, `stock`, `destacado`, `categoria`, `visible`, `created_at`, `updated_at`) VALUES (";
    
    $sql .= "'" . addslashes($product['id']) . "', ";
    $sql .= "'" . addslashes($product['slug']) . "', ";
    $sql .= "'" . addslashes($product['name']) . "', ";
    $sql .= "'" . addslashes($product['descripcion'] ?? '') . "', ";
    $sql .= "'" . addslashes($product['price'] ?? '') . "', ";
    $sql .= "'" . addslashes($product['image'] ?? '') . "', ";
    $sql .= "'" . addslashes($product['hoverImage'] ?? '') . "', ";
    $sql .= ($product['stock'] ?? 1) . ", ";
    $sql .= ($product['destacado'] ?? 0) . ", ";
    $sql .= "'" . addslashes($product['categoria']) . "', ";
    $sql .= ($product['visible'] ?? 0) . ", ";
    $sql .= "'" . ($product['created_at'] ?? date('Y-m-d H:i:s')) . "', ";
    $sql .= "'" . ($product['updated_at'] ?? null) . "'";
    
    $sql .= ") ON DUPLICATE KEY UPDATE ";
    $sql .= "`name` = VALUES(`name`), ";
    $sql .= "`descripcion` = VALUES(`descripcion`), ";
    $sql .= "`price` = VALUES(`price`), ";
    $sql .= "`image` = VALUES(`image`), ";
    $sql .= "`hoverImage` = VALUES(`hoverImage`), ";
    $sql .= "`stock` = VALUES(`stock`), ";
    $sql .= "`destacado` = VALUES(`destacado`), ";
    $sql .= "`categoria` = VALUES(`categoria`), ";
    $sql .= "`visible` = VALUES(`visible`), ";
    $sql .= "`updated_at` = NOW();\n";
    
    fwrite($file, $sql);
}

// Reactivar checks de claves foráneas
fwrite($file, "\nSET FOREIGN_KEY_CHECKS = 1;\n");

// Cerrar archivo
fclose($file);

echo "✅ Exportación completada.\n\n";
echo "📄 Archivo creado: $filename\n";
echo "📁 Ubicación: $filepath\n\n";
echo "📋 Próximos pasos:\n";
echo "   1. Descarga el archivo '$filename'\n";
echo "   2. Ve a phpMyAdmin en Hostinger\n";
echo "   3. Selecciona la base de datos 'u161673556_lume'\n";
echo "   4. Ve a la pestaña 'Importar'\n";
echo "   5. Selecciona el archivo '$filename' y haz clic en 'Continuar'\n";
echo "   6. Los productos se importarán (se saltarán duplicados si existen)\n\n";

