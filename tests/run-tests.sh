#!/bin/bash
# Script de pruebas para Linux/Mac

echo ""
echo "╔═══════════════════════════════════════════╗"
echo "║  PRUEBAS DE TORQUE STUDIO ERP             ║"
echo "╚═══════════════════════════════════════════╝"
echo ""

cd "$(dirname "$0")/.."

# Detectar PHP
if ! command -v php &> /dev/null; then
    echo "❌ ERROR: PHP no está instalado"
    exit 1
fi

echo "✅ PHP detectado: $(php -v | head -1)"
echo ""

# Ejecutar pruebas
echo "Ejecutando pruebas automatizadas..."
echo ""
php tests/TestRunner.php

echo ""
read -p "Presione Enter para salir..."
