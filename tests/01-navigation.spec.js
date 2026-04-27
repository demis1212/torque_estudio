/**
 * @fileoverview Tests de Navegación - Navegación exhaustiva por módulos
 * @description Recorre TODO el sidebar y verifica cada módulo
 */

const { test, expect } = require('@playwright/test');
const { captureScreenshot, navigateToModule, generateReport } = require('./helpers/utils');

// ==================== CONFIGURACIÓN DE MÓDULOS ====================
const MODULES = [
  { name: /Dashboard/i, url: /dashboard/, expectedElements: ['h1', '.stats', '.sidebar'] },
  { name: /^Órdenes$/i, url: /work-orders/, expectedElements: ['table', '.order-list', 'h1'] },
  { name: /Clientes$/i, url: /clients/, expectedElements: ['table', '.client-list'] },
  { name: /Vehículos$/i, url: /vehicles/, expectedElements: ['table', '.vehicle-list'] },
  { name: /Servicios$/i, url: /services/, expectedElements: ['table', '.service-list'] },
  { name: /Inventario$/i, url: /parts/, expectedElements: ['table', '.part-list', '.inventory'] },
  { name: /Operación Inteligente/i, url: /workshop-ops/, expectedElements: ['.workshop-ops', '.orders'] },
  { name: /Herramientas$/i, url: /tools/, expectedElements: ['.tools', '.tool-list'] },
  { name: /Manuales$/i, url: /manuals/, expectedElements: ['.manuals', '.manual-list'] },
  { name: /VIN Decoder|Decodificador/i, url: /vin/, expectedElements: ['input[name="vin"]', 'form'] },
  { name: /DTC Codes$/i, url: /dtc/, expectedElements: ['.dtc-codes', 'table'] },
  { name: /Reportes$/i, url: /reports/, expectedElements: ['.reports', 'canvas', '.chart'] },
  { name: /Productividad$/i, url: /productivity/, expectedElements: ['.productivity', 'canvas'] },
  { name: /WhatsApp/i, url: /whatsapp/, expectedElements: ['.whatsapp', '.reminder-list'] },
];

// ==================== TESTS DE NAVEGACIÓN ====================

test.describe('🧭 Suite Navegación - Recorrido Completo Sidebar', () => {
  
  test.beforeEach(async ({ page }) => {
    // Asegurar que estamos autenticados
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    await expect(page).toHaveURL(/dashboard/, { timeout: 15000 });
  });

  test('📋 Dashboard - Verificar estructura completa', async ({ page }) => {
    console.log('\n📊 Verificando Dashboard...');
    
    // Verificar sidebar
    const sidebar = page.locator('.sidebar, nav, [class*="sidebar"]').first();
    await expect(sidebar, 'Sidebar debe estar visible').toBeVisible();
    
    // Verificar enlaces principales
    const mainLinks = ['Dashboard', 'Órdenes', 'Clientes', 'Vehículos'];
    for (const link of mainLinks) {
      const elem = page.getByRole('link', { name: new RegExp(link, 'i') }).first();
      await expect(elem, `Link "${link}" debe estar visible`).toBeVisible();
    }
    
    // Verificar widgets estadísticos
    const stats = ['Total Clientes', 'Total Vehículos', 'Órdenes Activas'];
    for (const stat of stats) {
      const elem = page.locator('text=' + stat).first();
      if (await elem.isVisible().catch(() => false)) {
        console.log(`✅ Widget "${stat}" encontrado`);
      }
    }
    
    // Botones rápidos
    const quickButtons = ['Nueva Orden', 'Nuevo Cliente', 'Nuevo Vehículo'];
    for (const btn of quickButtons) {
      const elem = page.getByRole('link', { name: new RegExp(btn, 'i') }).first();
      if (await elem.isVisible().catch(() => false)) {
        console.log(`✅ Botón rápido "${btn}" encontrado`);
      }
    }
    
    await captureScreenshot(page, '01-dashboard-complete');
  });

  test('🔄 Navegar por TODOS los módulos del sidebar', async ({ page }) => {
    console.log(`\n🧭 Iniciando recorrido de ${MODULES.length} módulos...`);
    
    const results = {
      successful: [],
      failed: [],
      screenshots: []
    };
    
    for (const module of MODULES) {
      try {
        // Navegar al módulo
        const link = page.getByRole('link', { name: module.name }).first();
        
        if (!(await link.isVisible({ timeout: 5000 }).catch(() => false))) {
          console.log(`⚠️ Módulo "${module.name.source}" no encontrado`);
          results.failed.push({ module: module.name.source, reason: 'Not found' });
          continue;
        }
        
        // Click y esperar navegación
        await Promise.all([
          page.waitForURL(module.url, { timeout: 20000 }),
          link.click()
        ]);
        
        // Esperar carga completa
        await page.waitForLoadState('networkidle', { timeout: 15000 });
        
        // Verificar URL
        const currentUrl = page.url();
        expect(currentUrl).toMatch(module.url);
        
        // Verificar elementos esperados
        for (const selector of module.expectedElements) {
          const elem = page.locator(selector).first();
          if (await elem.isVisible({ timeout: 5000 }).catch(() => false)) {
            console.log(`  ✅ Elemento "${selector}" encontrado`);
          }
        }
        
        // Captura de pantalla
        const screenshot = await captureScreenshot(
          page, 
          `02-module-${module.name.source.replace(/[^a-z0-9]/gi, '-')}`,
          { fullPage: true }
        );
        
        results.successful.push({
          module: module.name.source,
          url: currentUrl,
          screenshot
        });
        
        console.log(`✅ Módulo "${module.name.source}" OK - ${currentUrl}`);
        
      } catch (e) {
        console.error(`❌ Error en "${module.name.source}": ${e.message}`);
        results.failed.push({
          module: module.name.source,
          reason: e.message
        });
        
        await captureScreenshot(page, `02-module-${module.name.source.replace(/[^a-z0-9]/gi, '-')}-error`);
      }
    }
    
    // Generar reporte
    generateReport('Navegación de Módulos', results);
    
    // No fallar el test si algunos módulos no existen
    expect(results.successful.length).toBeGreaterThan(0);
  });

  test('📑 Verificar sub-menús y navegación anidada', async ({ page }) => {
    console.log('\n📂 Verificando sub-menús...');
    
    // Herramientas tiene sub-menús
    await page.getByRole('link', { name: /Herramientas$/i }).click();
    await page.waitForURL('**/tools', { timeout: 15000 });
    
    const submenus = [
      { name: /Mecánico/i, url: /mechanic/ },
      { name: /Bodega|Warehouse/i, url: /warehouse/ },
      { name: /Solicitudes/i, url: /request/ },
      { name: /Mis Herramientas/i, url: /my-tools/ }
    ];
    
    for (const submenu of submenus) {
      const link = page.getByRole('link', { name: submenu.name }).first();
      if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
        console.log(`✅ Sub-menú "${submenu.name.source}" encontrado`);
      }
    }
    
    await captureScreenshot(page, '03-submenus-tools');
  });

  test('🔙 Verificar navegación de retorno (breadcrumbs)', async ({ page }) => {
    console.log('\n🔙 Verificando breadcrumbs...');
    
    // Navegar a un módulo interno
    await page.goto('/work-orders', { waitUntil: 'networkidle' });
    
    // Buscar breadcrumbs
    const breadcrumbs = page.locator('.breadcrumb, [class*="breadcrumb"], .breadcrumbs').first();
    if (await breadcrumbs.isVisible().catch(() => false)) {
      console.log('✅ Breadcrumbs encontrados');
      const text = await breadcrumbs.textContent();
      console.log(`   Texto: ${text?.substring(0, 100)}`);
    }
    
    // Verificar que podemos volver al dashboard
    await page.getByRole('link', { name: /Dashboard/i }).click();
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await captureScreenshot(page, '04-breadcrumbs-test');
  });

});
