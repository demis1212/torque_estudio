# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [CRÍTICO] Mass Assignment
- Location: tests\05-security-pentest.spec.js:445:3

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
              - text: +2%
          - heading "Total Clientes" [level=4] [ref=e92]
          - paragraph [ref=e93]: "5"
        - generic [ref=e94]:
          - generic [ref=e95]:
            - generic [ref=e97]: 
            - generic [ref=e98]:
              - generic [ref=e99]: 
              - text: +3%
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
              - text: +12%
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
  380 |     const response = await browser.newContext().newPage().goto(`${BASE_URL}/login`);
  381 |     const headers = response.headers();
  382 |     
  383 |     const securityHeaders = {
  384 |       'X-Frame-Options': 'Protección contra clickjacking',
  385 |       'X-Content-Type-Options': 'Previene MIME sniffing',
  386 |       'X-XSS-Protection': 'Filtro XSS legacy',
  387 |       'Content-Security-Policy': 'CSP moderno',
  388 |       'Strict-Transport-Security': 'HSTS',
  389 |       'Referrer-Policy': 'Control de referrer',
  390 |     };
  391 |     
  392 |     const missing = [];
  393 |     
  394 |     for (const [header, description] of Object.entries(securityHeaders)) {
  395 |       const headerLower = header.toLowerCase();
  396 |       const hasHeader = Object.keys(headers).some(h => h.toLowerCase() === headerLower);
  397 |       
  398 |       if (!hasHeader) {
  399 |         missing.push({ header, description });
  400 |         console.warn(`⚠️ Falta header: ${header} (${description})`);
  401 |       }
  402 |     }
  403 |     
  404 |     console.log(`\n📊 Headers de seguridad: ${Object.keys(securityHeaders).length - missing.length}/${Object.keys(securityHeaders).length}`);
  405 |   });
  406 | 
  407 |   // ==================== 9. SESSION SECURITY ====================
  408 |   
  409 |   test('🚨 [MEDIO] Session Security', async ({ browser }) => {
  410 |     console.log('\n🔑 Verificando seguridad de sesión...');
  411 |     
  412 |     const context = await browser.newContext();
  413 |     const page = await context.newPage();
  414 |     
  415 |     // Login
  416 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  417 |     await page.fill('input[name="email"]', 'admin@torque.com');
  418 |     await page.fill('input[name="password"]', 'admin123');
  419 |     await page.click('button[type="submit"]');
  420 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
  421 |     
  422 |     // Verificar cookies
  423 |     const cookies = await context.cookies();
  424 |     const sessionCookie = cookies.find(c => c.name.includes('PHPSESSID'));
  425 |     
  426 |     if (sessionCookie) {
  427 |       console.log(`🍪 Cookie de sesión encontrada:`);
  428 |       console.log(`   - HttpOnly: ${sessionCookie.httpOnly}`);
  429 |       console.log(`   - Secure: ${sessionCookie.secure}`);
  430 |       console.log(`   - SameSite: ${sessionCookie.sameSite}`);
  431 |       
  432 |       if (!sessionCookie.httpOnly) {
  433 |         console.error('❌ Cookie sin HttpOnly - Vulnerable a XSS');
  434 |       }
  435 |       if (!sessionCookie.secure) {
  436 |         console.warn('⚠️ Cookie sin Secure flag');
  437 |       }
  438 |     }
  439 |     
  440 |     await context.close();
  441 |   });
  442 | 
  443 |   // ==================== 10. MASS ASSIGNMENT ====================
  444 |   
  445 |   test('🚨 [CRÍTICO] Mass Assignment', async ({ browser }) => {
  446 |     console.log('\n📦 Probando Mass Assignment...');
  447 |     
  448 |     const context = await browser.newContext();
  449 |     const page = await context.newPage();
  450 |     
  451 |     // Login
  452 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
> 453 |     await page.fill('input[name="email"]', 'admin@torque.com');
      |                ^ TimeoutError: page.fill: Timeout 30000ms exceeded.
  454 |     await page.fill('input[name="password"]', 'admin123');
  455 |     await page.click('button[type="submit"]');
  456 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
  457 |     
  458 |     // Intentar crear con campos no permitidos
  459 |     try {
  460 |       await page.goto(`${BASE_URL}/work-orders/create`, { waitUntil: 'networkidle' });
  461 |       
  462 |       // Intentar enviar campos adicionales vía JS injection en formulario
  463 |       const result = await page.evaluate(() => {
  464 |         const form = document.querySelector('form');
  465 |         if (form) {
  466 |           // Agregar campo oculto
  467 |           const hiddenField = document.createElement('input');
  468 |           hiddenField.type = 'hidden';
  469 |           hiddenField.name = 'status';  // Campo que debería ser solo lectura
  470 |           hiddenField.value = 'completed';
  471 |           form.appendChild(hiddenField);
  472 |           return 'Campo agregado';
  473 |         }
  474 |         return 'No hay formulario';
  475 |       });
  476 |       
  477 |       console.log('Mass Assignment test:', result);
  478 |       
  479 |     } catch (e) {
  480 |       console.log(`⚠️ Error en Mass Assignment: ${e.message}`);
  481 |     }
  482 |   });
  483 | 
  484 | });
  485 | 
```