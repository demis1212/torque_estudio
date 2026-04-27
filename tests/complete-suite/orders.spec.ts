import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin, EXTREME_TEST_DATA } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('📋 Módulo Órdenes de Trabajo', () => {
  
  test('3.1 - Crear Orden de Trabajo - Flujo completo', async ({ page }) => {
    const logger = new AuditLogger('create-order');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    await evidence.screenshot('01-orders-list');
    
    // Buscar botón Nueva Orden
    const newOrderBtn = page.locator('a:has-text("Nueva Orden"), button:has-text("Nueva Orden"), a:has-text("Crear Orden"), button:has-text("Crear")').first();
    
    if (await newOrderBtn.isVisible().catch(() => false)) {
      await newOrderBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('02-order-form');
      
      // Llenar formulario
      try {
        // Seleccionar cliente
        const clientSelect = page.locator('select[name="client_id"]').first();
        if (await clientSelect.isVisible().catch(() => false)) {
          await clientSelect.selectOption({ index: 1 });
        }
        
        // Seleccionar vehículo
        const vehicleSelect = page.locator('select[name="vehicle_id"]').first();
        if (await vehicleSelect.isVisible().catch(() => false)) {
          await vehicleSelect.selectOption({ index: 1 });
        }
        
        // Descripción
        const descField = page.locator('textarea[name="description"], textarea[name="issue"]').first();
        if (await descField.isVisible().catch(() => false)) {
          await descField.fill(EXTREME_TEST_DATA.validData.order.description);
        }
        
        // Diagnóstico
        const diagField = page.locator('textarea[name="diagnosis"]').first();
        if (await diagField.isVisible().catch(() => false)) {
          await diagField.fill(EXTREME_TEST_DATA.validData.order.diagnosis);
        }
        
        await evidence.screenshot('03-order-form-filled');
        
        // NO guardamos para no crear datos basura
        logger.log('info', 'Formulario de orden completado correctamente');
        
      } catch (e: any) {
        bugReporter.addBug('medium', 'Orders', 'Error Formulario Orden', e.message);
      }
      
    } else {
      bugReporter.addBug('medium', 'Orders', 'Botón Nueva Orden No Encontrado', 'No se encuentra el botón para crear orden');
    }
    
    logger.saveReport();
  });

  test('3.2 - Editar Orden Existente', async ({ page }) => {
    const logger = new AuditLogger('edit-order');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar botón editar en la primera orden
    const editBtn = page.locator('table .btn:has-text("Editar"), table a:has-text("Editar"), table button:has-text("Editar"), .btn-edit').first();
    
    if (await editBtn.isVisible().catch(() => false)) {
      await editBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-edit-order-form');
      
      // Verificar que cargó el formulario
      const form = page.locator('form').first();
      const isFormVisible = await form.isVisible().catch(() => false);
      
      if (isFormVisible) {
        logger.log('info', 'Formulario de edición cargado');
        
        // Intentar cambiar el estado
        const statusSelect = page.locator('select[name="status"]').first();
        if (await statusSelect.isVisible().catch(() => false)) {
          const options = await statusSelect.locator('option').all();
          if (options.length > 1) {
            await statusSelect.selectOption({ index: 1 });
            logger.log('info', 'Estado cambiado');
          }
        }
        
        await evidence.screenshot('02-edit-form-modified');
        
      } else {
        bugReporter.addBug('medium', 'Orders', 'Formulario Edición No Carga', 'El formulario de edición no se muestra');
      }
      
    } else {
      logger.log('info', 'No hay órdenes para editar');
    }
    
    logger.saveReport();
  });

  test('3.3 - Asignar Mecánico a Orden', async ({ page }) => {
    const logger = new AuditLogger('assign-mechanic');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar selector de mecánico
    const mechanicSelect = page.locator('select[name="mechanic_id"], select[name="assigned_to"], select[name="user_id"]').first();
    
    if (await mechanicSelect.isVisible().catch(() => false)) {
      const options = await mechanicSelect.locator('option').all();
      if (options.length > 1) {
        await mechanicSelect.selectOption({ index: 1 });
        await page.waitForTimeout(1000);
        await evidence.screenshot('01-mechanic-assigned');
        logger.log('info', 'Mecánico asignado');
      }
    } else {
      // Buscar en formulario de edición
      const editBtn = page.locator('.btn:has-text("Editar"), .btn-edit').first();
      if (await editBtn.isVisible().catch(() => false)) {
        await editBtn.click();
        await page.waitForTimeout(2000);
        
        const mechSelect = page.locator('select[name="mechanic_id"]').first();
        if (await mechSelect.isVisible().catch(() => false)) {
          await mechSelect.selectOption({ index: 1 });
          await evidence.screenshot('02-mechanic-form');
        }
      }
    }
    
    logger.saveReport();
  });

  test('3.4 - Agregar Repuestos a Orden', async ({ page }) => {
    const logger = new AuditLogger('add-parts');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar botón de agregar repuestos
    const addPartsBtn = page.locator('button:has-text("Repuesto"), button:has-text("Parte"), a:has-text("Agregar Repuesto"), .btn-parts').first();
    
    if (await addPartsBtn.isVisible().catch(() => false)) {
      await addPartsBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-add-parts-modal');
      
      // Buscar selector de repuestos
      const partsSelect = page.locator('select[name="part_id"], select[name="product_id"]').first();
      if (await partsSelect.isVisible().catch(() => false)) {
        const options = await partsSelect.locator('option').all();
        if (options.length > 1) {
          await partsSelect.selectOption({ index: 1 });
          
          // Cantidad
          const qtyField = page.locator('input[name="quantity"]').first();
          if (await qtyField.isVisible().catch(() => false)) {
            await qtyField.fill('2');
          }
          
          await evidence.screenshot('02-part-selected');
        }
      }
    } else {
      logger.log('info', 'No se encontró botón de agregar repuestos');
    }
    
    logger.saveReport();
  });

  test('3.5 - Cambiar Estado de Orden', async ({ page }) => {
    const logger = new AuditLogger('change-status');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    const statuses = ['recepcion', 'diagnostico', 'reparacion', 'terminado', 'entregado'];
    
    // Buscar selector de estado
    const statusSelect = page.locator('select[name="status"]').first();
    
    if (await statusSelect.isVisible().catch(() => false)) {
      for (const status of statuses) {
        try {
          const option = statusSelect.locator(`option[value="${status}"]`);
          if (await option.count() > 0) {
            await statusSelect.selectOption(status);
            await page.waitForTimeout(1000);
            logger.log('info', `Estado cambiado a: ${status}`);
          }
        } catch (e) {
          // Ignorar si no existe el estado
        }
      }
      
      await evidence.screenshot('01-status-changed');
    }
    
    logger.saveReport();
  });

  test('3.6 - Filtrar y Buscar Órdenes', async ({ page }) => {
    const logger = new AuditLogger('filter-orders');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar campo de búsqueda
    const searchField = page.locator('input[type="search"], input[name="search"], input[placeholder*="Buscar"]').first();
    
    if (await searchField.isVisible().catch(() => false)) {
      await searchField.fill('test');
      await page.waitForTimeout(1000);
      await evidence.screenshot('01-search-results');
      
      // Limpiar búsqueda
      await searchField.fill('');
      await page.keyboard.press('Enter');
      await page.waitForTimeout(1000);
    }
    
    // Probar filtros
    const filterSelects = await page.locator('select').all();
    for (const select of filterSelects.slice(0, 3)) {
      try {
        if (await select.isVisible().catch(() => false)) {
          const options = await select.locator('option').all();
          if (options.length > 1) {
            await select.selectOption({ index: 1 });
            await page.waitForTimeout(1000);
          }
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    await evidence.screenshot('02-filters-applied');
    logger.saveReport();
  });

  test('3.7 - Eliminar Orden', async ({ page }) => {
    const logger = new AuditLogger('delete-order');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar botón eliminar (normalmente el último)
    const deleteBtn = page.locator('table .btn-danger, table a:has-text("Eliminar"), table button:has-text("Eliminar"), .btn-delete').first();
    
    if (await deleteBtn.isVisible().catch(() => false)) {
      // NO hacer click real para no borrar datos
      logger.log('info', 'Botón de eliminar encontrado (no se ejecuta para preservar datos)');
      await evidence.screenshot('01-delete-button-found');
    } else {
      logger.log('info', 'No se encontró botón de eliminar visible');
    }
    
    logger.saveReport();
  });

  test('3.8 - Validaciones de Formulario Orden', async ({ page }) => {
    const logger = new AuditLogger('order-validations');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders/create`);
    await page.waitForTimeout(2000);
    
    // Intentar enviar vacío
    const submitBtn = page.locator('button[type="submit"]').first();
    if (await submitBtn.isVisible().catch(() => false)) {
      await submitBtn.click();
      await page.waitForTimeout(2000);
      await evidence.screenshot('01-validation-empty');
      
      // Verificar mensajes de error
      const bodyText = await page.locator('body').textContent();
      const hasError = bodyText?.toLowerCase().includes('error') ||
                       bodyText?.toLowerCase().includes('requerido') ||
                       bodyText?.toLowerCase().includes('obligatorio');
      
      if (!hasError) {
        bugReporter.addBug('medium', 'Orders', 'Sin Validación Campos Vacíos', 'Permite enviar orden sin datos');
      }
    }
    
    // Probar XSS en descripción
    const descField = page.locator('textarea[name="description"]').first();
    if (await descField.isVisible().catch(() => false)) {
      await descField.fill('<script>alert("XSS")</script>');
      await evidence.screenshot('02-xss-attempt');
    }
    
    logger.saveReport();
  });

  test('3.9 - Orden con datos extremos', async ({ page }) => {
    const logger = new AuditLogger('order-extreme-data');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders/create`);
    await page.waitForTimeout(2000);
    
    // Llenar con datos extremos
    const descField = page.locator('textarea[name="description"]').first();
    if (await descField.isVisible().catch(() => false)) {
      await descField.fill(EXTREME_TEST_DATA.invalidData.longText);
      await evidence.screenshot('01-extreme-description');
    }
    
    // Caracteres especiales
    const diagField = page.locator('textarea[name="diagnosis"]').first();
    if (await diagField.isVisible().catch(() => false)) {
      await diagField.fill(EXTREME_TEST_DATA.invalidData.specialChars);
      await evidence.screenshot('02-special-chars');
    }
    
    logger.saveReport();
  });

  test('3.10 - Vista Detalle de Orden', async ({ page }) => {
    const logger = new AuditLogger('order-detail-view');
    const evidence = new EvidenceCollector(page, 'orders');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/work-orders`);
    await page.waitForTimeout(2000);
    
    // Buscar link de ver detalle
    const viewLinks = [
      'a:has-text("Ver")',
      'a:has-text("Detalle")',
      'a[href*="/work-orders/view"]',
      'a[href*="/work-orders/"]:not([href*="edit"])',
    ];
    
    for (const selector of viewLinks) {
      const link = page.locator(selector).first();
      if (await link.isVisible().catch(() => false)) {
        await link.click();
        await page.waitForTimeout(2000);
        await evidence.screenshot('01-order-detail');
        break;
      }
    }
    
    logger.saveReport();
  });

});
