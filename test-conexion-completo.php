<?php
/**
 * Test completo de conexión y datos
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Completo de Conexión</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #e0a4ce; color: white; }
    </style>
</head>
<body>
    <h1>🔍 Test Completo de Conexión y Datos</h1>
    
    <?php
    echo "<div class='info'>";
    echo "<strong>Configuración:</strong><br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Base de datos: " . DB_NAME . "<br>";
    echo "Usuario: " . DB_USER . "<br>";
    echo "BASE_URL: " . BASE_URL . "<br>";
    echo "ADMIN_URL: " . ADMIN_URL . "<br>";
    echo "</div>";
    
    // Test 1: Conexión
    echo "<h2>1. Test de Conexión</h2>";
    $pdo = getDB();
    
    if ($pdo) {
        echo "<div class='success'>✅ Conexión exitosa a la base de datos!</div>";
        
        // Test 2: Contar productos
        echo "<h2>2. Datos en la Base de Datos</h2>";
        $products = fetchAll("SELECT id, name, categoria, visible, stock FROM products LIMIT 10");
        if ($products !== false) {
            echo "<div class='success'>✅ Productos encontrados: " . count($products) . "</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Visible</th><th>Stock</th></tr>";
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($product['id']) . "</td>";
                echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                echo "<td>" . htmlspecialchars($product['categoria']) . "</td>";
                echo "<td>" . ($product['visible'] ? 'Sí' : 'No') . "</td>";
                echo "<td>" . ($product['stock'] ? 'Sí' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ No se pudieron obtener productos</div>";
        }
        
        // Test 3: Usuarios admin
        echo "<h2>3. Usuarios Admin</h2>";
        $admins = fetchAll("SELECT id, username, email FROM admin_users");
        if ($admins !== false) {
            echo "<div class='success'>✅ Usuarios admin: " . count($admins) . "</div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Email</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['email'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test 4: Hacer un cambio de prueba
        echo "<h2>4. Test de Escritura</h2>";
        try {
            $testResult = executeQuery("SELECT COUNT(*) as count FROM products");
            if ($testResult) {
                $count = $testResult->fetch()['count'];
                echo "<div class='success'>✅ Lectura OK - Total productos: " . $count . "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error en lectura: " . $e->getMessage() . "</div>";
        }
        
        echo "<hr>";
        echo "<div class='info'>";
        echo "<strong>✅ Todo está funcionando correctamente!</strong><br>";
        echo "<a href='admin/login.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Ir al Panel Admin</a>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ Error de conexión a la base de datos</div>";
        echo "<div class='info'>";
        echo "<strong>Verifica:</strong><br>";
        echo "1. Que MySQL esté corriendo en XAMPP<br>";
        echo "2. Que la base de datos 'lume_catalogo' exista<br>";
        echo "3. Que las credenciales en config.php sean correctas<br>";
        echo "</div>";
    }
    ?>
    
    <hr>
    <h2>5. Información del Servidor</h2>
    <div class="info">
        <strong>PHP Version:</strong> <?= phpversion() ?><br>
        <strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?><br>
        <strong>Host:</strong> <?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?><br>
        <strong>Request URI:</strong> <?= $_SERVER['REQUEST_URI'] ?? 'N/A' ?><br>
    </div>
</body>
</html>

