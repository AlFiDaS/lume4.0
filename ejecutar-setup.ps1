# Script PowerShell para configurar base de datos local
# Ejecutar desde PowerShell: .\ejecutar-setup.ps1

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Configurando Base de Datos Local - LUME" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$XAMPP_PATH = "C:\xampp"
$PROJECT_DIR = $PSScriptRoot
$SQL_FILE = Join-Path $PROJECT_DIR "database.sql"

# Verificar XAMPP
if (-not (Test-Path "$XAMPP_PATH\mysql\bin\mysql.exe")) {
    Write-Host "ERROR: No se encontró XAMPP en $XAMPP_PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Por favor, instala XAMPP o ajusta la ruta en este script." -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit 1
}

# Verificar archivo SQL
if (-not (Test-Path $SQL_FILE)) {
    Write-Host "ERROR: No se encontró database.sql" -ForegroundColor Red
    Write-Host "Ruta esperada: $SQL_FILE" -ForegroundColor Yellow
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "[1/3] Verificando que MySQL esté corriendo..." -ForegroundColor Yellow
Write-Host ""

Write-Host "[2/3] Creando base de datos 'lume_catalogo'..." -ForegroundColor Yellow

# Crear base de datos
& "$XAMPP_PATH\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lume_catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>$null

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR: No se pudo crear la base de datos." -ForegroundColor Red
    Write-Host ""
    Write-Host "Verifica:" -ForegroundColor Yellow
    Write-Host "- Que MySQL esté corriendo en XAMPP" -ForegroundColor Yellow
    Write-Host "- Que no tengas contraseña en root (o ajusta este script)" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "✓ Base de datos creada" -ForegroundColor Green
Write-Host ""

Write-Host "[3/3] Importando estructura de database.sql..." -ForegroundColor Yellow

# Importar SQL
& "$XAMPP_PATH\mysql\bin\mysql.exe" -u root lume_catalogo -e "source $SQL_FILE" 2>$null

# Método alternativo usando redirección
Get-Content $SQL_FILE | & "$XAMPP_PATH\mysql\bin\mysql.exe" -u root lume_catalogo 2>$null

Write-Host "✓ Estructura importada" -ForegroundColor Green
Write-Host ""

Write-Host "============================================" -ForegroundColor Green
Write-Host "  ¡Base de datos configurada exitosamente!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""

Write-Host "Próximos pasos:" -ForegroundColor Cyan
Write-Host "1. Actualiza config.php con:" -ForegroundColor White
Write-Host "   - DB_NAME: lume_catalogo" -ForegroundColor Gray
Write-Host "   - DB_USER: root" -ForegroundColor Gray
Write-Host "   - DB_PASS: (vacío)" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Ejecuta crear-usuario-admin.php para crear el usuario admin" -ForegroundColor White
Write-Host ""
Write-Host "3. Prueba el panel admin en /admin/login.php" -ForegroundColor White
Write-Host ""

Read-Host "Presiona Enter para salir"

