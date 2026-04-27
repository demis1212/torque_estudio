@echo off
chcp 65001 >nul
echo.
echo ╔═══════════════════════════════════════════╗
echo ║  PRUEBAS DE MODULO HERRAMIENTAS - TORQUE  ║
echo ╚═══════════════════════════════════════════╝
echo.
echo Ejecutando pruebas automatizadas...
echo.
cd /d "%~dp0.."
php tests/TestRunner.php 2>&1
echo.
echo Presione cualquier tecla para salir...
pause >nul
