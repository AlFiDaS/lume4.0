<?php
/**
 * Test directo del admin sin pasar por router
 */
echo "<h1>Test de Acceso al Admin</h1>";
echo "<p>Si ves esto, PHP está funcionando.</p>";

// Probar incluir config
try {
    define('LUME_ADMIN', true);
    require_once 'config.php';
    echo "<p style='color: green;'>✅ Config cargado correctamente</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
    echo "<p>ADMIN_URL: " . ADMIN_URL . "</p>";
    
    // Probar conexión a BD
    require_once 'helpers/db.php';
    $pdo = getDB();
    if ($pdo) {
        echo "<p style='color: green;'>✅ Conexión a BD exitosa</p>";
    } else {
        echo "<p style='color: red;'>❌ Error de conexión a BD</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='admin/login.php'>Ir al Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

