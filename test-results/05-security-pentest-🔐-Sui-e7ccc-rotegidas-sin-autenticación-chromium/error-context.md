# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 05-security-pentest.spec.js >> 🔐 Suite Pentesting - Auditoría de Seguridad >> 🚨 [CRÍTICO] Rutas protegidas sin autenticación
- Location: tests\05-security-pentest.spec.js:16:3

# Error details

```
Error: No debería haber rutas accesibles sin login

expect(received).toBe(expected) // Object.is equality

Expected: 0
Received: 10
```

# Test source

```ts
  1   | /**
  2   |  * @fileoverview Tests de Pentesting - Búsqueda de vulnerabilidades
  3   |  * @description Pruebas de seguridad automatizadas
  4   |  * @author Pentester
  5   |  */
  6   | 
  7   | const { test, expect } = require('@playwright/test');
  8   | const { captureScreenshot } = require('./helpers/utils');
  9   | 
  10  | test.describe('🔐 Suite Pentesting - Auditoría de Seguridad', () => {
  11  |   
  12  |   const BASE_URL = process.env.BASE_URL || 'http://localhost/torque';
  13  |   
  14  |   // ==================== 1. BYPASS DE AUTENTICACIÓN ====================
  15  |   
  16  |   test('🚨 [CRÍTICO] Rutas protegidas sin autenticación', async ({ browser }) => {
  17  |     console.log('\n🔓 Probando acceso sin autenticación...');
  18  |     
  19  |     const context = await browser.newContext(); // Sin cookies
  20  |     const page = await context.newPage();
  21  |     
  22  |     const protectedRoutes = [
  23  |       '/dashboard',
  24  |       '/work-orders',
  25  |       '/clients',
  26  |       '/vehicles',
  27  |       '/services',
  28  |       '/parts',
  29  |       '/tools',
  30  |       '/reports',
  31  |       '/workshop-ops',
  32  |       '/users'
  33  |     ];
  34  |     
  35  |     const vulnerabilities = [];
  36  |     
  37  |     for (const route of protectedRoutes) {
  38  |       try {
  39  |         const response = await page.goto(`${BASE_URL}${route}`, { 
  40  |           waitUntil: 'networkidle',
  41  |           timeout: 10000 
  42  |         });
  43  |         
  44  |         const url = page.url();
  45  |         const status = response?.status() || 0;
  46  |         
  47  |         // Si NO redirige a login, es vulnerable
  48  |         const isRedirectedToLogin = url.includes('/login');
  49  |         const hasLoginForm = await page.locator('input[name="email"]').isVisible().catch(() => false);
  50  |         
  51  |         if (!isRedirectedToLogin && !hasLoginForm && status === 200) {
  52  |           vulnerabilities.push({ route, url, status });
  53  |           console.error(`❌ VULNERABLE: ${route} accesible sin login!`);
  54  |           await captureScreenshot(page, `pentest-auth-bypass-${route.replace(/\//g, '-')}`);
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
> 67  |     expect(vulnerabilities.length, 'No debería haber rutas accesibles sin login').toBe(0);
      |                                                                                   ^ Error: No debería haber rutas accesibles sin login
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
```