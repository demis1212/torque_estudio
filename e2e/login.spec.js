// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Torque Studio - Tests Funcionales', () => {
  
  test('página de login carga correctamente', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await expect(page).toHaveTitle(/Torque/);
    await expect(page.locator('input[name="email"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('input[name="password"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('button[type="submit"]')).toBeVisible({ timeout: 10000 });
  });

  test('login con CSRF funciona', async ({ page }) => {
    // Ir al login
    await page.goto('http://localhost/torque/login');
    
    // Llenar credenciales
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    
    // El CSRF token ya está en el formulario hidden, se enviará automáticamente
    // Hacer click en submit
    await page.click('button[type="submit"]');
    
    // Esperar redirección al dashboard
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    // Verificar que estamos en el dashboard
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('h1:has-text("Dashboard")')).toBeVisible({ timeout: 10000 });
  });

  test('navegación completa: login → órdenes', async ({ page }) => {
    // Login
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    // Navegar a órdenes
    await page.click('text=Órdenes');
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    await expect(page).toHaveURL(/work-orders/);
  });

  test('navegación completa: login → clientes', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Clientes');
    await page.waitForURL('**/clients', { timeout: 15000 });
    await expect(page).toHaveURL(/clients/);
  });

  test('navegación completa: login → vehículos', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Vehículos');
    await page.waitForURL('**/vehicles', { timeout: 15000 });
    await expect(page).toHaveURL(/vehicles/);
  });

  test('navegación: dashboard → servicios', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Servicios');
    await page.waitForURL('**/services', { timeout: 15000 });
    await expect(page).toHaveURL(/services/);
  });

  test('navegación: dashboard → repuestos', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Inventario');
    await page.waitForURL('**/parts', { timeout: 15000 });
    await expect(page).toHaveURL(/parts/);
  });

});
