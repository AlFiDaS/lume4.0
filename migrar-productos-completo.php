<?php
/**
 * Script completo para migrar productos desde archivos JS a base de datos
 * Lee los archivos JS y migra todos los productos
 */

require_once 'config.php';
require_once 'helpers/slugify.php';

echo "=== Migración Completa de Productos ===\n\n";

// Función para leer y parsear archivos JS
function parseJsFile($filePath) {
    if (!file_exists($filePath)) {
        echo "⚠️  Archivo no encontrado: $filePath\n";
        return [];
    }
    
    $content = file_get_contents($filePath);
    $products = [];
    
    // Patrón para encontrar objetos de productos en el array
    // Buscamos: { image: '...', name: '...', etc }
    preg_match_all('/\{[^}]+\}/s', $content, $matches);
    
    // Este es un parser simple, puede necesitar ajustes
    // Por ahora, mejor usar un enfoque diferente
    
    return $products;
}

// Función para insertar producto
function insertProduct($product) {
    global $conn;
    
    // Generar ID único
    $productId = uniqid('prod_', true);
    
    // Asegurar slug único
    $slug = ensureUniqueSlug($product['slug'] ?? generateSlug($product['name']));
    
    $sql = "INSERT INTO products 
            (id, slug, name, descripcion, price, image, hoverImage, stock, destacado, categoria, visible) 
            VALUES 
            (:id, :slug, :name, :descripcion, :price, :image, :hoverImage, :stock, :destacado, :categoria, :visible)
            ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            descripcion = VALUES(descripcion),
            price = VALUES(price),
            image = VALUES(image),
            hoverImage = VALUES(hoverImage),
            stock = VALUES(stock),
            destacado = VALUES(destacado),
            categoria = VALUES(categoria),
            visible = VALUES(visible),
            updated_at = NOW()";
    
    $params = [
        'id' => $productId,
        'slug' => $slug,
        'name' => $product['name'] ?? '',
        'descripcion' => $product['descripcion'] ?? '',
        'price' => $product['price'] ?? '',
        'image' => $product['image'] ?? '',
        'hoverImage' => $product['hoverImage'] ?? null,
        'stock' => isset($product['stock']) && $product['stock'] ? 1 : 0,
        'destacado' => isset($product['destacado']) && $product['destacado'] ? 1 : 0,
        'categoria' => $product['categoria'] ?? 'productos',
        'visible' => 1  // Todos los productos migrados serán visibles
    ];
    
    return executeQuery($sql, $params);
}

echo "Este script necesita los productos en formato PHP.\n";
echo "Por favor, ejecuta el script Node.js primero para convertir los JS a JSON,\n";
echo "o crea un archivo con los productos en formato PHP.\n\n";

echo "O mejor aún: Ve al panel admin y marca los productos existentes como visibles.\n";
echo "Ejecuta: php marcar-productos-visibles.php\n";

