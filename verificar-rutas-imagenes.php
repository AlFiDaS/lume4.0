<?php
/**
 * Script para verificar rutas de imágenes en Hostinger
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>🔍 Verificación de Rutas de Imágenes</h2>";
echo "<pre>";

// 1. Verificar configuración
echo "1. Configuración:\n";
echo "   BASE_PATH: " . BASE_PATH . "\n";
echo "   IMAGES_PATH: " . IMAGES_PATH . "\n";
echo "   IMAGES_URL: " . IMAGES_URL . "\n";
echo "   DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "\n";

// 2. Verificar si existe public/images
echo "2. Verificando directorios:\n";
$dirs_to_check = [
    BASE_PATH . '/public/images',
    BASE_PATH . '/images',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/images',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/public/images',
];

foreach ($dirs_to_check as $dir) {
    if ($dir) {
        echo "   " . $dir . ": " . (is_dir($dir) ? "✅ Existe" : "❌ No existe") . "\n";
        if (is_dir($dir)) {
            echo "      Permisos: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
            echo "      Escritura: " . (is_writable($dir) ? "✅ Sí" : "❌ No") . "\n";
        }
    }
}
echo "\n";

// 3. Verificar una imagen de ejemplo de la BD
echo "3. Verificando imágenes en la base de datos:\n";
try {
    require_once 'helpers/db.php';
    
    $products = fetchAll("SELECT slug, categoria, image, hoverImage FROM products WHERE image IS NOT NULL LIMIT 5");
    
    if ($products) {
        foreach ($products as $product) {
            echo "   Producto: {$product['name']} ({$product['categoria']}/{$product['slug']})\n";
            
            // Verificar imagen principal
            if ($product['image']) {
                $imagePath = $product['image'];
                echo "      Image: {$imagePath}\n";
                
                // Probar diferentes rutas
                $possible_paths = [
                    BASE_PATH . $imagePath,
                    BASE_PATH . '/public' . $imagePath,
                    ($_SERVER['DOCUMENT_ROOT'] ?? '') . $imagePath,
                    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/public' . $imagePath,
                ];
                
                $found = false;
                foreach ($possible_paths as $path) {
                    if ($path && file_exists($path)) {
                        echo "         ✅ Encontrada en: {$path}\n";
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    echo "         ❌ NO encontrada en ninguna ubicación\n";
                }
            }
            
            // Verificar imagen hover
            if ($product['hoverImage']) {
                $hoverPath = $product['hoverImage'];
                echo "      Hover: {$hoverPath}\n";
                
                $possible_paths = [
                    BASE_PATH . $hoverPath,
                    BASE_PATH . '/public' . $hoverPath,
                    ($_SERVER['DOCUMENT_ROOT'] ?? '') . $hoverPath,
                    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/public' . $hoverPath,
                ];
                
                $found = false;
                foreach ($possible_paths as $path) {
                    if ($path && file_exists($path)) {
                        echo "         ✅ Encontrada en: {$path}\n";
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    echo "         ❌ NO encontrada en ninguna ubicación\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "   No hay productos con imágenes en la BD\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";

