import { test, expect, Page } from '@playwright/test';
import { CONFIG, robustLogin } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

interface PageNode {
  url: string;
  title: string;
  links: string[];
  childPages?: PageNode[];
  status: 'visited' | 'pending' | 'error';
  errorMessage?: string;
}

test.describe('🗺️ Full Site Map - Mapeo Completo del Sistema', () => {
  
  test('Crawler Recursivo - Mapear TODO el sitio', async ({ page }) => {
    test.setTimeout(60 * 60 * 1000); // 1 hora máximo
    
    await robustLogin(page);
    
    const siteMap: PageNode[] = [];
    const visitedUrls = new Set<string>();
    const urlsToVisit: Array<{url: string, parent?: PageNode}> = [
      { url: `${CONFIG.BASE_URL}/dashboard` }
    ];
    
    const maxPages = 300;
    let pageCount = 0;
    
    console.log('🗺️ Iniciando MAPEO COMPLETO del sitio...\n');
    
    while (urlsToVisit.length > 0 && pageCount < maxPages) {
      const { url: currentUrl, parent } = urlsToVisit.shift()!;
      
      if (visitedUrls.has(currentUrl)) continue;
      visitedUrls.add(currentUrl);
      pageCount++;
      
      const shortUrl = currentUrl.replace(CONFIG.BASE_URL, '') || '/';
      console.log(`[${pageCount}/${maxPages}] Mapeando: ${shortUrl}`);
      
      const pageNode: PageNode = {
        url: currentUrl,
        title: '',
        links: [],
        status: 'pending'
      };
      
      try {
        // Navegar a la página
        await page.goto(currentUrl, { 
          waitUntil: 'domcontentloaded',
          timeout: 20000 
        });
        
        await page.waitForTimeout(1500);
        
        // Obtener título
        pageNode.title = await page.title().catch(() => 'Sin título');
        
        // Extraer TODOS los links visibles
        const pageLinks = await page.evaluate((baseUrl) => {
          const links: string[] = [];
          const seen = new Set<string>();
          
          // Links normales
          document.querySelectorAll('a[href]').forEach(el => {
            const href = el.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
                href.startsWith('mailto:') || href.startsWith('tel:')) return;
            
            try {
              const absoluteUrl = new URL(href, baseUrl).href;
              if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                seen.add(absoluteUrl);
                links.push(absoluteUrl);
              }
            } catch (e) {}
          });
          
          // Links en botones con data-href
          document.querySelectorAll('[data-href], [data-url]').forEach(el => {
            const dataHref = el.getAttribute('data-href') || el.getAttribute('data-url');
            if (dataHref) {
              try {
                const absoluteUrl = new URL(dataHref, baseUrl).href;
                if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                  seen.add(absoluteUrl);
                  links.push(absoluteUrl);
                }
              } catch (e) {}
            }
          });
          
          // Links en botones de acción
          document.querySelectorAll('button[onclick*="location"], [onclick*="href"]').forEach(el => {
            const onclick = el.getAttribute('onclick') || '';
            const match = onclick.match(/(?:location\.href|window\.location)\s*=\s*['"]([^'"]+)['"]/);
            if (match) {
              try {
                const absoluteUrl = new URL(match[1], baseUrl).href;
                if (!seen.has(absoluteUrl) && absoluteUrl.includes(baseUrl)) {
                  seen.add(absoluteUrl);
                  links.push(absoluteUrl);
                }
              } catch (e) {}
            }
          });
          
          return links;
        }, CONFIG.BASE_URL);
        
        pageNode.links = pageLinks;
        pageNode.status = 'visited';
        
        console.log(`  ✅ Encontrados ${pageLinks.length} links internos`);
        
        // Agregar links a la cola
        for (const link of pageLinks) {
          if (!visitedUrls.has(link) && !urlsToVisit.some(u => u.url === link)) {
            urlsToVisit.push({ url: link, parent: pageNode });
          }
        }
        
      } catch (error) {
        pageNode.status = 'error';
        pageNode.errorMessage = String(error).substring(0, 100);
        console.log(`  ❌ Error: ${pageNode.errorMessage}`);
      }
      
      siteMap.push(pageNode);
    }
    
    console.log(`\n✅ MAPEO COMPLETADO`);
    console.log(`   Total páginas mapeadas: ${siteMap.length}`);
    console.log(`   Total links únicos: ${visitedUrls.size}`);
    
    // Generar reportes
    generateSiteMapHTML(siteMap);
    generateSiteMapJSON(siteMap);
    generateSiteMapText(siteMap);
    
    console.log('\n📄 Reportes generados:');
    console.log('   - Desktop/torque-sitemap.html (Visual)');
    console.log('   - Desktop/torque-sitemap.json (Datos)');
    console.log('   - Desktop/torque-sitemap.txt (Texto)');
  });

});

function generateSiteMapHTML(siteMap: PageNode[]) {
  const totalLinks = siteMap.reduce((acc, page) => acc + page.links.length, 0);
  const visitedPages = siteMap.filter(p => p.status === 'visited').length;
  const errorPages = siteMap.filter(p => p.status === 'error').length;
  
  const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>🗺️ Torque Studio - Mapa Completo del Sitio</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #0f1419;
      color: #e8eaf2;
      padding: 20px;
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
      margin-bottom: 15px;
    }
    
    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 40px;
    }
    .stat-card {
      background: rgba(255,255,255,0.05);
      border-radius: 16px;
      padding: 25px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      color: #4d8eff;
      margin-bottom: 5px;
    }
    .stat-label { color: #9aa3b2; }
    
    .search-box {
      width: 100%;
      padding: 15px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 10px;
      color: #e8eaf2;
      font-size: 1rem;
      margin-bottom: 30px;
    }
    
    .page-tree {
      background: rgba(255,255,255,0.03);
      border-radius: 16px;
      padding: 20px;
      max-height: 600px;
      overflow-y: auto;
    }
    
    .page-item {
      margin-bottom: 15px;
      padding: 15px;
      background: rgba(255,255,255,0.05);
      border-radius: 12px;
      border-left: 4px solid #4d8eff;
    }
    .page-item.error { border-left-color: #ef4444; }
    
    .page-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      flex-wrap: wrap;
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
    .page-status {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .status-visited { background: rgba(34,197,94,0.2); color: #86efac; }
    .status-error { background: rgba(239,68,68,0.2); color: #fca5a5; }
    
    .links-list {
      margin-left: 20px;
      margin-top: 10px;
    }
    .link-item {
      padding: 5px 0;
      font-size: 0.9rem;
      color: #9aa3b2;
      font-family: monospace;
    }
    .link-item::before {
      content: "→";
      margin-right: 8px;
      color: #4d8eff;
    }
    
    .export-btn {
      padding: 12px 24px;
      background: linear-gradient(135deg, #4d8eff, #6b8cff);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
      .stats { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🗺️ Mapa Completo del Sitio</h1>
      <p>Torque Studio - Todas las páginas y links internos</p>
      <p style="color: #6b7280; margin-top: 10px;">Generado: ${new Date().toLocaleString('es-CL')}</p>
    </div>
    
    <div class="stats">
      <div class="stat-card">
        <div class="stat-value">${siteMap.length}</div>
        <div class="stat-label">Páginas Totales</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${visitedPages}</div>
        <div class="stat-label">Visitadas OK</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${totalLinks}</div>
        <div class="stat-label">Links Internos</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">${errorPages}</div>
        <div class="stat-label">Con Error</div>
      </div>
    </div>
    
    <button class="export-btn" onclick="exportJSON()">📥 Exportar JSON</button>
    
    <input type="text" class="search-box" placeholder="🔍 Buscar páginas o links..." onkeyup="filterPages(this.value)">
    
    <div class="page-tree" id="pageTree">
      ${siteMap.map((page, i) => `
        <div class="page-item ${page.status}">
          <div class="page-header">
            <span class="page-status status-${page.status}">${page.status === 'visited' ? '✅' : '❌'} ${page.status}</span>
            <span class="page-title">${page.title || 'Sin título'}</span>
            <span class="page-url">${page.url.replace(CONFIG.BASE_URL, '') || '/'}</span>
          </div>
          ${page.links.length > 0 ? `
            <div class="links-list">
              <div style="color: #6b7280; font-size: 0.85rem; margin-bottom: 5px;">${page.links.length} links internos:</div>
              ${page.links.slice(0, 10).map(link => `
                <div class="link-item">${link.replace(CONFIG.BASE_URL, '')}</div>
              `).join('')}
              ${page.links.length > 10 ? `<div style="color: #6b7280; font-size: 0.8rem; margin-top: 5px;">... y ${page.links.length - 10} más</div>` : ''}
            </div>
          ` : ''}
          ${page.errorMessage ? `<div style="color: #fca5a5; font-size: 0.85rem; margin-top: 10px;">Error: ${page.errorMessage}</div>` : ''}
        </div>
      `).join('')}
    </div>
  </div>
  
  <script>
    const siteData = ${JSON.stringify(siteMap)};
    
    function filterPages(query) {
      const items = document.querySelectorAll('.page-item');
      items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query.toLowerCase()) ? 'block' : 'none';
      });
    }
    
    function exportJSON() {
      const dataStr = JSON.stringify(siteData, null, 2);
      const blob = new Blob([dataStr], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'torque-sitemap-' + new Date().toISOString().split('T')[0] + '.json';
      a.click();
    }
  </script>
</body>
</html>`;
  
  const desktopPath = path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-sitemap.html');
  fs.writeFileSync(desktopPath, html);
}

function generateSiteMapJSON(siteMap: PageNode[]) {
  const jsonPath = path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-sitemap.json');
  fs.writeFileSync(jsonPath, JSON.stringify(siteMap, null, 2));
}

function generateSiteMapText(siteMap: PageNode[]) {
  let text = 'TORQUE STUDIO - MAPA DEL SITIO\n';
  text += '=' .repeat(50) + '\n\n';
  text += `Total páginas: ${siteMap.length}\n`;
  text += `Generado: ${new Date().toLocaleString('es-CL')}\n\n`;
  
  siteMap.forEach((page, i) => {
    const shortUrl = page.url.replace(CONFIG.BASE_URL, '') || '/';
    text += `[${i + 1}] ${page.title || 'Sin título'}\n`;
    text += `    URL: ${shortUrl}\n`;
    text += `    Estado: ${page.status}\n`;
    text += `    Links: ${page.links.length}\n`;
    if (page.links.length > 0) {
      page.links.slice(0, 5).forEach(link => {
        text += `      → ${link.replace(CONFIG.BASE_URL, '')}\n`;
      });
      if (page.links.length > 5) {
        text += `      ... y ${page.links.length - 5} más\n`;
      }
    }
    text += '\n';
  });
  
  const textPath = path.join(process.env.USERPROFILE || '', 'Desktop', 'torque-sitemap.txt');
  fs.writeFileSync(textPath, text);
}
