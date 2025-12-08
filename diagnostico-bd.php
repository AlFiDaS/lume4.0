<?php
/**
 * Diagnóstico completo de la base de datos
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LUME_ADMIN', true);

echo "<h1>🔍 Diagnóstico de Base de Datos</h1>";
echo "<pre>";

// 1. Verificar configuración
echo "1. Verificando configuración...\n";
require_once 'config.php';
echo "   DB_HOST: " . DB_HOST . "\n";
echo "   DB_NAME: " . DB_NAME . "\n";
echo "   DB_USER: " . DB_USER . "\n";
echo "   DB_PASS: " . (empty(DB_PASS) ? '(vacía)' : '***') . "\n\n";

// 2. Probar conexión directa con PDO
echo "2. Probando conexión directa con PDO...\n";
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "   ✅ Conexión exitosa!\n\n";
    
    // 3. Verificar tablas
    echo "3. Verificando tablas...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "   ✅ Tabla: $table\n";
    }
    echo "\n";
    
    // 4. Verificar datos
    echo "4. Verificando datos...\n";
    $products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch();
    echo "   Productos: " . $products['count'] . "\n";
    
    $admin = $pdo->query("SELECT COUNT(*) as count FROM admin_users")->fetch();
    echo "   Usuarios admin: " . $admin['count'] . "\n";
    
    if ($admin['count'] > 0) {
        $users = $pdo->query("SELECT username FROM admin_users")->fetchAll(PDO::FETCH_COLUMN);
        echo "   Usuarios: " . implode(', ', $users) . "\n";
    }
    echo "\n";
    
    // 5. Probar helper getDB()
    echo "5. Probando helper getDB()...\n";
    require_once 'helpers/db.php';
    $db = getDB();
    if ($db) {
        echo "   ✅ Helper getDB() funciona correctamente!\n";
    } else {
        echo "   ❌ Helper getDB() falló!\n";
    }
    
    echo "\n✅ TODO ESTÁ FUNCIONANDO CORRECTAMENTE!\n";
    echo "\nPuedes acceder al login en: <a href='admin/login.php'>admin/login.php</a>\n";
    
} catch (PDOException $e) {
    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\n   Verifica:\n";
    echo "   - Que MySQL esté corriendo\n";
    echo "   - Que la base de datos 'lume_catalogo' exista\n";
    echo "   - Que las credenciales sean correctas\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

