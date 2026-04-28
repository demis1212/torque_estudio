# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [ALTO] XSS - Cross-Site Scripting
- Location: 05-security-pentest.spec.js:143:3

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
  55  |         } else {
  56  |           console.log(`✅ ${route} - Protegido correctamente`);
  57  |         }
  58  |       } catch (e) {
  59  |         console.log(`⚠️ ${route} - Error: ${e.message}`);
  60  |       }
  61  |     }
  62  |     
  63  |     await context.close();
  64  |     
  65  |     // Reporte
  66  |     console.log(`\n🔴 Rutas sin protección: ${vulnerabilities.length}`);
  67  |     expect(vulnerabilities.length, 'No debería haber rutas accesibles sin login').toBe(0);
  68  |   });
  69  | 
  70  |   // ==================== 2. IDOR - INSECURE DIRECT OBJECT REFERENCE ====================
  71  |   
  72  |   test('🚨 [ALTO] IDOR - Acceso a recursos de otros usuarios', async ({ browser }) => {
  73  |     console.log('\n🔑 Probando IDOR...');
  74  |     
  75  |     const findings = [];
  76  |     
  77  |     try {
  78  |       // Crear nuevo contexto y hacer login manual
  79  |       const context = await browser.newContext();
  80  |       const page = await context.newPage();
  81  |       
  82  |       // Login con timeout extendido
  83  |       await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 20000 });
  84  |       
  85  |       // Verificar que el formulario existe
  86  |       const emailField = page.locator('input[name="email"]');
  87  |       if (!(await emailField.isVisible().catch(() => false))) {
  88  |         console.log('⚠️ No se pudo cargar el login, saltando test IDOR');
  89  |         await context.close();
  90  |         return;
  91  |       }
  92  |       
  93  |       await emailField.fill('admin@torque.com');
  94  |       await page.fill('input[name="password"]', 'admin123');
  95  |       await page.click('button[type="submit"]');
  96  |       
  97  |       // Esperar navegación con timeout
  98  |       await page.waitForURL('**/dashboard', { timeout: 20000, waitUntil: 'domcontentloaded' });
  99  |       
  100 |       const idorTests = [
  101 |         { url: '/work-orders/edit/99999', name: 'Orden inexistente' },
  102 |         { url: '/clients/edit/99999', name: 'Cliente inexistente' },
  103 |         { url: '/vehicles/edit/99999', name: 'Vehículo inexistente' },
  104 |         { url: '/users/edit/2', name: 'Otro usuario (IDOR)' },
  105 |       ];
  106 |       
  107 |       for (const test of idorTests) {
  108 |         try {
  109 |           await page.goto(`${BASE_URL}${test.url}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
  110 |           await page.waitForTimeout(500);
  111 |           
  112 |           const bodyText = await page.locator('body').textContent().catch(() => '');
  113 |           const hasAccessDenied = /acceso denegado|denied|403|no autorizado/i.test(bodyText);
  114 |           const hasNotFound = /no encontrado|not found|404/i.test(bodyText);
  115 |           const isLoginPage = await page.locator('input[name="email"]').isVisible().catch(() => false);
  116 |           
  117 |           // Si permite ver el formulario de edición sin error de permisos
  118 |           const hasEditForm = await page.locator('form[action*="edit"], input[name="name"]').count() > 0;
  119 |           
  120 |           if (hasEditForm && !hasAccessDenied && !hasNotFound && !isLoginPage) {
  121 |             findings.push({ url: test.url, name: test.name });
  122 |             console.error(`❌ IDOR VULNERABLE: ${test.name}`);
  123 |             await captureScreenshot(page, `pentest-idor-${test.name.replace(/\s+/g, '-')}`);
  124 |           } else {
  125 |             console.log(`✅ ${test.name} - Protegido`);
  126 |           }
  127 |         } catch (e) {
  128 |           console.log(`⚠️ ${test.name} - Error: ${e.message}`);
  129 |         }
  130 |       }
  131 |       
  132 |       await context.close();
  133 |     } catch (e) {
  134 |       console.log(`⚠️ Error general en test IDOR: ${e.message}`);
  135 |     }
  136 |     
  137 |     // Solo fallar si encontramos vulnerabilidades reales
  138 |     expect(findings.length, 'No debería permitir IDOR').toBe(0);
  139 |   });
  140 | 
  141 |   // ==================== 3. XSS - CROSS-SITE SCRIPTING ====================
  142 |   
  143 |   test('🚨 [ALTO] XSS - Cross-Site Scripting', async ({ browser }) => {
  144 |     console.log('\n💉 Probando XSS...');
  145 |     
  146 |     // Crear nuevo contexto y login
  147 |     const context = await browser.newContext();
  148 |     const page = await context.newPage();
  149 |     
  150 |     // Login
  151 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  152 |     await page.fill('input[name="email"]', 'admin@torque.com');
  153 |     await page.fill('input[name="password"]', 'admin123');
  154 |     await page.click('button[type="submit"]');
> 155 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
      |                ^ TimeoutError: page.waitForURL: Timeout 15000ms exceeded.
  156 |     
  157 |     // Payloads XSS comunes
  158 |     const xssPayloads = [
  159 |       '<script>alert("XSS")</script>',
  160 |       '<img src=x onerror=alert("XSS")>',
  161 |       '"><script>alert(String.fromCharCode(88,83,83))</script>',
  162 |       "' onclick='alert(1)",
  163 |       '<body onload=alert("XSS")>',
  164 |     ];
  165 |     
  166 |     // Probar en campo de nombre de cliente
  167 |     try {
  168 |       await page.goto(`${BASE_URL}/clients/create`, { waitUntil: 'networkidle' });
  169 |       
  170 |       const xssPayload = xssPayloads[0];
  171 |       await page.fill('input[name="name"]', xssPayload);
  172 |       await page.fill('input[name="email"]', `xss@test.com`);
  173 |       await page.fill('input[name="phone"]', '1234567890');
  174 |       
  175 |       // Verificar si el payload está escapado en la página
  176 |       const nameFieldHTML = await page.locator('input[name="name"]').inputValue();
  177 |       
  178 |       // Verificar si el script se ejecutó (buscando alerta o cambio en DOM)
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
```