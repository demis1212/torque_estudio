import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin } from './utils/test-helpers';

const bugReporter = new BugReporter();

// Viewports para testing responsive
const viewports = [
  { name: 'Desktop HD', width: 1920, height: 1080 },
  { name: 'Laptop', width: 1366, height: 768 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Mobile Android', width: 375, height: 667 },
  { name: 'iPhone', width: 390, height: 844 },
];

test.describe('📱 Módulo Responsive', () => {
  
  test('6.1 - Dashboard en todos los dispositivos', async ({ page }) => {
    const logger = new AuditLogger('responsive-dashboard');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    for (const viewport of viewports) {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto(`${CONFIG.BASE_URL}/dashboard`);
      await page.waitForTimeout(2000);
      
      await evidence.screenshot(`dashboard-${viewport.name.toLowerCase().replace(/\s+/g, '-')}`);
      
      // Verificar que no hay scroll horizontal
      const hasHorizontalScroll = await page.evaluate(() => {
        return document.documentElement.scrollWidth > window.innerWidth;
      });
      
      if (hasHorizontalScroll && viewport.width < 768) {
        bugReporter.addBug('medium', 'Responsive', `Scroll Horizontal en ${viewport.name}`, 'Hay scroll horizontal en móvil');
      }
      
      logger.log('info', `${viewport.name}: ${viewport.width}x${viewport.height}`);
    }
    
    logger.saveReport();
  });

  test('6.2 - Sidebar colapsable en móvil', async ({ page }) => {
    const logger = new AuditLogger('responsive-sidebar');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    // Probar en móvil
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    
    await evidence.screenshot('01-mobile-sidebar-closed');
    
    // Buscar botón de toggle
    const toggleBtn = page.locator('.sidebar-toggle, .menu-toggle, button:has(.fa-bars), button:has(.fa-menu)').first();
    
    if (await toggleBtn.isVisible().catch(() => false)) {
      await toggleBtn.click();
      await page.waitForTimeout(1000);
      await evidence.screenshot('02-mobile-sidebar-open');
      
      // Verificar que el menú se abrió
      const sidebar = page.locator('.sidebar, .mobile-menu, nav.sidebar').first();
      const isVisible = await sidebar.isVisible().catch(() => false);
      
      if (!isVisible) {
        bugReporter.addBug('medium', 'Responsive', 'Sidebar No Se Abre', 'El menú móvil no se despliega');
      }
    } else {
      bugReporter.addBug('low', 'Responsive', 'Sin Botón Toggle Móvil', 'No hay botón para abrir menú en móvil');
    }
    
    logger.saveReport();
  });

  test('6.3 - Tablas responsive', async ({ page }) => {
    const logger = new AuditLogger('responsive-tables');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    // Probar tabla de clientes en móvil
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/clients`);
    await page.waitForTimeout(2000);
    
    await evidence.screenshot('01-mobile-clients-table');
    
    const table = page.locator('table').first();
    const hasTable = await table.isVisible().catch(() => false);
    
    if (hasTable) {
      // Verificar si la tabla tiene scroll horizontal
      const tableWidth = await table.evaluate(el => el.scrollWidth);
      const viewportWidth = 375;
      
      if (tableWidth > viewportWidth) {
        logger.log('info', 'Tabla con scroll horizontal (esperado en móvil)');
      }
    }
    
    logger.saveReport();
  });

  test('6.4 - Formularios en móvil', async ({ page }) => {
    const logger = new AuditLogger('responsive-forms');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    await page.waitForTimeout(2000);
    
    await evidence.screenshot('01-mobile-form');
    
    // Verificar que los inputs son accesibles
    const inputs = await page.locator('input, textarea, select').all();
    let accessibleInputs = 0;
    
    for (const input of inputs.slice(0, 5)) {
      const isVisible = await input.isVisible().catch(() => false);
      if (isVisible) accessibleInputs++;
    }
    
    logger.log('info', `Inputs accesibles: ${accessibleInputs}`);
    
    if (accessibleInputs === 0) {
      bugReporter.addBug('high', 'Responsive', 'Formularios No Accesibles', 'Los inputs no son visibles en móvil');
    }
    
    logger.saveReport();
  });

  test('6.5 - Botones en móvil', async ({ page }) => {
    const logger = new AuditLogger('responsive-buttons');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    await evidence.screenshot('01-mobile-buttons');
    
    // Verificar que los botones son clickeables
    const buttons = await page.locator('button, .btn, a.btn').all();
    let clickableButtons = 0;
    
    for (const btn of buttons.slice(0, 5)) {
      const box = await btn.boundingBox().catch(() => null);
      if (box && box.width >= 44 && box.height >= 44) {
        clickableButtons++;
      }
    }
    
    logger.log('info', `Botones clickeables (44x44px): ${clickableButtons}`);
    
    logger.saveReport();
  });

  test('6.6 - Texto legible en todos los tamaños', async ({ page }) => {
    const logger = new AuditLogger('responsive-text');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    for (const viewport of viewports) {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto(`${CONFIG.BASE_URL}/dashboard`);
      await page.waitForTimeout(1500);
      
      // Verificar tamaño mínimo de fuente
      const smallText = await page.evaluate(() => {
        const elements = Array.from(document.querySelectorAll('p, span, td, label'));
        let tooSmall = 0;
        for (const el of elements) {
          const size = parseInt(window.getComputedStyle(el).fontSize);
          if (size < 12) tooSmall++;
        }
        return tooSmall;
      });
      
      if (smallText > 5) {
        bugReporter.addBug('low', 'Responsive', `Texto Pequeño en ${viewport.name}`, `${smallText} elementos con fuente < 12px`);
      }
    }
    
    logger.saveReport();
  });

  test('6.7 - Imágenes y media responsive', async ({ page }) => {
    const logger = new AuditLogger('responsive-media');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    
    // Verificar imágenes
    const images = await page.locator('img').all();
    let brokenImages = 0;
    let oversizedImages = 0;
    
    for (const img of images.slice(0, 10)) {
      try {
        const src = await img.getAttribute('src');
        const box = await img.boundingBox().catch(() => null);
        
        if (src && box) {
          if (box.width > 375) {
            oversizedImages++;
          }
        } else {
          brokenImages++;
        }
      } catch (e) {
        brokenImages++;
      }
    }
    
    logger.log('info', `Imágenes: ${images.length}, Rotas: ${brokenImages}, Grandes: ${oversizedImages}`);
    
    if (brokenImages > 0) {
      bugReporter.addBug('medium', 'Responsive', 'Imágenes Rotas', `${brokenImages} imágenes no cargan`);
    }
    
    logger.saveReport();
  });

  test('6.8 - Touch targets en móvil', async ({ page }) => {
    const logger = new AuditLogger('touch-targets');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    
    // Verificar elementos clickeables
    const clickables = await page.locator('button, a, input, select, [role="button"]').all();
    let smallTargets = 0;
    
    for (const el of clickables.slice(0, 15)) {
      const box = await el.boundingBox().catch(() => null);
      if (box && (box.width < 44 || box.height < 44)) {
        smallTargets++;
      }
    }
    
    logger.log('info', `Elementos clickeables pequeños (< 44px): ${smallTargets}`);
    
    if (smallTargets > 3) {
      bugReporter.addBug('low', 'Responsive', 'Touch Targets Pequeños', `${smallTargets} elementos < 44x44px (recomendado para touch)`);
    }
    
    logger.saveReport();
  });

  test('6.9 - Rotación de pantalla', async ({ page }) => {
    const logger = new AuditLogger('orientation-change');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    // Probar en iPad (landscape vs portrait)
    await page.setViewportSize({ width: 768, height: 1024 }); // Portrait
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-ipad-portrait');
    
    await page.setViewportSize({ width: 1024, height: 768 }); // Landscape
    await page.waitForTimeout(2000);
    await evidence.screenshot('02-ipad-landscape');
    
    logger.log('info', 'Rotación de pantalla completada');
    logger.saveReport();
  });

  test('6.10 - Modal/Dialog en móvil', async ({ page }) => {
    const logger = new AuditLogger('responsive-modals');
    const evidence = new EvidenceCollector(page, 'responsive');
    
    await robustLogin(page);
    
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/clients`);
    await page.waitForTimeout(2000);
    
    // Intentar abrir un modal (si existe)
    const modalTrigger = page.locator('button:has-text("Nuevo"), .btn-primary, [data-toggle="modal"]').first();
    
    if (await modalTrigger.isVisible().catch(() => false)) {
      await modalTrigger.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-mobile-modal');
      
      // Verificar que el modal cabe en pantalla
      const modal = page.locator('.modal, .dialog, [role="dialog"]').first();
      const box = await modal.boundingBox().catch(() => null);
      
      if (box && box.width > 375) {
        bugReporter.addBug('medium', 'Responsive', 'Modal Desborda Pantalla', 'El modal es más ancho que la pantalla móvil');
      }
    }
    
    logger.saveReport();
  });

});
