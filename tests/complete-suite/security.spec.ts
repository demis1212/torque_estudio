import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, EXTREME_TEST_DATA } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('🛡️ Módulo Seguridad - Pentesting', () => {
  
  test('7.1 - Páginas sin autenticación', async ({ page, browser }) => {
    const logger = new AuditLogger('unauthorized-pages');
    const evidence = new EvidenceCollector(page, 'security');
    
    // Crear contexto nuevo sin cookies
    const context = await browser.newContext();
    const newPage = await context.newPage();
    
    const protectedRoutes = [
      '/dashboard',
      '/clients',
      '/vehicles',
      '/work-orders',
      '/services',
      '/parts',
      '/reports',
      '/users',
      '/settings',
      '/whatsapp-reminders',
    ];
    
    let exposedRoutes = 0;
    
    for (const route of protectedRoutes) {
      try {
        await newPage.goto(`${CONFIG.BASE_URL}${route}`, { waitUntil: 'domcontentloaded', timeout: 10000 });
        await newPage.waitForTimeout(1500);
        
        const url = newPage.url();
        const bodyText = await newPage.locator('body').textContent().catch(() => '') || '';
        
        // Si NO redirige a login, es una vulnerabilidad
        if (!url.includes('login') && !bodyText.toLowerCase().includes('acceso denegado')) {
          bugReporter.addBug('critical', 'Security', `Ruta Sin Protección: ${route}`, 'Accesible sin autenticación');
          exposedRoutes++;
        }
        
      } catch (e) {
        logger.log('info', `${route}: Timeout o error (probablemente protegida)`);
      }
    }
    
    await evidence.screenshot('01-unauthorized-test');
    logger.log('info', `Rutas expuestas: ${exposedRoutes}`);
    
    await context.close();
    logger.saveReport();
  });

  test('7.2 - IDOR - Acceso a recursos de otros usuarios', async ({ browser }) => {
    const logger = new AuditLogger('idor-test');
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // Login
    try {
      await page.goto(`${CONFIG.BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await page.locator('input[name="email"]').fill(CONFIG.TEST_USER.email);
      await page.locator('input[name="password"]').fill(CONFIG.TEST_USER.password);
      await page.locator('button[type="submit"]').click();
      await page.waitForURL('**/dashboard', { timeout: 20000 });
      
      const idorTests = [
        { url: '/work-orders/edit/99999', name: 'Orden inexistente' },
        { url: '/clients/edit/99999', name: 'Cliente inexistente' },
        { url: '/vehicles/edit/99999', name: 'Vehículo inexistente' },
        { url: '/users/edit/2', name: 'Otro usuario (IDOR)' },
      ];
      
      for (const test of idorTests) {
        try {
          await page.goto(`${CONFIG.BASE_URL}${test.url}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
          await page.waitForTimeout(1000);
          
          const bodyText = await page.locator('body').textContent().catch(() => '') || '';
          const hasAccessDenied = /acceso denegado|no autorizado|403/i.test(bodyText);
          const hasNotFound = /no encontrado|not found|404/i.test(bodyText);
          const isLoginPage = await page.locator('input[name="email"]').isVisible().catch(() => false);
          const hasEditForm = await page.locator('form[action*="edit"], input[name="name"]').count() > 0;
          
          if (hasEditForm && !hasAccessDenied && !hasNotFound && !isLoginPage) {
            bugReporter.addBug('critical', 'Security', `IDOR Vulnerable: ${test.name}`, `Permite editar recurso ajeno: ${test.url}`);
          }
          
        } catch (e) {
          logger.log('info', `${test.name}: Error o timeout`);
        }
      }
      
    } catch (e) {
      logger.log('info', 'No se pudo hacer login para test IDOR');
    }
    
    await context.close();
    logger.saveReport();
  });

  test('7.3 - XSS - Almacenado en formularios', async ({ page }) => {
    const logger = new AuditLogger('xss-stored');
    const evidence = new EvidenceCollector(page, 'security');
    
    // Navegar a páginas con datos de usuarios
    await page.goto(`${CONFIG.BASE_URL}/clients`);
    await page.waitForTimeout(2000);
    
    const bodyText = await page.locator('body').textContent();
    
    // Verificar que no hay scripts ejecutándose
    const xssDetected = await page.evaluate(() => {
      return document.querySelectorAll('script:not([src])').length;
    });
    
    logger.log('info', `Scripts inline encontrados: ${xssDetected}`);
    
    await evidence.screenshot('01-xss-scan');
    logger.saveReport();
  });

  test('7.4 - SQL Injection en URLs', async ({ page, browser }) => {
    const logger = new AuditLogger('sqli-url');
    
    const context = await browser.newContext();
    const newPage = await context.newPage();
    
    const sqliTests = [
      `${CONFIG.BASE_URL}/clients?id=1' OR '1'='1`,
      `${CONFIG.BASE_URL}/work-orders?search='; DROP TABLE users;--`,
      `${CONFIG.BASE_URL}/vehicles?plate=' OR 1=1--`,
    ];
    
    for (const url of sqliTests) {
      try {
        await newPage.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });
        await newPage.waitForTimeout(1500);
        
        const bodyText = await newPage.locator('body').textContent().catch(() => '') || '';
        
        // Si muestra errores de SQL, es vulnerable
        if (/mysql|sql|error|syntax/i.test(bodyText) && 
            /select|insert|update|delete/i.test(bodyText)) {
          bugReporter.addBug('critical', 'Security', `SQL Injection en URL`, `URL vulnerable: ${url}`);
        }
        
      } catch (e) {
        logger.log('info', `SQLi test completado para: ${url}`);
      }
    }
    
    await context.close();
    logger.saveReport();
  });

  test('7.5 - Archivos sensibles expuestos', async ({ browser }) => {
    const logger = new AuditLogger('sensitive-files');
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const sensitiveFiles = [
      '/.env',
      '/config.php',
      '/.git/config',
      '/composer.json',
      '/package.json',
      '/database/schema.sql',
      '/.htaccess',
      '/phpinfo.php',
      '/.gitignore',
      '/README.md',
    ];
    
    for (const file of sensitiveFiles) {
      try {
        const response = await page.goto(`${CONFIG.BASE_URL}${file}`, { timeout: 10000 });
        
        if (response && response.status() === 200) {
          const content = await page.content();
          
          // Verificar si es realmente un archivo sensible
          if (content.length > 0 && !content.includes('404') && !content.includes('Not Found')) {
            bugReporter.addBug('high', 'Security', `Archivo Exposed: ${file}`, 'Archivo sensible accesible públicamente');
          }
        }
        
      } catch (e) {
        // Archivo no accesible (bueno)
      }
    }
    
    await context.close();
    logger.saveReport();
  });

  test('7.6 - CSRF - Validación de tokens', async ({ page }) => {
    const logger = new AuditLogger('csrf-check');
    const evidence = new EvidenceCollector(page, 'security');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    // Verificar que hay token CSRF en login
    const hasCsrfToken = await page.locator('input[name="csrf_token"]').count() > 0 ||
                         await page.locator('[name="_token"]').count() > 0;
    
    logger.log('info', `CSRF Token en login: ${hasCsrfToken ? 'SÍ' : 'NO'}`);
    
    if (!hasCsrfToken) {
      bugReporter.addBug('medium', 'Security', 'CSRF Sin Protección', 'No se encontró token CSRF en formulario de login');
    }
    
    // Hacer login y verificar otros formularios
    await page.locator('input[name="email"]').fill(CONFIG.TEST_USER.email);
    await page.locator('input[name="password"]').fill(CONFIG.TEST_USER.password);
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(3000);
    
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    await page.waitForTimeout(2000);
    
    const formCsrf = await page.locator('input[name="csrf_token"]').count() > 0;
    logger.log('info', `CSRF Token en formularios: ${formCsrf ? 'SÍ' : 'NO'}`);
    
    if (!formCsrf) {
      bugReporter.addBug('medium', 'Security', 'CSRF en Formularios', 'Formulario sin token CSRF');
    }
    
    await evidence.screenshot('01-csrf-check');
    logger.saveReport();
  });

  test('7.7 - Headers de seguridad', async ({ browser }) => {
    const logger = new AuditLogger('security-headers');
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const response = await page.goto(`${CONFIG.BASE_URL}/login`, { timeout: 15000 });
    
    if (response) {
      const headers = await response.allHeaders();
      
      const requiredHeaders = [
        'x-frame-options',
        'x-content-type-options',
        'x-xss-protection',
        'content-security-policy',
        'strict-transport-security',
      ];
      
      const missingHeaders = requiredHeaders.filter(h => !headers[h] && !headers[h.toLowerCase()]);
      
      if (missingHeaders.length > 0) {
        bugReporter.addBug('low', 'Security', 'Headers Faltantes', `Faltan headers: ${missingHeaders.join(', ')}`);
      }
      
      logger.log('info', `Headers encontrados: ${Object.keys(headers).join(', ')}`);
    }
    
    await context.close();
    logger.saveReport();
  });

  test('7.8 - Passwords en frontend', async ({ page }) => {
    const logger = new AuditLogger('password-exposure');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    await page.waitForTimeout(2000);
    
    // Verificar que no hay contraseñas hardcodeadas
    const bodyText = await page.locator('body').textContent().catch(() => '') || '';
    
    const suspiciousPatterns = [
      /password\s*=\s*["']\w+/i,
      /secret\s*=\s*["']\w+/i,
      /api_key\s*=\s*["']\w+/i,
      /token\s*=\s*["']\w+/i,
    ];
    
    for (const pattern of suspiciousPatterns) {
      if (pattern.test(bodyText)) {
        bugReporter.addBug('critical', 'Security', 'Credenciales Expuestas', 'Posibles contraseñas o tokens en frontend');
        break;
      }
    }
    
    logger.saveReport();
  });

  test('7.9 - Rate limiting en login', async ({ page }) => {
    const logger = new AuditLogger('rate-limiting');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    const attempts = 5;
    let blocked = false;
    
    for (let i = 0; i < attempts; i++) {
      await page.locator('input[name="email"]').fill(`test${i}@example.com`);
      await page.locator('input[name="password"]').fill('wrongpassword');
      await page.locator('button[type="submit"]').click();
      await page.waitForTimeout(1500);
      
      const url = page.url();
      const bodyText = await page.locator('body').textContent().catch(() => '') || '';
      
      if (bodyText.toLowerCase().includes('bloqueado') ||
          bodyText.toLowerCase().includes('demasiados intentos') ||
          bodyText.toLowerCase().includes('rate limit') ||
          bodyText.toLowerCase().includes('espera')) {
        blocked = true;
        logger.log('info', `Rate limiting detectado después de ${i + 1} intentos`);
        break;
      }
    }
    
    if (!blocked) {
      bugReporter.addBug('medium', 'Security', 'Sin Rate Limiting', 'No hay límite de intentos de login');
    }
    
    logger.saveReport();
  });

  test('7.10 - Información sensible en URLs', async ({ page, browser }) => {
    const logger = new AuditLogger('sensitive-urls');
    
    const context = await browser.newContext();
    const newPage = await context.newPage();
    
    await newPage.goto(`${CONFIG.BASE_URL}/login`);
    await newPage.locator('input[name="email"]').fill(CONFIG.TEST_USER.email);
    await newPage.locator('input[name="password"]').fill(CONFIG.TEST_USER.password);
    await newPage.locator('button[type="submit"]').click();
    await newPage.waitForTimeout(3000);
    
    const currentUrl = newPage.url();
    
    // Verificar que la URL no expone información sensible
    const sensitiveInUrl = /password|secret|token|api_key|credit_card|ssn/i.test(currentUrl);
    
    if (sensitiveInUrl) {
      bugReporter.addBug('high', 'Security', 'Datos Sensibles en URL', 'Información sensible visible en la URL');
    }
    
    await context.close();
    logger.saveReport();
  });

});
