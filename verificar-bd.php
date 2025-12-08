<?php
/**
 * Script para verificar que PHP está usando la misma BD que VS Code
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
    <title>Verificación de Base de Datos</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { background: #d4edda; padding: 10px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 10px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #e0a4ce; color: white; }
    </style>
</head>
<body>
    <h1>Verificación de Base de Datos</h1>
    
    <?php
    echo "<h2>Configuración PHP:</h2>";
    echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>Base de datos:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Usuario:</strong> " . DB_USER . "</p>";
    
    $pdo = getDB();
    if ($pdo) {
        echo "<div class='success'>✅ Conexión exitosa</div>";
        
        // Obtener información de la conexión
        $info = $pdo->query("SELECT DATABASE() as db, USER() as user, CONNECTION_ID() as conn_id")->fetch();
        echo "<h2>Información de la Conexión:</h2>";
        echo "<p><strong>Base de datos conectada:</strong> " . $info['db'] . "</p>";
        echo "<p><strong>Usuario:</strong> " . $info['user'] . "</p>";
        echo "<p><strong>ID de conexión:</strong> " . $info['conn_id'] . "</p>";
        
        // Listar productos
        echo "<h2>Productos en la Base de Datos:</h2>";
        $products = fetchAll("SELECT id, name, categoria, visible, stock, updated_at FROM products ORDER BY updated_at DESC LIMIT 20");
        if ($products) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Visible</th><th>Stock</th><th>Última actualización</th></tr>";
            foreach ($products as $p) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($p['id']) . "</td>";
                echo "<td>" . htmlspecialchars($p['name']) . "</td>";
                echo "<td>" . htmlspecialchars($p['categoria']) . "</td>";
                echo "<td>" . ($p['visible'] ? 'Sí' : 'No') . "</td>";
                echo "<td>" . ($p['stock'] ? 'Sí' : 'No') . "</td>";
                echo "<td>" . ($p['updated_at'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<hr>";
        echo "<p><strong>Si ves los mismos datos aquí que en VS Code, entonces PHP está conectado correctamente.</strong></p>";
        echo "<p><strong>Si los datos son diferentes, hay un problema de conexión.</strong></p>";
        
    } else {
        echo "<div class='error'>❌ Error de conexión</div>";
    }
    ?>
    
    <hr>
    <h2>Pruebas de Acceso:</h2>
    <ul>
        <li><a href="admin/login.php">Admin Login</a></li>
        <li><a href="api/products.php">API Products</a></li>
        <li><a href="test-conexion-completo.php">Test Completo</a></li>
    </ul>
</body>
</html>

