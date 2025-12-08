<?php
/**
 * Script de prueba de conexión a base de datos
 */
define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Conexión</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 Test de Conexión a Base de Datos</h1>
    
    <?php
    echo "<div class='info'>";
    echo "<strong>Configuración:</strong><br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Base de datos: " . DB_NAME . "<br>";
    echo "Usuario: " . DB_USER . "<br>";
    echo "Contraseña: " . (empty(DB_PASS) ? '(vacía)' : '***') . "<br>";
    echo "</div>";
    
    $pdo = getDB();
    
    if ($pdo) {
        echo "<div class='success'>✅ <strong>Conexión exitosa!</strong></div>";
        
        // Probar consulta
        $result = fetchOne("SELECT COUNT(*) as count FROM products");
        if ($result !== false) {
            echo "<div class='success'>✅ Productos en BD: " . $result['count'] . "</div>";
        }
        
        $admin = fetchOne("SELECT COUNT(*) as count FROM admin_users");
        if ($admin !== false) {
            echo "<div class='success'>✅ Usuarios admin: " . $admin['count'] . "</div>";
        }
        
        echo "<div class='info'>";
        echo "<strong>✅ Todo está funcionando correctamente!</strong><br>";
        echo "<a href='admin/login.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Ir al Panel Admin</a>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ <strong>Error de conexión</strong></div>";
        echo "<div class='info'>";
        echo "<strong>Verifica:</strong><br>";
        echo "1. Que MySQL esté corriendo en XAMPP<br>";
        echo "2. Que la base de datos 'lume_catalogo' exista<br>";
        echo "3. Que las credenciales en config.php sean correctas<br>";
        echo "</div>";
    }
    ?>
</body>
</html>

