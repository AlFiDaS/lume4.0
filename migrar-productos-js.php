<?php
/**
 * ============================================
 * MIGRACIÓN: Importar productos desde archivos JS
 * ============================================
 * Importa productos desde productos.js, souvenirs.js y navidad.js a la base de datos
 * 
 * USO: php migrar-productos-js.php
 * ============================================
 */

define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';
require_once 'helpers/slugify.php';
require_once 'helpers/upload.php';

echo "============================================\n";
echo "  MIGRACIÓN DE PRODUCTOS DESDE JS\n";
echo "============================================\n\n";

// Verificar si la tabla existe
$tableExists = fetchOne("SHOW TABLES LIKE 'products'");
if (!$tableExists) {
    echo "❌ ERROR: La tabla 'products' no existe.\n";
    echo "Por favor, ejecuta primero el script SQL: database.sql\n";
    exit(1);
}

echo "✅ Tabla 'products' encontrada.\n\n";

/**
 * Función para extraer productos de archivos JS usando un parser simple pero robusto
 */
function parseJsFile($filePath, $defaultCategoria) {
    if (!file_exists($filePath)) {
        echo "⚠️  Advertencia: No se encontró el archivo $filePath\n";
        return [];
    }
    
    $content = file_get_contents($filePath);
    $products = [];
    
    // Remover comentarios de línea
    $content = preg_replace('/\/\/.*$/m', '', $content);
    
    // Buscar el array después de "export const"
    // Patrones para diferentes archivos
    $patterns = [
        '/export\s+const\s+productos\s*=\s*\[(.*)\];/s',
        '/export\s+const\s+souvenirs\s*=\s*\[(.*)\];/s',
        '/export\s+const\s+productosNavidad\s*=\s*\[(.*)\];/s'
    ];
    
    $arrayContent = null;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $arrayContent = $matches[1];
            break;
        }
    }
    
    if (!$arrayContent) {
        echo "⚠️  No se pudo encontrar el array en $filePath\n";
        return [];
    }
    
    // Dividir por objetos (buscar patrones { ... })
    // Usar un método más simple: dividir por "},\n  {" o "},\n    {" o "},\n\n  {"
    $objects = preg_split('/\},\s*\n\s*\{/', $arrayContent);
    
    foreach ($objects as $idx => $objStr) {
        // Limpiar: agregar llaves si no las tiene
        $objStr = trim($objStr);
        if (!preg_match('/^\{/', $objStr)) {
            $objStr = '{' . $objStr;
        }
        if (!preg_match('/\}$/', $objStr)) {
            $objStr = $objStr . '}';
        }
        
        $product = ['categoria' => $defaultCategoria];
        
        // Extraer campos usando regex más robustos
        // image
        if (preg_match("/image:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['image'] = $m[1];
        }
        
        // hoverImage
        if (preg_match("/hoverImage:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['hoverImage'] = $m[1];
        }
        
        // name
        if (preg_match("/name:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['name'] = $m[1];
        }
        
        // price
        if (preg_match("/price:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['price'] = $m[1];
        }
        
        // slug
        if (preg_match("/slug:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['slug'] = $m[1];
        }
        
        // descripcion (puede tener saltos de línea, manejar con cuidado)
        if (preg_match("/descripcion:\s*['\"](.*?)['\"]/s", $objStr, $m)) {
            $product['descripcion'] = trim($m[1]);
        }
        
        // categoria (sobrescribe la default si existe)
        if (preg_match("/categoria:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
            $product['categoria'] = $m[1];
        }
        
        // stock (boolean)
        if (preg_match("/stock:\s*(true|false)/", $objStr, $m)) {
            $product['stock'] = $m[1] === 'true';
        } else {
            $product['stock'] = true; // Por defecto
        }
        
        // destacado (boolean)
        if (preg_match("/destacado:\s*(true|false)/", $objStr, $m)) {
            $product['destacado'] = $m[1] === 'true';
        } else {
            $product['destacado'] = false; // Por defecto
        }
        
        // Solo agregar si tiene nombre y slug
        if (!empty($product['name']) && !empty($product['slug'])) {
            $products[] = $product;
        }
    }
    
    return $products;
}

// Leer archivos JS - Buscar en múltiples ubicaciones posibles
echo "📖 Leyendo archivos JS...\n";

// Función para encontrar archivo en múltiples ubicaciones
function findJsFile($filename) {
    $possiblePaths = [
        __DIR__ . '/src/data/' . $filename,
        __DIR__ . '/../src/data/' . $filename,
        dirname(__DIR__) . '/src/data/' . $filename,
        BASE_PATH . '/src/data/' . $filename,
        $_SERVER['DOCUMENT_ROOT'] . '/lumetest/src/data/' . $filename,
        $_SERVER['DOCUMENT_ROOT'] . '/src/data/' . $filename,
        __DIR__ . '/' . $filename, // Si está en la raíz
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}

// Buscar archivos
$productosPath = findJsFile('productos.js');
$souvenirsPath = findJsFile('souvenirs.js');
$navidadPath = findJsFile('navidad.js');

if ($productosPath) {
    echo "   ✅ productos.js encontrado en: $productosPath\n";
} else {
    echo "   ❌ productos.js no encontrado\n";
}

if ($souvenirsPath) {
    echo "   ✅ souvenirs.js encontrado en: $souvenirsPath\n";
} else {
    echo "   ❌ souvenirs.js no encontrado\n";
}

if ($navidadPath) {
    echo "   ✅ navidad.js encontrado en: $navidadPath\n";
} else {
    echo "   ❌ navidad.js no encontrado\n";
}

echo "\n";

// Leer archivos (usar null si no se encontraron)
$productosData = $productosPath ? parseJsFile($productosPath, 'productos') : [];
$souvenirsData = $souvenirsPath ? parseJsFile($souvenirsPath, 'souvenirs') : [];
$navidadData = $navidadPath ? parseJsFile($navidadPath, 'navidad') : [];

// Debug: mostrar cuántos productos se encontraron
echo "📦 Productos encontrados:\n";
echo "   • Productos: " . count($productosData) . "\n";
echo "   • Souvenirs: " . count($souvenirsData) . "\n";
echo "   • Navidad: " . count($navidadData) . "\n";

// Combinar todos los productos
$allProducts = array_merge($productosData, $souvenirsData, $navidadData);
echo "   • Total: " . count($allProducts) . "\n\n";

if (empty($allProducts)) {
    echo "❌ No se encontraron productos para importar.\n\n";
    echo "📋 Ubicaciones donde se buscaron los archivos:\n";
    echo "   • " . __DIR__ . "/src/data/productos.js\n";
    echo "   • " . BASE_PATH . "/src/data/productos.js\n";
    echo "   • " . $_SERVER['DOCUMENT_ROOT'] . "/lumetest/src/data/productos.js\n";
    echo "   • " . $_SERVER['DOCUMENT_ROOT'] . "/src/data/productos.js\n\n";
    echo "💡 SOLUCIÓN:\n";
    echo "   1. Sube los archivos JS a Hostinger en una de estas ubicaciones:\n";
    echo "      - public_html/lumetest/src/data/productos.js\n";
    echo "      - public_html/lumetest/src/data/souvenirs.js\n";
    echo "      - public_html/lumetest/src/data/navidad.js\n\n";
    echo "   2. O copia el contenido de los archivos JS locales y créalos en Hostinger\n\n";
    echo "   3. Alternativamente, puedes migrar desde tu base de datos local:\n";
    echo "      - Exporta los productos desde local\n";
    echo "      - Impórtalos directamente en Hostinger usando phpMyAdmin\n\n";
    exit(1);
}

$imported = 0;
$skipped = 0;
$errors = 0;
$imagesUpdated = 0;

echo "🔄 Iniciando migración...\n\n";

foreach ($allProducts as $item) {
    // Validar datos requeridos
    if (empty($item['name']) || empty($item['slug'])) {
        echo "⚠️  Saltando producto sin nombre o slug válido\n";
        $skipped++;
        continue;
    }
    
    // Normalizar slug (asegurar que no tenga ñ)
    $slug = slugify($item['slug']);
    
    // Verificar si ya existe
    $existing = fetchOne("SELECT id, image, categoria FROM products WHERE slug = :slug", ['slug' => $slug]);
    
    if ($existing) {
        echo "⏭️  Saltando {$item['name']} (slug: $slug ya existe)\n";
        $skipped++;
        continue;
    }
    
    // Procesar imagen principal
    $imagePath = $item['image'] ?? '';
    
    // Convertir rutas antiguas a nuevas (si es necesario)
    // Estructura antigua: /images/slug/main.webp o /images/00_navidad/slug/main.webp
    // Estructura nueva: /images/categoria/slug/main.webp
    if (!empty($imagePath)) {
        // Si la ruta no sigue el patrón nuevo, convertirla
        if (preg_match('#^/images/(?:00_navidad/)?([^/]+)/([^/]+)$#', $imagePath, $matches)) {
            $oldSlug = $matches[1];
            $filename = $matches[2];
            
            // Determinar tipo de archivo (main, hover)
            $fileType = 'main';
            if (strpos($filename, 'hover') !== false) {
                $fileType = 'hover';
                $filename = 'hover.' . pathinfo($filename, PATHINFO_EXTENSION);
            } else {
                $filename = 'main.' . pathinfo($filename, PATHINFO_EXTENSION);
            }
            
            // Nueva ruta
            $imagePath = '/images/' . $item['categoria'] . '/' . $slug . '/' . $filename;
            $imagesUpdated++;
        }
    }
    
    // Procesar imagen hover
    $hoverImagePath = $item['hoverImage'] ?? '';
    if (!empty($hoverImagePath)) {
        if (preg_match('#^/images/(?:00_navidad/)?([^/]+)/([^/]+)$#', $hoverImagePath, $matches)) {
            $ext = pathinfo($matches[2], PATHINFO_EXTENSION);
            $hoverImagePath = '/images/' . $item['categoria'] . '/' . $slug . '/hover.' . $ext;
        }
    }
    
    // Generar ID único
    $productId = generateProductId();
    
    // Asegurar que tenga categoría válida
    $categoria = in_array($item['categoria'], ['productos', 'souvenirs', 'navidad']) 
        ? $item['categoria'] 
        : 'productos';
    
    // Insertar en BD
    $sql = "INSERT INTO products 
            (id, slug, name, descripcion, price, image, hoverImage, stock, destacado, categoria, visible) 
            VALUES 
            (:id, :slug, :name, :descripcion, :price, :image, :hoverImage, :stock, :destacado, :categoria, :visible)";
    
    $params = [
        'id' => $productId,
        'slug' => $slug,
        'name' => $item['name'],
        'descripcion' => $item['descripcion'] ?? '',
        'price' => $item['price'] ?? '',
        'image' => $imagePath,
        'hoverImage' => $hoverImagePath,
        'stock' => isset($item['stock']) && $item['stock'] ? 1 : 0,
        'destacado' => isset($item['destacado']) && $item['destacado'] ? 1 : 0,
        'categoria' => $categoria,
        'visible' => 1 // Por defecto visible
    ];
    
    if (executeQuery($sql, $params)) {
        echo "✅ Importado: {$item['name']} ({$categoria})\n";
        $imported++;
    } else {
        echo "❌ Error al importar: {$item['name']}\n";
        $errors++;
    }
}

echo "\n============================================\n";
echo "  RESUMEN\n";
echo "============================================\n";
echo "✅ Importados: $imported\n";
echo "⏭️  Saltados: $skipped\n";
echo "❌ Errores: $errors\n";
echo "🔄 Rutas de imágenes actualizadas: $imagesUpdated\n";
echo "============================================\n";
echo "\n💡 NOTA: Las imágenes no se mueven físicamente.\n";
echo "   Si las imágenes están en la estructura antigua,\n";
echo "   deberás moverlas manualmente a la nueva estructura:\n";
echo "   /images/categoria/slug/main.webp\n";
echo "   /images/categoria/slug/hover.webp\n";

