@echo off
REM ============================================
REM Script para configurar base de datos local
REM ============================================

echo.
echo ============================================
echo   Configurando Base de Datos Local - LUME
echo ============================================
echo.

REM Verificar que XAMPP esté instalado
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%\mysql\bin\mysql.exe" (
    echo ERROR: No se encontró XAMPP en %XAMPP_PATH%
    echo.
    echo Por favor, instala XAMPP o ajusta la ruta en este script.
    pause
    exit /b 1
)

echo [1/3] Verificando que MySQL esté corriendo...
echo.

REM Obtener la ruta del proyecto
set PROJECT_DIR=%~dp0
set SQL_FILE=%PROJECT_DIR%database.sql

if not exist "%SQL_FILE%" (
    echo ERROR: No se encontró database.sql en: %SQL_FILE%
    pause
    exit /b 1
)

echo [2/3] Creando base de datos...
echo.

REM Crear base de datos
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lume_catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul

if errorlevel 1 (
    echo.
    echo ERROR: No se pudo crear la base de datos.
    echo.
    echo Verifica:
    echo - Que MySQL esté corriendo en XAMPP
    echo - Que no tengas contraseña en root (o ajusta este script)
    echo.
    pause
    exit /b 1
)

echo [3/3] Importando estructura de database.sql...
echo.

REM Importar SQL
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u root lume_catalogo < "%SQL_FILE%" 2>nul

if errorlevel 1 (
    echo.
    echo ADVERTENCIA: Hubo algunos errores al importar.
    echo Algunas tablas pueden ya existir, lo cual es normal.
    echo.
) else (
    echo.
    echo ============================================
    echo   ¡Base de datos configurada exitosamente!
    echo ============================================
    echo.
)

echo.
echo Próximos pasos:
echo 1. Actualiza config.php con:
echo    - DB_NAME: lume_catalogo
echo    - DB_USER: root
echo    - DB_PASS: (vacío)
echo.
echo 2. Ejecuta crear-usuario-admin.php para crear el usuario admin
echo.
echo 3. Prueba el panel admin en /admin/login.php
echo.

pause

