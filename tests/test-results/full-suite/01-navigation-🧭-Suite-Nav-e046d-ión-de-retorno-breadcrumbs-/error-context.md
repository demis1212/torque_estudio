# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 01-navigation.spec.js >> 🧭 Suite Navegación - Recorrido Completo Sidebar >> 🔙 Verificar navegación de retorno (breadcrumbs)
- Location: 01-navigation.spec.js:170:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/dashboard", waiting until "networkidle"

```

# Test source

```ts
  1   | /**
  2   |  * @fileoverview Tests de Navegación - Navegación exhaustiva por módulos
  3   |  * @description Recorre TODO el sidebar y verifica cada módulo
  4   |  */
  5   | 
  6   | const { test, expect } = require('@playwright/test');
  7   | const { captureScreenshot, navigateToModule, generateReport } = require('./helpers/utils');
  8   | 
  9   | // ==================== CONFIGURACIÓN DE MÓDULOS ====================
  10  | const MODULES = [
  11  |   { name: /Dashboard/i, url: /dashboard/, expectedElements: ['h1', '.stats', '.sidebar'] },
  12  |   { name: /^Órdenes$/i, url: /work-orders/, expectedElements: ['table', '.order-list', 'h1'] },
  13  |   { name: /Clientes$/i, url: /clients/, expectedElements: ['table', '.client-list'] },
  14  |   { name: /Vehículos$/i, url: /vehicles/, expectedElements: ['table', '.vehicle-list'] },
  15  |   { name: /Servicios$/i, url: /services/, expectedElements: ['table', '.service-list'] },
  16  |   { name: /Inventario$/i, url: /parts/, expectedElements: ['table', '.part-list', '.inventory'] },
  17  |   { name: /Operación Inteligente/i, url: /workshop-ops/, expectedElements: ['.workshop-ops', '.orders'] },
  18  |   { name: /Herramientas$/i, url: /tools/, expectedElements: ['.tools', '.tool-list'] },
  19  |   { name: /Manuales$/i, url: /manuals/, expectedElements: ['.manuals', '.manual-list'] },
  20  |   { name: /VIN Decoder|Decodificador/i, url: /vin/, expectedElements: ['input[name="vin"]', 'form'] },
  21  |   { name: /DTC Codes$/i, url: /dtc/, expectedElements: ['.dtc-codes', 'table'] },
  22  |   { name: /Reportes$/i, url: /reports/, expectedElements: ['.reports', 'canvas', '.chart'] },
  23  |   { name: /Productividad$/i, url: /productivity/, expectedElements: ['.productivity', 'canvas'] },
  24  |   { name: /WhatsApp/i, url: /whatsapp/, expectedElements: ['.whatsapp', '.reminder-list'] },
  25  | ];
  26  | 
  27  | // ==================== TESTS DE NAVEGACIÓN ====================
  28  | 
  29  | test.describe('🧭 Suite Navegación - Recorrido Completo Sidebar', () => {
  30  |   
  31  |   test.beforeEach(async ({ page }) => {
  32  |     // Asegurar que estamos autenticados
> 33  |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  34  |     await expect(page).toHaveURL(/dashboard/, { timeout: 15000 });
  35  |   });
  36  | 
  37  |   test('📋 Dashboard - Verificar estructura completa', async ({ page }) => {
  38  |     console.log('\n📊 Verificando Dashboard...');
  39  |     
  40  |     // Verificar sidebar
  41  |     const sidebar = page.locator('.sidebar, nav, [class*="sidebar"]').first();
  42  |     await expect(sidebar, 'Sidebar debe estar visible').toBeVisible();
  43  |     
  44  |     // Verificar enlaces principales
  45  |     const mainLinks = ['Dashboard', 'Órdenes', 'Clientes', 'Vehículos'];
  46  |     for (const link of mainLinks) {
  47  |       const elem = page.getByRole('link', { name: new RegExp(link, 'i') }).first();
  48  |       await expect(elem, `Link "${link}" debe estar visible`).toBeVisible();
  49  |     }
  50  |     
  51  |     // Verificar widgets estadísticos
  52  |     const stats = ['Total Clientes', 'Total Vehículos', 'Órdenes Activas'];
  53  |     for (const stat of stats) {
  54  |       const elem = page.locator('text=' + stat).first();
  55  |       if (await elem.isVisible().catch(() => false)) {
  56  |         console.log(`✅ Widget "${stat}" encontrado`);
  57  |       }
  58  |     }
  59  |     
  60  |     // Botones rápidos
  61  |     const quickButtons = ['Nueva Orden', 'Nuevo Cliente', 'Nuevo Vehículo'];
  62  |     for (const btn of quickButtons) {
  63  |       const elem = page.getByRole('link', { name: new RegExp(btn, 'i') }).first();
  64  |       if (await elem.isVisible().catch(() => false)) {
  65  |         console.log(`✅ Botón rápido "${btn}" encontrado`);
  66  |       }
  67  |     }
  68  |     
  69  |     await captureScreenshot(page, '01-dashboard-complete');
  70  |   });
  71  | 
  72  |   test('🔄 Navegar por TODOS los módulos del sidebar', async ({ page }) => {
  73  |     console.log(`\n🧭 Iniciando recorrido de ${MODULES.length} módulos...`);
  74  |     
  75  |     const results = {
  76  |       successful: [],
  77  |       failed: [],
  78  |       screenshots: []
  79  |     };
  80  |     
  81  |     for (const module of MODULES) {
  82  |       try {
  83  |         // Navegar al módulo
  84  |         const link = page.getByRole('link', { name: module.name }).first();
  85  |         
  86  |         if (!(await link.isVisible({ timeout: 5000 }).catch(() => false))) {
  87  |           console.log(`⚠️ Módulo "${module.name.source}" no encontrado`);
  88  |           results.failed.push({ module: module.name.source, reason: 'Not found' });
  89  |           continue;
  90  |         }
  91  |         
  92  |         // Click y esperar navegación
  93  |         await Promise.all([
  94  |           page.waitForURL(module.url, { timeout: 20000 }),
  95  |           link.click()
  96  |         ]);
  97  |         
  98  |         // Esperar carga completa
  99  |         await page.waitForLoadState('networkidle', { timeout: 15000 });
  100 |         
  101 |         // Verificar URL
  102 |         const currentUrl = page.url();
  103 |         expect(currentUrl).toMatch(module.url);
  104 |         
  105 |         // Verificar elementos esperados
  106 |         for (const selector of module.expectedElements) {
  107 |           const elem = page.locator(selector).first();
  108 |           if (await elem.isVisible({ timeout: 5000 }).catch(() => false)) {
  109 |             console.log(`  ✅ Elemento "${selector}" encontrado`);
  110 |           }
  111 |         }
  112 |         
  113 |         // Captura de pantalla
  114 |         const screenshot = await captureScreenshot(
  115 |           page, 
  116 |           `02-module-${module.name.source.replace(/[^a-z0-9]/gi, '-')}`,
  117 |           { fullPage: true }
  118 |         );
  119 |         
  120 |         results.successful.push({
  121 |           module: module.name.source,
  122 |           url: currentUrl,
  123 |           screenshot
  124 |         });
  125 |         
  126 |         console.log(`✅ Módulo "${module.name.source}" OK - ${currentUrl}`);
  127 |         
  128 |       } catch (e) {
  129 |         console.error(`❌ Error en "${module.name.source}": ${e.message}`);
  130 |         results.failed.push({
  131 |           module: module.name.source,
  132 |           reason: e.message
  133 |         });
```