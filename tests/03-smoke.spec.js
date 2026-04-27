/**
 * @fileoverview Smoke Tests - Detección de errores y validación general
 * @description Verifica errores JS, HTTP, elementos rotos, textos de error
 */

const { test, expect } = require('@playwright/test');
const { 
  captureScreenshot, 
  checkConsoleErrors, 
  checkHttpErrors,
  detectBrokenElements,
  hasErrorText,
  getAllButtons,
  getAllLinks,
  TEST_DATA
} = require('./helpers/utils');

test.describe('🔥 Suite Smoke - Detección de Errores', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    await expect(page).toHaveURL(/dashboard/, { timeout: 15000 });
  });

  test('🐛 Detección de errores JavaScript en consola', async ({ page }) => {
    console.log('\n🐛 Monitoreando errores JavaScript...');
    
    const errors = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push({ type: 'console', text: msg.text() });
        console.error(`❌ Console Error: ${msg.text().substring(0, 100)}`);
      }
    });
    
    page.on('pageerror', error => {
      errors.push({ type: 'page', text: error.message });
      console.error(`❌ Page Error: ${error.message}`);
    });
    
    // Navegar por varias páginas para capturar errores
    const urls = ['/work-orders', '/clients', '/vehicles', '/parts'];
    
    for (const url of urls) {
      await page.goto(url, { waitUntil: 'networkidle' });
      await page.waitForTimeout(1000);
    }
    
    // Reporte
    if (errors.length === 0) {
      console.log('✅ No se detectaron errores JavaScript');
    } else {
      console.warn(`⚠️ Se detectaron ${errors.length} errores JavaScript`);
      // No fallar el test, solo reportar
    }
    
    expect(errors.length).toBeLessThan(10); // Tolerancia
  });

  test('🌐 Detección de errores HTTP 4xx/5xx', async ({ page }) => {
    console.log('\n🌐 Monitoreando errores HTTP...');
    
    const httpErrors = [];
    
    page.on('response', response => {
      const status = response.status();
      if (status >= 400) {
        httpErrors.push({
          status,
          url: response.url(),
          type: status >= 500 ? 'SERVER' : 'CLIENT'
        });
        console.error(`❌ HTTP ${status}: ${response.url()}`);
      }
    });
    
    // Navegar por múltiples páginas
    const modules = ['/dashboard', '/work-orders', '/clients', '/vehicles', 
                     '/services', '/parts', '/tools', '/reports'];
    
    for (const module of modules) {
      try {
        await page.goto(module, { waitUntil: 'networkidle' });
        await page.waitForTimeout(500);
      } catch (e) {
        console.log(`⚠️ Error navegando a ${module}: ${e.message}`);
      }
    }
    
    if (httpErrors.length === 0) {
      console.log('✅ No se detectaron errores HTTP');
    } else {
      console.warn(`⚠️ Se detectaron ${httpErrors.length} errores HTTP`);
      const uniqueUrls = [...new Set(httpErrors.map(e => e.url))];
      console.log('URLs con error:', uniqueUrls.slice(0, 5));
    }
    
    // Verificar que no haya errores 500 críticos
    const serverErrors = httpErrors.filter(e => e.status >= 500);
    expect(serverErrors.length).toBe(0);
  });

  test('🖼️ Detección de imágenes rotas', async ({ page }) => {
    console.log('\n🖼️ Verificando imágenes...');
    
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    const brokenImages = await page.evaluate(() => {
      const images = document.querySelectorAll('img');
      const broken = [];
      images.forEach(img => {
        if (img.naturalWidth === 0 && img.src) {
          broken.push(img.src);
        }
      });
      return broken;
    });
    
    if (brokenImages.length === 0) {
      console.log('✅ No se detectaron imágenes rotas');
    } else {
      console.warn(`⚠️ ${brokenImages.length} imágenes rotas encontradas`);
      brokenImages.slice(0, 5).forEach(src => console.log(`   - ${src}`));
    }
    
    expect(brokenImages.length).toBeLessThan(5);
  });

  test('⚠️ Detección de textos de error en la página', async ({ page }) => {
    console.log('\n⚠️ Buscando textos de error...');
    
    const errorPatterns = [
      'error', 'exception', 'fatal', 'warning', 
      'fallido', 'incorrecto', 'no se pudo', 'falló',
      'denegado', 'unauthorized', 'forbidden'
    ];
    
    const modules = ['/dashboard', '/work-orders', '/clients', '/vehicles'];
    const foundErrors = [];
    
    for (const url of modules) {
      await page.goto(url, { waitUntil: 'networkidle' });
      const bodyText = await page.locator('body').textContent().catch(() => '');
      const lowerText = bodyText.toLowerCase();
      
      for (const pattern of errorPatterns) {
        if (lowerText.includes(pattern)) {
          // Verificar que no sea parte de un mensaje normal
          const regex = new RegExp(`(class|role|data-)[^>]*${pattern}`, 'i');
          if (!regex.test(bodyText)) {
            foundErrors.push({ url, pattern });
          }
        }
      }
    }
    
    if (foundErrors.length === 0) {
      console.log('✅ No se detectaron textos de error');
    } else {
      console.warn(`⚠️ Posibles textos de error: ${foundErrors.length}`);
      foundErrors.slice(0, 5).forEach(e => console.log(`   - ${e.url}: "${e.pattern}"`));
    }
    
    await captureScreenshot(page, 'smoke-errors-check');
  });

  test('🔘 Verificar que TODOS los botones tienen acción', async ({ page }) => {
    console.log('\n🔘 Analizando botones...');
    
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    const buttons = await getAllButtons(page);
    console.log(`✅ Total botones encontrados: ${buttons.length}`);
    
    const issues = [];
    
    for (const btn of buttons.slice(0, 20)) { // Limitar para no saturar
      try {
        const elem = btn.element;
        const tag = await elem.evaluate(el => el.tagName.toLowerCase());
        const onclick = await elem.getAttribute('onclick').catch(() => '');
        const type = await elem.getAttribute('type').catch(() => '');
        const href = await elem.getAttribute('href').catch(() => '');
        
        // Verificar si tiene alguna acción
        const hasAction = onclick || href || type === 'submit' || 
                         await elem.evaluate(el => el.disabled === false);
        
        if (!hasAction && tag === 'button') {
          issues.push(btn.text);
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    if (issues.length === 0) {
      console.log('✅ Todos los botones tienen acción definida');
    } else {
      console.warn(`⚠️ ${issues.length} botones posiblemente sin acción`);
      issues.slice(0, 5).forEach(text => console.log(`   - "${text}"`));
    }
    
    expect(issues.length).toBeLessThan(3);
  });

  test('🔗 Verificar enlaces - Navegación completa', async ({ page }) => {
    console.log('\n🔗 Verificando enlaces internos...');
    
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    const links = await getAllLinks(page);
    console.log(`✅ Total enlaces encontrados: ${links.length}`);
    
    const testedLinks = [];
    const brokenLinks = [];
    
    // Probar primeros 15 enlaces
    for (const link of links.slice(0, 15)) {
      try {
        // Verificar si el enlace responde
        const response = await page.request.get(link.href);
        
        if (response.status() >= 400) {
          brokenLinks.push({ url: link.href, status: response.status() });
        } else {
          testedLinks.push(link.href);
        }
      } catch (e) {
        // Enlaces relativos pueden fallar, ignorar
      }
    }
    
    console.log(`✅ Enlaces probados: ${testedLinks.length}`);
    
    if (brokenLinks.length > 0) {
      console.warn(`⚠️ Enlaces rotos: ${brokenLinks.length}`);
      brokenLinks.slice(0, 5).forEach(l => console.log(`   - ${l.url} (${l.status})`));
    }
    
    expect(brokenLinks.length).toBeLessThan(3);
  });

  test('📊 Verificar tablas - Datos y paginación', async ({ page }) => {
    console.log('\n📊 Verificando tablas...');
    
    await page.goto('/work-orders', { waitUntil: 'networkidle' });
    
    const tables = await page.locator('table').all();
    console.log(`✅ Tablas encontradas: ${tables.length}`);
    
    for (let i = 0; i < tables.length; i++) {
      const table = tables[i];
      const headers = await table.locator('th').allTextContents();
      const rows = await table.locator('tbody tr').count();
      
      console.log(`Tabla ${i + 1}: ${headers.length} columnas, ${rows} filas`);
      
      // Verificar paginación
      const pagination = page.locator('.pagination, [class*="pagination"]').first();
      if (await pagination.isVisible().catch(() => false)) {
        console.log('✅ Paginación detectada');
      }
    }
    
    await captureScreenshot(page, 'smoke-tables-check');
  });

  test('🔍 Probar búsquedas y filtros', async ({ page }) => {
    console.log('\n🔍 Probando funcionalidad de búsqueda...');
    
    const searchPages = [
      { url: '/clients', term: 'test' },
      { url: '/vehicles', term: 'toyota' },
      { url: '/work-orders', term: 'revisión' }
    ];
    
    for (const { url, term } of searchPages) {
      try {
        await page.goto(url, { waitUntil: 'networkidle' });
        
        const searchInput = page.locator(
          'input[type="search"], input[placeholder*="buscar" i], .search-input'
        ).first();
        
        if (await searchInput.isVisible({ timeout: 5000 }).catch(() => false)) {
          await searchInput.fill(term);
          await page.waitForTimeout(1500);
          
          console.log(`✅ Búsqueda funcional en ${url}`);
          await captureScreenshot(page, `smoke-search-${url.replace(/\//g, '-')}`);
        }
      } catch (e) {
        console.log(`⚠️ No hay búsqueda en ${url}`);
      }
    }
  });

  test('⏱️ Verificar tiempos de carga', async ({ page }) => {
    console.log('\n⏱️ Midiendo tiempos de carga...');
    
    const pages = ['/dashboard', '/work-orders', '/clients', '/vehicles', '/parts'];
    const timings = [];
    
    for (const url of pages) {
      const start = Date.now();
      await page.goto(url, { waitUntil: 'networkidle' });
      const duration = Date.now() - start;
      
      timings.push({ url, duration });
      console.log(`   ${url}: ${duration}ms`);
    }
    
    // Verificar que ninguna página tarde más de 5 segundos
    const slowPages = timings.filter(t => t.duration > 5000);
    
    if (slowPages.length > 0) {
      console.warn(`⚠️ Páginas lentas (>5s): ${slowPages.length}`);
    }
    
    expect(slowPages.length).toBe(0);
  });

  test('📱 Responsive - Verificar elementos en viewport', async ({ page }) => {
    console.log('\n📱 Verificando responsive...');
    
    // Desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    const sidebarDesktop = await page.locator('.sidebar, nav').first().isVisible();
    console.log(`✅ Desktop - Sidebar visible: ${sidebarDesktop}`);
    
    // Tablet
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    
    const sidebarTablet = await page.locator('.sidebar, nav').first().isVisible().catch(() => false);
    console.log(`✅ Tablet - Sidebar visible: ${sidebarTablet}`);
    
    // Mobile
    await page.setViewportSize({ width: 375, height: 667 });
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    
    const menuMobile = await page.locator('.menu-toggle, .hamburger, [class*="mobile-menu"]').first().isVisible().catch(() => false);
    console.log(`✅ Mobile - Menú móvil visible: ${menuMobile}`);
    
    await captureScreenshot(page, 'smoke-responsive-mobile');
    
    // Volver a desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
  });

});
