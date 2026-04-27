import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin, EXTREME_TEST_DATA } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('📦 Módulo Inventario', () => {
  
  test('4.1 - Listado de Inventario', async ({ page }) => {
    const logger = new AuditLogger('inventory-list');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-inventory-list');
    
    // Verificar tabla
    const table = page.locator('table').first();
    const hasTable = await table.isVisible().catch(() => false);
    
    if (!hasTable) {
      bugReporter.addBug('medium', 'Inventory', 'Tabla Inventario No Visible', 'No se muestra la tabla de inventario');
    } else {
      // Contar filas
      const rows = await table.locator('tbody tr').count();
      logger.log('info', `Filas en inventario: ${rows}`);
    }
    
    // Verificar totales
    const totalElements = await page.locator('.total, .summary, .stats').all();
    logger.log('info', `Elementos de totales: ${totalElements.length}`);
    
    logger.saveReport();
  });

  test('4.2 - Agregar Nuevo Producto', async ({ page }) => {
    const logger = new AuditLogger('add-product');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar botón agregar
    const addBtn = page.locator('a:has-text("Nuevo"), button:has-text("Nuevo"), a:has-text("Agregar"), button:has-text("Agregar"), .btn-primary').first();
    
    if (await addBtn.isVisible().catch(() => false)) {
      await addBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-product-form');
      
      // Llenar formulario
      const nameField = page.locator('input[name="name"], input[name="product_name"]').first();
      if (await nameField.isVisible().catch(() => false)) {
        await nameField.fill(EXTREME_TEST_DATA.validData.parts.name);
      }
      
      const codeField = page.locator('input[name="code"], input[name="sku"], input[name="part_number"]').first();
      if (await codeField.isVisible().catch(() => false)) {
        await codeField.fill(EXTREME_TEST_DATA.validData.parts.code);
      }
      
      const priceField = page.locator('input[name="price"], input[name="unit_price"]').first();
      if (await priceField.isVisible().catch(() => false)) {
        await priceField.fill(EXTREME_TEST_DATA.validData.parts.price.toString());
      }
      
      const stockField = page.locator('input[name="stock"], input[name="quantity"]').first();
      if (await stockField.isVisible().catch(() => false)) {
        await stockField.fill(EXTREME_TEST_DATA.validData.parts.stock.toString());
      }
      
      await evidence.screenshot('02-product-form-filled');
      logger.log('info', 'Formulario de producto completado');
      
    } else {
      bugReporter.addBug('medium', 'Inventory', 'Botón Agregar No Encontrado', 'No se encuentra botón para agregar producto');
    }
    
    logger.saveReport();
  });

  test('4.3 - Entrada de Stock', async ({ page }) => {
    const logger = new AuditLogger('stock-in');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar botón de entrada
    const stockInBtn = page.locator('button:has-text("Entrada"), a:has-text("Entrada"), .btn-success:has-text("+")').first();
    
    if (await stockInBtn.isVisible().catch(() => false)) {
      await stockInBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-stock-in-form');
      
      // Buscar campo de cantidad
      const qtyField = page.locator('input[name="quantity"], input[name="amount"]').first();
      if (await qtyField.isVisible().catch(() => false)) {
        await qtyField.fill('10');
        await evidence.screenshot('02-stock-quantity');
      }
      
    } else {
      logger.log('info', 'No se encontró botón de entrada de stock');
    }
    
    logger.saveReport();
  });

  test('4.4 - Salida de Stock', async ({ page }) => {
    const logger = new AuditLogger('stock-out');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar botón de salida
    const stockOutBtn = page.locator('button:has-text("Salida"), a:has-text("Salida"), .btn-danger:has-text("-")').first();
    
    if (await stockOutBtn.isVisible().catch(() => false)) {
      await stockOutBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-stock-out-form');
      
      const qtyField = page.locator('input[name="quantity"]').first();
      if (await qtyField.isVisible().catch(() => false)) {
        await qtyField.fill('5');
        await evidence.screenshot('02-stock-out-quantity');
      }
    }
    
    logger.saveReport();
  });

  test('4.5 - Stock Negativo - Validación', async ({ page }) => {
    const logger = new AuditLogger('negative-stock');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Intentar poner stock negativo
    const stockField = page.locator('input[name="stock"], input[name="quantity"]').first();
    
    if (await stockField.isVisible().catch(() => false)) {
      await stockField.fill('-10');
      await page.waitForTimeout(1000);
      await evidence.screenshot('01-negative-stock');
      
      // Intentar guardar
      const submitBtn = page.locator('button[type="submit"]').first();
      if (await submitBtn.isVisible().catch(() => false)) {
        await submitBtn.click();
        await page.waitForTimeout(2000);
        
        const bodyText = await page.locator('body').textContent();
        const hasError = bodyText?.toLowerCase().includes('negativo') ||
                        bodyText?.toLowerCase().includes('inválido') ||
                        bodyText?.toLowerCase().includes('error');
        
        if (!hasError) {
          bugReporter.addBug('high', 'Inventory', 'Permite Stock Negativo', 'El sistema acepta cantidades negativas de stock');
        }
      }
    }
    
    logger.saveReport();
  });

  test('4.6 - Búsqueda de Productos', async ({ page }) => {
    const logger = new AuditLogger('search-parts');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    const searchField = page.locator('input[type="search"], input[name="search"], input[placeholder*="Buscar"]').first();
    
    if (await searchField.isVisible().catch(() => false)) {
      await searchField.fill('filtro');
      await page.waitForTimeout(1000);
      await evidence.screenshot('01-search-filtro');
      
      await searchField.fill('aceite');
      await page.waitForTimeout(1000);
      await evidence.screenshot('02-search-aceite');
      
      // Buscar código
      await searchField.fill('PART-001');
      await page.waitForTimeout(1000);
      await evidence.screenshot('03-search-code');
      
    } else {
      bugReporter.addBug('low', 'Inventory', 'Campo Búsqueda No Encontrado', 'No hay campo de búsqueda en inventario');
    }
    
    logger.saveReport();
  });

  test('4.7 - Historial de Movimientos', async ({ page }) => {
    const logger = new AuditLogger('stock-history');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar link de historial
    const historyLink = page.locator('a:has-text("Historial"), a:has-text("Movimientos"), button:has-text("Historial")').first();
    
    if (await historyLink.isVisible().catch(() => false)) {
      await historyLink.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-stock-history');
      
      // Verificar que hay datos
      const table = page.locator('table').first();
      const hasData = await table.isVisible().catch(() => false);
      
      if (hasData) {
        const rows = await table.locator('tbody tr').count();
        logger.log('info', `Movimientos encontrados: ${rows}`);
      }
    } else {
      logger.log('info', 'No se encontró link de historial');
    }
    
    logger.saveReport();
  });

  test('4.8 - Editar Producto', async ({ page }) => {
    const logger = new AuditLogger('edit-part');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    const editBtn = page.locator('table .btn:has-text("Editar"), table a:has-text("Editar"), .btn-edit').first();
    
    if (await editBtn.isVisible().catch(() => false)) {
      await editBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-edit-part-form');
      
      const priceField = page.locator('input[name="price"]').first();
      if (await priceField.isVisible().catch(() => false)) {
        await priceField.fill('99999');
        await evidence.screenshot('02-price-changed');
      }
    }
    
    logger.saveReport();
  });

  test('4.9 - Categorías de Productos', async ({ page }) => {
    const logger = new AuditLogger('part-categories');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar filtros de categoría
    const categorySelect = page.locator('select[name="category"], select[name="category_id"]').first();
    
    if (await categorySelect.isVisible().catch(() => false)) {
      const options = await categorySelect.locator('option').all();
      logger.log('info', `Categorías disponibles: ${options.length}`);
      
      for (let i = 1; i < Math.min(options.length, 4); i++) {
        await categorySelect.selectOption({ index: i });
        await page.waitForTimeout(1000);
      }
      
      await evidence.screenshot('01-category-filter');
    }
    
    logger.saveReport();
  });

  test('4.10 - Alertas de Stock Bajo', async ({ page }) => {
    const logger = new AuditLogger('low-stock-alerts');
    const evidence = new EvidenceCollector(page, 'inventory');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/parts`);
    await page.waitForTimeout(2000);
    
    // Buscar alertas de stock bajo
    const alerts = await page.locator('.alert, .warning, .low-stock, .badge-danger, .text-danger').all();
    
    if (alerts.length > 0) {
      logger.log('info', `Alertas de stock bajo: ${alerts.length}`);
      await evidence.screenshot('01-low-stock-alerts');
    } else {
      logger.log('info', 'No hay alertas de stock bajo visibles');
    }
    
    // Buscar filtro de stock bajo
    const lowStockFilter = page.locator('button:has-text("Stock Bajo"), a:has-text("Stock Bajo"), label:has-text("Stock Bajo")').first();
    if (await lowStockFilter.isVisible().catch(() => false)) {
      await lowStockFilter.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('02-low-stock-filter');
    }
    
    logger.saveReport();
  });

});
