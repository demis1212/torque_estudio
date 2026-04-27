# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 04-exploratory.spec.js >> 🔍 Suite Exploratoria - Recorrido Profundo >> 📦 Exploración de Inventario - Stock y alertas
- Location: 04-exploratory.spec.js:207:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/parts", waiting until "networkidle"

```

# Test source

```ts
  110 |       } catch (e) {
  111 |         // Ignorar
  112 |       }
  113 |     }
  114 |     
  115 |     // 2. Probar filtros
  116 |     const filters = await page.locator('select, .filter, [class*="filter"]').all();
  117 |     console.log(`   🔍 Encontrados ${filters.length} filtros`);
  118 |     
  119 |     for (let i = 0; i < Math.min(filters.length, 3); i++) {
  120 |       try {
  121 |         const filter = filters[i];
  122 |         if (await filter.isVisible({ timeout: 2000 }).catch(() => false)) {
  123 |           const options = await filter.locator('option').all();
  124 |           if (options.length > 1) {
  125 |             await filter.selectOption({ index: 1 });
  126 |             await page.waitForTimeout(1000);
  127 |             console.log(`   ✅ Filtro ${i} aplicado`);
  128 |           }
  129 |         }
  130 |       } catch (e) {
  131 |         // Ignorar
  132 |       }
  133 |     }
  134 |     
  135 |     // 3. Probar paginación
  136 |     const paginationLinks = await page.locator('.pagination a, .page-link').all();
  137 |     if (paginationLinks.length > 0) {
  138 |       console.log(`   📄 Probando paginación (${paginationLinks.length} páginas)`);
  139 |       
  140 |       // Click en página 2 si existe
  141 |       const page2 = paginationLinks.find(async link => {
  142 |         const text = await link.textContent().catch(() => '');
  143 |         return text.trim() === '2';
  144 |       });
  145 |       
  146 |       if (page2) {
  147 |         await page2.click();
  148 |         await page.waitForTimeout(1500);
  149 |         await captureScreenshot(page, 'exp-ordenes-pagina-2');
  150 |       }
  151 |     }
  152 |     
  153 |     // 4. Ver detalle de orden si existe
  154 |     const verLinks = await page.locator('a[href*="/work-orders/show/"], .view-order').all();
  155 |     if (verLinks.length > 0) {
  156 |       console.log(`   📋 Probando vista de orden`);
  157 |       await verLinks[0].click();
  158 |       await page.waitForTimeout(2000);
  159 |       await captureScreenshot(page, 'exp-orden-detalle');
  160 |       
  161 |       // Probar botones dentro de la orden
  162 |       const actionButtons = await page.locator('button, .btn-action').all();
  163 |       console.log(`      Encontrados ${actionButtons.length} botones de acción`);
  164 |     }
  165 |   });
  166 | 
  167 |   test('🔧 Exploración de Operación Inteligente', async ({ page }) => {
  168 |     console.log('\n🔧 Explorando Operación Inteligente...');
  169 |     
  170 |     try {
  171 |       await page.goto('/workshop-ops', { waitUntil: 'networkidle', timeout: 10000 });
  172 |       await captureScreenshot(page, 'exp-workshop-ops', { fullPage: true });
  173 |       
  174 |       // Buscar órdenes de trabajo
  175 |       const orders = await page.locator('.order-card, [class*="order"], .work-order').all();
  176 |       console.log(`   📋 Encontradas ${orders.length} órdenes`);
  177 |       
  178 |       if (orders.length > 0) {
  179 |         // Click en primera orden
  180 |         await orders[0].click();
  181 |         await page.waitForTimeout(2000);
  182 |         await captureScreenshot(page, 'exp-workshop-detalle');
  183 |         
  184 |         // Probar botones de tiempo
  185 |         const timeButtons = ['Iniciar', 'Pausar', 'Detener', 'Finalizar'];
  186 |         for (const btn of timeButtons) {
  187 |           const button = page.getByRole('button', { name: new RegExp(btn, 'i') }).first();
  188 |           if (await button.isVisible({ timeout: 2000 }).catch(() => false)) {
  189 |             console.log(`   👆 Botón "${btn}" disponible`);
  190 |           }
  191 |         }
  192 |         
  193 |         // Probar pestañas si existen
  194 |         const tabs = await page.locator('.tab, [role="tab"]').all();
  195 |         for (const tab of tabs.slice(0, 3)) {
  196 |           try {
  197 |             await tab.click();
  198 |             await page.waitForTimeout(1000);
  199 |           } catch (e) {}
  200 |         }
  201 |       }
  202 |     } catch (e) {
  203 |       console.log(`   ⚠️ No se pudo acceder a Operación Inteligente: ${e.message}`);
  204 |     }
  205 |   });
  206 | 
  207 |   test('📦 Exploración de Inventario - Stock y alertas', async ({ page }) => {
  208 |     console.log('\n📦 Explorando Inventario...');
  209 |     
> 210 |     await page.goto('/parts', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  211 |     await captureScreenshot(page, 'exp-inventario-inicio', { fullPage: true });
  212 |     
  213 |     // 1. Buscar alertas de stock bajo
  214 |     const alertas = await page.locator('.alert, .alert-low-stock, [class*="alert"]').all();
  215 |     console.log(`   ⚠️ Encontradas ${alertas.length} alertas`);
  216 |     
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
```