<?php
/**
 * Script para verificar productos y rutas de imágenes
 */
define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verificación de Productos</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 1200px; margin: 0 auto; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #e0a4ce; color: white; }
        .ok { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        h2 { margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Productos e Imágenes</h1>
    
    <?php
    // Obtener todos los productos
    $products = fetchAll("SELECT * FROM products ORDER BY created_at DESC");
    
    if (!$products || count($products) === 0) {
        echo '<p class="error">❌ No hay productos en la base de datos</p>';
    } else {
        echo '<h2>📦 Productos en la Base de Datos (' . count($products) . ')</h2>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Visible</th><th>Destacado</th><th>Ruta Imagen</th><th>Imagen Existe</th></tr>';
        
        foreach ($products as $product) {
            $visible = $product['visible'] ? '✅ Sí' : '❌ No';
            $destacado = $product['destacado'] ? '⭐' : '';
            
            // Verificar si la imagen existe
            $imagePath = $product['image'] ?? '';
            $imageExists = '';
            $fullPath = '';
            
            if (!empty($imagePath)) {
                // Remover parámetros de cache busting
                $cleanPath = preg_replace('/\?.*$/', '', $imagePath);
                // La ruta en BD es /images/... (ruta web), físicamente está en public/images/...
                $fullPath = BASE_PATH . '/public' . $cleanPath;
                $exists = file_exists($fullPath);
                
                if ($exists) {
                    $imageExists = '<span class="ok">✅ Existe</span><br><small>' . htmlspecialchars($cleanPath) . '</small>';
                } else {
                    $imageExists = '<span class="error">❌ No existe: ' . htmlspecialchars($fullPath) . '</span>';
                }
            } else {
                $imageExists = '<span class="warning">⚠️ Sin imagen</span>';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($product['id']) . '</td>';
            echo '<td>' . htmlspecialchars($product['name']) . '</td>';
            echo '<td>' . htmlspecialchars($product['categoria']) . '</td>';
            echo '<td>' . $visible . '</td>';
            echo '<td>' . $destacado . '</td>';
            echo '<td>' . htmlspecialchars($imagePath) . '</td>';
            echo '<td>' . $imageExists . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
    
    // Productos visibles
    $visibleProducts = fetchAll("SELECT COUNT(*) as count FROM products WHERE visible = 1");
    $visibleCount = $visibleProducts[0]['count'] ?? 0;
    
    echo '<h2>👁️ Productos Visibles</h2>';
    echo '<p>Total de productos visibles: <strong>' . $visibleCount . '</strong></p>';
    
    // Verificar rutas de imágenes comunes
    echo '<h2>📁 Verificación de Rutas</h2>';
    echo '<ul>';
    echo '<li>BASE_PATH: <code>' . BASE_PATH . '</code></li>';
    echo '<li>IMAGES_PATH: <code>' . IMAGES_PATH . '</code></li>';
    echo '<li>IMAGES_URL: <code>' . IMAGES_URL . '</code></li>';
    echo '<li>IMAGES_PATH existe: ' . (is_dir(IMAGES_PATH) ? '<span class="ok">✅ Sí</span>' : '<span class="error">❌ No</span>') . '</li>';
    echo '</ul>';
    
    // Verificar archivos de imágenes
    if (is_dir(IMAGES_PATH)) {
        $imageDirs = glob(IMAGES_PATH . '/*', GLOB_ONLYDIR);
        echo '<h2>📂 Carpetas de Imágenes (' . count($imageDirs) . ')</h2>';
        echo '<ul>';
        foreach (array_slice($imageDirs, 0, 10) as $dir) {
            $dirName = basename($dir);
            $files = glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
            echo '<li><strong>' . htmlspecialchars($dirName) . '</strong> - ' . count($files) . ' archivo(s)</li>';
        }
        if (count($imageDirs) > 10) {
            echo '<li>... y ' . (count($imageDirs) - 10) . ' más</li>';
        }
        echo '</ul>';
    }
    ?>
    
    <h2>🔗 Prueba la API</h2>
    <ul>
        <li><a href="/api/products.php" target="_blank">Todos los productos</a></li>
        <li><a href="/api/products.php?categoria=productos" target="_blank">Productos</a></li>
        <li><a href="/api/products.php?categoria=souvenirs" target="_blank">Souvenirs</a></li>
        <li><a href="/api/products.php?categoria=navidad" target="_blank">Navidad</a></li>
    </ul>
</body>
</html>

