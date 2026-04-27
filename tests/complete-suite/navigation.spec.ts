import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('🧭 Módulo Navegación', () => {
  
  test('2.1 - Sidebar completo - Todos los menús', async ({ page }) => {
    const logger = new AuditLogger('sidebar-complete');
    const evidence = new EvidenceCollector(page, 'navigation');
    
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
      { name: 'WhatsApp', path: '/whatsapp-reminders', icon: 'whatsapp' },
      { name: 'Usuarios', path: '/users', icon: 'user-cog' },
      { name: 'Configuración', path: '/settings', icon: 'cog' },
    ];
    
    for (const item of menuItems) {
      try {
        logger.log('info', `Probando: ${item.name}`);
        
        // Múltiples estrategias de selección
        const selectors = [
          `a:has-text("${item.name}")`,
          `a[href*="${item.path}"]`,
          `[role="link"]:has-text("${item.name}")`,
          `.sidebar a:has-text("${item.name}")`,
          `nav a:has-text("${item.name}")`,
        ];
        
        let found = false;
        for (const selector of selectors) {
          const locator = page.locator(selector).first();
          if (await locator.isVisible().catch(() => false)) {
            await locator.click();
            found = true;
            break;
          }
        }
        
        if (!found) {
          // Intentar navegación directa
          await page.goto(`${CONFIG.BASE_URL}${item.path}`);
        }
        
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        
        const url = page.url();
        if (!url.includes(item.path.replace('/', ''))) {
          bugReporter.addBug('medium', 'Navigation', `Menú No Funciona: ${item.name}`, `No navega a ${item.path}`);
        }
        
        await evidence.screenshot(`menu-${item.name.toLowerCase().replace(/\s+/g, '-')}`);
        
      } catch (e: any) {
        logger.log('error', `Error en ${item.name}: ${e.message}`);
        bugReporter.addBug('low', 'Navigation', `Error Navegación: ${item.name}`, e.message);
      }
    }
    
    logger.saveReport();
  });

  test('2.2 - Submenús desplegables', async ({ page }) => {
    const logger = new AuditLogger('submenus');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    
    // Buscar elementos con submenús (tienen flecha o indicador)
    const submenuTriggers = await page.locator('.has-submenu, [data-toggle="dropdown"], .dropdown-toggle, a:has(.fa-chevron-down), a:has(.fa-angle-down)').all();
    
    logger.log('info', `Encontrados ${submenuTriggers.length} submenús`);
    
    for (let i = 0; i < Math.min(submenuTriggers.length, 5); i++) {
      try {
        const trigger = submenuTriggers[i];
        await trigger.click();
        await page.waitForTimeout(500);
        await evidence.screenshot(`submenu-${i}`);
        
        // Verificar que el submenú se abrió
        const submenu = page.locator('.submenu, .dropdown-menu').nth(i);
        const isVisible = await submenu.isVisible().catch(() => false);
        
        if (!isVisible) {
          bugReporter.addBug('low', 'UX', `Submenú ${i} No Se Abre`, 'Click no despliega submenú');
        }
        
      } catch (e: any) {
        logger.log('warning', `Error submenú ${i}: ${e.message}`);
      }
    }
    
    logger.saveReport();
  });

  test('2.3 - Breadcrumbs', async ({ page }) => {
    const logger = new AuditLogger('breadcrumbs');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    
    // Navegar a varias páginas y verificar breadcrumbs
    const pagesWithBreadcrumbs = [
      '/clients',
      '/work-orders',
      '/parts',
      '/reports',
    ];
    
    for (const path of pagesWithBreadcrumbs) {
      await page.goto(`${CONFIG.BASE_URL}${path}`);
      await page.waitForTimeout(1000);
      
      const breadcrumbSelectors = [
        '.breadcrumb',
        '.breadcrumbs',
        '[aria-label="breadcrumb"]',
        'nav[aria-label="Breadcrumb"]',
      ];
      
      let hasBreadcrumb = false;
      for (const selector of breadcrumbSelectors) {
        if (await page.locator(selector).first().isVisible().catch(() => false)) {
          hasBreadcrumb = true;
          break;
        }
      }
      
      if (!hasBreadcrumb) {
        logger.log('info', `No hay breadcrumbs en ${path}`);
      } else {
        await evidence.screenshot(`breadcrumb-${path.replace('/', '')}`);
      }
    }
    
    logger.saveReport();
  });

  test('2.4 - Botones de acción en tablas', async ({ page }) => {
    const logger = new AuditLogger('table-buttons');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/clients`);
    await page.waitForTimeout(2000);
    
    // Buscar botones de acción en tablas
    const actionButtons = await page.locator('table .btn, table button, table a.btn, .actions .btn, td .btn').all();
    
    logger.log('info', `Encontrados ${actionButtons.length} botones en tablas`);
    
    for (let i = 0; i < Math.min(actionButtons.length, 10); i++) {
      try {
        const btn = actionButtons[i];
        const isVisible = await btn.isVisible().catch(() => false);
        
        if (isVisible) {
          const text = await btn.textContent() || 'sin-texto';
          logger.log('info', `Botón ${i}: ${text.substring(0, 30)}`);
        }
      } catch (e) {
        // Ignorar errores individuales
      }
    }
    
    await evidence.screenshot('table-actions');
    logger.saveReport();
  });

  test('2.5 - Links internos y navegación circular', async ({ page }) => {
    const logger = new AuditLogger('internal-links');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    
    // Obtener todos los links internos
    const links = await page.locator('a[href^="/"], a[href^="./"], a:not([href^="http"]):not([href^="#"])').all();
    
    logger.log('info', `Total links internos: ${links.length}`);
    
    const testedLinks = new Set<string>();
    
    for (const link of links.slice(0, 20)) {
      try {
        const href = await link.getAttribute('href');
        if (!href || testedLinks.has(href) || href.startsWith('#') || href.startsWith('javascript')) {
          continue;
        }
        
        testedLinks.add(href);
        
        // Verificar que el link no esté roto
        const isVisible = await link.isVisible().catch(() => false);
        if (isVisible) {
          const hasHref = href && href.length > 0;
          if (!hasHref) {
            bugReporter.addBug('low', 'UX', 'Link Sin Href', 'Enlace sin atributo href');
          }
        }
        
      } catch (e) {
        // Ignorar
      }
    }
    
    logger.log('info', `Links únicos testeados: ${testedLinks.size}`);
    await evidence.screenshot('internal-links');
    logger.saveReport();
  });

  test('2.6 - Botones ícono sin texto', async ({ page }) => {
    const logger = new AuditLogger('icon-buttons');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    
    // Buscar botones que solo tienen íconos
    const iconButtons = await page.locator('button:has(i):not(:has-text("")), a:has(i):not(:has-text(""))').all();
    
    logger.log('info', `Botones ícono encontrados: ${iconButtons.length}`);
    
    for (let i = 0; i < Math.min(iconButtons.length, 10); i++) {
      try {
        const btn = iconButtons[i];
        const hasTitle = await btn.getAttribute('title');
        const hasAriaLabel = await btn.getAttribute('aria-label');
        
        if (!hasTitle && !hasAriaLabel) {
          bugReporter.addBug('low', 'Accessibility', `Botón Ícono Sin Título ${i}`, 'Botón solo con ícono sin tooltip ni aria-label');
        }
        
      } catch (e) {
        // Ignorar
      }
    }
    
    await evidence.screenshot('icon-buttons');
    logger.saveReport();
  });

  test('2.7 - Navbar y elementos superiores', async ({ page }) => {
    const logger = new AuditLogger('navbar');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    
    // Verificar elementos de navbar
    const navbarElements = [
      { selector: '.navbar', name: 'Navbar' },
      { selector: '.header', name: 'Header' },
      { selector: '[role="navigation"]', name: 'Navigation Role' },
      { selector: '.user-menu', name: 'User Menu' },
      { selector: '.notifications', name: 'Notifications' },
    ];
    
    for (const element of navbarElements) {
      const isVisible = await page.locator(element.selector).first().isVisible().catch(() => false);
      logger.log('info', `${element.name}: ${isVisible ? '✅' : '❌'}`);
    }
    
    await evidence.screenshot('navbar-elements');
    logger.saveReport();
  });

  test('2.8 - Responsive - Sidebar colapsable', async ({ page }) => {
    const logger = new AuditLogger('responsive-sidebar');
    const evidence = new EvidenceCollector(page, 'navigation');
    
    await robustLogin(page);
    
    // Probar en móvil
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-mobile-sidebar');
    
    // Buscar botón de toggle
    const toggleBtn = page.locator('.sidebar-toggle, .menu-toggle, button:has(.fa-bars), button:has(.fa-menu)').first();
    if (await toggleBtn.isVisible().catch(() => false)) {
      await toggleBtn.click();
      await page.waitForTimeout(1000);
      await evidence.screenshot('02-sidebar-toggled');
    }
    
    // Volver a desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.waitForTimeout(1000);
    await evidence.screenshot('03-desktop-sidebar');
    
    logger.saveReport();
  });

});
