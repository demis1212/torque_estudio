# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [MEDIO] Rate Limiting - Fuerza bruta
- Location: tests\05-security-pentest.spec.js:240:3

# Error details

```
TimeoutError: page.fill: Timeout 30000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Test source

```ts
  150 |     // Login
  151 |     await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
  152 |     await page.fill('input[name="email"]', 'admin@torque.com');
  153 |     await page.fill('input[name="password"]', 'admin123');
  154 |     await page.click('button[type="submit"]');
  155 |     await page.waitForURL('**/dashboard', { timeout: 15000 });
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
> 250 |       await page.fill('input[name="email"]', `test${i}@test.com`);
      |                  ^ TimeoutError: page.fill: Timeout 30000ms exceeded.
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
  279 |     await page.fill('input[name="email"]', 'admin@torque.com');
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
```