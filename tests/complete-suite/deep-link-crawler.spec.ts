import { test, expect } from '@playwright/test';
import { CONFIG, robustLogin } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

interface CrawledPage {
  url: string;
  title: string;
  linksFound: number;
  links: string[];
  status: 'ok' | 'error';
  error?: string;
}

test.describe('🕸️ Deep Link Crawler - Todos los Links Internos', () => {
  
  test('Crawler Profundo - Extraer TODOS los links', async ({ page }) => {
    test.setTimeout(45 * 60 * 1000);
    
    await robustLogin(page);
    
    const crawledPages: CrawledPage[] = [];
    const visitedUrls = new Set<string>();
    const urlsToVisit: string[] = [`${CONFIG.BASE_URL}/dashboard`];
    
    console.log('\n🕸️ Iniciando DEEP LINK CRAWLER...\n');
    
    while (urlsToVisit.length > 0 && crawledPages.length < 150) {
      const currentUrl = urlsToVisit.shift()!;
      
      if (visitedUrls.has(currentUrl)) continue;
      visitedUrls.add(currentUrl);
      
      const shortUrl = currentUrl.replace(CONFIG.BASE_URL, '') || '/';
      console.log(`[${crawledPages.length + 1}] Crawleando: ${shortUrl}`);
      
      const pageData: CrawledPage = {
        url: currentUrl,
        title: '',
        linksFound: 0,
        links: [],
        status: 'ok'
      };
      
      try {
        // Navegar
        await page.goto(currentUrl, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await page.waitForTimeout(2000);
        
        // Obtener título
        pageData.title = await page.title().catch(() => 'Sin título');
        
        // MÉTODO 1: Usar locator de Playwright (más confiable)
        const links = await page.locator('a[href]').all();
        const uniqueUrls = new Set<string>();
        
        for (const link of links) {
          try {
            const href = await link.getAttribute('href');
            if (!href) continue;
            
            // Filtrar links no deseados
            if (href.startsWith('#') || href.startsWith('javascript:') ||
                href.startsWith('mailto:') || href.startsWith('tel:')) continue;
            
            // Convertir a URL absoluta
            let absoluteUrl: string;
            if (href.startsWith('http')) {
              absoluteUrl = href;
            } else if (href.startsWith('/')) {
              absoluteUrl = `${CONFIG.BASE_URL}${href}`;
            } else {
              absoluteUrl = `${CONFIG.BASE_URL}/${href}`;
            }
            
            // Solo links internos
            if (absoluteUrl.includes('localhost') && !uniqueUrls.has(absoluteUrl)) {
              uniqueUrls.add(absoluteUrl);
              pageData.links.push(absoluteUrl.replace(CONFIG.BASE_URL, '') || '/');
              
              // Agregar a cola si no visitado
              if (!visitedUrls.has(absoluteUrl) && !urlsToVisit.includes(absoluteUrl)) {
                urlsToVisit.push(absoluteUrl);
              }
            }
          } catch (e) {
            // Ignorar errores individuales
          }
        }
        
        pageData.linksFound = pageData.links.length;
        console.log(`  ✅ ${pageData.linksFound} links encontrados`);
        
      } catch (error) {
        pageData.status = 'error';
        pageData.error = String(error).substring(0, 100);
        console.log(`  ❌ Error: ${pageData.error}`);
      }
      
      crawledPages.push(pageData);
    }
    
    console.log(`\n✅ CRAWLER COMPLETADO`);
    console.log(`   Páginas crawleadas: ${crawledPages.length}`);
    console.log(`   Total links únicos: ${visitedUrls.size}`);
    
    // Generar reportes
    generateReports(crawledPages, visitedUrls);
    
    console.log('\n📄 Reportes generados en Desktop:');
    console.log('   - torque-deep-links.html');
    console.log('   - torque-all-links.json');
    console.log('   - torque-links-summary.txt');
  });

});

function generateReports(pages: CrawledPage[], allUrls: Set<string>) {
  // HTML Report
  const totalLinks = pages.reduce((sum, p) => sum + p.linksFound, 0);
  const avgLinks = pages.length > 0 ? Math.round(totalLinks / pages.length) : 0;
  
  const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🕸️ Torque Studio - Deep Link Crawler</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #0f1419 0%, #1a1d26 100%);
      color: #e8eaf2;
      padding: 30px 20px;
      min-height: 100vh;
    }
    .container { max-width: 1400px; margin: 0 auto; }
    
    .header {
      background: linear-gradient(135deg, rgba(138,180,248,0.15), rgba(77,142,255,0.15));
      padding: 40px;
      border-radius: 20px;
      margin-bottom: 40px;
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
    }
    .stat-value {
      font-size: 3rem;
      font-weight: 700;
      color: #4d8eff;
      margin-bottom: 8px;
    }
    .stat-label { color: #9aa3b2; }
    
    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .tab {
      padding: 15px 25px;
      background: transparent;
      border: none;
      color: #9aa3b2;
      cursor: pointer;
      font-size: 1rem;
      transition: all 0.2s;
      border-bottom: 3px solid transparent;
    }
    .tab:hover, .tab.active {
      color: #4d8eff;
      border-bottom-color: #4d8eff;
    }
    
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    
    .page-list {
      background: rgba(255,255,255,0.03);
      border-radius: 16px;
      padding: 20px;
      max-height: 600px;
      overflow-y: auto;
    }
    .page-item {
      padding: 20px;
      margin-bottom: 15px;
      background: rgba(255,255,255,0.05);
      border-radius: 12px;
      border-left: 4px solid #22c55e;
    }
    .page-item.error { border-left-color: #ef4444; }
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 10px;
      flex-wrap: wrap;
      gap: 10px;
    }
    .page-title {
      font-weight: 600;
      font-size: 1.1rem;
    }
    .page-url {
      font-family: monospace;
      font-size: 0.85rem;
      color: #8ab4f8;
    }
    .page-stats {
      display: flex;
      gap: 15px;
      font-size: 0.85rem;
      color: #9aa3b2;
    }
    
    .links-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 10px;
      margin-top: 15px;
    }
    .link-tag {
      background: rgba(77,142,255,0.1);
      padding: 8px 12px;
      border-radius: 8px;
      font-family: monospace;
      font-size: 0.85rem;
      color: #8ab4f8;
      word-break: break-all;
    }
    
    .all-links-list {
      column-count: 3;
      column-gap: 20px;
    }
    @media (max-width: 768px) {
      .all-links-list { column-count: 1; }
    }
    .all-link-item {
      break-inside: avoid;
      padding: 8px 0;
      font-family: monospace;
      font-size: 0.9rem;
      color: #e8eaf2;
      border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .all-link-item::before {
      content: "🔗";
      margin-right: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🕸️ Deep Link Crawler</h1>
      <p>Todos los links encontrados en el sistema Torque Studio</p>
      <p style="color: #6b7280; margin-top: 10px;">Generado: ${new Date().toLocaleString('es-CL')}</p>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value">${pages.length}</div>
        <div class="stat-label">Páginas Crawleadas</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${allUrls.size}</div>
        <div class="stat-label">Links Únicos</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${totalLinks}</div>
        <div class="stat-label">Total Links</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${avgLinks}</div>
        <div class="stat-label">Promedio por Página</div>
      </div>
    </div>
    
    <div class="tabs">
      <button class="tab active" onclick="showTab('pages')">📄 Por Página (${pages.length})</button>
      <button class="tab" onclick="showTab('all')">🔗 Todos los Links (${allUrls.size})</button>
    </div>
    
    <div id="pages-tab" class="tab-content active">
      <div class="page-list">
        ${pages.map((p, i) => `
          <div class="page-item ${p.status}">
            <div class="page-header">
              <div>
                <div class="page-title">${p.title || 'Sin título'}</div>
                <div class="page-url">${p.url.replace(CONFIG.BASE_URL, '') || '/'}</div>
              </div>
              <div class="page-stats">
                <span>${p.linksFound} links</span>
                <span>${p.status === 'ok' ? '✅ OK' : '❌ Error'}</span>
              </div>
            </div>
            ${p.links.length > 0 ? `
              <div class="links-grid">
                ${p.links.slice(0, 12).map(l => `<div class="link-tag">${l}</div>`).join('')}
                ${p.links.length > 12 ? `<div class="link-tag">+${p.links.length - 12} más</div>` : ''}
              </div>
            ` : ''}
            ${p.error ? `<div style="color: #fca5a5; margin-top: 10px;">${p.error}</div>` : ''}
          </div>
        `).join('')}
      </div>
    </div>
    
    <div id="all-tab" class="tab-content">
      <div class="page-list">
        <div class="all-links-list">
          ${Array.from(allUrls).sort().map(url => `
            <div class="all-link-item">${url.replace(CONFIG.BASE_URL, '') || '/'}</div>
          `).join('')}
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function showTab(tabName) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
      
      event.target.classList.add('active');
      document.getElementById(tabName + '-tab').classList.add('active');
    }
  </script>
</body>
</html>`;
  
  // Guardar HTML
  fs.writeFileSync(
    path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-deep-links.html'),
    html
  );
  
  // JSON con todos los datos
  fs.writeFileSync(
    path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-all-links.json'),
    JSON.stringify({ pages, totalUniqueUrls: allUrls.size, allUrls: Array.from(allUrls) }, null, 2)
  );
  
  // Resumen en texto
  let summary = `TORQUE STUDIO - DEEP LINK CRAWLER\n`;
  summary += `${'='.repeat(60)}\n\n`;
  summary += `Fecha: ${new Date().toLocaleString('es-CL')}\n`;
  summary += `Páginas crawleadas: ${pages.length}\n`;
  summary += `Links únicos: ${allUrls.size}\n`;
  summary += `Total links: ${totalLinks}\n\n`;
  summary += `PÁGINAS ENCONTRADAS:\n`;
  summary += `${'-'.repeat(60)}\n\n`;
  
  pages.forEach((p, i) => {
    const shortUrl = p.url.replace(CONFIG.BASE_URL, '') || '/';
    summary += `[${i + 1}] ${p.title || 'Sin título'}\n`;
    summary += `    URL: ${shortUrl}\n`;
    summary += `    Links internos: ${p.linksFound}\n`;
    if (p.links.length > 0) {
      p.links.slice(0, 8).forEach(l => {
        summary += `      → ${l}\n`;
      });
      if (p.links.length > 8) {
        summary += `      ... y ${p.links.length - 8} más\n`;
      }
    }
    summary += `\n`;
  });
  
  summary += `\n${'='.repeat(60)}\n`;
  summary += `TODOS LOS LINKS ÚNICOS (${allUrls.size}):\n`;
  summary += `${'-'.repeat(60)}\n\n`;
  
  Array.from(allUrls).sort().forEach(url => {
    summary += `${url.replace(CONFIG.BASE_URL, '') || '/'}\n`;
  });
  
  fs.writeFileSync(
    path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-links-summary.txt'),
    summary
  );
}
