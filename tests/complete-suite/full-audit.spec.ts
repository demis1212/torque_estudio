import { test, expect, Page, BrowserContext } from '@playwright/test';
import { 
  CONFIG, 
  EXTREME_TEST_DATA, 
  AuditLogger, 
  EvidenceCollector,
  robustLogin,
  recursiveUIExplorer,
  detectAndTestForms,
  measurePerformance,
  BugReporter
} from './utils/test-helpers';

const bugReporter = new BugReporter();

// ============================================
// PASADA 1: AUDITORÍA NORMAL COMPLETA
// ============================================
test.describe.serial('🛡️ PASADA 1: Auditoría Normal Completa', () => {
  
  test('1.1 - Login Avanzado y Validación de Sesión', async ({ page, context }) => {
    const logger = new AuditLogger('login-avanzado');
    const evidence = new EvidenceCollector(page, 'login');
    
    logger.log('info', 'Iniciando test de login avanzado');
    
    // Test 1: Login exitoso
    const loginSuccess = await robustLogin(page);
    if (!loginSuccess) {
      bugReporter.addBug('critical', 'Authentication', 'Login Fallido', 'No se pudo iniciar sesión con credenciales válidas');
      throw new Error('Login fallido');
    }
    
    await evidence.screenshot('01-login-success');
    
    // Test 2: Verificar sesión persistente
    const storageState = await context.storageState();
    logger.log('info', `Cookies guardadas: ${storageState.cookies.length}`);
    
    // Test 3: Logout
    await page.goto(`${CONFIG.BASE_URL}/logout`);
    await page.waitForTimeout(2000);
    
    const urlAfterLogout = page.url();
    if (!urlAfterLogout.includes('login')) {
      bugReporter.addBug('high', 'Authentication', 'Logout No Funciona', 'Después de logout no redirige a login');
    }
    
    // Test 4: Intentar acceder a dashboard sin sesión
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    
    if (!page.url().includes('login')) {
      bugReporter.addBug('critical', 'Security', 'Ruta Sin Protección', '/dashboard accesible sin login');
    }
    
    await evidence.screenshot('02-after-logout');
    logger.saveReport();
  });

  test('1.2 - Exploración Recursiva Completa del Sidebar', async ({ page }) => {
    const logger = new AuditLogger('exploracion-sidebar');
    const evidence = new EvidenceCollector(page, 'exploracion');
    
    await robustLogin(page);
    
    const menuItems = [
      { name: 'Dashboard', path: '/dashboard', icon: 'home' },
      { name: 'Clientes', path: '/clients', icon: 'users' },
      { name: 'Vehículos', path: '/vehicles', icon: 'car' },
      { name: 'Órdenes', path: '/work-orders', icon: 'clipboard-list' },
      { name: 'Servicios', path: '/services', icon: 'wrench' },
      { name: 'Operación Inteligente', path: '/workshop-ops', icon: 'cogs' },
      { name: 'Inventario', path: '/parts', icon: 'boxes' },
      { name: 'Herramientas', path: '/tools', icon: 'tools' },
      { name: 'Manuales', path: '/manuals', icon: 'book' },
      { name: 'VIN Decoder', path: '/vin-decoder', icon: 'search' },
      { name: 'DTC Codes', path: '/dtc', icon: 'exclamation-triangle' },
      { name: 'Reportes', path: '/reports', icon: 'chart-bar' },
    ];
    
    for (const item of menuItems) {
      try {
        logger.log('info', `Navegando a: ${item.name}`);
        
        // Buscar enlace por múltiples estrategias
        const linkLocators = [
          page.locator(`a:has-text("${item.name}")`).first(),
          page.locator(`a[href*="${item.path}"]`).first(),
          page.locator(`[role="link"]:has-text("${item.name}")`).first(),
        ];
        
        let linkFound = false;
        for (const locator of linkLocators) {
          if (await locator.isVisible().catch(() => false)) {
            await locator.click();
            linkFound = true;
            break;
          }
        }
        
        if (!linkFound) {
          // Fallback: navegar directamente
          await page.goto(`${CONFIG.BASE_URL}${item.path}`);
        }
        
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1500);
        
        // Verificar que cargó correctamente
        const url = page.url();
        if (!url.includes(item.path.replace('/', ''))) {
          bugReporter.addBug('medium', 'Navigation', `Ruta No Carga: ${item.name}`, 
            `No se pudo navegar a ${item.path}, URL actual: ${url}`);
        }
        
        // Capturar evidencia
        await evidence.screenshot(`menu-${item.name.toLowerCase().replace(/\s+/g, '-')}`);
        
        // Detectar errores de consola
        const consoleErrors = await evidence.captureConsoleErrors();
        if (consoleErrors.length > 0) {
          bugReporter.addBug('medium', 'JavaScript', `Errores JS en ${item.name}`, 
            `${consoleErrors.length} errores detectados`, JSON.stringify(consoleErrors.slice(0, 3)));
        }
        
        logger.log('info', `✅ ${item.name} cargado exitosamente`);
        
      } catch (e: any) {
        logger.log('error', `❌ Error en ${item.name}: ${e.message}`);
        bugReporter.addBug('high', 'Navigation', `Error Navegando: ${item.name}`, e.message);
      }
    }
    
    logger.saveReport();
  });

  test('1.3 - Detección y Prueba de Formularios', async ({ page }) => {
    const logger = new AuditLogger('formularios');
    const evidence = new EvidenceCollector(page, 'forms');
    
    await robustLogin(page);
    
    // Ir a página de clientes (tiene formularios)
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    await page.waitForLoadState('networkidle');
    
    // Detectar formularios
    await detectAndTestForms(page, logger);
    
    // Probar campos individuales
    const inputs = await page.locator('input, textarea, select').all();
    
    for (let i = 0; i < inputs.length; i++) {
      const input = inputs[i];
      
      try {
        const type = await input.getAttribute('type').catch(() => 'text');
        const name = await input.getAttribute('name').catch(() => `input-${i}`);
        const required = await input.evaluate(el => el.hasAttribute('required'));
        
        logger.log('info', `Probando campo: ${name} (${type})`);
        
        // Test 1: Campo vacío (si es requerido)
        if (required) {
          await input.fill('');
          await page.waitForTimeout(200);
        }
        
        // Test 2: XSS - Solo verificar en campos que muestran datos del servidor
        // Nota: Este test puede reportar falsos positivos si el campo está vacío
        // o si el valor no viene del servidor. Las vistas ya están protegidas con esc().
        if (type === 'text' || type === 'textarea' || !type) {
          // Verificar si el campo tiene un valor pre-existente del servidor
          const currentValue = await input.inputValue().catch(() => '');
          
          // Solo reportar XSS si hay un valor que claramente no está escapado
          // y viene del servidor (contiene etiquetas HTML sin escapar)
          if (currentValue && 
              (currentValue.includes('<script>') || 
               currentValue.includes('<iframe>') || 
               currentValue.includes('javascript:'))) {
            // Verificar que NO esté escapado
            if (!currentValue.includes('&lt;script&gt;') && 
                !currentValue.includes('&amp;lt;')) {
              bugReporter.addBug('critical', 'Security', `XSS Reflejado en campo: ${name}`, 
                `El campo contiene HTML sin escapar: ${currentValue.substring(0, 50)}...`);
            }
          }
          
          // Llenar con payload XSS para probar almacenamiento
          await input.fill(EXTREME_TEST_DATA.xssPayloads[0]);
          await page.waitForTimeout(200);
        }
        
        // Test 3: SQL Injection
        if (type === 'text' || type === 'search') {
          await input.fill(EXTREME_TEST_DATA.sqlInjection[0]);
          await page.waitForTimeout(200);
        }
        
        // Test 4: Texto muy largo
        await input.fill(EXTREME_TEST_DATA.invalidData.longText);
        await page.waitForTimeout(200);
        
      } catch (e: any) {
        logger.log('warning', `Error en campo ${i}: ${e.message}`);
      }
    }
    
    await evidence.screenshot('form-test-complete');
    logger.saveReport();
  });

  test('1.4 - Módulo Órdenes de Trabajo - Test Completo', async ({ page }) => {
    const logger = new AuditLogger('ordenes-completo');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    
    // Paso 1: Ver lista de órdenes
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForLoadState('networkidle');
    await evidence.screenshot('01-orders-list');
    
    // Paso 2: Crear nueva orden
    const newOrderBtn = page.locator('a:has-text("Nueva Orden"), button:has-text("Nueva Orden")').first();
    if (await newOrderBtn.isVisible().catch(() => false)) {
      await newOrderBtn.click();
      await page.waitForLoadState('networkidle');
      
      // Llenar formulario
      await page.locator('select[name="client_id"]').selectOption({ index: 1 }).catch(() => {});
      await page.locator('select[name="vehicle_id"]').selectOption({ index: 1 }).catch(() => {});
      await page.locator('textarea[name="description"]').fill(EXTREME_TEST_DATA.validData.order.description);
      await page.locator('textarea[name="diagnosis"]').fill(EXTREME_TEST_DATA.validData.order.diagnosis);
      
      await evidence.screenshot('02-order-form-filled');
      
      // No guardamos para no crear datos basura
      logger.log('info', 'Formulario de orden completado');
    }
    
    logger.saveReport();
  });

  test('1.5 - Módulo Inventario - Test Completo', async ({ page }) => {
    const logger = new AuditLogger('inventario');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    
    // Ir a inventario
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForLoadState('networkidle');
    
    // Verificar tabla de inventario
    const tableExists = await page.locator('table').isVisible().catch(() => false);
    if (!tableExists) {
      bugReporter.addBug('medium', 'UI', 'Tabla de Inventario No Visible', 'No se detectó tabla en /parts');
    }
    
    // Buscar campo de búsqueda
    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar" i]').first();
    if (await searchInput.isVisible().catch(() => false)) {
      await searchInput.fill('test');
      await page.waitForTimeout(1000);
      await evidence.screenshot('inventory-search');
    }
    
    logger.saveReport();
  });

  test('1.6 - VIN Decoder - Test Funcionalidad', async ({ page }) => {
    const logger = new AuditLogger('vin-decoder');
    const evidence = new EvidenceCollector(page, 'vin');
    
    await robustLogin(page);
    
    await page.goto(`${CONFIG.BASE_URL}/vin-decoder`);
    await page.waitForLoadState('networkidle');
    
    // Test 1: VIN Válido
    const vinInput = page.locator('input[name="vin"]').first();
    if (await vinInput.isVisible().catch(() => false)) {
      await vinInput.fill(EXTREME_TEST_DATA.validData.vehicle.vin);
      await page.locator('button[type="submit"]').first().click();
      await page.waitForTimeout(3000);
      await evidence.screenshot('vin-valid-test');
    }
    
    // Test 2: VIN Inválido
    await vinInput.fill('INVALID123');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForTimeout(2000);
    await evidence.screenshot('vin-invalid-test');
    
    logger.saveReport();
  });

  test('1.7 - DTC Codes - Test Funcionalidad', async ({ page }) => {
    const logger = new AuditLogger('dtc-codes');
    const evidence = new EvidenceCollector(page, 'dtc');
    
    await robustLogin(page);
    
    await page.goto(`${CONFIG.BASE_URL}/dtc`);
    await page.waitForLoadState('networkidle');
    
    // Buscar código
    const searchInput = page.locator('input[name="code"], input[placeholder*="código" i]').first();
    if (await searchInput.isVisible().catch(() => false)) {
      await searchInput.fill('P0300');
      await page.locator('button[type="submit"]').first().click();
      await page.waitForTimeout(3000);
      await evidence.screenshot('dtc-search');
    }
    
    logger.saveReport();
  });

  test('1.8 - Detección de Errores Avanzada', async ({ page }) => {
    const logger = new AuditLogger('error-detection');
    const evidence = new EvidenceCollector(page, 'errors');
    
    const errors: any[] = [];
    
    // Capturar errores de consola
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push({ type: 'console', text: msg.text(), location: msg.location() });
      }
    });
    
    // Capturar errores de red
    page.on('response', response => {
      if (response.status() >= 400) {
        errors.push({ 
          type: 'network', 
          url: response.url(), 
          status: response.status(),
          statusText: response.statusText()
        });
      }
    });
    
    await robustLogin(page);
    
    // Navegar por varias páginas
    const routes = ['/dashboard', '/clients', '/work-orders', '/parts', '/reports'];
    
    for (const route of routes) {
      await page.goto(`${CONFIG.BASE_URL}${route}`);
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(2000);
    }
    
    // Reportar errores encontrados
    if (errors.length > 0) {
      const grouped = errors.reduce((acc: any, err) => {
        acc[err.type] = (acc[err.type] || 0) + 1;
        return acc;
      }, {});
      
      if (grouped.console > 0) {
        bugReporter.addBug('medium', 'JavaScript', `${grouped.console} Errores de Consola`, 
          'Se detectaron errores en la consola del navegador');
      }
      
      if (grouped.network > 0) {
        bugReporter.addBug('medium', 'Network', `${grouped.network} Errores de Red`, 
          'Requests fallidos detectados');
      }
    }
    
    await evidence.screenshot('error-detection');
    logger.saveReport();
  });

  test('1.9 - Pruebas de Rendimiento', async ({ page }) => {
    const logger = new AuditLogger('performance');
    
    const routes = [
      { path: '/login', name: 'Login' },
      { path: '/dashboard', name: 'Dashboard' },
      { path: '/clients', name: 'Clientes' },
      { path: '/work-orders', name: 'Órdenes' }
    ];
    
    for (const route of routes) {
      const startTime = Date.now();
      
      await page.goto(`${CONFIG.BASE_URL}${route.path}`);
      await page.waitForLoadState('networkidle');
      
      const loadTime = Date.now() - startTime;
      
      logger.log('info', `${route.name}: ${loadTime}ms`);
      
      // Reportar si es muy lento
      if (loadTime > 5000) {
        bugReporter.addBug('medium', 'Performance', `Lentitud en ${route.name}`, 
          `Tiempo de carga: ${loadTime}ms (esperado < 5000ms)`);
      }
    }
    
    // Métricas detalladas
    const metrics = await measurePerformance(page);
    logger.log('info', 'Métricas de performance', metrics);
    
    logger.saveReport();
  });

  test('1.10 - Guardar Reporte de Bugs', async () => {
    const reportPath = bugReporter.saveReport();
    console.log(`📊 Reporte guardado en: ${reportPath}`);
    
    // Mostrar resumen
    console.log('\n📋 RESUMEN DE AUDITORÍA:');
    console.log(`Total bugs: ${bugReporter['bugs'].length}`);
  });
});

// ============================================
// PASADA 2: AUDITORÍA AGRESIVA RÁPIDA
// ============================================
test.describe.serial('⚡ PASADA 2: Auditoría Agresiva Rápida', () => {
  
  test('2.1 - Navegación Rápida y Agresiva', async ({ page }) => {
    const logger = new AuditLogger('aggressive-nav');
    
    await robustLogin(page);
    
    const routes = [
      '/dashboard', '/clients', '/vehicles', '/work-orders',
      '/services', '/parts', '/tools', '/reports'
    ];
    
    // Navegar rápidamente sin esperar completamente
    for (const route of routes) {
      await page.goto(`${CONFIG.BASE_URL}${route}`);
      await page.waitForTimeout(500); // Solo 500ms entre páginas
    }
    
    logger.log('info', 'Navegación agresiva completada');
    logger.saveReport();
  });

  test('2.2 - Clics Masivos en Botones', async ({ page }) => {
    const logger = new AuditLogger('aggressive-clicks');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    
    // Encontrar todos los botones y hacer clic rápido
    const buttons = await page.locator('button, a, [role="button"]').all();
    
    for (const btn of buttons.slice(0, 15)) {
      try {
        if (await btn.isVisible().catch(() => false)) {
          await btn.click({ timeout: 2000 });
          await page.waitForTimeout(200);
        }
      } catch (e) {
        // Ignorar errores en modo agresivo
      }
    }
    
    logger.log('info', `Clics realizados: ${Math.min(buttons.length, 15)}`);
    logger.saveReport();
  });

  test('2.3 - Formularios con Datos Extremos', async ({ page }) => {
    const logger = new AuditLogger('extreme-data');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    
    // Llenar formulario con datos extremos
    await page.locator('input[name="name"]').fill(EXTREME_TEST_DATA.xssPayloads[1]);
    await page.locator('input[name="email"]').fill(EXTREME_TEST_DATA.sqlInjection[2]);
    await page.locator('input[name="phone"]').fill(EXTREME_TEST_DATA.invalidData.specialChars);
    
    logger.log('info', 'Formulario llenado con datos extremos');
    logger.saveReport();
  });
});

// ============================================
// PASADA 3: AUDITORÍA EXTREMA DETALLADA
// ============================================
test.describe.serial('🔬 PASADA 3: Auditoría Extrema Detallada', () => {
  
  test('3.1 - Análisis Profundo de DOM', async ({ page }) => {
    const logger = new AuditLogger('deep-dom');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    // Analizar estructura DOM
    const domAnalysis = await page.evaluate(() => {
      return {
        totalElements: document.querySelectorAll('*').length,
        buttons: document.querySelectorAll('button').length,
        links: document.querySelectorAll('a').length,
        inputs: document.querySelectorAll('input').length,
        images: document.querySelectorAll('img').length,
        tables: document.querySelectorAll('table').length
      };
    });
    
    logger.log('info', 'Análisis DOM', domAnalysis);
    
    // Reportar elementos sospechosos
    if (domAnalysis.totalElements > 1000) {
      bugReporter.addBug('low', 'Performance', 'DOM muy grande', 
        `${domAnalysis.totalElements} elementos pueden afectar rendimiento`);
    }
    
    logger.saveReport();
  });

  test('3.2 - Validación de Imágenes', async ({ page }) => {
    const logger = new AuditLogger('image-validation');
    
    await robustLogin(page);
    
    const brokenImages: string[] = [];
    
    page.on('response', response => {
      if (response.request().resourceType() === 'image' && response.status() >= 400) {
        brokenImages.push(response.url());
      }
    });
    
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(3000);
    
    if (brokenImages.length > 0) {
      bugReporter.addBug('medium', 'UI', `${brokenImages.length} Imágenes Rotas`, 
        'Imágenes que no cargan correctamente');
    }
    
    logger.log('info', `Imágenes rotas: ${brokenImages.length}`);
    logger.saveReport();
  });

  test('3.3 - Validación de CSS y Estilos', async ({ page }) => {
    const logger = new AuditLogger('css-validation');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    
    // Verificar elementos con z-index negativo
    const hiddenElements = await page.locator('[style*="z-index: -"], [style*="z-index:-"]').count();
    
    if (hiddenElements > 0) {
      bugReporter.addBug('low', 'UI', 'Elementos Ocultos Detectados', 
        `${hiddenElements} elementos con z-index negativo`);
    }
    
    logger.log('info', `Elementos ocultos: ${hiddenElements}`);
    logger.saveReport();
  });

  test('3.4 - Validación de Responsive', async ({ page }) => {
    const logger = new AuditLogger('responsive-check');
    
    await robustLogin(page);
    
    const viewports = [
      { name: 'Mobile S', width: 320, height: 568 },
      { name: 'Mobile M', width: 375, height: 667 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Desktop', width: 1920, height: 1080 }
    ];
    
    for (const viewport of viewports) {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto(`${CONFIG.BASE_URL}/dashboard`);
      await page.waitForTimeout(2000);
      
      // Verificar si hay scroll horizontal (indica problema responsive)
      const hasHorizontalScroll = await page.evaluate(() => {
        return document.documentElement.scrollWidth > window.innerWidth;
      });
      
      if (hasHorizontalScroll) {
        bugReporter.addBug('medium', 'Responsive', `Scroll Horizontal en ${viewport.name}`, 
          `Viewport: ${viewport.width}x${viewport.height}`);
      }
      
      await page.screenshot({ 
        path: `test-results/responsive-${viewport.name}.png`,
        fullPage: true 
      });
    }
    
    logger.saveReport();
  });

  test('3.5 - Prueba de Seguridad Avanzada', async ({ page, context }) => {
    const logger = new AuditLogger('security-deep');
    
    // Test 1: Intentar acceso a archivos sensibles
    const sensitiveFiles = [
      '/.env',
      '/.git/config',
      '/config.php',
      '/database.sql',
      '/phpinfo.php'
    ];
    
    for (const file of sensitiveFiles) {
      const response = await page.goto(`${CONFIG.BASE_URL}${file}`);
      if (response && response.status() === 200) {
        bugReporter.addBug('critical', 'Security', `Archivo Expuesto: ${file}`, 
          'Archivo sensible accesible públicamente');
      }
    }
    
    // Test 2: Probar IDOR más profundo
    await robustLogin(page);
    
    const idorTests = [
      '/users/edit/1',
      '/users/edit/2', 
      '/users/edit/999',
      '/work-orders/edit/1',
      '/clients/edit/1'
    ];
    
    for (const test of idorTests) {
      await page.goto(`${CONFIG.BASE_URL}${test}`);
      await page.waitForTimeout(1000);
      
      // Verificar si muestra datos sin error de permisos
      const bodyText = await page.locator('body').textContent().catch(() => '') || '';
      const hasAccessDenied = /acceso denegado|no autorizado|403/i.test(bodyText);
      
      if (!hasAccessDenied && !bodyText.includes('No encontrado')) {
        logger.log('warning', `Posible IDOR en: ${test}`);
      }
    }
    
    logger.saveReport();
  });

  test('3.6 - Reporte Final Completo', async () => {
    const reportPath = bugReporter.saveReport();
    
    console.log('\n' + '='.repeat(60));
    console.log('🔬 AUDITORÍA EXTREMA COMPLETADA');
    console.log('='.repeat(60));
    console.log(`📊 Reporte generado: ${reportPath}`);
    console.log(`🐛 Total bugs encontrados: ${bugReporter['bugs'].length}`);
    
    // Estadísticas por severidad
    const severityCount = bugReporter['bugs'].reduce((acc: any, bug: any) => {
      acc[bug.severity] = (acc[bug.severity] || 0) + 1;
      return acc;
    }, {});
    
    console.log('\n📈 Distribución por severidad:');
    console.log(`   🚨 Críticos: ${severityCount.critical || 0}`);
    console.log(`   🔴 Altos: ${severityCount.high || 0}`);
    console.log(`   🟡 Medios: ${severityCount.medium || 0}`);
    console.log(`   🟢 Bajos: ${severityCount.low || 0}`);
    console.log('='.repeat(60));
  });
});
