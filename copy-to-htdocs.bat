@echo off
echo ==========================================
echo  COPIANDO TORQUE ERP A HTDOCS
echo ==========================================
echo.
echo Origen: C:\Users\victuspc\Desktop\Nueva carpeta
echo Destino: C:\xampp\htdocs\torque
echo.

REM Crear directorio si no existe
if not exist "C:\xampp\htdocs\torque" (
    mkdir "C:\xampp\htdocs\torque"
    echo [+] Directorio creado
)

echo.
echo [*] Copiando archivos...
echo.

REM Copiar todo con xcopy
xcopy "C:\Users\victuspc\Desktop\Nueva carpeta\*" "C:\xampp\htdocs\torque\" /E /I /H /Y

echo.
echo ==========================================
if %ERRORLEVEL% EQU 0 (
    echo  ✅ COPIA COMPLETADA EXITOSAMENTE
    echo.
    echo  Ahora abre tu navegador y ve a:
    echo  http://localhost/torque/fix-data.php
) else (
    echo  ⚠️  ALGUNOS ARCHIVOS PUEDEN HABER FALLADO
    echo  ErrorLevel: %ERRORLEVEL%
)
echo ==========================================
echo.
pause
