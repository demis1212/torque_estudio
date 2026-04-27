import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('🔐 Módulo Autenticación', () => {
  
  test('1.1 - Login con credenciales válidas', async ({ page }) => {
    const logger = new AuditLogger('login-valido');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    await evidence.screenshot('01-login-page');
    
    await page.locator('input[name="email"]').fill(CONFIG.TEST_USER.email);
    await page.locator('input[name="password"]').fill(CONFIG.TEST_USER.password);
    await evidence.screenshot('02-credentials-filled');
    
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
    await evidence.screenshot('03-after-submit');
    
    const url = page.url();
    if (url.includes('dashboard') || url.includes('home')) {
      logger.log('info', '✅ Login exitoso');
    } else {
      bugReporter.addBug('critical', 'Authentication', 'Login Fallido', 'No redirige después de login válido');
      throw new Error('Login fallido');
    }
    
    logger.saveReport();
  });

  test('1.2 - Login con credenciales inválidas', async ({ page }) => {
    const logger = new AuditLogger('login-invalido');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    await page.locator('input[name="email"]').fill('invalid@email.com');
    await page.locator('input[name="password"]').fill('wrongpassword');
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-invalid-credentials');
    
    const bodyText = await page.locator('body').textContent();
    const hasError = bodyText?.toLowerCase().includes('error') || 
                     bodyText?.toLowerCase().includes('inválido') ||
                     bodyText?.toLowerCase().includes('incorrecto');
    
    if (!hasError) {
      bugReporter.addBug('medium', 'UX', 'Sin Mensaje Error Login', 'No muestra error con credenciales inválidas');
    }
    
    const url = page.url();
    if (url.includes('dashboard')) {
      bugReporter.addBug('critical', 'Security', 'Login Bypass', 'Permite acceso con credenciales inválidas');
    }
    
    logger.saveReport();
  });

  test('1.3 - Campos vacíos en login', async ({ page }) => {
    const logger = new AuditLogger('login-empty');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    // Intentar enviar vacío
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(1000);
    await evidence.screenshot('01-empty-submit');
    
    // Verificar que sigue en login
    const url = page.url();
    if (!url.includes('login')) {
      bugReporter.addBug('high', 'Security', 'Login Vacío Permitido', 'Acepta submit sin credenciales');
    }
    
    logger.saveReport();
  });

  test('1.4 - SQL Injection en login', async ({ page }) => {
    const logger = new AuditLogger('login-sqli');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    const sqlPayloads = [
      "' OR '1'='1",
      "' OR 1=1--",
      "admin'--",
      "' OR '1'='1' /*",
    ];
    
    for (const payload of sqlPayloads) {
      await page.locator('input[name="email"]').fill(payload);
      await page.locator('input[name="password"]').fill(payload);
      await page.locator('button[type="submit"]').click();
      await page.waitForTimeout(2000);
      
      const url = page.url();
      if (url.includes('dashboard')) {
        bugReporter.addBug('critical', 'Security', `SQL Injection Login: ${payload}`, 'Permite bypass con SQLi');
        break;
      }
      
      // Recargar para siguiente intento
      await page.goto(`${CONFIG.BASE_URL}/login`);
    }
    
    await evidence.screenshot('01-sqli-test');
    logger.saveReport();
  });

  test('1.5 - XSS en campos de login', async ({ page }) => {
    const logger = new AuditLogger('login-xss');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    const xssPayload = '<script>alert("XSS")</script>';
    
    await page.locator('input[name="email"]').fill(xssPayload);
    await page.locator('input[name="password"]').fill('test123');
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-xss-attempt');
    
    // Verificar si el script se ejecutó (no debería)
    const hasXSS = await page.evaluate(() => {
      return (window as any).xssDetected === true;
    }).catch(() => false);
    
    if (hasXSS) {
      bugReporter.addBug('critical', 'Security', 'XSS en Login', 'Permite ejecución de scripts en login');
    }
    
    logger.saveReport();
  });

  test('1.6 - Logout funcional', async ({ page }) => {
    const logger = new AuditLogger('logout');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await robustLogin(page);
    await evidence.screenshot('01-logged-in');
    
    // Buscar y hacer click en logout
    const logoutSelectors = [
      'a[href*="logout"]',
      'a:has-text("Salir")',
      'a:has-text("Logout")',
      'a:has-text("Cerrar sesión")',
    ];
    
    let logoutClicked = false;
    for (const selector of logoutSelectors) {
      const btn = page.locator(selector).first();
      if (await btn.isVisible().catch(() => false)) {
        await btn.click();
        logoutClicked = true;
        break;
      }
    }
    
    await page.waitForTimeout(2000);
    await evidence.screenshot('02-after-logout');
    
    const url = page.url();
    if (!url.includes('login') && !logoutClicked) {
      bugReporter.addBug('high', 'Authentication', 'Logout No Encontrado', 'No se encuentra botón de logout');
    } else if (!url.includes('login')) {
      bugReporter.addBug('medium', 'Authentication', 'Logout No Redirige', 'Logout no redirige a login');
    }
    
    logger.saveReport();
  });

  test('1.7 - Acceso sin sesión a rutas protegidas', async ({ page }) => {
    const logger = new AuditLogger('unauthorized-access');
    const evidence = new EvidenceCollector(page, 'auth');
    
    // Limpiar cookies/storage
    await page.context().clearCookies();
    
    const protectedRoutes = [
      '/dashboard',
      '/clients',
      '/work-orders',
      '/parts',
      '/reports',
      '/users',
    ];
    
    for (const route of protectedRoutes) {
      await page.goto(`${CONFIG.BASE_URL}${route}`);
      await page.waitForTimeout(1000);
      
      const url = page.url();
      if (!url.includes('login')) {
        bugReporter.addBug('critical', 'Security', `Ruta Sin Protección: ${route}`, `Accesible sin autenticación`);
      }
    }
    
    await evidence.screenshot('01-protected-routes');
    logger.saveReport();
  });

  test('1.8 - Recuperación de contraseña', async ({ page }) => {
    const logger = new AuditLogger('password-recovery');
    const evidence = new EvidenceCollector(page, 'auth');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    // Buscar link de recuperación
    const recoverySelectors = [
      'a:has-text("Olvidé")',
      'a:has-text("recuperar")',
      'a:has-text("password")',
      'a[href*="forgot"]',
      'a[href*="reset"]',
    ];
    
    let recoveryFound = false;
    for (const selector of recoverySelectors) {
      const link = page.locator(selector).first();
      if (await link.isVisible().catch(() => false)) {
        await link.click();
        recoveryFound = true;
        break;
      }
    }
    
    if (recoveryFound) {
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-recovery-page');
      
      // Probar con email válido
      const emailInput = page.locator('input[type="email"]').first();
      if (await emailInput.isVisible().catch(() => false)) {
        await emailInput.fill('test@example.com');
        await page.locator('button[type="submit"]').click();
        await page.waitForTimeout(2000);
        await evidence.screenshot('02-recovery-submit');
      }
    } else {
      logger.log('info', 'No se encontró página de recuperación');
    }
    
    logger.saveReport();
  });

});
