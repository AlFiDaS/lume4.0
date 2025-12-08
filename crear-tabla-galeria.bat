@echo off
REM ============================================
REM Crear tabla de galería
REM ============================================

echo.
echo ============================================
echo   Creando tabla de galería
echo ============================================
echo.

REM Configurar ruta de PHP de XAMPP
set PHP_PATH=C:\xampp\php\php.exe

REM Verificar si PHP de XAMPP existe
if not exist "%PHP_PATH%" (
    echo ERROR: No se encontró PHP en %PHP_PATH%
    echo.
    echo Verifica que XAMPP esté instalado
    pause
    exit /b 1
)

REM Ejecutar script PHP
"%PHP_PATH%" crear-tabla-galeria.php

echo.
pause

