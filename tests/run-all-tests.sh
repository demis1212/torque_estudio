#!/bin/bash

# Torque Studio ERP - Ejecutor de Pruebas Completo
# Uso: ./tests/run-all-tests.sh [--verbose] [--html]

clear

echo ""
echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║       TORQUE STUDIO ERP - EJECUTOR DE PRUEBAS COMPLETO        ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

# Colores para terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ ERROR: PHP no está instalado${NC}"
    echo ""
    echo "Instala PHP con:"
    echo "  Ubuntu/Debian: sudo apt-get install php php-mysql php-mbstring"
    echo "  CentOS/RHEL:   sudo yum install php php-mysql php-mbstring"
    echo "  macOS:         brew install php"
    exit 1
fi

echo -e "${GREEN}✅ PHP encontrado${NC}"
php -v | grep "PHP [0-9]"
echo ""

# Obtener directorio del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

# Ejecutar pruebas
echo "🧪 Ejecutando pruebas completas..."
echo ""
php tests/full-test.php "$@"

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ TODAS LAS PRUEBAS PASARON${NC}"
    echo "El sistema está listo para deployment."
else
    echo -e "${YELLOW}⚠️  HAY PRUEBAS FALLIDAS - Revisar el reporte arriba${NC}"
fi

echo ""
exit $EXIT_CODE
