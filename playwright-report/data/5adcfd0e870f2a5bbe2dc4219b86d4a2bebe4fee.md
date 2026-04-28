# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [CRÍTICO] Mass Assignment
- Location: 05-security-pentest.spec.js:445:3

# Error details

```
TimeoutError: page.waitForURL: Timeout 15000ms exceeded.
=========================== logs ===========================
waiting for navigation to "**/dashboard" until "load"
  navigated to "chrome-error://chromewebdata/"
============================================================
```

# Test source

```ts
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
  453 |     await page.fill('input[name="email"]', 'admin@torque.com');
  454 |     await page.fill('input[name="password"]', 'admin123');
  455 |     await page.click('button[type="submit"]');
> 456 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
      |                ^ TimeoutError: page.waitForURL: Timeout 15000ms exceeded.
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