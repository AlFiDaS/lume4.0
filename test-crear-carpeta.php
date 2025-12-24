<?php
/**
 * Script para probar creación de carpetas en Hostinger
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>🔍 Test de Creación de Carpetas</h2>";
echo "<pre>";

// 1. Verificar configuración
echo "1. Configuración:\n";
echo "   BASE_PATH: " . BASE_PATH . "\n";
echo "   IMAGES_PATH: " . IMAGES_PATH . "\n";
echo "\n";

// 2. Verificar si existe el directorio base
echo "2. Verificando directorios base:\n";
echo "   " . BASE_PATH . "/public: " . (is_dir(BASE_PATH . '/public') ? "✅ Existe" : "❌ No existe") . "\n";
echo "   " . BASE_PATH . "/public/images: " . (is_dir(BASE_PATH . '/public/images') ? "✅ Existe" : "❌ No existe") . "\n";
echo "   " . IMAGES_PATH . ": " . (is_dir(IMAGES_PATH) ? "✅ Existe" : "❌ No existe") . "\n";

if (is_dir(IMAGES_PATH)) {
    echo "      Permisos: " . substr(sprintf('%o', fileperms(IMAGES_PATH)), -4) . "\n";
    echo "      Escritura: " . (is_writable(IMAGES_PATH) ? "✅ Sí" : "❌ No") . "\n";
}
echo "\n";

// 3. Intentar crear una carpeta de prueba
echo "3. Intentando crear carpeta de prueba:\n";
$testDir = IMAGES_PATH . '/navidad/test-' . time();

echo "   Ruta: {$testDir}\n";

// Verificar directorio padre
$parentDir = dirname($testDir);
echo "   Directorio padre: {$parentDir}\n";
echo "   Existe: " . (is_dir($parentDir) ? "✅" : "❌") . "\n";
if (is_dir($parentDir)) {
    echo "   Permisos: " . substr(sprintf('%o', fileperms($parentDir)), -4) . "\n";
    echo "   Escritura: " . (is_writable($parentDir) ? "✅" : "❌") . "\n";
}
echo "\n";

// Intentar crear la carpeta
if (mkdir($testDir, 0755, true)) {
    echo "   ✅ Carpeta creada exitosamente\n";
    
    // Intentar crear un archivo de prueba
    $testFile = $testDir . '/test.txt';
    if (file_put_contents($testFile, 'test')) {
        echo "   ✅ Archivo de prueba creado\n";
        
        // Limpiar
        unlink($testFile);
        rmdir($testDir);
        echo "   ✅ Carpeta de prueba eliminada\n";
    } else {
        echo "   ❌ No se pudo crear archivo de prueba\n";
    }
} else {
    echo "   ❌ ERROR: No se pudo crear la carpeta\n";
    $error = error_get_last();
    if ($error) {
        echo "   Mensaje: " . $error['message'] . "\n";
    }
}

echo "\n";

// 4. Verificar carpeta específica del producto
echo "4. Verificando carpeta del producto 'luky':\n";
$lukyDir = IMAGES_PATH . '/navidad/luky';
echo "   Ruta: {$lukyDir}\n";
echo "   Existe: " . (is_dir($lukyDir) ? "✅" : "❌") . "\n";

if (!is_dir($lukyDir)) {
    echo "   Intentando crear...\n";
    if (mkdir($lukyDir, 0755, true)) {
        echo "   ✅ Carpeta creada\n";
    } else {
        echo "   ❌ No se pudo crear\n";
        $error = error_get_last();
        if ($error) {
            echo "   Error: " . $error['message'] . "\n";
        }
    }
}

echo "</pre>";

