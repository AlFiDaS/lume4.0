<?php
/**
 * Script automático para crear/actualizar usuario admin
 * Ejecuta este archivo y luego accede al panel admin
 */

define('LUME_ADMIN', true);
require_once 'config.php';
require_once 'helpers/db.php';

$username = 'Gisela';
$password = 'Luky123!';
$email = 'gisela@lume.com';

echo "🔧 Verificando y configurando usuario admin...\n\n";

// Verificar conexión
$pdo = getDB();
if (!$pdo) {
    die("❌ Error: No se puede conectar a la base de datos. Verifica que MySQL esté corriendo.\n");
}

echo "✅ Conexión a BD exitosa\n";

// Verificar tabla
$tableExists = fetchOne("SHOW TABLES LIKE 'admin_users'");
if (!$tableExists) {
    die("❌ Error: La tabla admin_users no existe. Ejecuta setup-database.bat primero.\n");
}

echo "✅ Tabla admin_users existe\n";

// Verificar/crear usuario
$existing = fetchOne("SELECT * FROM admin_users WHERE username = :username", ['username' => $username]);

$hash = password_hash($password, PASSWORD_BCRYPT);

if ($existing) {
    // Actualizar
    $sql = "UPDATE admin_users SET password = :password, email = :email WHERE username = :username";
    executeQuery($sql, ['password' => $hash, 'email' => $email, 'username' => $username]);
    echo "✅ Usuario actualizado\n";
} else {
    // Crear
    $sql = "INSERT INTO admin_users (username, password, email) VALUES (:username, :password, :email)";
    executeQuery($sql, ['username' => $username, 'password' => $hash, 'email' => $email]);
    echo "✅ Usuario creado\n";
}

echo "\n🎉 ¡Listo! Puedes acceder con:\n";
echo "   Usuario: Gisela\n";
echo "   Contraseña: Luky123!\n";
echo "\n   URL: http://localhost:8080/admin/login.php\n";

