# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 04-exploratory.spec.js >> 🔍 Suite Exploratoria - Recorrido Profundo >> 🎪 Exploración completa del Dashboard
- Location: 04-exploratory.spec.js:11:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/dashboard", waiting until "networkidle"

```

# Test source

```ts
  1   | /**
  2   |  * @fileoverview Tests Exploratorios - Click recursivo en botones y elementos
  3   |  * @description Explora la aplicación haciendo click en todos los elementos interactivos
  4   |  */
  5   | 
  6   | const { test, expect } = require('@playwright/test');
  7   | const { captureScreenshot } = require('./helpers/utils');
  8   | 
  9   | test.describe('🔍 Suite Exploratoria - Recorrido Profundo', () => {
  10  |   
  11  |   test('🎪 Exploración completa del Dashboard', async ({ page }) => {
  12  |     console.log('\n🎪 Iniciando exploración del Dashboard...');
  13  |     
> 14  |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  15  |     
  16  |     // Capturar estado inicial
  17  |     await captureScreenshot(page, 'exp-dashboard-initial', { fullPage: true });
  18  |     
  19  |     // 1. Click en TODOS los botones rápidos
  20  |     const quickButtons = [
  21  |       'Nueva Orden',
  22  |       'Nuevo Cliente', 
  23  |       'Nuevo Vehículo',
  24  |       'Nuevo Servicio',
  25  |       'Solicitar Tool',
  26  |       'Ver Reportes'
  27  |     ];
  28  |     
  29  |     for (const btnText of quickButtons) {
  30  |       try {
  31  |         const btn = page.getByRole('link', { name: new RegExp(btnText, 'i') }).first();
  32  |         if (await btn.isVisible({ timeout: 3000 }).catch(() => false)) {
  33  |           console.log(`   👆 Click en: ${btnText}`);
  34  |           await btn.click();
  35  |           await page.waitForTimeout(1500);
  36  |           await captureScreenshot(page, `exp-btn-${btnText.replace(/\s+/g, '-')}`);
  37  |           
  38  |           // Volver al dashboard
  39  |           await page.goto('/dashboard', { waitUntil: 'networkidle' });
  40  |           await page.waitForTimeout(1000);
  41  |         }
  42  |       } catch (e) {
  43  |         console.log(`   ⚠️ No se pudo probar: ${btnText}`);
  44  |       }
  45  |     }
  46  |     
  47  |     // 2. Click en widgets de estadísticas
  48  |     const statCards = await page.locator('.stat-card, [class*="stat"], .card').all();
  49  |     console.log(`\n   📊 Encontrados ${statCards.length} widgets de estadísticas`);
  50  |     
  51  |     for (let i = 0; i < Math.min(statCards.length, 3); i++) {
  52  |       try {
  53  |         const card = statCards[i];
  54  |         if (await card.isVisible()) {
  55  |           await card.click();
  56  |           await page.waitForTimeout(1000);
  57  |           
  58  |           // Si cambió la URL, volver
  59  |           if (!page.url().includes('/dashboard')) {
  60  |             await page.goBack();
  61  |             await page.waitForTimeout(1000);
  62  |           }
  63  |         }
  64  |       } catch (e) {
  65  |         // Ignorar
  66  |       }
  67  |     }
  68  |     
  69  |     // 3. Click en enlaces del sidebar
  70  |     const sidebarLinks = await page.locator('.sidebar a, nav a').all();
  71  |     console.log(`   🔗 ${sidebarLinks.length} enlaces en sidebar`);
  72  |     
  73  |     for (let i = 0; i < Math.min(sidebarLinks.length, 5); i++) {
  74  |       try {
  75  |         const link = sidebarLinks[i];
  76  |         const href = await link.getAttribute('href').catch(() => '');
  77  |         const text = await link.textContent().catch(() => '');
  78  |         
  79  |         if (href && !href.startsWith('http') && !href.startsWith('#')) {
  80  |           console.log(`   👆 Probando enlace: ${text?.substring(0, 30) || href}`);
  81  |           await link.click();
  82  |           await page.waitForTimeout(1500);
  83  |           await page.goto('/dashboard', { waitUntil: 'networkidle' });
  84  |         }
  85  |       } catch (e) {
  86  |         // Ignorar
  87  |       }
  88  |     }
  89  |   });
  90  | 
  91  |   test('🔄 Exploración de Órdenes - Tabs, filtros, acciones', async ({ page }) => {
  92  |     console.log('\n🔄 Explorando módulo Órdenes...');
  93  |     
  94  |     await page.goto('/work-orders', { waitUntil: 'networkidle' });
  95  |     await captureScreenshot(page, 'exp-ordenes-inicio', { fullPage: true });
  96  |     
  97  |     // 1. Buscar y probar tabs
  98  |     const tabs = await page.locator('.tab, [role="tab"], .nav-tab').all();
  99  |     console.log(`   📑 Encontrados ${tabs.length} tabs`);
  100 |     
  101 |     for (let i = 0; i < Math.min(tabs.length, 5); i++) {
  102 |       try {
  103 |         const tab = tabs[i];
  104 |         if (await tab.isVisible({ timeout: 2000 }).catch(() => false)) {
  105 |           const text = await tab.textContent().catch(() => '');
  106 |           console.log(`   👆 Click en tab: ${text?.substring(0, 20)}`);
  107 |           await tab.click();
  108 |           await page.waitForTimeout(1000);
  109 |         }
  110 |       } catch (e) {
  111 |         // Ignorar
  112 |       }
  113 |     }
  114 |     
```