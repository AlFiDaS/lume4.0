<?php
/**
 * Script de prueba para diagnosticar la búsqueda
 */
define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

$buscar = 'angel';

echo "============================================\n";
echo "  PRUEBA DE BÚSQUEDA\n";
echo "============================================\n\n";
echo "Buscando: '$buscar'\n\n";

// Prueba 1: Búsqueda simple sin LIKE
echo "1. Productos que contienen 'angel' (sin LIKE):\n";
$sql1 = "SELECT id, name, slug FROM products WHERE name LIKE '%$buscar%' OR slug LIKE '%$buscar%' LIMIT 10";
$result1 = fetchAll($sql1, []);
if (is_array($result1)) {
    echo "   Encontrados: " . count($result1) . "\n";
    foreach ($result1 as $p) {
        echo "   - {$p['name']} (slug: {$p['slug']})\n";
    }
} else {
    echo "   Error o sin resultados\n";
}
echo "\n";

// Prueba 2: Búsqueda con parámetros preparados
echo "2. Búsqueda con parámetros preparados:\n";
$sql2 = "SELECT id, name, slug FROM products WHERE name LIKE :buscar OR slug LIKE :buscar LIMIT 10";
$params2 = ['buscar' => '%' . $buscar . '%'];
$result2 = fetchAll($sql2, $params2);
if (is_array($result2)) {
    echo "   Encontrados: " . count($result2) . "\n";
    foreach ($result2 as $p) {
        echo "   - {$p['name']} (slug: {$p['slug']})\n";
    }
} else {
    echo "   Error o sin resultados\n";
}
echo "\n";

// Prueba 3: Ver todos los productos con 'angel' en cualquier parte
echo "3. Todos los productos (primeros 20):\n";
$sql3 = "SELECT id, name, slug FROM products LIMIT 20";
$result3 = fetchAll($sql3, []);
if (is_array($result3)) {
    echo "   Total en BD: " . count($result3) . "\n";
    $found = 0;
    foreach ($result3 as $p) {
        if (stripos($p['name'], $buscar) !== false || stripos($p['slug'], $buscar) !== false) {
            echo "   ✓ {$p['name']} (slug: {$p['slug']})\n";
            $found++;
        }
    }
    echo "   Encontrados con stripos: $found\n";
}
echo "\n";

// Prueba 4: Búsqueda exacta con COLLATE
echo "4. Búsqueda con COLLATE utf8mb4_unicode_ci:\n";
$sql4 = "SELECT id, name, slug FROM products WHERE name COLLATE utf8mb4_unicode_ci LIKE :buscar OR slug COLLATE utf8mb4_unicode_ci LIKE :buscar LIMIT 10";
$params4 = ['buscar' => '%' . $buscar . '%'];
$result4 = fetchAll($sql4, $params4);
if (is_array($result4)) {
    echo "   Encontrados: " . count($result4) . "\n";
    foreach ($result4 as $p) {
        echo "   - {$p['name']} (slug: {$p['slug']})\n";
    }
} else {
    echo "   Error o sin resultados\n";
}
echo "\n";

// Prueba 5: Verificar collation de la tabla
echo "5. Información de la tabla:\n";
$sql5 = "SHOW FULL COLUMNS FROM products WHERE Field IN ('name', 'slug')";
$result5 = fetchAll($sql5, []);
if (is_array($result5)) {
    foreach ($result5 as $col) {
        echo "   {$col['Field']}: Collation = {$col['Collation']}\n";
    }
}
echo "\n";

echo "============================================\n";

