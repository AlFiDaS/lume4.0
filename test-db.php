<?php
/**
 * Script de prueba de conexión a base de datos
 * Elimina este archivo después de probar
 */

// Permitir acceso directo para este script
define('LUME_ADMIN', true);

require_once 'config.php';
require_once 'helpers/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - LUME</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Test de Conexión a Base de Datos</h2>
        
        <?php
        echo "<h3>Configuración actual:</h3>";
        echo "<ul>";
        echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
        echo "<li><strong>Base de datos:</strong> " . DB_NAME . "</li>";
        echo "<li><strong>Usuario:</strong> " . DB_USER . "</li>";
        echo "<li><strong>Contraseña:</strong> " . (empty(DB_PASS) ? '(vacía)' : '***') . "</li>";
        echo "</ul>";
        
        echo "<hr>";
        
        // Probar conexión
        $pdo = getDB();
        
        if ($pdo) {
            echo "<div class='success'>✅ <strong>Conexión exitosa a la base de datos!</strong></div>";
            
            // Probar consulta de productos
            echo "<h3>Información de la base de datos:</h3>";
            
            $result = fetchOne("SELECT COUNT(*) as count FROM products");
            if ($result !== false) {
                echo "<p><strong>Productos en BD:</strong> " . $result['count'] . "</p>";
            } else {
                echo "<p class='error'>⚠️ No se pudo contar productos (la tabla puede no existir)</p>";
            }
            
            // Verificar tablas
            $tables = fetchAll("SHOW TABLES");
            if ($tables !== false && !empty($tables)) {
                echo "<p><strong>Tablas encontradas:</strong></p>";
                echo "<ul>";
                foreach ($tables as $table) {
                    $tableName = array_values($table)[0];
                    echo "<li><code>" . htmlspecialchars($tableName) . "</code></li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'>⚠️ No se encontraron tablas. Debes importar database.sql</p>";
            }
            
            // Verificar tabla admin_users
            $adminUsers = fetchOne("SELECT COUNT(*) as count FROM admin_users");
            if ($adminUsers !== false) {
                echo "<p><strong>Usuarios admin:</strong> " . $adminUsers['count'] . "</p>";
                if ($adminUsers['count'] == 0) {
                    echo "<p class='info'>💡 No hay usuarios admin. Ejecuta <code>crear-usuario-admin.php</code></p>";
                }
            }
            
            echo "<hr>";
            echo "<p><strong>✅ Todo parece estar bien configurado!</strong></p>";
            echo "<p><a href='admin/login.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #e0a4ce; color: white; text-decoration: none; border-radius: 4px;'>Ir al Panel Admin</a></p>";
            
        } else {
            echo "<div class='error'>❌ <strong>Error de conexión a la base de datos</strong></div>";
            
            echo "<h3>Verifica lo siguiente:</h3>";
            echo "<ul>";
            echo "<li>Que MySQL esté corriendo en XAMPP/MAMP</li>";
            echo "<li>Que las credenciales en <code>config.php</code> sean correctas</li>";
            echo "<li>Que la base de datos <code>" . DB_NAME . "</code> exista</li>";
            echo "<li>Que el usuario <code>" . DB_USER . "</code> tenga permisos</li>";
            echo "</ul>";
            
            echo "<h3>Pasos para solucionarlo:</h3>";
            echo "<ol>";
            echo "<li>Ve a phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
            echo "<li>Crea la base de datos: <code>" . DB_NAME . "</code></li>";
            echo "<li>Importa el archivo <code>database.sql</code></li>";
            echo "<li>Recarga esta página</li>";
            echo "</ol>";
        }
        ?>
        
        <hr>
        <p style="color: #666; font-size: 0.9em;">
            <strong>⚠️ Importante:</strong> Elimina este archivo (<code>test-db.php</code>) después de verificar que todo funciona, por seguridad.
        </p>
    </div>
</body>
</html>

