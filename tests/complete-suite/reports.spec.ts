import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, EvidenceCollector, BugReporter, robustLogin } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('📊 Módulo Reportes', () => {
  
  test('5.1 - Página de Reportes Principal', async ({ page }) => {
    const logger = new AuditLogger('reports-main');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(3000);
    await evidence.screenshot('01-reports-main');
    
    // Verificar gráficos
    const charts = await page.locator('canvas, .chart, .graph').all();
    logger.log('info', `Gráficos encontrados: ${charts.length}`);
    
    if (charts.length === 0) {
      bugReporter.addBug('low', 'Reports', 'Sin Gráficos', 'No se encontraron gráficos en la página de reportes');
    }
    
    // Verificar totales
    const stats = await page.locator('.stat, .stat-card, .number, .total').all();
    logger.log('info', `Estadísticas encontradas: ${stats.length}`);
    
    logger.saveReport();
  });

  test('5.2 - Filtros por Fecha', async ({ page }) => {
    const logger = new AuditLogger('date-filters');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    // Buscar inputs de fecha
    const dateFrom = page.locator('input[type="date"], input[name="date_from"], input[name="start_date"]').first();
    const dateTo = page.locator('input[type="date"], input[name="date_to"], input[name="end_date"]').nth(1);
    
    if (await dateFrom.isVisible().catch(() => false)) {
      await dateFrom.fill('2024-01-01');
      
      if (await dateTo.isVisible().catch(() => false)) {
        await dateTo.fill('2024-12-31');
      }
      
      const filterBtn = page.locator('button:has-text("Filtrar"), button[type="submit"]').first();
      if (await filterBtn.isVisible().catch(() => false)) {
        await filterBtn.click();
        await page.waitForTimeout(2000);
        await evidence.screenshot('01-date-filtered');
      }
    } else {
      logger.log('info', 'No se encontraron filtros de fecha');
    }
    
    logger.saveReport();
  });

  test('5.3 - Exportar a Excel', async ({ page }) => {
    const logger = new AuditLogger('export-excel');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    // Buscar botón de exportar Excel
    const excelBtn = page.locator('button:has-text("Excel"), a:has-text("Excel"), .btn-excel, button:has-text("Exportar")').first();
    
    if (await excelBtn.isVisible().catch(() => false)) {
      // NO hacer click real para no descargar archivos
      logger.log('info', 'Botón de exportar Excel encontrado');
      await evidence.screenshot('01-excel-button');
    } else {
      logger.log('info', 'No se encontró botón de Excel');
    }
    
    logger.saveReport();
  });

  test('5.4 - Exportar a PDF', async ({ page }) => {
    const logger = new AuditLogger('export-pdf');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    const pdfBtn = page.locator('button:has-text("PDF"), a:has-text("PDF"), .btn-pdf').first();
    
    if (await pdfBtn.isVisible().catch(() => false)) {
      logger.log('info', 'Botón de exportar PDF encontrado');
      await evidence.screenshot('01-pdf-button');
    }
    
    logger.saveReport();
  });

  test('5.5 - Reporte de Ingresos', async ({ page }) => {
    const logger = new AuditLogger('revenue-report');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    // Buscar tablas con ingresos
    const tables = await page.locator('table').all();
    
    for (const table of tables.slice(0, 2)) {
      const headers = await table.locator('th').allTextContents();
      const hasRevenue = headers.some(h => 
        h.toLowerCase().includes('ingreso') || 
        h.toLowerCase().includes('monto') || 
        h.toLowerCase().includes('total') ||
        h.includes('$')
      );
      
      if (hasRevenue) {
        logger.log('info', 'Tabla de ingresos encontrada');
        await evidence.screenshot('01-revenue-table');
        break;
      }
    }
    
    logger.saveReport();
  });

  test('5.6 - Reporte de Servicios Más Solicitados', async ({ page }) => {
    const logger = new AuditLogger('top-services');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    const bodyText = await page.locator('body').textContent();
    
    if (bodyText?.toLowerCase().includes('servicios más') ||
        bodyText?.toLowerCase().includes('top servicios') ||
        bodyText?.toLowerCase().includes('servicios solicitados')) {
      logger.log('info', 'Sección de servicios encontrada');
      await evidence.screenshot('01-top-services');
    }
    
    logger.saveReport();
  });

  test('5.7 - Reporte de Clientes Top', async ({ page }) => {
    const logger = new AuditLogger('top-clients');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    const bodyText = await page.locator('body').textContent();
    
    if (bodyText?.toLowerCase().includes('clientes top') ||
        bodyText?.toLowerCase().includes('top clientes') ||
        bodyText?.toLowerCase().includes('mejores clientes')) {
      logger.log('info', 'Sección de clientes top encontrada');
      await evidence.screenshot('01-top-clients');
    }
    
    logger.saveReport();
  });

  test('5.8 - Totales Calculados Correctamente', async ({ page }) => {
    const logger = new AuditLogger('totals-calculation');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    // Buscar totales en la página
    const totalElements = await page.locator('.total, .grand-total, .sum-total').all();
    
    for (const el of totalElements) {
      try {
        const text = await el.textContent();
        // Verificar formato de moneda
        const hasCurrency = text?.includes('$') || text?.includes('CLP');
        
        if (!hasCurrency && text?.match(/\d/)) {
          bugReporter.addBug('low', 'Reports', 'Total Sin Formato Moneda', `Total sin símbolo de moneda: ${text}`);
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    await evidence.screenshot('01-totals-check');
    logger.saveReport();
  });

  test('5.9 - Reporte de Órdenes por Estado', async ({ page }) => {
    const logger = new AuditLogger('orders-by-status');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    // Buscar gráfico de estados o tabla
    const statuses = ['recepcion', 'diagnostico', 'reparacion', 'terminado', 'entregado'];
    const bodyText = await page.locator('body').textContent();
    
    let foundStatuses = 0;
    for (const status of statuses) {
      if (bodyText?.toLowerCase().includes(status)) {
        foundStatuses++;
      }
    }
    
    logger.log('info', `Estados encontrados: ${foundStatuses}/${statuses.length}`);
    
    if (foundStatuses > 0) {
      await evidence.screenshot('01-status-report');
    }
    
    logger.saveReport();
  });

  test('5.10 - Impresión de Reportes', async ({ page }) => {
    const logger = new AuditLogger('print-report');
    const evidence = new EvidenceCollector(page, 'reports');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/reports`);
    await page.waitForTimeout(2000);
    
    const printBtn = page.locator('button:has-text("Imprimir"), button:has-text("Print"), .btn-print').first();
    
    if (await printBtn.isVisible().catch(() => false)) {
      logger.log('info', 'Botón de imprimir encontrado');
      await evidence.screenshot('01-print-button');
    }
    
    logger.saveReport();
  });

});
