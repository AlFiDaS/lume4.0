<?php
/**
 * Script para configurar la base de datos local automáticamente
 * Crea la base de datos e importa la estructura
 */

echo "🔧 Configurando Base de Datos Local...\n\n";

// Configuración para local
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Sin contraseña por defecto en XAMPP
$dbName = 'lume_catalogo';

try {
    // Conectar sin especificar base de datos (para crearla)
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado a MySQL\n";
    
    // Verificar si la base de datos ya existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "⚠️  La base de datos '$dbName' ya existe.\n";
        echo "¿Deseas eliminarla y recrearla? (esto borrará todos los datos)\n";
        echo "Escribe 'si' para continuar, o presiona Enter para usar la existente: ";
        
        // En modo web, simplemente continúa
        if (php_sapi_name() !== 'cli') {
            echo "\n\n";
            echo "<form method='POST'>";
            echo "<input type='hidden' name='recreate' value='1'>";
            echo "<button type='submit' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;'>Eliminar y Recrear BD</button>";
            echo "</form>";
            echo "<a href='?continue=1' style='display: inline-block; margin-left: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Usar Base Existente</a>";
            
            if (isset($_POST['recreate']) || isset($_GET['continue'])) {
                if (isset($_POST['recreate'])) {
                    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
                    echo "🗑️  Base de datos eliminada\n";
                }
                
                // Crear base de datos
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "✅ Base de datos '$dbName' creada\n";
                
                // Usar la base de datos
                $pdo->exec("USE `$dbName`");
                
                // Leer y ejecutar SQL
                $sqlFile = __DIR__ . '/database.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    
                    // Dividir en statements
                    $statements = array_filter(
                        array_map('trim', explode(';', $sql)),
                        function($stmt) {
                            return !empty($stmt) && 
                                   !preg_match('/^\s*--/', $stmt) && 
                                   !preg_match('/^\s*CREATE DATABASE/i', $stmt) &&
                                   !preg_match('/^\s*USE/i', $stmt);
                        }
                    );
                    
                    $successCount = 0;
                    foreach ($statements as $statement) {
                        if (!empty(trim($statement))) {
                            try {
                                $pdo->exec($statement);
                                $successCount++;
                            } catch (PDOException $e) {
                                // Ignorar errores de "ya existe"
                                if (strpos($e->getMessage(), 'already exists') === false) {
                                    echo "⚠️  Error en statement: " . substr($statement, 0, 50) . "...\n";
                                    echo "   " . $e->getMessage() . "\n";
                                }
                            }
                        }
                    }
                    
                    echo "✅ Estructura importada ($successCount statements ejecutados)\n";
                } else {
                    echo "❌ No se encontró el archivo database.sql\n";
                }
                
                echo "\n🎉 ¡Base de datos configurada exitosamente!\n\n";
                echo "Próximos pasos:\n";
                echo "1. Actualiza config.php con estas credenciales:\n";
                echo "   DB_NAME: $dbName\n";
                echo "   DB_USER: $dbUser\n";
                echo "   DB_PASS: (vacío)\n\n";
                echo "2. Ejecuta crear-usuario-admin.php para crear el usuario admin\n";
                echo "3. Prueba el panel admin en /admin/login.php\n";
            }
            exit;
        }
    } else {
        // Crear base de datos
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Base de datos '$dbName' creada\n";
        
        // Usar la base de datos
        $pdo->exec("USE `$dbName`");
        
        // Leer y ejecutar SQL
        $sqlFile = __DIR__ . '/database.sql';
        if (file_exists($sqlFile)) {
            echo "📖 Leyendo database.sql...\n";
            $sql = file_get_contents($sqlFile);
            
            // Dividir en statements y ejecutar
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && 
                           !preg_match('/^\s*--/', $stmt) && 
                           !preg_match('/^\s*CREATE DATABASE/i', $stmt) &&
                           !preg_match('/^\s*USE/i', $stmt);
                }
            );
            
            $successCount = 0;
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    try {
                        $pdo->exec($statement);
                        $successCount++;
                    } catch (PDOException $e) {
                        // Ignorar errores de "ya existe"
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "⚠️  " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
            
            echo "✅ Estructura importada ($successCount statements ejecutados)\n";
        } else {
            echo "❌ No se encontró el archivo database.sql en: $sqlFile\n";
            exit(1);
        }
        
        echo "\n🎉 ¡Base de datos configurada exitosamente!\n\n";
        echo "Próximos pasos:\n";
        echo "1. Actualiza config.php:\n";
        echo "   define('DB_NAME', '$dbName');\n";
        echo "   define('DB_USER', '$dbUser');\n";
        echo "   define('DB_PASS', '');\n\n";
        echo "2. Ejecuta: crear-usuario-admin.php\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "Verifica:\n";
    echo "- Que MySQL esté corriendo en XAMPP\n";
    echo "- Que las credenciales sean correctas\n";
    echo "- Que tengas permisos para crear bases de datos\n";
    exit(1);
}

?>

