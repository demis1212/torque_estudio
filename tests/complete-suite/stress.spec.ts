import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin, measurePerformance } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('⚡ Módulo Rendimiento y Stress', () => {
  
  test('8.1 - Tiempo de carga - Login', async ({ page }) => {
    const logger = new AuditLogger('perf-login');
    const evidence = new EvidenceCollector(page, 'performance');
    
    const startTime = Date.now();
    await page.goto(`${CONFIG.BASE_URL}/login`);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;
    
    await evidence.screenshot('01-login-loaded');
    
    logger.log('info', `Tiempo de carga login: ${loadTime}ms`);
    
    if (loadTime > 5000) {
      bugReporter.addBug('medium', 'Performance', 'Login Lento', `Tiempo: ${loadTime}ms (límite: 5000ms)`);
    }
    
    expect(loadTime).toBeLessThan(10000);
    logger.saveReport();
  });

  test('8.2 - Tiempo de carga - Dashboard', async ({ page }) => {
    const logger = new AuditLogger('perf-dashboard');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    
    const startTime = Date.now();
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;
    
    await evidence.screenshot('01-dashboard-loaded');
    
    logger.log('info', `Tiempo de carga dashboard: ${loadTime}ms`);
    
    if (loadTime > 4000) {
      bugReporter.addBug('medium', 'Performance', 'Dashboard Lento', `Tiempo: ${loadTime}ms`);
    }
    
    logger.saveReport();
  });

  test('8.3 - Tiempo de carga - Listados', async ({ page }) => {
    const logger = new AuditLogger('perf-listings');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    
    const routes = [
      { path: '/clients', name: 'Clientes' },
      { path: '/vehicles', name: 'Vehículos' },
      { path: '/work-orders', name: 'Órdenes' },
      { path: '/parts', name: 'Inventario' },
    ];
    
    for (const route of routes) {
      const startTime = Date.now();
      await page.goto(`${CONFIG.BASE_URL}${route.path}`);
      await page.waitForLoadState('networkidle');
      const loadTime = Date.now() - startTime;
      
      logger.log('info', `${route.name}: ${loadTime}ms`);
      
      if (loadTime > 5000) {
        bugReporter.addBug('medium', 'Performance', `${route.name} Lento`, `Tiempo: ${loadTime}ms`);
      }
    }
    
    await evidence.screenshot('01-listings-loaded');
    logger.saveReport();
  });

  test('8.4 - Rendimiento de formularios', async ({ page }) => {
    const logger = new AuditLogger('perf-forms');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    
    // Navegar a formulario de cliente
    const startTime = Date.now();
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;
    
    await evidence.screenshot('01-form-loaded');
    
    logger.log('info', `Tiempo carga formulario: ${loadTime}ms`);
    
    // Medir tiempo de interacción
    const inputStart = Date.now();
    await page.locator('input[name="name"]').fill('Test Performance');
    await page.locator('input[name="email"]').fill('test@perf.com');
    const interactionTime = Date.now() - inputStart;
    
    logger.log('info', `Tiempo interacción: ${interactionTime}ms`);
    
    if (interactionTime > 2000) {
      bugReporter.addBug('low', 'Performance', 'Formulario Lento', `Input lag: ${interactionTime}ms`);
    }
    
    logger.saveReport();
  });

  test('8.5 - Consultas pesadas', async ({ page }) => {
    const logger = new AuditLogger('perf-queries');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    
    // Buscar con término que traiga muchos resultados
    await page.goto(`${CONFIG.BASE_URL}/clients`);
    await page.waitForTimeout(2000);
    
    const searchField = page.locator('input[type="search"]').first();
    if (await searchField.isVisible().catch(() => false)) {
      const startTime = Date.now();
      await searchField.fill('a'); // Buscar letra común
      await page.waitForTimeout(2000);
      const searchTime = Date.now() - startTime;
      
      await evidence.screenshot('01-search-performance');
      
      logger.log('info', `Tiempo de búsqueda: ${searchTime}ms`);
      
      if (searchTime > 3000) {
        bugReporter.addBug('medium', 'Performance', 'Búsqueda Lenta', `Tiempo: ${searchTime}ms`);
      }
    }
    
    logger.saveReport();
  });

  test('8.6 - Recursos pesados', async ({ page }) => {
    const logger = new AuditLogger('heavy-resources');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    await page.waitForLoadState('networkidle');
    
    // Analizar recursos cargados
    const resources = await page.evaluate(() => {
      return performance.getEntriesByType('resource').map((r: any) => ({
        name: r.name,
        duration: r.duration,
        size: r.transferSize,
      }));
    });
    
    const heavyResources = resources.filter((r: any) => r.duration > 1000 || r.size > 1000000);
    
    logger.log('info', `Total recursos: ${resources.length}`);
    logger.log('info', `Recursos pesados: ${heavyResources.length}`);
    
    if (heavyResources.length > 0) {
      bugReporter.addBug('low', 'Performance', 'Recursos Pesados', `${heavyResources.length} recursos > 1MB o > 1s`);
    }
    
    await evidence.screenshot('01-resources');
    logger.saveReport();
  });

  test('8.7 - Memory leaks', async ({ page }) => {
    const logger = new AuditLogger('memory-check');
    
    await robustLogin(page);
    
    // Navegar entre páginas múltiples veces
    const routes = ['/dashboard', '/clients', '/work-orders', '/parts'];
    
    for (let i = 0; i < 5; i++) {
      for (const route of routes) {
        await page.goto(`${CONFIG.BASE_URL}${route}`);
        await page.waitForTimeout(500);
      }
    }
    
    // Verificar memoria (si está disponible)
    const memory = await page.evaluate(() => {
      return (performance as any).memory ? {
        used: (performance as any).memory.usedJSHeapSize,
        total: (performance as any).memory.totalJSHeapSize,
      } : null;
    });
    
    if (memory) {
      const usedMB = memory.used / 1024 / 1024;
      logger.log('info', `Memoria usada: ${usedMB.toFixed(2)}MB`);
      
      if (usedMB > 100) {
        bugReporter.addBug('medium', 'Performance', 'Alto Uso de Memoria', `Usando ${usedMB.toFixed(2)}MB`);
      }
    }
    
    logger.saveReport();
  });

  test('8.8 - Navegación rápida (stress)', async ({ page }) => {
    const logger = new AuditLogger('stress-navigation');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    
    const routes = [
      '/dashboard',
      '/clients',
      '/work-orders',
      '/services',
      '/parts',
    ];
    
    const startTime = Date.now();
    
    // Navegar rápidamente entre páginas
    for (let i = 0; i < 10; i++) {
      for (const route of routes) {
        try {
          await page.goto(`${CONFIG.BASE_URL}${route}`, { timeout: 5000 });
          await page.waitForTimeout(200);
        } catch (e) {
          logger.log('warning', `Timeout en ${route}`);
        }
      }
    }
    
    const totalTime = Date.now() - startTime;
    logger.log('info', `Navegación stress completada: ${totalTime}ms`);
    
    await evidence.screenshot('01-stress-complete');
    
    if (totalTime > 60000) {
      bugReporter.addBug('medium', 'Performance', 'Navegación Lenta', `Stress test tomó ${totalTime}ms`);
    }
    
    logger.saveReport();
  });

  test('8.9 - Carga de imágenes', async ({ page }) => {
    const logger = new AuditLogger('image-loading');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(3000);
    
    const images = await page.locator('img').all();
    let brokenImages = 0;
    let slowImages = 0;
    
    for (const img of images.slice(0, 10)) {
      try {
        const startTime = Date.now();
        const loaded = await img.evaluate((el: HTMLImageElement) => el.complete && el.naturalHeight !== 0);
        const loadTime = Date.now() - startTime;
        
        if (!loaded) {
          brokenImages++;
        } else if (loadTime > 2000) {
          slowImages++;
        }
      } catch (e) {
        brokenImages++;
      }
    }
    
    logger.log('info', `Imágenes: ${images.length}, Rotas: ${brokenImages}, Lentas: ${slowImages}`);
    
    if (brokenImages > 0) {
      bugReporter.addBug('medium', 'Performance', 'Imágenes Rotas', `${brokenImages} imágenes no cargan`);
    }
    
    await evidence.screenshot('01-images-check');
    logger.saveReport();
  });

  test('8.10 - Tiempo hasta interactivo (TTI)', async ({ page }) => {
    const logger = new AuditLogger('tti-check');
    const evidence = new EvidenceCollector(page, 'performance');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    const tti = await page.evaluate(() => {
      return new Promise((resolve) => {
        if ('PerformanceObserver' in window) {
          const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
              if ((entry as any).entryType === 'first-input') {
                resolve(Date.now());
                observer.disconnect();
              }
            }
          });
          observer.observe({ entryTypes: ['first-input'] as any });
          
          // Fallback
          setTimeout(() => resolve(Date.now()), 5000);
        } else {
          resolve(Date.now());
        }
      });
    });
    
    logger.log('info', `Time to Interactive: ${tti}`);
    
    await evidence.screenshot('01-tti-check');
    logger.saveReport();
  });

});
