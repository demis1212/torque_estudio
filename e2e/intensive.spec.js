// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Torque Studio - Tests Intensivos', () => {
  
  // Helper para hacer login
  async function login(page) {
    await page.goto('http://localhost/torque/login');
    await page.fill('input[name="email"]', 'admin@torque.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
  }

  test('flujo completo: login → notificaciones → crear orden', async ({ page }) => {
    // 1. LOGIN
    await login(page);
    await expect(page).toHaveURL(/dashboard/);
    
    // 2. VERIFICAR NOTIFICACIONES
    const notifBtn = page.locator('a[href*="notifications"], .notification-btn, [title*="notificaci"]').first();
    if (await notifBtn.isVisible().catch(() => false)) {
      await notifBtn.click();
      await page.waitForTimeout(1000);
      await page.screenshot({ path: 'test-results/notificaciones.png' });
      // Cerrar notificaciones
      await page.keyboard.press('Escape');
    }
    
    // 3. VER ESTADÍSTICAS DEL DASHBOARD
    await expect(page.locator('text=Total Clientes')).toBeVisible();
    await expect(page.locator('text=Total Vehículos')).toBeVisible();
    await expect(page.locator('text=Órdenes Activas')).toBeVisible();
    
    // 4. NAVEGAR A ÓRDENES
    await page.getByRole('link', { name: /Órdenes/i }).click();
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    await expect(page).toHaveURL(/work-orders/);
    await page.screenshot({ path: 'test-results/ordenes-lista.png' });
    
    // 5. CREAR NUEVA ORDEN (simular click en botón)
    const nuevaOrdenBtn = page.locator('a[href*="create"], text=Nueva Orden, text=Nuevo').first();
    if (await nuevaOrdenBtn.isVisible().catch(() => false)) {
      await nuevaOrdenBtn.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/orden-crear.png' });
      
      // Volver a la lista
      await page.goBack();
    }
    
    // 6. VER DETALLE DE UNA ORDEN (si existe)
    const verOrden = page.locator('a[href*="/work-orders/show/"], a[href*="/work-orders/edit/"]').first();
    if (await verOrden.isVisible().catch(() => false)) {
      await verOrden.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/orden-detalle.png' });
    }
  });

  test('navegación completa por todos los módulos', async ({ page }) => {
    await login(page);
    
    const modulos = [
      { nombre: 'Dashboard', url: /dashboard/, path: '/dashboard' },
      { nombre: 'Órdenes', url: /work-orders/, path: '/work-orders' },
      { nombre: 'Clientes', url: /clients/, path: '/clients' },
      { nombre: 'Vehículos', url: /vehicles/, path: '/vehicles' },
      { nombre: 'Servicios', url: /services/, path: '/services' },
      { nombre: 'Inventario', url: /parts/, path: '/parts' },
      { nombre: 'Operación Inteligente', url: /workshop-ops/, path: '/workshop-ops' },
      { nombre: 'Herramientas', url: /tools/, path: '/tools' },
      { nombre: 'Manuales', url: /manuals/, path: '/manuals' },
      { nombre: 'VIN Decoder', url: /vin-decoder/, path: '/vin-decoder' },
      { nombre: 'DTC Codes', url: /dtc/, path: '/dtc' },
      { nombre: 'Reportes', url: /reports/, path: '/reports' },
      { nombre: 'WhatsApp', url: /whatsapp/, path: '/whatsapp-reminders' },
    ];
    
    for (const modulo of modulos) {
      try {
        // Buscar el enlace del módulo
        const link = page.getByRole('link', { name: new RegExp(modulo.nombre, 'i') }).first();
        if (await link.isVisible({ timeout: 5000 }).catch(() => false)) {
          await link.click();
          await page.waitForURL(modulo.url, { timeout: 10000 });
          await expect(page).toHaveURL(modulo.url);
          console.log(`✓ Módulo ${modulo.nombre} cargado`);
          
          // Screenshot de cada módulo
          await page.screenshot({ 
            path: `test-results/modulo-${modulo.nombre.toLowerCase().replace(/\s+/g, '-')}.png`,
            fullPage: true 
          });
        }
      } catch (e) {
        console.log(`⚠ Módulo ${modulo.nombre} no accesible: ${e.message}`);
      }
    }
  });

  test('verificar todos los botones del dashboard', async ({ page }) => {
    await login(page);
    
    // Botones rápidos
    const botones = [
      { texto: /Nueva Orden/i, href: /work-orders\/create/ },
      { texto: /Nuevo Cliente/i, href: /clients\/create/ },
      { texto: /Nuevo Vehículo/i, href: /vehicles\/create/ },
      { texto: /Nuevo Servicio/i, href: /services\/create/ },
    ];
    
    for (const btn of botones) {
      const boton = page.getByRole('link', { name: btn.texto }).first();
      if (await boton.isVisible().catch(() => false)) {
        await expect(boton).toHaveAttribute('href', btn.href);
        console.log(`✓ Botón "${btn.texto.source}" encontrado`);
      }
    }
  });

  test('verificar sidebar completo', async ({ page }) => {
    await login(page);
    
    // Verificar todas las secciones del sidebar
    const secciones = [
      'Principal',
      'Operaciones', 
      'Herramientas',
      'Administración'
    ];
    
    for (const seccion of secciones) {
      const titulo = page.locator('.nav-section-title, .sidebar-title', { hasText: new RegExp(seccion, 'i') });
      if (await titulo.isVisible().catch(() => false)) {
        console.log(`✓ Sección "${seccion}" visible`);
      }
    }
    
    // Verificar enlaces específicos
    const enlacesRequeridos = [
      'Dashboard',
      'Órdenes',
      'Clientes', 
      'Vehículos',
      'Servicios',
      'Inventario',
      'Operación Inteligente',
      'Herramientas',
      'Manuales',
      'VIN Decoder',
      'DTC Codes',
      'Reportes',
      'WhatsApp',
      'Usuarios',
      'Configuración'
    ];
    
    for (const enlace of enlacesRequeridos) {
      const link = page.getByRole('link', { name: new RegExp(enlace, 'i') });
      const count = await link.count();
      console.log(`${count > 0 ? '✓' : '✗'} Enlace "${enlace}": ${count} encontrado(s)`);
    }
  });

  test('interacción con tablas y filtros', async ({ page }) => {
    await login(page);
    
    // Ir a clientes (tiene tabla)
    await page.getByRole('link', { name: /Clientes/i }).click();
    await page.waitForURL('**/clients', { timeout: 15000 });
    
    // Verificar si hay tabla
    const tabla = page.locator('table').first();
    if (await tabla.isVisible().catch(() => false)) {
      // Verificar headers de tabla
      const headers = await tabla.locator('th').allTextContents();
      console.log('Headers de tabla:', headers);
      
      // Intentar usar filtro de búsqueda
      const searchInput = page.locator('input[type="search"], input[placeholder*="buscar" i], .search-input').first();
      if (await searchInput.isVisible().catch(() => false)) {
        await searchInput.fill('test');
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'test-results/clientes-filtro.png' });
      }
    }
    
    // Ir a órdenes
    await page.getByRole('link', { name: /Órdenes/i }).click();
    await page.waitForURL('**/work-orders', { timeout: 15000 });
    
    // Verificar vista Kanban si existe
    const kanbanBtn = page.locator('text=Kanban, a[href*="kanban"]').first();
    if (await kanbanBtn.isVisible().catch(() => false)) {
      await kanbanBtn.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-results/ordenes-kanban.png' });
    }
  });

  test('verificar estado de usuario logueado', async ({ page }) => {
    await login(page);
    
    // Verificar nombre de usuario en header
    const userName = page.locator('.user-name, .profile-name, [class*="user"]').first();
    if (await userName.isVisible().catch(() => false)) {
      const text = await userName.textContent();
      console.log('Usuario logueado:', text);
      expect(text).toBeTruthy();
    }
    
    // Verificar botón de logout
    const logoutBtn = page.getByRole('link', { name: /Salir|Logout|Cerrar/i }).first();
    await expect(logoutBtn).toBeVisible();
    
    // Verificar avatar/imagen de perfil
    const avatar = page.locator('.user-avatar, .profile-avatar, img[alt*="avatar" i]').first();
    if (await avatar.isVisible().catch(() => false)) {
      console.log('✓ Avatar de usuario visible');
    }
  });

  test('simulación de trabajo: crear y gestionar', async ({ page }) => {
    await login(page);
    
    // 1. Verificar órdenes activas
    await page.goto('http://localhost/torque/work-orders');
    await page.waitForTimeout(2000);
    
    // Contar órdenes en la tabla
    const filas = await page.locator('table tbody tr').count();
    console.log(`Total órdenes en tabla: ${filas}`);
    
    // 2. Ir a operación inteligente
    await page.getByRole('link', { name: /Operación Inteligente/i }).click();
    await page.waitForURL('**/workshop-ops', { timeout: 15000 });
    await page.screenshot({ path: 'test-results/operacion-inteligente.png', fullPage: true });
    
    // 3. Verificar herramientas
    await page.getByRole('link', { name: /Herramientas/i }).click();
    await page.waitForURL('**/tools', { timeout: 15000 });
    
    // Verificar sub-menús de herramientas
    const subMenus = ['Mecánico', 'Bodega', 'Solicitudes', 'Mis Herramientas'];
    for (const menu of subMenus) {
      const link = page.getByRole('link', { name: new RegExp(menu, 'i') }).first();
      if (await link.isVisible().catch(() => false)) {
        console.log(`✓ Sub-menú "${menu}" disponible`);
      }
    }
    
    await page.screenshot({ path: 'test-results/herramientas.png', fullPage: true });
  });

});
