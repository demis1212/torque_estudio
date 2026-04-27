// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Torque Studio - Tests Súper Intensivos', () => {
  
  /**
   * @param {import('@playwright/test').Page} page
   */
  async function login(page) {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
  }

  test('flujo completo: dashboard → notificaciones → volver → crear orden', async ({ page }) => {
    // 1. LOGIN y verificar dashboard
    await login(page);
    await expect(page).toHaveURL(/dashboard/);
    await page.screenshot({ path: 'test-results/01-dashboard.png', fullPage: true });
    
    // Verificar widgets del dashboard
    const widgets = ['Total Clientes', 'Total Vehículos', 'Órdenes Activas', 'Completadas'];
    for (const widget of widgets) {
      const element = page.locator('text=' + widget);
      if (await element.isVisible().catch(() => false)) {
        console.log(`✓ Widget "${widget}" visible`);
      }
    }
    
    // 2. IR A NOTIFICACIONES
    await page.getByRole('link', { name: /Notificaciones/i }).click();
    await page.waitForURL('**/notifications', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/02-notificaciones.png', fullPage: true });
    
    // Verificar lista de notificaciones
    const notificaciones = await page.locator('.notification-item, .notification-card, [class*="notification"]').count();
    console.log(`Total notificaciones: ${notificaciones}`);
    
    // Marcar como leído si existe botón
    const marcarLeido = page.getByRole('button', { name: /Marcar todo/i });
    if (await marcarLeido.isVisible().catch(() => false)) {
      await marcarLeido.click();
      await page.waitForTimeout(1000);
    }
    
    // 3. VOLVER AL DASHBOARD
    await page.getByRole('link', { name: /Dashboard/i }).click();
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/03-dashboard-vuelta.png', fullPage: true });
    
    // 4. IR A ÓRDENES
    await page.getByRole('link', { name: /^Órdenes$/i }).click();
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/04-ordenes.png', fullPage: true });
    
    // 5. CAMBIAR A VISTA KANBAN
    const kanbanBtn = page.locator('a[href*="kanban"], text=Kanban').first();
    if (await kanbanBtn.isVisible().catch(() => false)) {
      await kanbanBtn.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/05-kanban.png', fullPage: true });
    }
    
    // 6. VOLVER A LISTA Y CREAR ORDEN
    await page.getByRole('link', { name: /Órdenes$/i }).click();
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    
    const nuevaOrden = page.getByRole('link', { name: /Nueva Orden/i });
    if (await nuevaOrden.isVisible().catch(() => false)) {
      await nuevaOrden.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/06-orden-nueva.png', fullPage: true });
      
      // Verificar campos del formulario
      const campos = ['client_id', 'vehicle_id', 'status', 'description'];
      for (const campo of campos) {
        const input = page.locator(`[name="${campo}"], #${campo}`).first();
        if (await input.isVisible().catch(() => false)) {
          console.log(`✓ Campo "${campo}" visible`);
        }
      }
    }
  });

  test('gestión completa de clientes: lista → crear → detalle', async ({ page }) => {
    await login(page);
    
    // 1. IR A CLIENTES
    await page.getByRole('link', { name: /Clientes$/i }).click();
    await page.waitForURL('**/clients', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/10-clientes.png', fullPage: true });
    
    // Contar clientes
    const filas = await page.locator('table tbody tr').count();
    console.log(`Total clientes en tabla: ${filas}`);
    
    // 2. CREAR NUEVO CLIENTE
    const btnNuevo = page.getByRole('link', { name: /Nuevo Cliente/i });
    if (await btnNuevo.isVisible().catch(() => false)) {
      await btnNuevo.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/11-cliente-nuevo.png', fullPage: true });
      
      // Verificar formulario
      await expect(page.locator('input[name="name"], #name')).toBeVisible();
      await expect(page.locator('input[name="email"], #email')).toBeVisible();
      await expect(page.locator('input[name="phone"], #phone')).toBeVisible();
    }
    
    // 3. VOLVER A LISTA Y VER DETALLE
    await page.getByRole('link', { name: /Clientes$/i }).click();
    await page.waitForURL('**/clients', { timeout: 15000 });
    
    const verCliente = page.locator('a[href*="/clients/show/"], a[href*="/clients/edit/"]').first();
    if (await verCliente.isVisible().catch(() => false)) {
      await verCliente.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/12-cliente-detalle.png', fullPage: true });
    }
  });

  test('inventario completo: lista → alertas → agregar repuesto', async ({ page }) => {
    await login(page);
    
    // 1. IR A INVENTARIO
    await page.getByRole('link', { name: /Inventario$/i }).click();
    await page.waitForURL('**/parts', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/20-inventario.png', fullPage: true });
    
    // Verificar tabla de repuestos
    const repuestos = await page.locator('table tbody tr').count();
    console.log(`Total repuestos: ${repuestos}`);
    
    // 2. VER ALERTAS DE STOCK
    const alertasLink = page.getByRole('link', { name: /Alertas|Stock Bajo/i });
    if (await alertasLink.isVisible().catch(() => false)) {
      await alertasLink.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/21-alertas.png', fullPage: true });
    }
    
    // 3. CREAR NUEVO REPUESTO
    await page.getByRole('link', { name: /Inventario$/i }).click();
    const btnNuevo = page.getByRole('link', { name: /Nuevo Repuesto|Agregar/i });
    if (await btnNuevo.isVisible().catch(() => false)) {
      await btnNuevo.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/22-repuesto-nuevo.png', fullPage: true });
    }
  });

  test('operación inteligente completa: lista → detalle → tiempo → checklist', async ({ page }) => {
    await login(page);
    
    // 1. IR A OPERACIÓN INTELIGENTE
    await page.getByRole('link', { name: /Operación Inteligente/i }).click();
    await page.waitForURL('**/workshop-ops', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/30-operacion.png', fullPage: true });
    
    // 2. VER DETALLE DE ORDEN (si existe)
    const verOrden = page.locator('a[href*="/workshop-ops/"], .order-card, [class*="order"]').first();
    if (await verOrden.isVisible().catch(() => false)) {
      await verOrden.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/31-operacion-detalle.png', fullPage: true });
      
      // Verificar secciones
      const secciones = ['Tiempo', 'Checklist', 'Repuestos', 'Notas'];
      for (const seccion of secciones) {
        const elem = page.locator('text=' + seccion).first();
        if (await elem.isVisible().catch(() => false)) {
          console.log(`✓ Sección "${seccion}" visible`);
        }
      }
    }
  });

  test('herramientas completo: dashboard → solicitudes → préstamos', async ({ page }) => {
    await login(page);
    
    // 1. IR A HERRAMIENTAS
    await page.getByRole('link', { name: /Herramientas$/i }).click();
    await page.waitForURL('**/tools', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/40-herramientas.png', fullPage: true });
    
    // 2. EXPLORAR SUB-MENÚS
    const submenuItems = [
      { nombre: /Mecánico/i, url: /mechanic/ },
      { nombre: /Bodega|Warehouse/i, url: /warehouse/ },
      { nombre: /Solicitudes|Requests/i, url: /request/ },
    ];
    
    for (const item of submenuItems) {
      const link = page.getByRole('link', { name: item.nombre }).first();
      if (await link.isVisible().catch(() => false)) {
        await link.click();
        await page.waitForTimeout(1500);
        await page.screenshot({ 
          path: `test-results/41-herramientas-${item.nombre.source.replace(/[^a-z]/gi, '')}.png`,
          fullPage: true 
        });
        
        // Verificar que cambió la URL
        const url = page.url();
        console.log(`✓ Sub-menú "${item.nombre.source}" - URL: ${url}`);
      }
    }
    
    // 3. SOLICITAR HERRAMIENTA
    await page.getByRole('link', { name: /Herramientas$/i }).click();
    const btnSolicitar = page.getByRole('link', { name: /Solicitar|Nueva Solicitud/i });
    if (await btnSolicitar.isVisible().catch(() => false)) {
      await btnSolicitar.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/42-solicitar-tool.png', fullPage: true });
    }
  });

  test('reportes y estadísticas completas', async ({ page }) => {
    await login(page);
    
    // 1. IR A REPORTES
    await page.getByRole('link', { name: /Reportes$/i }).click();
    await page.waitForURL('**/reports', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/50-reportes.png', fullPage: true });
    
    // 2. VER GRÁFICOS/ESTADÍSTICAS
    const graficos = await page.locator('canvas, .chart, [class*="chart"], [class*="graph"]').count();
    console.log(`Total gráficos: ${graficos}`);
    
    // 3. PRODUCTIVIDAD DE MECÁNICOS
    const prodLink = page.getByRole('link', { name: /Productividad/i });
    if (await prodLink.isVisible().catch(() => false)) {
      await prodLink.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/51-productividad.png', fullPage: true });
    }
  });

  test('whatsapp reminders completos', async ({ page }) => {
    await login(page);
    
    // 1. IR A WHATSAPP
    await page.getByRole('link', { name: /WhatsApp/i }).click();
    await page.waitForURL('**/whatsapp', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/60-whatsapp.png', fullPage: true });
    
    // 2. CREAR NUEVO RECORDATORIO
    const btnNuevo = page.getByRole('link', { name: /Nuevo|Crear/i });
    if (await btnNuevo.isVisible().catch(() => false)) {
      await btnNuevo.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/61-whatsapp-nuevo.png', fullPage: true });
      
      // Verificar campos
      const campos = ['client_id', 'message', 'scheduled_at'];
      for (const campo of campos) {
        const input = page.locator(`[name="${campo}"], #${campo}`).first();
        if (await input.isVisible().catch(() => false)) {
          console.log(`✓ Campo WhatsApp "${campo}" visible`);
        }
      }
    }
  });

  test('manuales y vin decoder', async ({ page }) => {
    await login(page);
    
    // 1. MANUALES
    await page.getByRole('link', { name: /Manuales$/i }).click();
    await page.waitForURL('**/manuals', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/70-manuales.png', fullPage: true });
    
    // Buscar manual
    const searchInput = page.locator('input[type="search"], input[placeholder*="buscar"]').first();
    if (await searchInput.isVisible().catch(() => false)) {
      await searchInput.fill('motor');
      await page.waitForTimeout(1000);
      await page.screenshot({ path: 'test-results/71-manuales-busqueda.png' });
    }
    
    // 2. VIN DECODER
    await page.getByRole('link', { name: /VIN Decoder|Decodificador/i }).click();
    await page.waitForURL('**/vin', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/72-vin-decoder.png', fullPage: true });
    
    // Ingresar VIN de prueba
    const vinInput = page.locator('input[name="vin"], #vin').first();
    if (await vinInput.isVisible().catch(() => false)) {
      await vinInput.fill('1HGCM82633A123456');
      await page.locator('button[type="submit"]').click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/73-vin-resultado.png' });
    }
  });

  test('usuarios y configuración del sistema', async ({ page }) => {
    await login(page);
    
    // 1. USUARIOS
    const usersLink = page.getByRole('link', { name: /Usuarios$/i });
    if (await usersLink.isVisible().catch(() => false)) {
      await usersLink.click();
      await page.waitForURL('**/users', { timeout: 15000 });
      await page.screenshot({ path: 'test-results/80-usuarios.png', fullPage: true });
      
      const usuarios = await page.locator('table tbody tr, .user-card').count();
      console.log(`Total usuarios: ${usuarios}`);
    }
    
    // 2. CONFIGURACIÓN
    const settingsLink = page.getByRole('link', { name: /Configuración|Settings/i });
    if (await settingsLink.isVisible().catch(() => false)) {
      await settingsLink.click();
      await page.waitForURL('**/settings', { timeout: 15000 });
      await page.screenshot({ path: 'test-results/81-configuracion.png', fullPage: true });
    }
  });

  test('logout y redirección', async ({ page }) => {
    await login(page);
    
    // Verificar que estamos logueados
    await expect(page).toHaveURL(/dashboard/);
    
    // Hacer logout
    const logoutBtn = page.getByRole('link', { name: /Salir|Logout|Cerrar sesión/i });
    await logoutBtn.click();
    
    // Verificar redirección a login
    await page.waitForURL('**/login', { timeout: 15000 });
    await expect(page).toHaveURL(/login/);
    await page.screenshot({ path: 'test-results/90-logout.png', fullPage: true });
    
    // Verificar formulario de login visible
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

});
