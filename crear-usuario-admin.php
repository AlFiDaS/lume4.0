<?php
/**
 * Script para crear/actualizar usuario administrador
 * ⚠️ ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO POR SEGURIDAD
 */

// Permitir acceso directo para este script
define('LUME_ADMIN', true);

require_once 'config.php';
require_once 'helpers/db.php';

$username = 'Gisela';
$password = 'Luky123!';
$email = 'gisela@lume.com';

// Generar hash de la contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verificar si el usuario ya existe
$existing = fetchOne("SELECT id FROM admin_users WHERE username = :username", ['username' => $username]);

if ($existing) {
    // Actualizar usuario existente
    $sql = "UPDATE admin_users SET password = :password, email = :email WHERE username = :username";
    $result = executeQuery($sql, [
        'password' => $hash,
        'email' => $email,
        'username' => $username
    ]);
    
    if ($result) {
        echo "✅ Usuario 'Gisela' actualizado exitosamente.<br>";
        echo "Usuario: <strong>Gisela</strong><br>";
        echo "Contraseña: <strong>Luky123!</strong><br>";
        echo "<br><strong>⚠️ IMPORTANTE: Elimina este archivo (crear-usuario-admin.php) por seguridad.</strong>";
    } else {
        echo "❌ Error al actualizar el usuario.<br>";
        echo "Verifica los logs de errores de PHP.";
    }
} else {
    // Crear nuevo usuario
    $sql = "INSERT INTO admin_users (username, password, email) VALUES (:username, :password, :email)";
    $result = executeQuery($sql, [
        'username' => $username,
        'password' => $hash,
        'email' => $email
    ]);
    
    if ($result) {
        echo "<h2>✅ Usuario creado exitosamente!</h2>";
        echo "<p><strong>Usuario:</strong> Gisela</p>";
        echo "<p><strong>Contraseña:</strong> Luky123!</p>";
        echo "<br>";
        echo "<p><a href='/admin/login.php' style='background: #e0a4ce; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Ir al Panel Admin</a></p>";
        echo "<br>";
        echo "<p style='color: red; font-weight: bold;'>⚠️ IMPORTANTE: Elimina este archivo (crear-usuario-admin.php) por seguridad después de usarlo.</p>";
    } else {
        echo "❌ Error al crear el usuario.<br>";
        echo "Verifica:<br>";
        echo "- Que la base de datos exista y esté correctamente configurada en config.php<br>";
        echo "- Que la tabla admin_users esté creada (ejecuta database.sql)<br>";
        echo "- Los logs de errores de PHP";
    }
}
?>

