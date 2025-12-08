<?php
/**
 * ============================================
 * MIGRACIÓN: Importar productos desde archivos JS
 * ============================================
 * Importa productos desde productos.js, souvenirs.js y navidad.js a la base de datos
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

// Función para extraer datos de archivos JS
function extractProductsFromJS($filePath, $defaultCategoria) {
    if (!file_exists($filePath)) {
        echo "⚠️  Advertencia: No se encontró el archivo $filePath\n";
        return [];
    }
    
    $content = file_get_contents($filePath);
    $products = [];
    
    // Remover comentarios de línea
    $content = preg_replace('/\/\/.*$/m', '', $content);
    
    // Buscar el array completo entre corchetes (después de export const)
    if (preg_match('/\[(.*)\];?\s*$/s', $content, $arrayMatch)) {
        $arrayContent = $arrayMatch[1];
        
        // Extraer objetos usando balanceo de llaves
        $objects = [];
        $depth = 0;
        $currentObj = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($arrayContent); $i++) {
            $char = $arrayContent[$i];
            $prevChar = $i > 0 ? $arrayContent[$i-1] : '';
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar && $prevChar !== '\\') {
                $inString = false;
            }
            
            if (!$inString) {
                if ($char === '{') {
                    if ($depth === 0) {
                        $currentObj = '';
                    }
                    $depth++;
                    $currentObj .= $char;
                } elseif ($char === '}') {
                    $currentObj .= $char;
                    $depth--;
                    if ($depth === 0) {
                        $objects[] = $currentObj;
                        $currentObj = '';
                    }
                } elseif ($depth > 0) {
                    $currentObj .= $char;
                }
            } else {
                if ($depth > 0) {
                    $currentObj .= $char;
                }
            }
        }
        
        // Si no se encontraron objetos con balanceo, usar método simple
        if (empty($objects)) {
            $objects = preg_split('/\},\s*\{/', $arrayContent);
        }
        
        // Procesar todos los objetos encontrados
        foreach ($objects as $objStr) {
            // Limpiar el objeto
            $objStr = trim($objStr, '{}');
            
            // Extraer campos individuales
            $product = ['categoria' => $defaultCategoria];
            
            // Extraer image (puede tener comillas simples o dobles)
            if (preg_match("/image:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['image'] = $m[1];
            }
            
            // Extraer hoverImage
            if (preg_match("/hoverImage:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['hoverImage'] = $m[1];
            }
            
            // Extraer name
            if (preg_match("/name:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['name'] = $m[1];
            }
            
            // Extraer price
            if (preg_match("/price:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['price'] = $m[1];
            }
            
            // Extraer slug
            if (preg_match("/slug:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['slug'] = $m[1];
            }
            
            // Extraer descripcion (puede tener múltiples líneas)
            if (preg_match("/descripcion:\s*['\"]([^'\"]+)['\"]/s", $objStr, $m)) {
                $product['descripcion'] = $m[1];
            }
            
            // Extraer categoria (si está definida)
            if (preg_match("/categoria:\s*['\"]([^'\"]+)['\"]/", $objStr, $m)) {
                $product['categoria'] = $m[1];
            }
            
            // Extraer stock (puede ser true/false)
            if (preg_match("/stock:\s*(true|false|1|0)/", $objStr, $m)) {
                $product['stock'] = in_array($m[1], ['true', '1']);
            }
            
            // Extraer destacado
            if (preg_match("/destacado:\s*(true|false|1|0)/", $objStr, $m)) {
                $product['destacado'] = in_array($m[1], ['true', '1']);
            }
            
            if (!empty($product['name']) && !empty($product['slug'])) {
                $products[] = $product;
            }
        }
    }
    
    return $products;
}

// Leer archivos JS
echo "📖 Leyendo archivos JS...\n";
$productosData = extractProductsFromJS(__DIR__ . '/src/data/productos.js', 'productos');
$souvenirsData = extractProductsFromJS(__DIR__ . '/src/data/souvenirs.js', 'souvenirs');
$navidadData = extractProductsFromJS(__DIR__ . '/src/data/navidad.js', 'navidad');

// Si no se pudieron leer, mostrar mensaje
if (empty($productosData) && empty($souvenirsData) && empty($navidadData)) {
    echo "\n❌ ERROR: No se pudieron extraer productos de los archivos JS.\n";
    echo "Por favor, verifica que los archivos existan en:\n";
    echo "  - src/data/productos.js\n";
    echo "  - src/data/souvenirs.js\n";
    echo "  - src/data/navidad.js\n";
    exit(1);
}

// Combinar todos los productos
$allProducts = array_merge($productosData, $souvenirsData, $navidadData);

echo "📦 Productos encontrados:\n";
echo "   • Productos: " . count($productosData) . "\n";
echo "   • Souvenirs: " . count($souvenirsData) . "\n";
echo "   • Navidad: " . count($navidadData) . "\n";
echo "   • Total: " . count($allProducts) . "\n\n";

if (empty($allProducts)) {
    echo "❌ No se encontraron productos para importar.\n";
    exit(1);
}

$imported = 0;
$skipped = 0;
$errors = 0;
$movedImages = 0;

foreach ($allProducts as $item) {
    // Validar datos requeridos
    if (empty($item['name']) || empty($item['slug'])) {
        echo "⚠️  Saltando producto sin nombre o slug: " . json_encode($item) . "\n";
        $skipped++;
        continue;
    }
    
    // Asegurar que tenga categoría
    if (empty($item['categoria'])) {
        $item['categoria'] = 'productos'; // Por defecto
    }
    
    // Normalizar slug (asegurar que no tenga ñ)
    $slug = slugify($item['slug']);
    
    // Verificar si ya existe
    $existing = fetchOne("SELECT id FROM products WHERE slug = :slug", ['slug' => $slug]);
    
    if ($existing) {
        echo "⏭️  Saltando {$item['name']} (slug: $slug ya existe)\n";
        $skipped++;
        continue;
    }
    
    // Procesar imagen principal
    $imagePath = '';
    if (!empty($item['image'])) {
        $imagePath = $item['image'];
        
        // Si la imagen está en la estructura antigua, intentar moverla
        // Estructura antigua: /images/slug/main.webp o /images/00_navidad/slug/main.webp
        // Estructura nueva: /images/categoria/slug/main.webp
        
        // Extraer nombre del archivo y slug de la ruta antigua
        if (preg_match('#^/images/(?:00_navidad/)?([^/]+)/([^/]+\.(webp|jpg|png))$#', $imagePath, $matches)) {
            $oldSlug = $matches[1];
            $filename = $matches[2];
            
            // Determinar si es main o hover
            $fileType = 'main';
            if (strpos($filename, 'hover') !== false) {
                $fileType = 'hover';
            } elseif (strpos($filename, 'main') === false) {
                // Si no tiene main ni hover, asumir que es main
                $fileType = 'main';
                $filename = 'main.' . pathinfo($filename, PATHINFO_EXTENSION);
            }
            
            $oldPath = BASE_PATH . '/public/images/' . (strpos($imagePath, '00_navidad') !== false ? '00_navidad/' : '') . $oldSlug . '/' . basename($item['image']);
            $newPath = BASE_PATH . '/public/images/' . $item['categoria'] . '/' . $slug . '/' . $fileType . '.' . pathinfo($filename, PATHINFO_EXTENSION);
            
            // Crear directorio nuevo si no existe
            $newDir = dirname($newPath);
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }
            
            // Mover archivo si existe en la ubicación antigua y no existe en la nueva
            if (file_exists($oldPath) && !file_exists($newPath)) {
                if (rename($oldPath, $newPath)) {
                    $imagePath = '/images/' . $item['categoria'] . '/' . $slug . '/' . $fileType . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                    $movedImages++;
                    echo "   📁 Imagen movida: $oldSlug -> {$item['categoria']}/$slug\n";
                }
            } elseif (file_exists($oldPath)) {
                // Si ya existe en la nueva ubicación, solo actualizar la ruta
                $imagePath = '/images/' . $item['categoria'] . '/' . $slug . '/' . $fileType . '.' . pathinfo($filename, PATHINFO_EXTENSION);
            }
        }
    }
    
    // Procesar imagen hover
    $hoverImagePath = '';
    if (!empty($item['hoverImage'])) {
        $hoverImagePath = $item['hoverImage'];
        
        // Mover hover image si está en estructura antigua
        if (preg_match('#^/images/(?:00_navidad/)?([^/]+)/([^/]+\.(webp|jpg|png))$#', $hoverImagePath, $matches)) {
            $oldSlug = $matches[1];
            $filename = basename($hoverImagePath);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            $oldPath = BASE_PATH . '/public/images/' . (strpos($hoverImagePath, '00_navidad') !== false ? '00_navidad/' : '') . $oldSlug . '/' . $filename;
            $newPath = BASE_PATH . '/public/images/' . $item['categoria'] . '/' . $slug . '/hover.' . $ext;
            
            $newDir = dirname($newPath);
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }
            
            if (file_exists($oldPath) && !file_exists($newPath)) {
                if (rename($oldPath, $newPath)) {
                    $hoverImagePath = '/images/' . $item['categoria'] . '/' . $slug . '/hover.' . $ext;
                    $movedImages++;
                }
            } elseif (file_exists($oldPath)) {
                $hoverImagePath = '/images/' . $item['categoria'] . '/' . $slug . '/hover.' . $ext;
            }
        }
    }
    
    // Generar ID único
    $productId = generateProductId();
    
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
        'categoria' => $item['categoria'],
        'visible' => 1 // Por defecto visible
    ];
    
    if (executeQuery($sql, $params)) {
        echo "✅ Importado: {$item['name']} ({$item['categoria']})\n";
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
echo "📁 Imágenes movidas: $movedImages\n";
echo "============================================\n";
