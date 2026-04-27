# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [MEDIO] Information Disclosure
- Location: tests\05-security-pentest.spec.js:271:3

# Error details

```
TimeoutError: page.fill: Timeout 30000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - generic [ref=e3]:
      - generic [ref=e4]: 🔧
      - heading "Torque Studio" [level=2] [ref=e5]
    - generic [ref=e6]:
      - generic [ref=e7]: Principal
      - link " Dashboard" [ref=e8] [cursor=pointer]:
        - /url: /dashboard
        - generic [ref=e9]: 
        - text: Dashboard
      - link " Clientes" [ref=e10] [cursor=pointer]:
        - /url: /clients
        - generic [ref=e11]: 
        - text: Clientes
      - link " Vehículos" [ref=e12] [cursor=pointer]:
        - /url: /vehicles
        - generic [ref=e13]: 
        - text: Vehículos
      - link " Órdenes" [ref=e14] [cursor=pointer]:
        - /url: /work-orders
        - generic [ref=e15]: 
        - text: Órdenes
    - generic [ref=e16]:
      - generic [ref=e17]: Operaciones
      - link " Servicios" [ref=e18] [cursor=pointer]:
        - /url: /services
        - generic [ref=e19]: 
        - text: Servicios
      - link " Operación Inteligente" [ref=e20] [cursor=pointer]:
        - /url: /workshop-ops
        - generic [ref=e21]: 
        - text: Operación Inteligente
      - link " Inventario" [ref=e22] [cursor=pointer]:
        - /url: /parts
        - generic [ref=e23]: 
        - text: Inventario
      - link " Herramientas" [ref=e24] [cursor=pointer]:
        - /url: /tools
        - generic [ref=e25]: 
        - text: Herramientas
    - generic [ref=e26]:
      - generic [ref=e27]: Herramientas
      - link " Manuales" [ref=e28] [cursor=pointer]:
        - /url: /manuals
        - generic [ref=e29]: 
        - text: Manuales
      - link " VIN Decoder" [ref=e30] [cursor=pointer]:
        - /url: /vin-decoder
        - generic [ref=e31]: 
        - text: VIN Decoder
      - link " DTC Codes" [ref=e32] [cursor=pointer]:
        - /url: /dtc
        - generic [ref=e33]: 
        - text: DTC Codes
    - generic [ref=e34]:
      - generic [ref=e35]: Administración
      - link " Reportes" [ref=e36] [cursor=pointer]:
        - /url: /reports
        - generic [ref=e37]: 
        - text: Reportes
      - link " Productividad" [ref=e38] [cursor=pointer]:
        - /url: /reports/mechanic-productivity
        - generic [ref=e39]: 
        - text: Productividad
      - link " WhatsApp" [ref=e40] [cursor=pointer]:
        - /url: /whatsapp-reminders
        - generic [ref=e41]: 
        - text: WhatsApp
      - link " Usuarios" [ref=e42] [cursor=pointer]:
        - /url: /users
        - generic [ref=e43]: 
        - text: Usuarios
      - link " Configuración" [ref=e44] [cursor=pointer]:
        - /url: /settings
        - generic [ref=e45]: 
        - text: Configuración
    - generic [ref=e47]:
      - generic [ref=e48]: A
      - generic [ref=e49]:
        - generic [ref=e50]: Administrador
        - generic [ref=e51]: Administrador
  - generic [ref=e52]:
    - generic [ref=e53]:
      - generic [ref=e54]:
        - generic [ref=e55]: 
        - textbox "Buscar órdenes, clientes, vehículos..." [ref=e56]
      - generic [ref=e57]:
        - link "" [ref=e58] [cursor=pointer]:
          - /url: /notifications
          - generic [ref=e59]: 
        - link " Salir" [ref=e60] [cursor=pointer]:
          - /url: /logout
          - generic [ref=e61]: 
          - text: Salir
    - generic [ref=e62]:
      - generic [ref=e63]:
        - heading "Dashboard" [level=1] [ref=e64]
        - paragraph [ref=e65]: Bienvenido de vuelta, Administrador. Aquí está el resumen de tu taller hoy.
      - generic [ref=e66]:
        - link " Nueva Orden" [ref=e67] [cursor=pointer]:
          - /url: /work-orders/create
          - generic [ref=e68]: 
          - generic [ref=e69]: Nueva Orden
        - link " Nuevo Cliente" [ref=e70] [cursor=pointer]:
          - /url: /clients/create
          - generic [ref=e71]: 
          - generic [ref=e72]: Nuevo Cliente
        - link " Nuevo Vehículo" [ref=e73] [cursor=pointer]:
          - /url: /vehicles/create
          - generic [ref=e74]: 
          - generic [ref=e75]: Nuevo Vehículo
        - link " Nuevo Servicio" [ref=e76] [cursor=pointer]:
          - /url: /services/create
          - generic [ref=e77]: 
          - generic [ref=e78]: Nuevo Servicio
        - link " Solicitar Tool" [ref=e79] [cursor=pointer]:
          - /url: /tools/requests/create
          - generic [ref=e80]: 
          - generic [ref=e81]: Solicitar Tool
        - link " Ver Reportes" [ref=e82] [cursor=pointer]:
          - /url: /reports
          - generic [ref=e83]: 
          - generic [ref=e84]: Ver Reportes
      - generic [ref=e85]:
        - generic [ref=e86]:
          - generic [ref=e87]:
            - generic [ref=e89]: 
            - generic [ref=e90]:
              - generic [ref=e91]: 
              - text: +3%
          - heading "Total Clientes" [level=4] [ref=e92]
          - paragraph [ref=e93]: "5"
        - generic [ref=e94]:
          - generic [ref=e95]:
            - generic [ref=e97]: 
            - generic [ref=e98]:
              - generic [ref=e99]: 
              - text: +1%
          - heading "Total Vehículos" [level=4] [ref=e100]
          - paragraph [ref=e101]: "1"
        - generic [ref=e102]:
          - generic [ref=e103]:
            - generic [ref=e105]: 
            - generic [ref=e106]:
              - generic [ref=e107]: "!"
              - text: Activas
          - heading "Órdenes Activas" [level=4] [ref=e108]
          - paragraph [ref=e109]: "1"
        - generic [ref=e110]:
          - generic [ref=e111]:
            - generic [ref=e113]: 
            - generic [ref=e114]:
              - generic [ref=e115]: 
              - text: +5%
          - heading "Completadas" [level=4] [ref=e116]
          - paragraph [ref=e117]: "0"
      - generic [ref=e118]:
        - generic [ref=e119]:
          - generic [ref=e120]:
            - heading " Órdenes Recientes" [level=3] [ref=e121]:
              - generic [ref=e122]: 
              - text: Órdenes Recientes
            - link "Ver todas " [ref=e123] [cursor=pointer]:
              - /url: /work-orders
              - text: Ver todas
              - generic [ref=e124]: 
          - link "#7 - Carlos Rodríguez  chery tiggo  26/04/2026 $0 Recepción" [ref=e126] [cursor=pointer]:
            - /url: /work-orders/show/7
            - generic [ref=e128]:
              - generic [ref=e129]: "#7 - Carlos Rodríguez"
              - generic [ref=e130]:
                - generic [ref=e131]:
                  - generic [ref=e132]: 
                  - text: chery tiggo
                - generic [ref=e133]:
                  - generic [ref=e134]: 
                  - text: 26/04/2026
            - generic [ref=e135]: $0
            - generic [ref=e136]: Recepción
        - generic [ref=e137]:
          - generic [ref=e138]:
            - heading " Actividad Reciente" [level=3] [ref=e140]:
              - generic [ref=e141]: 
              - text: Actividad Reciente
            - generic [ref=e142]:
              - generic [ref=e143]:
                - generic [ref=e144]: 
                - heading "Sin actividad reciente" [level=4] [ref=e145]
              - generic [ref=e146]:
                - generic [ref=e148]: +
                - generic [ref=e149]:
                  - generic [ref=e150]:
                    - strong [ref=e151]: Sistema
                    - text: registró una nueva orden de trabajo
                  - generic [ref=e152]:
                    - generic [ref=e153]: 
                    - text: hace 2 horas
              - generic [ref=e154]:
                - generic [ref=e156]: 
                - generic [ref=e157]:
                  - generic [ref=e158]:
                    - strong [ref=e159]: Admin
                    - text: actualizó el estado de una orden
                  - generic [ref=e160]:
                    - generic [ref=e161]: 
                    - text: hace 4 horas
          - generic [ref=e162]:
            - generic [ref=e163]:
              - heading " Estado de Herramientas" [level=3] [ref=e164]:
                - generic [ref=e165]: 
                - text: Estado de Herramientas
              - link "Ver " [ref=e166] [cursor=pointer]:
                - /url: /tools
                - text: Ver
                - generic [ref=e167]: 
            - generic [ref=e168]:
              - generic [ref=e169]:
                - generic [ref=e171]: 
                - generic [ref=e173]:
                  - strong [ref=e174]: Bodega
                  - text: 0 herramientas registradas
              - generic [ref=e175]:
                - generic [ref=e177]: 
                - generic [ref=e179]:
                  - strong [ref=e180]: Préstamos
                  - text: 0 herramientas prestadas
              - generic [ref=e181]:
                - generic [ref=e183]: "!"
                - generic [ref=e185]:
                  - strong [ref=e186]: Pendientes
                  - text: 0 solicitudes por aprobar
```

# Test source

```ts
  179 |       const hasXSS = await page.locator('text=<script>alert(1)</script>').count() > 0 ||
  180 |                      await page.locator('body:has-text("alert(1)")').count() > 0;
  181 |       
  182 |       await context.close();
  183 |       
  184 |       if (hasXSS) {
  185 |         console.error('❌ XSS VULNERABLE: Script ejecutado');
  186 |         await captureScreenshot(page, 'pentest-xss-executed');
  187 |       } else {
  188 |         console.log('✅ XSS test completado');
  189 |       }
  190 |     } catch (e) {
  191 |       console.log(`⚠️ Error en test XSS: ${e.message}`);
  192 |       await context.close().catch(() => {});
  193 |     }
  194 |   });
  195 | 
  196 |   // ==================== 4. CSRF BYPASS ====================
  197 |   
  198 |   test('🚨 [ALTO] CSRF - Bypass de protección', async ({ browser }) => {
  199 |     console.log('\n🛡️ Probando CSRF...');
  200 |     
  201 |     // Intentar POST sin token CSRF
  202 |     const csrfTestUrls = [
  203 |       { url: '/clients/create', data: { name: 'CSRF Test', email: 'csrf@test.com' } },
  204 |       { url: '/vehicles/create', data: { brand: 'CSRF', model: 'Test', plate: 'CSRF123' } },
  205 |     ];
  206 |     
  207 |     const vulnerabilities = [];
  208 |     
  209 |     for (const test of csrfTestUrls) {
  210 |       try {
  211 |         // Request sin CSRF token
  212 |         const response = await browser.newContext().newPage().goto(`${BASE_URL}${test.url}`, {
  213 |           method: 'POST',
  214 |           data: test.data,
  215 |           headers: {
  216 |             'Content-Type': 'application/x-www-form-urlencoded',
  217 |           }
  218 |         });
  219 |         
  220 |         const status = response.status();
  221 |         const body = await response.text().catch(() => '');
  222 |         
  223 |         // Si acepta el request sin CSRF token
  224 |         if (status === 200 && !body.includes('CSRF') && !body.includes('token')) {
  225 |           vulnerabilities.push(test.url);
  226 |           console.error(`❌ CSRF VULNERABLE: ${test.url} acepta requests sin token`);
  227 |         } else {
  228 |           console.log(`✅ ${test.url} - CSRF protegido`);
  229 |         }
  230 |       } catch (e) {
  231 |         console.log(`⚠️ ${test.url} - Error: ${e.message}`);
  232 |       }
  233 |     }
  234 |     
  235 |     expect(vulnerabilities.length, 'CSRF debería rechazar requests sin token').toBe(0);
  236 |   });
  237 | 
  238 |   // ==================== 5. FUERZA BRUTA EN LOGIN ====================
  239 |   
  240 |   test('🚨 [MEDIO] Rate Limiting - Fuerza bruta', async ({ browser }) => {
  241 |     const context = await browser.newContext();
  242 |     const page = await context.newPage();
  243 |     console.log('\n🔨 Probando rate limiting...');
  244 |     
  245 |     const attempts = 10;
  246 |     const startTime = Date.now();
  247 |     
  248 |     for (let i = 0; i < attempts; i++) {
  249 |       await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  250 |       await page.fill('input[name="email"]', `test${i}@test.com`);
  251 |       await page.fill('input[name="password"]', `wrongpassword${i}`);
  252 |       await page.click('button[type="submit"]');
  253 |       await page.waitForTimeout(500);
  254 |     }
  255 |     
  256 |     const duration = Date.now() - startTime;
  257 |     const avgTime = duration / attempts;
  258 |     
  259 |     console.log(`⏱️ ${attempts} intentos en ${duration}ms (${avgTime.toFixed(0)}ms/promedio)`);
  260 |     
  261 |     // Si no hay rate limiting, los intentos serán rápidos
  262 |     if (avgTime < 1000) {
  263 |       console.warn('⚠️ No se detectó rate limiting en login');
  264 |     } else {
  265 |       console.log('✅ Posible rate limiting detectado');
  266 |     }
  267 |   });
  268 | 
  269 |   // ==================== 6. EXPOSICIÓN DE INFORMACIÓN ====================
  270 |   
  271 |   test('🚨 [MEDIO] Information Disclosure', async ({ browser }) => {
  272 |     console.log('\n📋 Buscando exposición de información...');
  273 |     
  274 |     const context = await browser.newContext();
  275 |     const page = await context.newPage();
  276 |     
  277 |     // Login
  278 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
> 279 |     await page.fill('input[name="email"]', 'admin@torque.com');
      |                ^ TimeoutError: page.fill: Timeout 30000ms exceeded.
  280 |     await page.fill('input[name="password"]', 'admin123');
  281 |     await page.click('button[type="submit"]');
  282 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
  283 |     
  284 |     const sensitivePaths = [
  285 |       '/.env',
  286 |       '/config.php',
  287 |       '/.htaccess',
  288 |       '/phpinfo.php',
  289 |       '/test-login.php',
  290 |       '/fix-password.php',
  291 |       '/server-info',
  292 |     ];
  293 |     
  294 |     const exposedPaths = [];
  295 |     
  296 |     for (const path of sensitivePaths) {
  297 |       try {
  298 |         const response = await page.goto(`${BASE_URL}${path}`, { timeout: 5000 });
  299 |         const status = response?.status() || 0;
  300 |         
  301 |         if (status === 200) {
  302 |           const content = await page.locator('body').textContent();
  303 |           
  304 |           // Buscar información sensible
  305 |           const hasSensitiveInfo = /password|database|config|DB_|API_KEY|SECRET/i.test(content);
  306 |           
  307 |           if (hasSensitiveInfo) {
  308 |             exposedPaths.push({ path, reason: 'Contiene info sensible' });
  309 |             console.error(`❌ EXPOSICIÓN: ${path} contiene información sensible`);
  310 |             await captureScreenshot(page, `pentest-info-disclosure-${path.replace(/\//g, '')}`);
  311 |           }
  312 |         }
  313 |       } catch (e) {
  314 |         // Ignorar errores
  315 |       }
  316 |     }
  317 |     
  318 |     await context.close();
  319 |     
  320 |     expect(exposedPaths.length, 'No debería exponer información sensible').toBe(0);
  321 |   });
  322 | 
  323 |   // ==================== 7. SQL INJECTION ====================
  324 |   
  325 |   test('🚨 [ALTO] SQL Injection', async ({ browser }) => {
  326 |     console.log('\n💾 Probando SQL Injection...');
  327 |     
  328 |     const context = await browser.newContext();
  329 |     const page = await context.newPage();
  330 |     
  331 |     // Login
  332 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  333 |     await page.fill('input[name="email"]', 'admin@torque.com');
  334 |     await page.fill('input[name="password"]', 'admin123');
  335 |     await page.click('button[type="submit"]');
  336 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
  337 |     
  338 |     const sqlPayloads = [
  339 |       "' OR '1'='1",
  340 |       "' UNION SELECT * FROM users --",
  341 |       "1; DROP TABLE users; --",
  342 |       "' OR 1=1#",
  343 |       "admin'--",
  344 |     ];
  345 |     
  346 |     // Probar en búsquedas
  347 |     try {
  348 |       await page.goto(`${BASE_URL}/clients`, { waitUntil: 'networkidle' });
  349 |       
  350 |       const searchInput = page.locator('input[type="search"], input[name="search"]').first();
  351 |       if (await searchInput.isVisible({ timeout: 3000 }).catch(() => false)) {
  352 |         for (const payload of sqlPayloads) {
  353 |           await searchInput.fill(payload);
  354 |           await searchInput.press('Enter');
  355 |           await page.waitForTimeout(1000);
  356 |           
  357 |           // Buscar errores SQL
  358 |           const bodyText = await page.locator('body').textContent();
  359 |           const hasSqlError = /sql|mysql|syntax|error in your SQL/i.test(bodyText);
  360 |           
  361 |           if (hasSqlError) {
  362 |             console.error(`❌ SQL Injection detectado con payload: ${payload}`);
  363 |             await captureScreenshot(page, 'pentest-sqli-error');
  364 |             break;
  365 |           }
  366 |         }
  367 |       }
  368 |       
  369 |       console.log('✅ SQL Injection test completado');
  370 |     } catch (e) {
  371 |       console.log(`⚠️ Error en SQLi test: ${e.message}`);
  372 |     }
  373 |   });
  374 | 
  375 |   // ==================== 8. HTTP SECURITY HEADERS ====================
  376 |   
  377 |   test('🚨 [BAJO] Security Headers', async ({ browser }) => {
  378 |     console.log('\n🔒 Verificando headers de seguridad...');
  379 |     
```