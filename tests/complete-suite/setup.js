#!/usr/bin/env node
/**
 * Setup script para Torque Studio Audit Suite
 * Instala todas las dependencias necesarias
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Torque Studio - Audit Suite Setup\n');

// Verificar si package.json existe
if (!fs.existsSync('package.json')) {
  console.error('❌ Error: No se encontró package.json');
  process.exit(1);
}

// Instalar dependencias
console.log('📦 Instalando dependencias...');
try {
  execSync('npm install', { stdio: 'inherit' });
  console.log('✅ Dependencias instaladas\n');
} catch (e) {
  console.error('❌ Error instalando dependencias:', e.message);
  process.exit(1);
}

// Instalar browsers de Playwright
console.log('🌐 Instalando browsers de Playwright...');
try {
  execSync('npx playwright install chromium', { stdio: 'inherit' });
  console.log('✅ Browsers instalados\n');
} catch (e) {
  console.error('❌ Error instalando browsers:', e.message);
  process.exit(1);
}

// Crear directorios necesarios
const dirs = [
  'test-results/screenshots',
  'test-results/audit-logs',
  'test-results/traces',
  'test-results/videos',
];

console.log('📁 Creando directorios...');
dirs.forEach(dir => {
  const fullPath = path.join(__dirname, dir);
  if (!fs.existsSync(fullPath)) {
    fs.mkdirSync(fullPath, { recursive: true });
    console.log(`  ✅ ${dir}`);
  }
});

// Verificar configuración
console.log('\n⚙️ Verificando configuración...');
const helpersPath = path.join(__dirname, 'utils', 'test-helpers.ts');
if (fs.existsSync(helpersPath)) {
  console.log('  ✅ test-helpers.ts encontrado');
} else {
  console.error('  ❌ test-helpers.ts no encontrado');
}

console.log('\n✨ Setup completado!');
console.log('\n📖 Comandos disponibles:');
console.log('  npm run test        - Ejecutar todos los tests');
console.log('  npm run test:auth   - Solo tests de autenticación');
console.log('  npm run test:security - Solo tests de seguridad');
console.log('  npm run test:api    - Solo tests de API');
console.log('  npm run test:a11y   - Solo tests de accesibilidad');
console.log('  npm run test:report - Ejecutar y mostrar reporte HTML');
console.log('  npm run audit       - Ejecutar auditoría completa (3 pasadas)');
console.log('\n🎯 Para comenzar:');
console.log('  npm run test -- --list');
console.log('  npm run test -- auth.spec.ts --headed\n');
