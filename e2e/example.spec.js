// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Torque Studio - Navegación', () => {
  
  test('página de login carga correctamente', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await expect(page).toHaveTitle(/Torque/);
    await expect(page.locator('input[name="email"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('input[name="password"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('button[type="submit"]')).toBeVisible({ timeout: 10000 });
  });

  test('login funciona con credenciales válidas', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    await expect(page).toHaveURL(/dashboard/);
  });

  test('menú de navegación visible en dashboard', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    // Verificar elementos del menú (usando role link para evitar múltiples matches)
    await expect(page.locator('h1:has-text("Dashboard")')).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('link', { name: /Órdenes/ })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('link', { name: /Clientes/ })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('link', { name: /Vehículos/ })).toBeVisible({ timeout: 10000 });
  });

  test('navegación a /work-orders funciona', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Órdenes');
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    await expect(page).toHaveURL(/work-orders/);
  });

  test('navegación a /clients funciona', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Clientes');
    await page.waitForURL('**/clients', { timeout: 15000 });
    await expect(page).toHaveURL(/clients/);
  });

  test('navegación a /vehicles funciona', async ({ page }) => {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    
    await page.click('text=Vehículos');
    await page.waitForURL('**/vehicles', { timeout: 15000 });
    await expect(page).toHaveURL(/vehicles/);
  });

});
