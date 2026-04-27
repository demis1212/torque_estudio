import { test, expect, Page } from '@playwright/test';
import { CONFIG, robustLogin } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

interface LinkData {
  url: string;
  text: string;
  status: number;
  sourcePage: string;
  checked: boolean;
}

test.describe('🕷️ Deep Crawler - Revisión Exhaustiva', () => {
  
  test('Deep Crawler - Revisar TODO el sistema', async ({ page }) => {
    test.setTimeout(45 * 60 * 1000); // 45 minutos máximo
    
    // Login
    await robustLogin(page);
    
    const allLinks: LinkData[] = [];
    const visitedUrls = new Set<string>();
    const urlsToVisit: string[] = [`${CONFIG.BASE_URL}/dashboard`];
    const maxPages = 200;
    
    console.log('🔍 Iniciando DEEP CRAWLER...');
    
    while (urlsToVisit.length > 0 && visitedUrls.size < maxPages) {
      const currentUrl = urlsToVisit.shift()!;
      
      if (visitedUrls.has(currentUrl)) continue;
      visitedUrls.add(currentUrl);
      
      const pageNum = visitedUrls.size;
      console.log(`\n📄 [${pageNum}/${maxPages}] Analizando: ${currentUrl}`);
      
      try {
        // Navegar con timeout largo
        await page.goto(currentUrl, { 
          waitUntil: 'networkidle',
          timeout: 20000 
        });
        
        // Esperar a que cargue contenido
        await page.waitForTimeout(1500);
        
        // Scrollear para cargar contenido dinámico
        await page.evaluate(async () => {
          await new Promise<void>(resolve => {
            let scrolls = 0;
            const timer = setInterval(() => {
              window.scrollBy(0, 500);
              scrolls++;
              if (scrolls > 10) {
                clearInterval(timer);
                window.scrollTo(0, 0);
                resolve();
              }
            }, 200);
          });
        });
        
        await page.waitForTimeout(500);
        
        // Extraer TODOS los links
        const pageLinks = await page.evaluate((baseUrl) => {
          const links: Array<{url: string, text: string}> = [];
          const seen = new Set<string>();
          
          // Buscar en <a href="...">
          document.querySelectorAll('a[href]').forEach(el => {
            const href = el.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript') || 
                href.startsWith('mailto:') || href.startsWith('tel:')) return;
            
            try {
              const absoluteUrl = new URL(href, baseUrl).href;
              if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                seen.add(absoluteUrl);
                const text = (el.textContent || '').trim().substring(0, 50) || 'Sin texto';
                links.push({ url: absoluteUrl, text });
              }
            } catch (e) {}
          });
          
          // Buscar en elementos con data-href
          document.querySelectorAll('[data-href], [data-url], [data-link]').forEach(el => {
            const dataUrl = el.getAttribute('data-href') || 
                          el.getAttribute('data-url') || 
                          el.getAttribute('data-link');
            if (!dataUrl) return;
            
            try {
              const absoluteUrl = new URL(dataUrl, baseUrl).href;
              if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                seen.add(absoluteUrl);
                const text = (el.textContent || '').trim().substring(0, 50) || 'Data Link';
                links.push({ url: absoluteUrl, text });
              }
            } catch (e) {}
          });
          
          // Buscar en onclick con location
          document.querySelectorAll('[onclick*="location"]').forEach(el => {
            const onclick = el.getAttribute('onclick') || '';
            const match = onclick.match(/location\.href=['"]([^'"]+)['"]/);
            if (match && match[1]) {
              try {
                const absoluteUrl = new URL(match[1], baseUrl).href;
                if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                  seen.add(absoluteUrl);
                  const text = (el.textContent || '').trim().substring(0, 50) || 'Onclick Link';
                  links.push({ url: absoluteUrl, text });
                }
              } catch (e) {}
            }
          });
          
          return links;
        }, CONFIG.BASE_URL);
        
        console.log(`  🔗 Encontrados ${pageLinks.length} links en esta página`);
        
        // Agregar a la lista general
        for (const link of pageLinks) {
          // Evitar duplicados
          if (!allLinks.some(l => l.url === link.url)) {
            allLinks.push({
              url: link.url,
              text: link.text,
              status: 0,
              sourcePage: currentUrl,
              checked: false
            });
          }
          
          // Agregar a cola si no visitado
          if (!visitedUrls.has(link.url) && !urlsToVisit.includes(link.url)) {
            urlsToVisit.push(link.url);
          }
        }
        
      } catch (error) {
        console.log(`  ❌ Error: ${error}`);
      }
    }
    
    console.log(`\n✅ Crawler terminado. Total links únicos: ${allLinks.length}`);
    
    // Verificar status de cada link (muestra de 50)
    const linksToCheck = allLinks.slice(0, 50);
    console.log(`\n🔍 Verificando ${linksToCheck.length} links...`);
    
    for (let i = 0; i < linksToCheck.length; i++) {
      const link = linksToCheck[i];
      try {
        const response = await fetch(link.url, { method: 'HEAD' });
        link.status = response.status;
        link.checked = true;
        
        if (i % 10 === 0) {
          console.log(`  [${i+1}/${linksToCheck.length}] ${link.url.substring(0, 60)}... -> HTTP ${link.status}`);
        }
      } catch (e) {
        link.status = 0;
        link.checked = true;
      }
    }
    
    // Generar reporte HTML
    generateHTMLReport(allLinks, linksToCheck);
    
    console.log(`\n📄 Reporte guardado en: C:\Users\victuspc\Desktop\torque-deep-crawler-report.html`);
    console.log(`\n📊 RESUMEN:`);
    console.log(`  - Total links encontrados: ${allLinks.length}`);
    console.log(`  - Páginas visitadas: ${visitedUrls.size}`);
    console.log(`  - Links verificados: ${linksToCheck.filter(l => l.checked).length}`);
  });

});

function generateHTMLReport(allLinks: LinkData[], checkedLinks: LinkData[]) {
  const working = checkedLinks.filter(l => l.status >= 200 && l.status < 400).length;
  const broken = checkedLinks.filter(l => l.status >= 400 || l.status === 0).length;
  const redirect = checkedLinks.filter(l => l.status >= 300 && l.status < 400).length;
  
  const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🔍 Torque Studio - Deep Link Crawler Report</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #0f1419 0%, #1a1d26 100%);
      color: #e8eaf2;
      padding: 20px;
      min-height: 100vh;
    }
    .container { max-width: 1400px; margin: 0 auto; }
    
    .header {
      background: linear-gradient(135deg, rgba(138,180,248,0.15), rgba(77,142,255,0.15));
      padding: 40px;
      border-radius: 20px;
      margin-bottom: 30px;
      border: 1px solid rgba(138,180,248,0.3);
      text-align: center;
    }
    .header h1 {
      font-size: 2.5rem;
      background: linear-gradient(135deg, #8ab4f8, #4d8eff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 10px;
    }
    .header p {
      color: #9aa3b2;
      font-size: 1.1rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    .stat-card {
      background: rgba(255,255,255,0.05);
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      background: rgba(255,255,255,0.08);
    }
    .stat-value {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .stat-label {
      color: #9aa3b2;
      font-size: 1rem;
    }
    .stat-total { color: #3b82f6; }
    .stat-working { color: #22c55e; }
    .stat-broken { color: #ef4444; }
    .stat-redirect { color: #f59e0b; }
    
    .section {
      background: rgba(255,255,255,0.03);
      border-radius: 16px;
      padding: 30px;
      margin-bottom: 30px;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .section h2 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      color: #e8eaf2;
    }
    
    .link-grid {
      display: grid;
      gap: 12px;
    }
    .link-item {
      background: rgba(255,255,255,0.05);
      border-radius: 10px;
      padding: 16px 20px;
      border-left: 4px solid;
      transition: all 0.2s;
    }
    .link-item:hover {
      background: rgba(255,255,255,0.08);
      transform: translateX(5px);
    }
    .link-item.working { border-color: #22c55e; }
    .link-item.broken { border-color: #ef4444; }
    .link-item.redirect { border-color: #f59e0b; }
    .link-item.unchecked { border-color: #6b7280; }
    
    .link-url {
      font-family: monospace;
      font-size: 0.9rem;
      color: #8ab4f8;
      word-break: break-all;
      margin-bottom: 6px;
    }
    .link-text {
      color: #e8eaf2;
      font-size: 0.95rem;
      margin-bottom: 8px;
    }
    .link-meta {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      font-size: 0.85rem;
      color: #9aa3b2;
    }
    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .badge-working { background: rgba(34,197,94,0.2); color: #86efac; }
    .badge-broken { background: rgba(239,68,68,0.2); color: #fca5a5; }
    .badge-redirect { background: rgba(245,158,11,0.2); color: #fcd34d; }
    .badge-unchecked { background: rgba(107,114,128,0.2); color: #d1d5db; }
    
    .export-section {
      text-align: center;
      margin-top: 30px;
    }
    .btn {
      padding: 14px 28px;
      background: linear-gradient(135deg, #4d8eff, #6b8cff);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      margin: 5px;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(77,142,255,0.3);
    }
    
    .summary-text {
      background: rgba(34,197,94,0.1);
      border: 1px solid rgba(34,197,94,0.3);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 30px;
      text-align: center;
    }
    .summary-text h3 {
      color: #86efac;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🔍 Deep Link Crawler Report</h1>
      <p>Revisión exhaustiva de todos los links del sistema Torque Studio</p>
      <p style="margin-top: 10px; color: #6b7280;">Generado: ${new Date().toLocaleString('es-CL')}</p>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value stat-total">${allLinks.length}</div>
        <div class="stat-label">🔗 Total Links Encontrados</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-working">${working}</div>
        <div class="stat-label">✅ Funcionando (200-399)</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-broken">${broken}</div>
        <div class="stat-label">❌ Rotos (400+ / Error)</div>
      </div>
      <div class="stat-card">
        <div class="stat-value stat-redirect">${redirect}</div>
        <div class="stat-label">🔄 Redirecciones (300-399)</div>
      </div>
    </div>
    
    <div class="summary-text">
      <h3>📊 Resumen de la Auditoría</h3>
      <p>Se encontraron <strong>${allLinks.length} links únicos</strong> en todo el sistema. 
         De los cuales se verificaron <strong>${checkedLinks.length}</strong>. 
         <strong>${working}</strong> funcionan correctamente, 
         <strong>${broken}</strong> están rotos y 
         <strong>${redirect}</strong> son redirecciones.</p>
    </div>
    
    <div class="section">
      <h2>📋 Links Verificados (${checkedLinks.length})</h2>
      <div class="link-grid">
        ${checkedLinks.map(link => {
          const statusClass = link.status >= 200 && link.status < 300 ? 'working' :
                             link.status >= 300 && link.status < 400 ? 'redirect' :
                             link.status >= 400 ? 'broken' : 'unchecked';
          const badgeClass = statusClass === 'working' ? 'badge-working' :
                           statusClass === 'broken' ? 'badge-broken' :
                           statusClass === 'redirect' ? 'badge-redirect' : 'badge-unchecked';
          const statusText = link.status === 0 ? 'Error' : `HTTP ${link.status}`;
          
          return `
        <div class="link-item ${statusClass}">
          <div class="link-url">${link.url}</div>
          <div class="link-text">${link.text || 'Sin descripción'}</div>
          <div class="link-meta">
            <span class="badge ${badgeClass}">${statusText}</span>
            <span>📄 Encontrado en: ${link.sourcePage}</span>
          </div>
        </div>`;
        }).join('')}
      </div>
    </div>
    
    ${allLinks.length > checkedLinks.length ? `
    <div class="section">
      <h2>📎 Links Encontrados sin Verificar (${allLinks.length - checkedLinks.length})</h2>
      <div class="link-grid">
        ${allLinks.slice(checkedLinks.length, checkedLinks.length + 50).map(link => `
        <div class="link-item unchecked">
          <div class="link-url">${link.url}</div>
          <div class="link-text">${link.text || 'Sin descripción'}</div>
          <div class="link-meta">
            <span class="badge badge-unchecked">Sin verificar</span>
            <span>📄 Encontrado en: ${link.sourcePage}</span>
          </div>
        </div>`).join('')}
        ${allLinks.length > checkedLinks.length + 50 ? `
        <div style="text-align: center; padding: 20px; color: #6b7280;">
          ... y ${allLinks.length - checkedLinks.length - 50} links más
        </div>` : ''}
      </div>
    </div>` : ''}
    
    <div class="export-section">
      <button class="btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>
  </div>
</body>
</html>`;
  
  const desktopPath = path.join(process.env.USERPROFILE || process.env.HOME || '', 'Desktop', 'torque-deep-crawler-report.html');
  fs.writeFileSync(desktopPath, html);
}
