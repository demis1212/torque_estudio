import { Page, BrowserContext, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

// Configuración
export const CONFIG = {
  BASE_URL: 'http://localhost/torque',
  LOGIN_URL: '/login',
  DASHBOARD_URL: '/dashboard',
  TEST_USER: {
    email: 'admin@torque.com',
    password: 'admin123'
  },
  // Modos de auditoría
  MODES: {
    NORMAL: 'normal',      // 1 pasada completa
    AGGRESSIVE: 'aggressive', // Rápida y agresiva
    DETAILED: 'detailed',   // Lenta y detallada
    EXTREME: 'extreme'     // Extremadamente detallada
  }
};

// Datos de prueba extremos
export const EXTREME_TEST_DATA = {
  // SQL Injection
  sqlInjection: [
    "' OR '1'='1",
    "' OR 1=1--",
    "'; DROP TABLE users;--",
    "' UNION SELECT * FROM users--",
    "1' AND 1=1--",
    "admin'--",
  ],
  // XSS
  xssPayloads: [
    '<script>alert("XSS")</script>',
    '<img src=x onerror=alert("XSS")>',
    '"><script>alert(String.fromCharCode(88,83,83))</script>',
    "' onclick='alert(1)",
    '<body onload=alert("XSS")>',
    '<svg onload=alert(1)>',
    'javascript:alert(1)',
  ],
  // Datos inválidos
  invalidData: {
    emails: ['invalid', '@test.com', 'user@', 'user@@test.com', 'user @test.com'],
    phones: ['abc', '123', '12345678901234567890', '+abc123'],
    dates: ['2024-13-45', '32/12/2024', 'invalid', ''],
    longText: 'A'.repeat(10000),
    specialChars: '!@#$%^&*()_+-=[]{}|;:,.<>?',
    unicode: '日本語中文العربيةעברית',
    nullBytes: '\x00\x01\x02\x03',
  },
  // Datos válidos
  validData: {
    client: {
      name: 'Cliente Test QA',
      email: 'cliente.test@qa.com',
      phone: '+56912345678',
      rut: '12.345.678-9',
      address: 'Av. Principal 123, Santiago'
    },
    vehicle: {
      brand: 'Toyota',
      model: 'Corolla Test QA',
      year: '2023',
      plate: 'TEST-123',
      vin: '1HGCM82633A004352',
      mileage: '50000'
    },
    order: {
      description: 'Cambio de aceite y revisión general',
      diagnosis: 'Fuga de aceite en tapa de punterías',
      notes: 'Cliente solicita urgencia'
    },
    parts: {
      name: 'Filtro de Aceite Premium',
      code: 'PART-' + Date.now(),
      price: 25990,
      stock: 50,
      min_stock: 10,
      category: 'Filtros'
    }
  }
};

// Logger de auditoría
export class AuditLogger {
  private logs: any[] = [];
  private startTime: number;

  constructor(private testName: string) {
    this.startTime = Date.now();
  }

  log(level: 'info' | 'warning' | 'error' | 'critical', message: string, details?: any) {
    const entry = {
      timestamp: new Date().toISOString(),
      level,
      message,
      details,
      elapsed: Date.now() - this.startTime
    };
    this.logs.push(entry);
    console.log(`[${level.toUpperCase()}] ${message}`, details || '');
  }

  saveReport() {
    const reportPath = path.join('test-results', 'audit-logs', `${this.testName}.json`);
    fs.mkdirSync(path.dirname(reportPath), { recursive: true });
    fs.writeFileSync(reportPath, JSON.stringify(this.logs, null, 2));
  }
}

// Capturador de evidencias
export class EvidenceCollector {
  private evidenceCount = 0;

  constructor(private page: Page, private testName: string) {}

  async screenshot(name: string) {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filename = `${this.testName}-${name}-${timestamp}.png`;
    const filepath = path.join('test-results', 'screenshots', filename);
    
    fs.mkdirSync(path.dirname(filepath), { recursive: true });
    await this.page.screenshot({ path: filepath, fullPage: true });
    this.evidenceCount++;
    return filepath;
  }

  async captureConsoleErrors() {
    const errors: any[] = [];
    this.page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push({
          type: msg.type(),
          text: msg.text(),
          location: msg.location(),
          timestamp: new Date().toISOString()
        });
      }
    });
    return errors;
  }

  async captureNetworkErrors() {
    const errors: any[] = [];
    this.page.on('response', response => {
      if (response.status() >= 400) {
        errors.push({
          url: response.url(),
          status: response.status(),
          statusText: response.statusText(),
          timestamp: new Date().toISOString()
        });
      }
    });
    return errors;
  }
}

// Login robusto
export async function robustLogin(page: Page, email?: string, password?: string) {
  const logger = new AuditLogger('login');
  const testEmail = email || CONFIG.TEST_USER.email;
  const testPassword = password || CONFIG.TEST_USER.password;

  try {
    logger.log('info', 'Iniciando proceso de login');
    
    // Navegar a login con retry
    await page.goto(`${CONFIG.BASE_URL}${CONFIG.LOGIN_URL}`, {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    // Verificar que estamos en login
    const url = page.url();
    if (!url.includes('login')) {
      // Ya estamos logueados
      logger.log('info', 'Sesión existente detectada');
      return true;
    }

    // Llenar formulario con validaciones
    const emailField = page.locator('input[name="email"], input[type="email"]').first();
    const passwordField = page.locator('input[name="password"], input[type="password"]').first();
    
    // Esperar elementos visibles
    await expect(emailField).toBeVisible({ timeout: 15000 });
    await expect(passwordField).toBeVisible({ timeout: 15000 });

    // Llenar con delay humano
    await emailField.fill(testEmail);
    await page.waitForTimeout(300);
    await passwordField.fill(testPassword);
    await page.waitForTimeout(300);

    // Submit
    const submitBtn = page.locator('button[type="submit"]').first();
    await submitBtn.click();

    // Esperar dashboard con timeout extendido
    await page.waitForURL('**/dashboard', { 
      timeout: 20000,
      waitUntil: 'networkidle'
    });

    logger.log('info', 'Login exitoso');
    return true;

  } catch (error: any) {
    logger.log('error', 'Error en login', error.message);
    await page.screenshot({ 
      path: `test-results/login-error-${Date.now()}.png`,
      fullPage: true 
    });
    return false;
  }
}

// Explorador recursivo de UI
export async function recursiveUIExplorer(
  page: Page, 
  visitedUrls: Set<string>, 
  depth: number = 0,
  maxDepth: number = 5
) {
  if (depth >= maxDepth) return;

  const currentUrl = page.url();
  if (visitedUrls.has(currentUrl)) return;
  visitedUrls.add(currentUrl);

  console.log(`🔍 Explorando (nivel ${depth}): ${currentUrl}`);

  // Esperar carga completa
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1000);

  // Encontrar todos los elementos interactivos
  const elements = await page.locator('a, button, [role="button"], [role="link"]').all();
  
  for (let i = 0; i < Math.min(elements.length, 20); i++) {
    const element = elements[i];
    
    try {
      const isVisible = await element.isVisible().catch(() => false);
      if (!isVisible) continue;

      const text = await element.textContent().catch(() => '');
      const href = await element.getAttribute('href').catch(() => null);

      // Ignorar elementos externos
      if (href && (href.startsWith('http') || href.startsWith('//'))) continue;
      if (href && href.startsWith('#')) continue;

      // Hacer clic
      await element.click({ timeout: 5000 });
      await page.waitForTimeout(1500);

      // Si cambió de página, explorar recursivamente
      const newUrl = page.url();
      if (newUrl !== currentUrl && !visitedUrls.has(newUrl)) {
        await recursiveUIExplorer(page, visitedUrls, depth + 1, maxDepth);
        
        // Volver atrás
        await page.goto(currentUrl);
        await page.waitForLoadState('networkidle');
      }

    } catch (e) {
      // Ignorar errores de elementos no clickeables
    }
  }
}

// Detector de formularios
export async function detectAndTestForms(page: Page, logger: AuditLogger) {
  const forms = await page.locator('form').all();
  logger.log('info', `Detectados ${forms.length} formularios`);

  for (let i = 0; i < forms.length; i++) {
    const form = forms[i];
    
    try {
      // Encontrar inputs
      const inputs = await form.locator('input, textarea, select').all();
      
      for (const input of inputs) {
        const type = await input.getAttribute('type').catch(() => 'text');
        const name = await input.getAttribute('name').catch(() => 'unknown');
        const required = await input.getAttribute('required').catch(() => null);

        logger.log('info', `Campo detectado: ${name} (${type})${required ? ' [REQUERIDO]' : ''}`);

        // Probar con datos extremos
        if (type === 'email') {
          // Probar email inválido
          await input.fill('invalid-email');
        } else if (type === 'text' || !type) {
          // Probar XSS
          await input.fill(EXTREME_TEST_DATA.xssPayloads[0]);
        }
      }

    } catch (e) {
      logger.log('warning', `Error procesando formulario ${i}`, e);
    }
  }
}

// Validador de rendimiento
export async function measurePerformance(page: Page) {
  const metrics: any = {};

  const navigationTiming = await page.evaluate(() => {
    const perf = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
    return {
      dns: perf.domainLookupEnd - perf.domainLookupStart,
      tcp: perf.connectEnd - perf.connectStart,
      ttfb: perf.responseStart - perf.requestStart,
      download: perf.responseEnd - perf.responseStart,
      dom: perf.domComplete - perf.domInteractive,
      load: perf.loadEventEnd - perf.fetchStart
    };
  });

  metrics.navigation = navigationTiming;

  // Métricas de recursos
  const resourceTiming = await page.evaluate(() => {
    return performance.getEntriesByType('resource').map(r => ({
      name: r.name,
      duration: r.duration,
      size: (r as any).transferSize
    }));
  });

  metrics.resources = resourceTiming;

  return metrics;
}

// Reporte de bugs
export class BugReporter {
  private bugs: any[] = [];

  addBug(severity: 'low' | 'medium' | 'high' | 'critical', 
         category: string, 
         title: string, 
         description: string,
         evidence?: string) {
    this.bugs.push({
      id: `BUG-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date().toISOString(),
      severity,
      category,
      title,
      description,
      evidence
    });
  }

  generateHTMLReport() {
    const severityColors: Record<string, string> = {
      critical: '#dc2626',
      high: '#ea580c',
      medium: '#ca8a04',
      low: '#16a34a'
    };

    const bugsBySeverity = this.bugs.reduce((acc, bug) => {
      acc[bug.severity] = (acc[bug.severity] || 0) + 1;
      return acc;
    }, {});

    return `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Torque Studio - Reporte de Auditoría</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 0; padding: 20px; background: #0f1419; color: #e8eaf2; }
    .header { text-align: center; margin-bottom: 30px; }
    .header h1 { background: linear-gradient(135deg, #8ab4f8, #4d8eff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
    .stat-card { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; text-align: center; }
    .stat-number { font-size: 2.5rem; font-weight: bold; }
    .critical { color: #dc2626; }
    .high { color: #ea580c; }
    .medium { color: #ca8a04; }
    .low { color: #16a34a; }
    .bug-list { background: rgba(255,255,255,0.03); border-radius: 12px; padding: 20px; }
    .bug-item { border-left: 4px solid; padding: 15px; margin: 10px 0; background: rgba(255,255,255,0.02); border-radius: 0 8px 8px 0; }
    .bug-id { font-family: monospace; color: #9aa3b2; font-size: 0.85rem; }
    .bug-title { font-size: 1.1rem; font-weight: 600; margin: 5px 0; }
    .bug-desc { color: #9aa3b2; font-size: 0.95rem; }
    .timestamp { font-size: 0.8rem; color: #6b7280; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>🛡️ Torque Studio - Reporte de Auditoría</h1>
    <p>Generado: ${new Date().toLocaleString()}</p>
    <p>Total Bugs Encontrados: ${this.bugs.length}</p>
  </div>
  
  <div class="summary">
    <div class="stat-card">
      <div class="stat-number critical">${bugsBySeverity.critical || 0}</div>
      <div>Críticos</div>
    </div>
    <div class="stat-card">
      <div class="stat-number high">${bugsBySeverity.high || 0}</div>
      <div>Altos</div>
    </div>
    <div class="stat-card">
      <div class="stat-number medium">${bugsBySeverity.medium || 0}</div>
      <div>Medios</div>
    </div>
    <div class="stat-card">
      <div class="stat-number low">${bugsBySeverity.low || 0}</div>
      <div>Bajos</div>
    </div>
  </div>

  <div class="bug-list">
    <h2>🐛 Bugs Detallados</h2>
    ${this.bugs.map(bug => `
      <div class="bug-item" style="border-color: ${severityColors[bug.severity]}">
        <div class="bug-id">${bug.id}</div>
        <div class="bug-title">[${bug.severity.toUpperCase()}] ${bug.title}</div>
        <div class="bug-desc">${bug.description}</div>
        <div class="timestamp">${new Date(bug.timestamp).toLocaleString()}</div>
        ${bug.evidence ? `<div style="margin-top:10px;font-size:0.85rem;color:#6b7280;">Evidencia: ${bug.evidence}</div>` : ''}
      </div>
    `).join('')}
  </div>
</body>
</html>`;
  }

  saveReport() {
    const html = this.generateHTMLReport();
    const reportPath = path.join('test-results', 'audit-report.html');
    fs.mkdirSync(path.dirname(reportPath), { recursive: true });
    fs.writeFileSync(reportPath, html);
    return reportPath;
  }
}
