<?php
/**
 * Script de diagnóstico para verificar productos de navidad
 */
define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

echo "============================================\n";
echo "  DIAGNÓSTICO: Productos Navidad\n";
echo "============================================\n\n";

// Verificar si la columna orden existe
echo "1. Verificando columna 'orden':\n";
try {
    $checkOrden = fetchOne("SHOW COLUMNS FROM products LIKE 'orden'");
    if ($checkOrden) {
        echo "   ✅ Columna 'orden' existe\n";
    } else {
        echo "   ⚠️  Columna 'orden' NO existe\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Contar productos de navidad
echo "2. Contando productos de navidad:\n";
$countAll = fetchOne("SELECT COUNT(*) as count FROM products WHERE categoria = 'navidad'");
$countVisible = fetchOne("SELECT COUNT(*) as count FROM products WHERE categoria = 'navidad' AND visible = 1");
echo "   • Total navidad: " . ($countAll['count'] ?? 0) . "\n";
echo "   • Navidad visibles: " . ($countVisible['count'] ?? 0) . "\n";
echo "\n";

// Listar productos de navidad
echo "3. Listando productos de navidad visibles:\n";
$sql = "SELECT id, name, categoria, visible, orden FROM products WHERE categoria = 'navidad' AND visible = 1 LIMIT 10";
$products = fetchAll($sql, []);
if (is_array($products) && !empty($products)) {
    echo "   Encontrados: " . count($products) . "\n";
    foreach ($products as $p) {
        echo "   - {$p['name']} (visible: {$p['visible']}, orden: " . ($p['orden'] ?? 'NULL') . ")\n";
    }
} else {
    echo "   ⚠️  No se encontraron productos\n";
}
echo "\n";

// Probar la consulta exacta de ordenar.php
echo "4. Probando consulta de ordenar.php:\n";
$hasOrdenColumn = false;
try {
    $checkOrden = fetchOne("SHOW COLUMNS FROM products LIKE 'orden'");
    $hasOrdenColumn = !empty($checkOrden);
} catch (Exception $e) {
    $hasOrdenColumn = false;
}

$sql = "SELECT id, name, image, slug, categoria";
if ($hasOrdenColumn) {
    $sql .= ", orden";
}
$sql .= " FROM products 
        WHERE visible = 1 AND categoria = :categoria";

if ($hasOrdenColumn) {
    $sql .= " ORDER BY 
            CASE WHEN orden IS NULL THEN 1 ELSE 0 END,
            orden ASC,
            destacado DESC,
            name ASC";
} else {
    $sql .= " ORDER BY destacado DESC, name ASC";
}

$testProducts = fetchAll($sql, ['categoria' => 'navidad']);
if (is_array($testProducts)) {
    echo "   ✅ Consulta exitosa: " . count($testProducts) . " productos\n";
    if (!empty($testProducts)) {
        echo "   Primeros 3 productos:\n";
        foreach (array_slice($testProducts, 0, 3) as $p) {
            echo "   - {$p['name']}\n";
        }
    }
} else {
    echo "   ❌ Error en la consulta\n";
}
echo "\n";

echo "============================================\n";

