<?php
/**
 * ============================================
 * MIGRACIÓN: Importar imágenes de galería
 * ============================================
 * Importa las imágenes existentes de galeria.js a la base de datos
 * ============================================
 */

require_once 'config.php';

// Datos de galería desde galeria.js
$galeriaData = [
    ['nombre' => 'idea1', 'imagen' => '/images/0_galeria/idea1.webp', 'alt' => 'Idea 1', 'orden' => 1],
    ['nombre' => 'idea2', 'imagen' => '/images/0_galeria/idea2.webp', 'alt' => 'Idea 2', 'orden' => 2],
    ['nombre' => 'idea3', 'imagen' => '/images/0_galeria/idea3.webp', 'alt' => 'Idea 3', 'orden' => 3],
    ['nombre' => 'idea4', 'imagen' => '/images/0_galeria/idea4.webp', 'alt' => 'Idea 4', 'orden' => 4],
    ['nombre' => 'idea5', 'imagen' => '/images/0_galeria/idea5.webp', 'alt' => 'Idea 5', 'orden' => 5],
    ['nombre' => 'idea6', 'imagen' => '/images/0_galeria/idea6.webp', 'alt' => 'Idea 6', 'orden' => 6],
    ['nombre' => 'idea7', 'imagen' => '/images/0_galeria/idea7.webp', 'alt' => 'Idea 7', 'orden' => 7],
    ['nombre' => 'idea8', 'imagen' => '/images/0_galeria/idea8.webp', 'alt' => 'Idea 8', 'orden' => 8],
    ['nombre' => 'idea9', 'imagen' => '/images/0_galeria/idea9.webp', 'alt' => 'Idea 9', 'orden' => 9],
    ['nombre' => 'idea10', 'imagen' => '/images/0_galeria/idea10.webp', 'alt' => 'Idea 10', 'orden' => 10],
    ['nombre' => 'idea11', 'imagen' => '/images/0_galeria/idea11.webp', 'alt' => 'Idea 11', 'orden' => 11],
    ['nombre' => 'idea12', 'imagen' => '/images/0_galeria/idea12.webp', 'alt' => 'Idea 12', 'orden' => 12],
    ['nombre' => 'idea14', 'imagen' => '/images/0_galeria/idea14.webp', 'alt' => 'Idea 14', 'orden' => 14],
    ['nombre' => 'idea15', 'imagen' => '/images/0_galeria/idea15.webp', 'alt' => 'Idea 15', 'orden' => 15],
    ['nombre' => 'idea16', 'imagen' => '/images/0_galeria/idea16.webp', 'alt' => 'Idea 16', 'orden' => 16],
    ['nombre' => 'idea17', 'imagen' => '/images/0_galeria/idea17.webp', 'alt' => 'Idea 17', 'orden' => 17],
    ['nombre' => 'idea18', 'imagen' => '/images/0_galeria/idea18.webp', 'alt' => 'Idea 18', 'orden' => 18],
    ['nombre' => 'idea20', 'imagen' => '/images/0_galeria/idea20.webp', 'alt' => 'Idea 20', 'orden' => 20],
    ['nombre' => 'idea19', 'imagen' => '/images/0_galeria/idea19.webp', 'alt' => 'Idea 19', 'orden' => 19],
    ['nombre' => 'idea21', 'imagen' => '/images/0_galeria/idea21.webp', 'alt' => 'Idea 21', 'orden' => 21],
    ['nombre' => 'idea22', 'imagen' => '/images/0_galeria/idea22.webp', 'alt' => 'Idea 22', 'orden' => 22],
    ['nombre' => 'idea23', 'imagen' => '/images/0_galeria/idea23.webp', 'alt' => 'Idea 23', 'orden' => 23],
    ['nombre' => 'idea24', 'imagen' => '/images/0_galeria/idea24.webp', 'alt' => 'Idea 24', 'orden' => 24],
    ['nombre' => 'idea25', 'imagen' => '/images/0_galeria/idea25.webp', 'alt' => 'Idea 25', 'orden' => 25],
    ['nombre' => 'idea26', 'imagen' => '/images/0_galeria/idea26.webp', 'alt' => 'Idea 26', 'orden' => 26],
    ['nombre' => 'idea27', 'imagen' => '/images/0_galeria/idea27.webp', 'alt' => 'Idea 27', 'orden' => 27],
    ['nombre' => 'idea28', 'imagen' => '/images/0_galeria/idea28.webp', 'alt' => 'Idea 28', 'orden' => 28],
    ['nombre' => 'idea29', 'imagen' => '/images/0_galeria/idea29.webp', 'alt' => 'Idea 29', 'orden' => 29],
    ['nombre' => 'idea30', 'imagen' => '/images/0_galeria/idea30.webp', 'alt' => 'Idea 30', 'orden' => 30],
    ['nombre' => 'idea31', 'imagen' => '/images/0_galeria/idea31.webp', 'alt' => 'Idea 31', 'orden' => 31],
    ['nombre' => 'idea32', 'imagen' => '/images/0_galeria/idea32.webp', 'alt' => 'Idea 32', 'orden' => 32],
    ['nombre' => 'idea33', 'imagen' => '/images/0_galeria/idea33.webp', 'alt' => 'Idea 33', 'orden' => 33],
    ['nombre' => 'idea34', 'imagen' => '/images/0_galeria/idea34.webp', 'alt' => 'Idea 34', 'orden' => 34],
    ['nombre' => 'idea35', 'imagen' => '/images/0_galeria/idea35.webp', 'alt' => 'Idea 35', 'orden' => 35],
    ['nombre' => 'idea36', 'imagen' => '/images/0_galeria/idea36.webp', 'alt' => 'Idea 36', 'orden' => 36],
    ['nombre' => 'idea37', 'imagen' => '/images/0_galeria/idea37.webp', 'alt' => 'Idea 37', 'orden' => 37],
    ['nombre' => 'idea38', 'imagen' => '/images/0_galeria/idea38.webp', 'alt' => 'Idea 38', 'orden' => 38],
    ['nombre' => 'idea39', 'imagen' => '/images/0_galeria/idea39.webp', 'alt' => 'Idea 39', 'orden' => 39],
    ['nombre' => 'idea40', 'imagen' => '/images/0_galeria/idea40.webp', 'alt' => 'Idea 40', 'orden' => 40],
    ['nombre' => 'idea41', 'imagen' => '/images/0_galeria/idea41.webp', 'alt' => 'Idea 41', 'orden' => 41],
];

echo "============================================\n";
echo "  MIGRACIÓN DE GALERÍA\n";
echo "============================================\n\n";

// Verificar si la tabla existe
$tableExists = fetchOne("SHOW TABLES LIKE 'galeria'");
if (!$tableExists) {
    echo "❌ ERROR: La tabla 'galeria' no existe.\n";
    echo "Por favor, ejecuta primero el script SQL: database-galeria.sql\n";
    exit(1);
}

echo "✅ Tabla 'galeria' encontrada.\n\n";

$imported = 0;
$skipped = 0;
$errors = 0;

foreach ($galeriaData as $item) {
    // Verificar si ya existe
    $existing = fetchOne("SELECT id FROM galeria WHERE nombre = :nombre", ['nombre' => $item['nombre']]);
    
    if ($existing) {
        echo "⏭️  Saltando {$item['nombre']} (ya existe)\n";
        $skipped++;
        continue;
    }
    
    // Verificar si el archivo existe
    $fullPath = BASE_PATH . '/public' . $item['imagen'];
    if (!file_exists($fullPath)) {
        echo "⚠️  Advertencia: {$item['imagen']} no existe en el servidor\n";
    }
    
    // Insertar
    $sql = "INSERT INTO galeria (nombre, imagen, alt, orden, visible) 
            VALUES (:nombre, :imagen, :alt, :orden, 1)";
    
    $params = [
        'nombre' => $item['nombre'],
        'imagen' => $item['imagen'],
        'alt' => $item['alt'],
        'orden' => $item['orden']
    ];
    
    if (executeQuery($sql, $params)) {
        echo "✅ Importado: {$item['nombre']}\n";
        $imported++;
    } else {
        echo "❌ Error al importar: {$item['nombre']}\n";
        $errors++;
    }
}

echo "\n============================================\n";
echo "  RESUMEN\n";
echo "============================================\n";
echo "✅ Importados: $imported\n";
echo "⏭️  Saltados: $skipped\n";
echo "❌ Errores: $errors\n";
echo "============================================\n";

