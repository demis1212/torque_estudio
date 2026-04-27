/**
 * @fileoverview Tests Exploratorios - Click recursivo en botones y elementos
 * @description Explora la aplicación haciendo click en todos los elementos interactivos
 */

const { test, expect } = require('@playwright/test');
const { captureScreenshot } = require('./helpers/utils');

test.describe('🔍 Suite Exploratoria - Recorrido Profundo', () => {
  
  test('🎪 Exploración completa del Dashboard', async ({ page }) => {
    console.log('\n🎪 Iniciando exploración del Dashboard...');
    
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    // Capturar estado inicial
    await captureScreenshot(page, 'exp-dashboard-initial', { fullPage: true });
    
    // 1. Click en TODOS los botones rápidos
    const quickButtons = [
      'Nueva Orden',
      'Nuevo Cliente', 
      'Nuevo Vehículo',
      'Nuevo Servicio',
      'Solicitar Tool',
      'Ver Reportes'
    ];
    
    for (const btnText of quickButtons) {
      try {
        const btn = page.getByRole('link', { name: new RegExp(btnText, 'i') }).first();
        if (await btn.isVisible({ timeout: 3000 }).catch(() => false)) {
          console.log(`   👆 Click en: ${btnText}`);
          await btn.click();
          await page.waitForTimeout(1500);
          await captureScreenshot(page, `exp-btn-${btnText.replace(/\s+/g, '-')}`);
          
          // Volver al dashboard
          await page.goto('/dashboard', { waitUntil: 'networkidle' });
          await page.waitForTimeout(1000);
        }
      } catch (e) {
        console.log(`   ⚠️ No se pudo probar: ${btnText}`);
      }
    }
    
    // 2. Click en widgets de estadísticas
    const statCards = await page.locator('.stat-card, [class*="stat"], .card').all();
    console.log(`\n   📊 Encontrados ${statCards.length} widgets de estadísticas`);
    
    for (let i = 0; i < Math.min(statCards.length, 3); i++) {
      try {
        const card = statCards[i];
        if (await card.isVisible()) {
          await card.click();
          await page.waitForTimeout(1000);
          
          // Si cambió la URL, volver
          if (!page.url().includes('/dashboard')) {
            await page.goBack();
            await page.waitForTimeout(1000);
          }
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    // 3. Click en enlaces del sidebar
    const sidebarLinks = await page.locator('.sidebar a, nav a').all();
    console.log(`   🔗 ${sidebarLinks.length} enlaces en sidebar`);
    
    for (let i = 0; i < Math.min(sidebarLinks.length, 5); i++) {
      try {
        const link = sidebarLinks[i];
        const href = await link.getAttribute('href').catch(() => '');
        const text = await link.textContent().catch(() => '');
        
        if (href && !href.startsWith('http') && !href.startsWith('#')) {
          console.log(`   👆 Probando enlace: ${text?.substring(0, 30) || href}`);
          await link.click();
          await page.waitForTimeout(1500);
          await page.goto('/dashboard', { waitUntil: 'networkidle' });
        }
      } catch (e) {
        // Ignorar
      }
    }
  });

  test('🔄 Exploración de Órdenes - Tabs, filtros, acciones', async ({ page }) => {
    console.log('\n🔄 Explorando módulo Órdenes...');
    
    await page.goto('/work-orders', { waitUntil: 'networkidle' });
    await captureScreenshot(page, 'exp-ordenes-inicio', { fullPage: true });
    
    // 1. Buscar y probar tabs
    const tabs = await page.locator('.tab, [role="tab"], .nav-tab').all();
    console.log(`   📑 Encontrados ${tabs.length} tabs`);
    
    for (let i = 0; i < Math.min(tabs.length, 5); i++) {
      try {
        const tab = tabs[i];
        if (await tab.isVisible({ timeout: 2000 }).catch(() => false)) {
          const text = await tab.textContent().catch(() => '');
          console.log(`   👆 Click en tab: ${text?.substring(0, 20)}`);
          await tab.click();
          await page.waitForTimeout(1000);
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    // 2. Probar filtros
    const filters = await page.locator('select, .filter, [class*="filter"]').all();
    console.log(`   🔍 Encontrados ${filters.length} filtros`);
    
    for (let i = 0; i < Math.min(filters.length, 3); i++) {
      try {
        const filter = filters[i];
        if (await filter.isVisible({ timeout: 2000 }).catch(() => false)) {
          const options = await filter.locator('option').all();
          if (options.length > 1) {
            await filter.selectOption({ index: 1 });
            await page.waitForTimeout(1000);
            console.log(`   ✅ Filtro ${i} aplicado`);
          }
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    // 3. Probar paginación
    const paginationLinks = await page.locator('.pagination a, .page-link').all();
    if (paginationLinks.length > 0) {
      console.log(`   📄 Probando paginación (${paginationLinks.length} páginas)`);
      
      // Click en página 2 si existe
      const page2 = paginationLinks.find(async link => {
        const text = await link.textContent().catch(() => '');
        return text.trim() === '2';
      });
      
      if (page2) {
        await page2.click();
        await page.waitForTimeout(1500);
        await captureScreenshot(page, 'exp-ordenes-pagina-2');
      }
    }
    
    // 4. Ver detalle de orden si existe
    const verLinks = await page.locator('a[href*="/work-orders/show/"], .view-order').all();
    if (verLinks.length > 0) {
      console.log(`   📋 Probando vista de orden`);
      await verLinks[0].click();
      await page.waitForTimeout(2000);
      await captureScreenshot(page, 'exp-orden-detalle');
      
      // Probar botones dentro de la orden
      const actionButtons = await page.locator('button, .btn-action').all();
      console.log(`      Encontrados ${actionButtons.length} botones de acción`);
    }
  });

  test('🔧 Exploración de Operación Inteligente', async ({ page }) => {
    console.log('\n🔧 Explorando Operación Inteligente...');
    
    try {
      await page.goto('/workshop-ops', { waitUntil: 'networkidle', timeout: 10000 });
      await captureScreenshot(page, 'exp-workshop-ops', { fullPage: true });
      
      // Buscar órdenes de trabajo
      const orders = await page.locator('.order-card, [class*="order"], .work-order').all();
      console.log(`   📋 Encontradas ${orders.length} órdenes`);
      
      if (orders.length > 0) {
        // Click en primera orden
        await orders[0].click();
        await page.waitForTimeout(2000);
        await captureScreenshot(page, 'exp-workshop-detalle');
        
        // Probar botones de tiempo
        const timeButtons = ['Iniciar', 'Pausar', 'Detener', 'Finalizar'];
        for (const btn of timeButtons) {
          const button = page.getByRole('button', { name: new RegExp(btn, 'i') }).first();
          if (await button.isVisible({ timeout: 2000 }).catch(() => false)) {
            console.log(`   👆 Botón "${btn}" disponible`);
          }
        }
        
        // Probar pestañas si existen
        const tabs = await page.locator('.tab, [role="tab"]').all();
        for (const tab of tabs.slice(0, 3)) {
          try {
            await tab.click();
            await page.waitForTimeout(1000);
          } catch (e) {}
        }
      }
    } catch (e) {
      console.log(`   ⚠️ No se pudo acceder a Operación Inteligente: ${e.message}`);
    }
  });

  test('📦 Exploración de Inventario - Stock y alertas', async ({ page }) => {
    console.log('\n📦 Explorando Inventario...');
    
    await page.goto('/parts', { waitUntil: 'networkidle' });
    await captureScreenshot(page, 'exp-inventario-inicio', { fullPage: true });
    
    // 1. Buscar alertas de stock bajo
    const alertas = await page.locator('.alert, .alert-low-stock, [class*="alert"]').all();
    console.log(`   ⚠️ Encontradas ${alertas.length} alertas`);
    
    if (alertas.length > 0) {
      for (let i = 0; i < Math.min(alertas.length, 2); i++) {
        try {
          await alertas[i].click();
          await page.waitForTimeout(1000);
          
          // Si abre modal o cambia página, volver
          if (!page.url().includes('/parts')) {
            await page.goto('/parts', { waitUntil: 'networkidle' });
          }
        } catch (e) {}
      }
    }
    
    // 2. Probar acciones en repuestos
    const actionLinks = await page.locator('a[href*="/parts/edit/"], .edit-part, .btn-edit').all();
    console.log(`   ✏️ Encontrados ${actionLinks.length} enlaces de edición`);
    
    if (actionLinks.length > 0) {
      await actionLinks[0].click();
      await page.waitForTimeout(2000);
      await captureScreenshot(page, 'exp-repuesto-edicion');
      await page.goBack();
    }
  });

  test('🔔 Exploración de Notificaciones', async ({ page }) => {
    console.log('\n🔔 Explorando Notificaciones...');
    
    try {
      await page.goto('/notifications', { waitUntil: 'networkidle' });
      await captureScreenshot(page, 'exp-notificaciones', { fullPage: true });
      
      // Verificar lista de notificaciones
      const notifs = await page.locator('.notification, [class*="notification"]').all();
      console.log(`   📨 Encontradas ${notifs.length} notificaciones`);
      
      // Probar "Marcar todo como leído"
      const marcarBtn = page.getByRole('button', { name: /Marcar todo/i });
      if (await marcarBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
        console.log('   👆 Click en "Marcar todo como leído"');
        await marcarBtn.click();
        await page.waitForTimeout(1500);
      }
      
      // Probar eliminar notificación
      const deleteBtns = await page.locator('button:has-text("Eliminar"), .delete-notification').all();
      if (deleteBtns.length > 1) {
        // No eliminar la primera, evitar problemas
        await captureScreenshot(page, 'exp-notificaciones-pre-delete');
      }
    } catch (e) {
      console.log(`   ⚠️ Error en notificaciones: ${e.message}`);
    }
  });

  test('📊 Exploración de Reportes - Gráficos y exportación', async ({ page }) => {
    console.log('\n📊 Explorando Reportes...');
    
    try {
      await page.goto('/reports', { waitUntil: 'networkidle' });
      await captureScreenshot(page, 'exp-reportes', { fullPage: true });
      
      // Buscar gráficos
      const charts = await page.locator('canvas, .chart, [class*="chart"]').all();
      console.log(`   📈 Encontrados ${charts.length} gráficos`);
      
      // Probar filtros de fecha
      const dateInputs = await page.locator('input[type="date"]').all();
      for (const input of dateInputs.slice(0, 2)) {
        try {
          await input.fill('2024-01-01');
          await page.waitForTimeout(500);
        } catch (e) {}
      }
      
      // Buscar botones de exportar
      const exportBtns = await page.locator('button:has-text("PDF"), button:has-text("Excel"), .export-btn').all();
      console.log(`   💾 Encontrados ${exportBtns.length} botones de exportación`);
      
      for (const btn of exportBtns) {
        const text = await btn.textContent().catch(() => '');
        console.log(`      - Botón: ${text?.substring(0, 30)}`);
      }
      
      // Probar productividad si existe enlace
      const prodLink = page.getByRole('link', { name: /Productividad/i });
      if (await prodLink.isVisible({ timeout: 3000 }).catch(() => false)) {
        await prodLink.click();
        await page.waitForURL('**/productivity', { timeout: 10000 });
        await captureScreenshot(page, 'exp-productividad');
      }
    } catch (e) {
      console.log(`   ⚠️ Error en reportes: ${e.message}`);
    }
  });

  test('🛠️ Exploración Completa de Herramientas', async ({ page }) => {
    console.log('\n🛠️ Explorando Herramientas...');
    
    await page.goto('/tools', { waitUntil: 'networkidle' });
    await captureScreenshot(page, 'exp-tools-inicio', { fullPage: true });
    
    // Explorar sub-módulos
    const submodulos = [
      { name: 'Mecánico', url: 'mechanic' },
      { name: 'Bodega|Warehouse', url: 'warehouse' },
      { name: 'Solicitudes', url: 'request' },
      { name: 'Mis Herramientas', url: 'my-tools' }
    ];
    
    for (const sub of submodulos) {
      try {
        const link = page.getByRole('link', { name: new RegExp(sub.name, 'i') }).first();
        if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
          console.log(`   👆 Explorando: ${sub.name}`);
          await link.click();
          await page.waitForTimeout(2000);
          await captureScreenshot(page, `exp-tools-${sub.url}`);
          await page.goto('/tools', { waitUntil: 'networkidle' });
        }
      } catch (e) {}
    }
  });

  test('🎯 Exploración recursiva - Todos los botones visibles', async ({ page }) => {
    console.log('\n🎯 Exploración intensiva de botones...');
    
    const visitedUrls = new Set();
    const maxIterations = 20;
    
    for (let i = 0; i < maxIterations; i++) {
      const currentUrl = page.url();
      if (visitedUrls.has(currentUrl)) continue;
      visitedUrls.add(currentUrl);
      
      console.log(`   Iteración ${i + 1}: ${currentUrl}`);
      
      // Encontrar todos los botones visibles
      const allButtons = await page.locator('button:not([disabled]), .btn:not(.disabled), [role="button"]').all();
      const visibleButtons = [];
      
      for (const btn of allButtons.slice(0, 10)) {
        try {
          if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) {
            const text = await btn.textContent().catch(() => '');
            visibleButtons.push({ element: btn, text: text?.substring(0, 30) });
          }
        } catch (e) {}
      }
      
      if (visibleButtons.length === 0) break;
      
      // Click en el primer botón que no sea peligroso
      for (const btn of visibleButtons) {
        const text = (btn.text || '').toLowerCase();
        
        // Evitar botones peligrosos
        if (text.includes('eliminar') || text.includes('borrar') || 
            text.includes('delete') || text.includes('logout') ||
            text.includes('salir')) {
          continue;
        }
        
        try {
          console.log(`   👆 Probando botón: "${btn.text}"`);
          await btn.element.click();
          await page.waitForTimeout(1500);
          
          // Capturar resultado
          await captureScreenshot(page, `exp-button-${i}-${btn.text.replace(/[^a-z0-9]/gi, '')}`);
          break;
        } catch (e) {
          // Continuar con siguiente botón
        }
      }
      
      // Navegar a una nueva página si estamos atascados
      if (page.url() === currentUrl) {
        const randomModules = ['/work-orders', '/clients', '/vehicles', '/parts'];
        const randomModule = randomModules[Math.floor(Math.random() * randomModules.length)];
        await page.goto(randomModule, { waitUntil: 'networkidle' });
      }
    }
    
    console.log(`   ✅ URLs visitadas: ${visitedUrls.size}`);
  });

});
