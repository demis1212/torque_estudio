# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 04-exploratory.spec.js >> 🔍 Suite Exploratoria - Recorrido Profundo >> 🛠️ Exploración Completa de Herramientas
- Location: 04-exploratory.spec.js:314:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/tools", waiting until "networkidle"

```

# Test source

```ts
  217 |     if (alertas.length > 0) {
  218 |       for (let i = 0; i < Math.min(alertas.length, 2); i++) {
  219 |         try {
  220 |           await alertas[i].click();
  221 |           await page.waitForTimeout(1000);
  222 |           
  223 |           // Si abre modal o cambia página, volver
  224 |           if (!page.url().includes('/parts')) {
  225 |             await page.goto('/parts', { waitUntil: 'networkidle' });
  226 |           }
  227 |         } catch (e) {}
  228 |       }
  229 |     }
  230 |     
  231 |     // 2. Probar acciones en repuestos
  232 |     const actionLinks = await page.locator('a[href*="/parts/edit/"], .edit-part, .btn-edit').all();
  233 |     console.log(`   ✏️ Encontrados ${actionLinks.length} enlaces de edición`);
  234 |     
  235 |     if (actionLinks.length > 0) {
  236 |       await actionLinks[0].click();
  237 |       await page.waitForTimeout(2000);
  238 |       await captureScreenshot(page, 'exp-repuesto-edicion');
  239 |       await page.goBack();
  240 |     }
  241 |   });
  242 | 
  243 |   test('🔔 Exploración de Notificaciones', async ({ page }) => {
  244 |     console.log('\n🔔 Explorando Notificaciones...');
  245 |     
  246 |     try {
  247 |       await page.goto('/notifications', { waitUntil: 'networkidle' });
  248 |       await captureScreenshot(page, 'exp-notificaciones', { fullPage: true });
  249 |       
  250 |       // Verificar lista de notificaciones
  251 |       const notifs = await page.locator('.notification, [class*="notification"]').all();
  252 |       console.log(`   📨 Encontradas ${notifs.length} notificaciones`);
  253 |       
  254 |       // Probar "Marcar todo como leído"
  255 |       const marcarBtn = page.getByRole('button', { name: /Marcar todo/i });
  256 |       if (await marcarBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
  257 |         console.log('   👆 Click en "Marcar todo como leído"');
  258 |         await marcarBtn.click();
  259 |         await page.waitForTimeout(1500);
  260 |       }
  261 |       
  262 |       // Probar eliminar notificación
  263 |       const deleteBtns = await page.locator('button:has-text("Eliminar"), .delete-notification').all();
  264 |       if (deleteBtns.length > 1) {
  265 |         // No eliminar la primera, evitar problemas
  266 |         await captureScreenshot(page, 'exp-notificaciones-pre-delete');
  267 |       }
  268 |     } catch (e) {
  269 |       console.log(`   ⚠️ Error en notificaciones: ${e.message}`);
  270 |     }
  271 |   });
  272 | 
  273 |   test('📊 Exploración de Reportes - Gráficos y exportación', async ({ page }) => {
  274 |     console.log('\n📊 Explorando Reportes...');
  275 |     
  276 |     try {
  277 |       await page.goto('/reports', { waitUntil: 'networkidle' });
  278 |       await captureScreenshot(page, 'exp-reportes', { fullPage: true });
  279 |       
  280 |       // Buscar gráficos
  281 |       const charts = await page.locator('canvas, .chart, [class*="chart"]').all();
  282 |       console.log(`   📈 Encontrados ${charts.length} gráficos`);
  283 |       
  284 |       // Probar filtros de fecha
  285 |       const dateInputs = await page.locator('input[type="date"]').all();
  286 |       for (const input of dateInputs.slice(0, 2)) {
  287 |         try {
  288 |           await input.fill('2024-01-01');
  289 |           await page.waitForTimeout(500);
  290 |         } catch (e) {}
  291 |       }
  292 |       
  293 |       // Buscar botones de exportar
  294 |       const exportBtns = await page.locator('button:has-text("PDF"), button:has-text("Excel"), .export-btn').all();
  295 |       console.log(`   💾 Encontrados ${exportBtns.length} botones de exportación`);
  296 |       
  297 |       for (const btn of exportBtns) {
  298 |         const text = await btn.textContent().catch(() => '');
  299 |         console.log(`      - Botón: ${text?.substring(0, 30)}`);
  300 |       }
  301 |       
  302 |       // Probar productividad si existe enlace
  303 |       const prodLink = page.getByRole('link', { name: /Productividad/i });
  304 |       if (await prodLink.isVisible({ timeout: 3000 }).catch(() => false)) {
  305 |         await prodLink.click();
  306 |         await page.waitForURL('**/productivity', { timeout: 10000 });
  307 |         await captureScreenshot(page, 'exp-productividad');
  308 |       }
  309 |     } catch (e) {
  310 |       console.log(`   ⚠️ Error en reportes: ${e.message}`);
  311 |     }
  312 |   });
  313 | 
  314 |   test('🛠️ Exploración Completa de Herramientas', async ({ page }) => {
  315 |     console.log('\n🛠️ Explorando Herramientas...');
  316 |     
> 317 |     await page.goto('/tools', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  318 |     await captureScreenshot(page, 'exp-tools-inicio', { fullPage: true });
  319 |     
  320 |     // Explorar sub-módulos
  321 |     const submodulos = [
  322 |       { name: 'Mecánico', url: 'mechanic' },
  323 |       { name: 'Bodega|Warehouse', url: 'warehouse' },
  324 |       { name: 'Solicitudes', url: 'request' },
  325 |       { name: 'Mis Herramientas', url: 'my-tools' }
  326 |     ];
  327 |     
  328 |     for (const sub of submodulos) {
  329 |       try {
  330 |         const link = page.getByRole('link', { name: new RegExp(sub.name, 'i') }).first();
  331 |         if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
  332 |           console.log(`   👆 Explorando: ${sub.name}`);
  333 |           await link.click();
  334 |           await page.waitForTimeout(2000);
  335 |           await captureScreenshot(page, `exp-tools-${sub.url}`);
  336 |           await page.goto('/tools', { waitUntil: 'networkidle' });
  337 |         }
  338 |       } catch (e) {}
  339 |     }
  340 |   });
  341 | 
  342 |   test('🎯 Exploración recursiva - Todos los botones visibles', async ({ page }) => {
  343 |     console.log('\n🎯 Exploración intensiva de botones...');
  344 |     
  345 |     const visitedUrls = new Set();
  346 |     const maxIterations = 20;
  347 |     
  348 |     for (let i = 0; i < maxIterations; i++) {
  349 |       const currentUrl = page.url();
  350 |       if (visitedUrls.has(currentUrl)) continue;
  351 |       visitedUrls.add(currentUrl);
  352 |       
  353 |       console.log(`   Iteración ${i + 1}: ${currentUrl}`);
  354 |       
  355 |       // Encontrar todos los botones visibles
  356 |       const allButtons = await page.locator('button:not([disabled]), .btn:not(.disabled), [role="button"]').all();
  357 |       const visibleButtons = [];
  358 |       
  359 |       for (const btn of allButtons.slice(0, 10)) {
  360 |         try {
  361 |           if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) {
  362 |             const text = await btn.textContent().catch(() => '');
  363 |             visibleButtons.push({ element: btn, text: text?.substring(0, 30) });
  364 |           }
  365 |         } catch (e) {}
  366 |       }
  367 |       
  368 |       if (visibleButtons.length === 0) break;
  369 |       
  370 |       // Click en el primer botón que no sea peligroso
  371 |       for (const btn of visibleButtons) {
  372 |         const text = (btn.text || '').toLowerCase();
  373 |         
  374 |         // Evitar botones peligrosos
  375 |         if (text.includes('eliminar') || text.includes('borrar') || 
  376 |             text.includes('delete') || text.includes('logout') ||
  377 |             text.includes('salir')) {
  378 |           continue;
  379 |         }
  380 |         
  381 |         try {
  382 |           console.log(`   👆 Probando botón: "${btn.text}"`);
  383 |           await btn.element.click();
  384 |           await page.waitForTimeout(1500);
  385 |           
  386 |           // Capturar resultado
  387 |           await captureScreenshot(page, `exp-button-${i}-${btn.text.replace(/[^a-z0-9]/gi, '')}`);
  388 |           break;
  389 |         } catch (e) {
  390 |           // Continuar con siguiente botón
  391 |         }
  392 |       }
  393 |       
  394 |       // Navegar a una nueva página si estamos atascados
  395 |       if (page.url() === currentUrl) {
  396 |         const randomModules = ['/work-orders', '/clients', '/vehicles', '/parts'];
  397 |         const randomModule = randomModules[Math.floor(Math.random() * randomModules.length)];
  398 |         await page.goto(randomModule, { waitUntil: 'networkidle' });
  399 |       }
  400 |     }
  401 |     
  402 |     console.log(`   ✅ URLs visitadas: ${visitedUrls.size}`);
  403 |   });
  404 | 
  405 | });
  406 | 
```