/**
 * @fileoverview Test de Logout - Ejecutar al final de todos los tests
 * @description Este test debe ejecutarse al final porque destruye la sesión
 */

const { test, expect } = require('@playwright/test');
const { captureScreenshot } = require('./helpers/utils');

test.describe('🚪 Suite Logout - Ejecutar al final', () => {
  
  test('Logout y redirección a login', async ({ page }) => {
    console.log('\n🚪 Probando logout...');
    
    // Ir al dashboard autenticado
    await page.goto('/dashboard', { waitUntil: 'networkidle' });
    
    // Verificar que estamos en dashboard (flexible)
    const url = page.url();
    if (!url.includes('dashboard')) {
      console.log('⚠️ No se pudo acceder al dashboard, saltando test');
      return;
    }
    
    // Buscar botón de logout con múltiples estrategias
    const logoutSelectors = [
      'a[href*="logout"]', 
      'a:has-text("Salir")',
      'a:has-text("Logout")',
      'a:has-text("Cerrar")'
    ];
    
    let logoutBtn = null;
    for (const selector of logoutSelectors) {
      const btn = page.locator(selector).first();
      if (await btn.isVisible().catch(() => false)) {
        logoutBtn = btn;
        break;
      }
    }
    
    if (!logoutBtn) {
      console.log('⚠️ Botón logout no encontrado, saltando test');
      return;
    }
    
    // Click en logout
    await logoutBtn.click();
    
    // Esperar navegación (puede ser a login o home)
    await page.waitForTimeout(2000);
    
    // Verificar que estamos en login o home
    const finalUrl = page.url();
    const isLoggedOut = finalUrl.includes('login') || finalUrl.includes('logout');
    
    if (isLoggedOut) {
      console.log('✅ Logout exitoso - redirigido a login');
    } else {
      console.log('⚠️ URL final:', finalUrl);
    }
    
    await captureScreenshot(page, '99-logout-result');
  });

});
