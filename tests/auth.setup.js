/**
 * @fileoverview Setup de Autenticación - Guarda estado de sesión
 * @description Realiza login una sola vez y guarda el estado para reusar en todos los tests
 */

const { test: setup, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const TEST_USER = {
  email: process.env.TEST_EMAIL || 'admin@torque.com',
  password: process.env.TEST_PASSWORD || 'admin123'
};

// Asegurar que existe el directorio .auth
const authDir = path.join(__dirname, '.auth');
if (!fs.existsSync(authDir)) {
  fs.mkdirSync(authDir, { recursive: true });
}

setup('🔄 Autenticación - Login y guardar estado', async ({ page, baseURL }) => {
  console.log(`\n🔑 Intentando login en: ${baseURL || 'http://localhost/torque'}/login`);
  console.log(`👤 Usuario: ${TEST_USER.email}`);
  
  // 1. Navegar a login
  await page.goto('/login', { waitUntil: 'networkidle' });
  
  // 2. Verificar que estamos en la página de login
  const emailField = page.locator('input[name="email"]').first();
  const passwordField = page.locator('input[name="password"]').first();
  
  await expect(emailField, 'Campo email debe estar visible')
    .toBeVisible({ timeout: 15000 });
  
  await expect(passwordField, 'Campo password debe estar visible')
    .toBeVisible({ timeout: 15000 });
  
  // 3. Verificar que existe el CSRF token
  const csrfToken = await page.locator('input[name="csrf_token"]').inputValue().catch(() => {
    console.warn('⚠️ No se encontró CSRF token');
    return null;
  });
  
  if (csrfToken) {
    console.log('✅ CSRF token encontrado');
  }
  
  // 4. Llenar credenciales
  await emailField.fill(TEST_USER.email);
  await passwordField.fill(TEST_USER.password);
  
  // 5. Capturar estado antes de submit
  await page.screenshot({ path: 'test-results/setup-login-filled.png' });
  
  // 6. Click en login
  const submitButton = page.locator('button[type="submit"]').first();
  await submitButton.click();
  
  // 7. Esperar navegación con timeout extendido
  await page.waitForURL('**/dashboard', { timeout: 30000 });
  
  // 8. Verificar que NO hay mensaje de error
  const errorMessage = await page.locator('.error-message').textContent().catch(() => '');
  if (errorMessage && errorMessage.includes('Credenciales')) {
    console.error('❌ Login falló - Credenciales incorrectas');
    await page.screenshot({ path: 'test-results/setup-login-error.png', fullPage: true });
    throw new Error(`Login falló: ${errorMessage}`);
  }
  
  // 9. Verificar login exitoso - URL contiene dashboard
  const currentUrl = page.url();
  console.log(`📍 URL actual: ${currentUrl}`);
  
  if (!currentUrl.includes('dashboard')) {
    console.error('❌ No se redirigió al dashboard');
    await page.screenshot({ path: 'test-results/setup-login-redirect-fail.png', fullPage: true });
    throw new Error('Redirección fallida - No está en dashboard');
  }
  
  // 10. Esperar a que la página del dashboard cargue completamente
  await page.waitForLoadState('networkidle', { timeout: 15000 });
  await page.waitForTimeout(2000);
  
  // 11. Verificar elementos del dashboard (flexible)
  const pageContent = await page.content();
  const hasDashboardContent = pageContent.includes('Dashboard') || 
                              pageContent.includes('dashboard') ||
                              pageContent.includes('sidebar') ||
                              pageContent.includes('nav');
  
  if (!hasDashboardContent) {
    console.warn('⚠️ No se detectó contenido típico del dashboard');
  }
  
  // 12. Captura de éxito
  await page.screenshot({ path: 'test-results/setup-login-success.png', fullPage: true });
  
  // 13. Guardar estado de autenticación
  const storagePath = path.join(__dirname, '.auth', 'user.json');
  await page.context().storageState({ path: storagePath });
  
  // Verificar que el archivo se creó
  if (fs.existsSync(storagePath)) {
    const stats = fs.statSync(storagePath);
    console.log(`✅ Estado guardado: ${storagePath} (${stats.size} bytes)`);
  } else {
    throw new Error('No se pudo guardar el estado de autenticación');
  }
  
  console.log('✅ Login exitoso');
});

setup('🔄 Verificar sesión persistida', async ({ browser }) => {
  // Crear contexto con el estado guardado
  const storagePath = path.join(__dirname, '.auth', 'user.json');
  
  if (!fs.existsSync(storagePath)) {
    console.log('⚠️ No hay estado guardado, saltando verificación');
    return;
  }
  
  const context = await browser.newContext({ 
    storageState: storagePath 
  });
  const page = await context.newPage();
  
  // Navegar directamente al dashboard sin login
  await page.goto('/dashboard', { waitUntil: 'networkidle' });
  
  // Verificar que sigue autenticado (no redirigió a login)
  const currentUrl = page.url();
  console.log(`📍 URL en verificación: ${currentUrl}`);
  
  if (currentUrl.includes('login')) {
    console.error('❌ Sesión no persistió - Redirigido a login');
    throw new Error('Sesión no persistió');
  }
  
  console.log('✅ Sesión persistida correctamente');
  await context.close();
});
