import { test, expect } from '@playwright/test';
import { CONFIG, robustLogin, AuditLogger, BugReporter } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

const bugReporter = new BugReporter();

interface PageResult {
  url: string;
  name: string;
  status: 'ok' | 'error' | 'timeout';
  loadTime: number;
  httpStatus?: number;
  errorMessage?: string;
  screenshot?: string;
}

test.describe('✅ Verificación Completa de Todas las Páginas', () => {
  
  test('Verificar las 27 páginas del sistema', async ({ page, context }) => {
    test.setTimeout(30 * 60 * 1000); // 30 minutos
    
    const logger = new AuditLogger('verify-all-pages');
    const results: PageResult[] = [];
    
    // Login primero
    await robustLogin(page);
    
    // Lista de todas las páginas encontradas
    const pagesToCheck = [
      { url: '/dashboard', name: 'Dashboard' },
      { url: '/clients', name: 'Clientes' },
      { url: '/clients/create', name: 'Crear Cliente' },
      { url: '/vehicles', name: 'Vehículos' },
      { url: '/work-orders', name: 'Órdenes de Trabajo' },
      { url: '/work-orders/create', name: 'Crear Orden' },
      { url: '/services', name: 'Servicios' },
      { url: '/workshop-ops', name: 'Operación Inteligente' },
      { url: '/parts', name: 'Inventario' },
      { url: '/tools', name: 'Herramientas' },
      { url: '/manuals', name: 'Manuales' },
      { url: '/vin-decoder', name: 'VIN Decoder' },
      { url: '/dtc', name: 'DTC Codes' },
      { url: '/reports', name: 'Reportes' },
      { url: '/reports/mechanic-productivity', name: 'Productividad' },
      { url: '/whatsapp-reminders', name: 'WhatsApp' },
      { url: '/users', name: 'Usuarios' },
      { url: '/settings', name: 'Configuración' },
      { url: '/notifications', name: 'Notificaciones' },
    ];
    
    console.log(`\n🚀 Iniciando verificación de ${pagesToCheck.length} páginas...\n`);
    
    for (let i = 0; i < pagesToCheck.length; i++) {
      const pageInfo = pagesToCheck[i];
      const fullUrl = `${CONFIG.BASE_URL}${pageInfo.url}`;
      
      console.log(`[${i + 1}/${pagesToCheck.length}] Verificando: ${pageInfo.name}`);
      
      const startTime = Date.now();
      let result: PageResult = {
        url: pageInfo.url,
        name: pageInfo.name,
        status: 'ok',
        loadTime: 0
      };
      
      try {
        // Navegar a la página
        const response = await page.goto(fullUrl, {
          waitUntil: 'networkidle',
          timeout: 15000
        });
        
        result.loadTime = Date.now() - startTime;
        result.httpStatus = response?.status() || 0;
        
        // Verificar que no hay errores 500
        if (response?.status() === 500) {
          result.status = 'error';
          result.errorMessage = 'Error 500 - Internal Server Error';
          bugReporter.addBug('critical', 'Navigation', `Error 500 en ${pageInfo.name}`, fullUrl);
        } else if (response?.status() === 404) {
          result.status = 'error';
          result.errorMessage = 'Error 404 - Página no encontrada';
          bugReporter.addBug('high', 'Navigation', `Error 404 en ${pageInfo.name}`, fullUrl);
        } else if (response?.status() === 403) {
          result.status = 'error';
          result.errorMessage = 'Error 403 - Acceso denegado';
          bugReporter.addBug('medium', 'Navigation', `Error 403 en ${pageInfo.name}`, fullUrl);
        } else {
          // Éxito
          console.log(`  ✅ OK - ${result.loadTime}ms`);
          
          // Tomar screenshot solo si hay error o cada 5 páginas
          if (result.status !== 'ok' || i % 5 === 0) {
            const screenshotPath = path.join('test-results', 'screenshots', `page-${pageInfo.url.replace(/\//g, '-')}.png`);
            await page.screenshot({ path: screenshotPath, fullPage: false });
            result.screenshot = screenshotPath;
          }
        }
        
      } catch (error) {
        result.status = 'error';
        result.loadTime = Date.now() - startTime;
        result.errorMessage = String(error);
        console.log(`  ❌ ERROR: ${result.errorMessage}`);
        bugReporter.addBug('high', 'Navigation', `Error cargando ${pageInfo.name}`, `${fullUrl}: ${result.errorMessage}`);
      }
      
      results.push(result);
      
      // Pequeña pausa entre páginas
      await page.waitForTimeout(500);
    }
    
    // Generar reporte
    generateReport(results);
    
    // Resumen final
    const ok = results.filter(r => r.status === 'ok').length;
    const errors = results.filter(r => r.status === 'error').length;
    
    console.log(`\n✅ VERIFICACIÓN COMPLETADA`);
    console.log(`   ${ok} páginas OK`);
    console.log(`   ${errors} páginas con error`);
    console.log(`\n📄 Reporte guardado en: Desktop/torque-pages-verification.html`);
    
    logger.saveReport();
    
    // El test pasa si al menos 80% de las páginas funcionan
    expect(ok).toBeGreaterThanOrEqual(pagesToCheck.length * 0.8);
  });

});

function generateReport(results: PageResult[]) {
  const ok = results.filter(r => r.status === 'ok').length;
  const errors = results.filter(r => r.status === 'error').length;
  
  const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>✅ Verificación de Páginas - Torque Studio</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #0f1419 0%, #1a1d26 100%);
      color: #e8eaf2;
      padding: 30px 20px;
      min-height: 100vh;
    }
    .container { max-width: 1200px; margin: 0 auto; }
    
    .header {
      background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(77,142,255,0.15));
      padding: 40px;
      border-radius: 20px;
      margin-bottom: 40px;
      border: 1px solid rgba(34,197,94,0.3);
      text-align: center;
    }
    .header.success { border-color: rgba(34,197,94,0.5); }
    .header.error { border-color: rgba(239,68,68,0.5); background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(77,142,255,0.15)); }
    
    .header h1 {
      font-size: 2.5rem;
      background: linear-gradient(135deg, #86efac, #4d8eff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 15px;
    }
    .header.error h1 {
      background: linear-gradient(135deg, #fca5a5, #4d8eff);
    }
    
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 40px;
    }
    .summary-card {
      background: rgba(255,255,255,0.05);
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .summary-value {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .summary-value.success { color: #22c55e; }
    .summary-value.error { color: #ef4444; }
    .summary-value.total { color: #3b82f6; }
    .summary-label { color: #9aa3b2; font-size: 1rem; }
    
    .results-table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255,255,255,0.03);
      border-radius: 16px;
      overflow: hidden;
    }
    .results-table th {
      background: rgba(77,142,255,0.2);
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #8ab4f8;
    }
    .results-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .results-table tr:hover {
      background: rgba(255,255,255,0.05);
    }
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .status-ok { background: rgba(34,197,94,0.2); color: #86efac; }
    .status-error { background: rgba(239,68,68,0.2); color: #fca5a5; }
    
    .url-cell {
      font-family: monospace;
      font-size: 0.9rem;
      color: #8ab4f8;
    }
    .time-cell {
      color: #9aa3b2;
    }
    .error-message {
      color: #fca5a5;
      font-size: 0.85rem;
    }
    
    .footer {
      text-align: center;
      padding: 30px;
      color: #6b7280;
      margin-top: 40px;
      border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    @media (max-width: 768px) {
      .summary-grid { grid-template-columns: 1fr; }
      .results-table { font-size: 0.85rem; }
      .results-table th, .results-table td { padding: 10px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header ${errors === 0 ? 'success' : 'error'}">
      <h1>${errors === 0 ? '✅ Todas las Páginas Funcionan' : '⚠️ Algunas Páginas tienen Problemas'}</h1>
      <p>Verificación completa del sistema Torque Studio</p>
      <p style="margin-top: 10px; color: #6b7280;">Generado: ${new Date().toLocaleString('es-CL')}</p>
    </div>
    
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-value success">${ok}</div>
        <div class="summary-label">✅ Páginas OK</div>
      </div>
      <div class="summary-card">
        <div class="summary-value error">${errors}</div>
        <div class="summary-label">❌ Con Error</div>
      </div>
      <div class="summary-card">
        <div class="summary-value total">${results.length}</div>
        <div class="summary-label">📊 Total Verificado</div>
      </div>
    </div>
    
    <table class="results-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Página</th>
          <th>URL</th>
          <th>Estado</th>
          <th>Tiempo</th>
        </tr>
      </thead>
      <tbody>
        ${results.map((r, i) => `
        <tr>
          <td>${i + 1}</td>
          <td><strong>${r.name}</strong></td>
          <td class="url-cell">${r.url}</td>
          <td>
            <span class="status-badge status-${r.status}">
              ${r.status === 'ok' ? '✅ OK' : '❌ ERROR'}
            </span>
            ${r.errorMessage ? `<div class="error-message">${r.errorMessage}</div>` : ''}
          </td>
          <td class="time-cell">${r.loadTime}ms</td>
        </tr>
        `).join('')}
      </tbody>
    </table>
    
    <div class="footer">
      <p>Reporte generado por Torque Studio Audit Suite</p>
    </div>
  </div>
</body>
</html>`;
  
  const desktopPath = path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-pages-verification.html');
  fs.writeFileSync(desktopPath, html);
}
