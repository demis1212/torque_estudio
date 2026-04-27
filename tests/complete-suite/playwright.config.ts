import { defineConfig, devices } from '@playwright/test';

// Detectar si estamos en CI
const isCI = !!process.env.CI;

export default defineConfig({
  testDir: './',
  
  // Paralelización optimizada
  fullyParallel: !isCI, // Paralelo en local, serial en CI
  workers: isCI ? 1 : 3, // 1 worker en CI, 3 en local
  
  // Tiempos de ejecución
  timeout: isCI ? 5 * 60 * 1000 : 10 * 60 * 1000, // 5 min CI, 10 min local
  
  expect: {
    timeout: isCI ? 15000 : 30000,
  },
  
  // Configuración de reintentos
  retries: isCI ? 2 : 1, // 2 reintentos en CI
  
  // Reportes mejorados
  reporter: [
    ['html', { 
      open: 'never', 
      outputFolder: 'test-results/complete-report',
      attachmentsBaseURL: 'https://ci.torque.studio/artifacts/'
    }],
    ['list'],
    ['json', { outputFile: 'test-results/audit-results.json' }],
    ['junit', { outputFile: 'test-results/junit-results.xml' }], // Para integración CI
  ],
  
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost/torque',
    
    // Captura de evidencias
    trace: isCI ? 'retain-on-failure' : 'on-first-retry',
    screenshot: isCI ? 'on' : 'only-on-failure',
    video: isCI ? 'retain-on-failure' : 'on-first-retry',
    
    // Timeouts optimizados
    actionTimeout: isCI ? 15000 : 30000,
    navigationTimeout: isCI ? 20000 : 30000,
    
    // Configuración de viewport por defecto
    viewport: { width: 1920, height: 1080 },
    
    // Headers para identificar requests de test
    extraHTTPHeaders: {
      'X-Test-Request': 'playwright-audit-suite',
    },
  },
  
  // Agrupar tests por proyecto para mejor organización
  projects: [
    // Tests funcionales - Desktop
    {
      name: 'chromium',
      testMatch: /^(?!.*responsive).*\.spec\.ts$/, // Excluir tests responsive
      use: { 
        ...devices['Desktop Chrome'],
        viewport: { width: 1920, height: 1080 }
      },
    },
    
    // Tests de seguridad - Solo desktop
    {
      name: 'security',
      testMatch: /security\.spec\.ts$/,
      use: { 
        ...devices['Desktop Chrome'],
      },
    },
    
    // Tests API - Sin browser
    {
      name: 'api',
      testMatch: /api\.spec\.ts$/,
      use: {
        // API tests no necesitan browser
      },
    },
    
    // Tests responsive - Múltiples dispositivos
    {
      name: 'mobile-chrome',
      testMatch: /responsive\.spec\.ts$/,
      use: { 
        ...devices['Pixel 5'],
      },
    },
    {
      name: 'tablet-safari',
      testMatch: /responsive\.spec\.ts$/,
      use: { 
        ...devices['iPad Pro 11'],
      },
    },
    {
      name: 'mobile-safari',
      testMatch: /responsive\.spec\.ts$/,
      use: { 
        ...devices['iPhone 14'],
      },
    },
  ],
  
  // Configuración de output
  outputDir: 'test-results/artifacts/',
  
  // Web server para CI (auto-iniciar servidor de test)
  webServer: isCI ? {
    command: 'cd ../.. && php -S localhost:8080 -t public/',
    url: 'http://localhost:8080',
    reuseExistingServer: !isCI,
    timeout: 120 * 1000,
  } : undefined,
});
