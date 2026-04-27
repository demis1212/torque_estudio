// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Configuración Playwright - Suite Completa QA Torque Studio
 * Autor: QA Engineer Senior
 * Versión: 2.1
 */

// ==================== CONFIGURACIÓN ====================
const BASE_URL = process.env.BASE_URL || 'http://localhost/torque';
const TEST_USER = {
  email: process.env.TEST_EMAIL || 'admin@torque.com',
  password: process.env.TEST_PASSWORD || 'admin123'
};

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: false, // Secuencial para evitar conflictos
  forbidOnly: !!process.env.CI,
  retries: 1, // Reintentar una vez si falla
  workers: 1, // Un worker para mantener sesión
  
  // Reportes múltiples
  reporter: [
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }],
  ],
  
  // Configuración global de tests
  timeout: 120000, // 2 minutos por test
  expect: {
    timeout: 15000,
  },
  
  use: {
    // URL base configurable
    baseURL: BASE_URL,
    
    // Navegador
    browserName: 'chromium',
    headless: true, // Cambiar a false para ver ejecución
    viewport: { width: 1920, height: 1080 },
    
    // Grabación de evidencia
    screenshot: 'on', // Screenshot en cada paso
    video: 'on-first-retry', // Video si falla
    trace: 'on-first-retry', // Trace si falla
    
    // Timeouts
    actionTimeout: 30000,
    navigationTimeout: 30000,
    
    // Permisos
    permissions: ['notifications'],
    
    // NOTA: storageState se configura solo en el proyecto chromium que depende de setup
    // No configurar aquí globalmente para evitar error cuando el archivo no existe
  },
  
  // Proyectos
  projects: [
    {
      name: 'setup',
      testMatch: /.*\.setup\.js/,
      // El proyecto setup NO usa storageState, es el que lo crea
    },
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        storageState: 'tests/.auth/user.json',
      },
      dependencies: ['setup'],
    },
  ],
  
  // Directorio de salida
  outputDir: 'test-results/',
});

// Exportar configuración para tests
module.exports.TEST_USER = TEST_USER;
module.exports.BASE_URL = BASE_URL;
