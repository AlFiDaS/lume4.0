<?php
/**
 * Script para marcar todos los productos existentes como visibles
 */
require_once 'config.php';

echo "=== Marcando productos como visibles ===\n\n";

try {
    // Actualizar todos los productos para que sean visibles
    $sql = "UPDATE products SET visible = 1";
    $result = executeQuery($sql, []);
    
    if ($result !== false) {
        $count = fetchOne("SELECT COUNT(*) as count FROM products WHERE visible = 1");
        echo "✅ Todos los productos han sido marcados como visibles.\n";
        echo "Total de productos visibles: " . ($count['count'] ?? 0) . "\n";
    } else {
        echo "❌ Error al actualizar productos.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

