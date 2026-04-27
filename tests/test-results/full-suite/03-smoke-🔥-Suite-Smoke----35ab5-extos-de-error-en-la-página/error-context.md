# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 03-smoke.spec.js >> 🔥 Suite Smoke - Detección de Errores >> ⚠️ Detección de textos de error en la página
- Location: 03-smoke.spec.js:130:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/dashboard", waiting until "networkidle"

```

# Test source

```ts
  1   | /**
  2   |  * @fileoverview Smoke Tests - Detección de errores y validación general
  3   |  * @description Verifica errores JS, HTTP, elementos rotos, textos de error
  4   |  */
  5   | 
  6   | const { test, expect } = require('@playwright/test');
  7   | const { 
  8   |   captureScreenshot, 
  9   |   checkConsoleErrors, 
  10  |   checkHttpErrors,
  11  |   detectBrokenElements,
  12  |   hasErrorText,
  13  |   getAllButtons,
  14  |   getAllLinks,
  15  |   TEST_DATA
  16  | } = require('./helpers/utils');
  17  | 
  18  | test.describe('🔥 Suite Smoke - Detección de Errores', () => {
  19  |   
  20  |   test.beforeEach(async ({ page }) => {
> 21  |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  22  |     await expect(page).toHaveURL(/dashboard/, { timeout: 15000 });
  23  |   });
  24  | 
  25  |   test('🐛 Detección de errores JavaScript en consola', async ({ page }) => {
  26  |     console.log('\n🐛 Monitoreando errores JavaScript...');
  27  |     
  28  |     const errors = [];
  29  |     
  30  |     page.on('console', msg => {
  31  |       if (msg.type() === 'error') {
  32  |         errors.push({ type: 'console', text: msg.text() });
  33  |         console.error(`❌ Console Error: ${msg.text().substring(0, 100)}`);
  34  |       }
  35  |     });
  36  |     
  37  |     page.on('pageerror', error => {
  38  |       errors.push({ type: 'page', text: error.message });
  39  |       console.error(`❌ Page Error: ${error.message}`);
  40  |     });
  41  |     
  42  |     // Navegar por varias páginas para capturar errores
  43  |     const urls = ['/work-orders', '/clients', '/vehicles', '/parts'];
  44  |     
  45  |     for (const url of urls) {
  46  |       await page.goto(url, { waitUntil: 'networkidle' });
  47  |       await page.waitForTimeout(1000);
  48  |     }
  49  |     
  50  |     // Reporte
  51  |     if (errors.length === 0) {
  52  |       console.log('✅ No se detectaron errores JavaScript');
  53  |     } else {
  54  |       console.warn(`⚠️ Se detectaron ${errors.length} errores JavaScript`);
  55  |       // No fallar el test, solo reportar
  56  |     }
  57  |     
  58  |     expect(errors.length).toBeLessThan(10); // Tolerancia
  59  |   });
  60  | 
  61  |   test('🌐 Detección de errores HTTP 4xx/5xx', async ({ page }) => {
  62  |     console.log('\n🌐 Monitoreando errores HTTP...');
  63  |     
  64  |     const httpErrors = [];
  65  |     
  66  |     page.on('response', response => {
  67  |       const status = response.status();
  68  |       if (status >= 400) {
  69  |         httpErrors.push({
  70  |           status,
  71  |           url: response.url(),
  72  |           type: status >= 500 ? 'SERVER' : 'CLIENT'
  73  |         });
  74  |         console.error(`❌ HTTP ${status}: ${response.url()}`);
  75  |       }
  76  |     });
  77  |     
  78  |     // Navegar por múltiples páginas
  79  |     const modules = ['/dashboard', '/work-orders', '/clients', '/vehicles', 
  80  |                      '/services', '/parts', '/tools', '/reports'];
  81  |     
  82  |     for (const module of modules) {
  83  |       try {
  84  |         await page.goto(module, { waitUntil: 'networkidle' });
  85  |         await page.waitForTimeout(500);
  86  |       } catch (e) {
  87  |         console.log(`⚠️ Error navegando a ${module}: ${e.message}`);
  88  |       }
  89  |     }
  90  |     
  91  |     if (httpErrors.length === 0) {
  92  |       console.log('✅ No se detectaron errores HTTP');
  93  |     } else {
  94  |       console.warn(`⚠️ Se detectaron ${httpErrors.length} errores HTTP`);
  95  |       const uniqueUrls = [...new Set(httpErrors.map(e => e.url))];
  96  |       console.log('URLs con error:', uniqueUrls.slice(0, 5));
  97  |     }
  98  |     
  99  |     // Verificar que no haya errores 500 críticos
  100 |     const serverErrors = httpErrors.filter(e => e.status >= 500);
  101 |     expect(serverErrors.length).toBe(0);
  102 |   });
  103 | 
  104 |   test('🖼️ Detección de imágenes rotas', async ({ page }) => {
  105 |     console.log('\n🖼️ Verificando imágenes...');
  106 |     
  107 |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
  108 |     
  109 |     const brokenImages = await page.evaluate(() => {
  110 |       const images = document.querySelectorAll('img');
  111 |       const broken = [];
  112 |       images.forEach(img => {
  113 |         if (img.naturalWidth === 0 && img.src) {
  114 |           broken.push(img.src);
  115 |         }
  116 |       });
  117 |       return broken;
  118 |     });
  119 |     
  120 |     if (brokenImages.length === 0) {
  121 |       console.log('✅ No se detectaron imágenes rotas');
```