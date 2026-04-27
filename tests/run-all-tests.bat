@echo off
chcp 65001 >nul
cls

echo.
echo ╔═══════════════════════════════════════════════════════════════╗
echo ║       TORQUE STUDIO ERP - EJECUTOR DE PRUEBAS COMPLETO        ║
echo ╚═══════════════════════════════════════════════════════════════╝
echo.

REM Verificar si PHP está instalado
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ ERROR: PHP no está instalado o no está en el PATH
    echo.
    echo Por favor instala PHP y asegurate de que esté en el PATH:
    echo https://windows.php.net/download/
    echo.
    pause
    exit /b 1
)

echo ✅ PHP encontrado
php -v | findstr /R "PHP [0-9]"
echo.

REM Ir al directorio del proyecto
cd /d "%~dp0\.."

REM Ejecutar pruebas completas
echo 🧪 Ejecutando pruebas completas...
echo.
php tests\full-test.php %*

set EXIT_CODE=%ERRORLEVEL%

echo.
if %EXIT_CODE% EQU 0 (
    echo ✅ TODAS LAS PRUEBAS PASARON
    echo El sistema está listo para deployment.
) else (
    echo ⚠️  HAY PRUEBAS FALLIDAS - Revisar el reporte arriba
)

echo.
echo Presiona cualquier tecla para salir...
pause >nul
exit /b %EXIT_CODE%
