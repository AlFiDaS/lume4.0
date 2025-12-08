<?php
/**
 * Script para verificar y crear/actualizar usuario admin
 * Ejecuta este archivo para solucionar problemas de login
 */

// Permitir acceso directo
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
    <title>Verificar Usuario Admin - LUME</title>
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
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .btn { display: inline-block; padding: 10px 20px; background: #e0a4ce; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Verificar y Crear Usuario Admin</h2>
        
        <?php
        $username = 'Gisela';
        $password = 'Luky123!';
        $email = 'gisela@lume.com';
        
        echo "<h3>1. Verificando conexión a base de datos...</h3>";
        
        $pdo = getDB();
        if (!$pdo) {
            echo "<div class='error'>❌ <strong>Error de conexión a la base de datos</strong></div>";
            echo "<p>Verifica:</p>";
            echo "<ul>";
            echo "<li>Host: " . DB_HOST . "</li>";
            echo "<li>Base de datos: " . DB_NAME . "</li>";
            echo "<li>Usuario: " . DB_USER . "</li>";
            echo "<li>MySQL debe estar corriendo</li>";
            echo "</ul>";
            echo "<p><strong>Solución:</strong> Ejecuta <code>setup-database.bat</code> o crea la base de datos manualmente.</p>";
            exit;
        }
        
        echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
        
        echo "<h3>2. Verificando tabla admin_users...</h3>";
        
        $tableExists = fetchOne("SHOW TABLES LIKE 'admin_users'");
        if (!$tableExists) {
            echo "<div class='error'>❌ La tabla admin_users no existe</div>";
            echo "<p><strong>Solución:</strong> Importa database.sql en phpMyAdmin</p>";
            exit;
        }
        
        echo "<div class='success'>✅ La tabla admin_users existe</div>";
        
        echo "<h3>3. Verificando usuario 'Gisela'...</h3>";
        
        $existing = fetchOne("SELECT * FROM admin_users WHERE username = :username", ['username' => $username]);
        
        if ($existing) {
            echo "<div class='info'>ℹ️ Usuario 'Gisela' ya existe</div>";
            echo "<pre>";
            echo "ID: " . $existing['id'] . "\n";
            echo "Username: " . $existing['username'] . "\n";
            echo "Email: " . ($existing['email'] ?? 'N/A') . "\n";
            echo "Creado: " . ($existing['created_at'] ?? 'N/A') . "\n";
            echo "</pre>";
            
            // Verificar si la contraseña actual funciona
            echo "<h3>4. Probando contraseña actual...</h3>";
            
            if (password_verify($password, $existing['password'])) {
                echo "<div class='success'>✅ La contraseña actual es correcta</div>";
                echo "<p>El usuario debería funcionar. Si no puedes iniciar sesión, verifica:</p>";
                echo "<ul>";
                echo "<li>Que estés escribiendo exactamente: <strong>Gisela</strong> (con G mayúscula)</li>";
                echo "<li>Que estés escribiendo exactamente: <strong>Luky123!</strong> (con L mayúscula, L, u, k, y, 1, 2, 3, !)</li>";
                echo "<li>Revisa los logs de errores de PHP</li>";
                echo "</ul>";
            } else {
                echo "<div class='warning'>⚠️ La contraseña actual NO coincide. Actualizando...</div>";
                
                // Actualizar contraseña
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE admin_users SET password = :password, email = :email WHERE username = :username";
                $result = executeQuery($sql, [
                    'password' => $hash,
                    'email' => $email,
                    'username' => $username
                ]);
                
                if ($result) {
                    echo "<div class='success'>✅ Contraseña actualizada exitosamente</div>";
                } else {
                    echo "<div class='error'>❌ Error al actualizar la contraseña</div>";
                }
            }
            
        } else {
            echo "<div class='warning'>⚠️ Usuario 'Gisela' no existe. Creando...</div>";
            
            // Crear usuario
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO admin_users (username, password, email) VALUES (:username, :password, :email)";
            $result = executeQuery($sql, [
                'username' => $username,
                'password' => $hash,
                'email' => $email
            ]);
            
            if ($result) {
                echo "<div class='success'>✅ Usuario creado exitosamente</div>";
            } else {
                echo "<div class='error'>❌ Error al crear el usuario</div>";
                echo "<p>Revisa los logs de errores de PHP</p>";
            }
        }
        
        echo "<h3>5. Resumen</h3>";
        echo "<div class='info'>";
        echo "<p><strong>Usuario:</strong> Gisela</p>";
        echo "<p><strong>Contraseña:</strong> Luky123!</p>";
        echo "<p><strong>URL de acceso:</strong> <a href='/admin/login.php'>/admin/login.php</a></p>";
        echo "</div>";
        ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <a href="admin/login.php" class="btn">➜ Ir al Panel Admin</a>
            <p style="margin-top: 20px; color: #666; font-size: 0.9em;">
                <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (verificar-y-crear-usuario.php) después de usarlo por seguridad.
            </p>
        </div>
    </div>
</body>
</html>

