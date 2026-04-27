# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 99-logout.spec.js >> 🚪 Suite Logout - Ejecutar al final >> Logout y redirección a login
- Location: 99-logout.spec.js:11:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/dashboard", waiting until "networkidle"

```

# Test source

```ts
  1  | /**
  2  |  * @fileoverview Test de Logout - Ejecutar al final de todos los tests
  3  |  * @description Este test debe ejecutarse al final porque destruye la sesión
  4  |  */
  5  | 
  6  | const { test, expect } = require('@playwright/test');
  7  | const { captureScreenshot } = require('./helpers/utils');
  8  | 
  9  | test.describe('🚪 Suite Logout - Ejecutar al final', () => {
  10 |   
  11 |   test('Logout y redirección a login', async ({ page }) => {
  12 |     console.log('\n🚪 Probando logout...');
  13 |     
  14 |     // Ir al dashboard autenticado
> 15 |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  16 |     
  17 |     // Verificar que estamos en dashboard (flexible)
  18 |     const url = page.url();
  19 |     if (!url.includes('dashboard')) {
  20 |       console.log('⚠️ No se pudo acceder al dashboard, saltando test');
  21 |       return;
  22 |     }
  23 |     
  24 |     // Buscar botón de logout con múltiples estrategias
  25 |     const logoutSelectors = [
  26 |       'a[href*="logout"]', 
  27 |       'a:has-text("Salir")',
  28 |       'a:has-text("Logout")',
  29 |       'a:has-text("Cerrar")'
  30 |     ];
  31 |     
  32 |     let logoutBtn = null;
  33 |     for (const selector of logoutSelectors) {
  34 |       const btn = page.locator(selector).first();
  35 |       if (await btn.isVisible().catch(() => false)) {
  36 |         logoutBtn = btn;
  37 |         break;
  38 |       }
  39 |     }
  40 |     
  41 |     if (!logoutBtn) {
  42 |       console.log('⚠️ Botón logout no encontrado, saltando test');
  43 |       return;
  44 |     }
  45 |     
  46 |     // Click en logout
  47 |     await logoutBtn.click();
  48 |     
  49 |     // Esperar navegación (puede ser a login o home)
  50 |     await page.waitForTimeout(2000);
  51 |     
  52 |     // Verificar que estamos en login o home
  53 |     const finalUrl = page.url();
  54 |     const isLoggedOut = finalUrl.includes('login') || finalUrl.includes('logout');
  55 |     
  56 |     if (isLoggedOut) {
  57 |       console.log('✅ Logout exitoso - redirigido a login');
  58 |     } else {
  59 |       console.log('⚠️ URL final:', finalUrl);
  60 |     }
  61 |     
  62 |     await captureScreenshot(page, '99-logout-result');
  63 |   });
  64 | 
  65 | });
  66 | 
```