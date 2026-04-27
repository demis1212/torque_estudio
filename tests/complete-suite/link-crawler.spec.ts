import { test, expect, Page } from '@playwright/test';
import { CONFIG, AuditLogger, robustLogin } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

interface LinkReport {
  url: string;
  text: string;
  status: number;
  statusText: string;
  sourcePage: string;
  error?: string;
  timestamp: string;
}

class LinkCrawler {
  private visitedUrls = new Set<string>();
  private foundLinks: LinkReport[] = [];
  private logger: AuditLogger;
  private desktopPath: string;

  constructor(private page: Page) {
    this.logger = new AuditLogger('link-crawler');
    this.desktopPath = path.join(process.env.USERPROFILE || process.env.HOME || '', 'Desktop', 'torque-links-report.html');
  }

  async crawl(startUrl: string, maxPages: number = 100) {
    this.logger.log('info', `🔍 Iniciando crawler desde: ${startUrl}`);
    
    const pagesToVisit = [startUrl];
    let pageCount = 0;

    while (pagesToVisit.length > 0 && pageCount < maxPages) {
      const currentUrl = pagesToVisit.shift()!;
      
      if (this.visitedUrls.has(currentUrl)) continue;
      this.visitedUrls.add(currentUrl);
      pageCount++;

      try {
        this.logger.log('info', `📄 [${pageCount}/${maxPages}] Analizando: ${currentUrl}`);
        
        // Navegar a la página
        const response = await this.page.goto(currentUrl, { 
          waitUntil: 'networkidle',
          timeout: 30000 
        });

        // Esperar a que se cargue contenido dinámico
        await this.page.waitForTimeout(2000);
        
        // Scrollear para cargar contenido lazy
        await this.scrollPage();

        // Extraer todos los links
        const links = await this.extractAllLinks(currentUrl);
        
        this.logger.log('info', `🔗 Encontrados ${links.length} links en ${currentUrl}`);

        // Verificar cada link
        for (const link of links) {
          if (!this.visitedUrls.has(link.url) && this.isInternalLink(link.url)) {
            // Verificar status HTTP
            const report = await this.checkLinkStatus(link);
            this.foundLinks.push(report);

            // Agregar a cola si es interno y no visitado
            if (!pagesToVisit.includes(link.url) && !this.visitedUrls.has(link.url)) {
              pagesToVisit.push(link.url);
            }
          }
        }

      } catch (error) {
        this.logger.log('error', `❌ Error en ${currentUrl}: ${error}`);
        this.foundLinks.push({
          url: currentUrl,
          text: 'PAGE_ERROR',
          status: 0,
          statusText: 'Navigation Error',
          sourcePage: currentUrl,
          error: String(error),
          timestamp: new Date().toISOString()
        });
      }
    }

    this.logger.log('info', `✅ Crawler completado. Total links verificados: ${this.foundLinks.length}`);
    this.logger.saveReport();
    
    // Generar reporte en escritorio
    await this.generateDesktopReport();
  }

  private async scrollPage() {
    // Scrollear hasta el final para cargar contenido lazy
    await this.page.evaluate(async () => {
      await new Promise<void>(resolve => {
        let totalHeight = 0;
        const distance = 300;
        const timer = setInterval(() => {
          const scrollHeight = document.body.scrollHeight;
          window.scrollBy(0, distance);
          totalHeight += distance;

          if (totalHeight >= scrollHeight) {
            clearInterval(timer);
            resolve();
          }
        }, 100);
      });
    });
    
    // Volver arriba
    await this.page.evaluate(() => window.scrollTo(0, 0));
    await this.page.waitForTimeout(500);
  }

  private async extractAllLinks(sourcePage: string): Promise<Array<{url: string, text: string}>> {
    return await this.page.evaluate((baseUrl) => {
      const links: Array<{url: string, text: string}> = [];
      const seen = new Set<string>();
      
      // Buscar en todos los links
      document.querySelectorAll('a[href]').forEach((el) => {
        const a = el as HTMLAnchorElement;
        try {
          const href = a.getAttribute('href') || '';
          const absoluteUrl = new URL(href, baseUrl).href;
          
          // Ignorar duplicados, anchors y javascript
          if (!seen.has(absoluteUrl) && 
              !href.startsWith('#') && 
              !href.startsWith('javascript:') &&
              !href.startsWith('mailto:') &&
              !href.startsWith('tel:')) {
            
            seen.add(absoluteUrl);
            links.push({
              url: absoluteUrl,
              text: a.textContent?.trim().substring(0, 50) || 'Sin texto'
            });
          }
        } catch (e) {
          // URL inválida, ignorar
        }
      });

      // También buscar en elementos clickeables que actúan como links
      document.querySelectorAll('[data-href], [data-url], [data-link], [onclick*="location"]').forEach((el: Element) => {
        try {
          const dataHref = el.getAttribute('data-href') || 
                          el.getAttribute('data-url') || 
                          el.getAttribute('data-link');
          
          if (dataHref) {
            const absoluteUrl = new URL(dataHref, baseUrl).href;
            if (!seen.has(absoluteUrl)) {
              seen.add(absoluteUrl);
              links.push({
                url: absoluteUrl,
                text: (el as HTMLElement).textContent?.trim().substring(0, 50) || 'Data Link'
              });
            }
          }
        } catch (e) {
          // Ignorar
        }
      });

      return links;
    }, sourcePage);
  }

  private isInternalLink(url: string): boolean {
    const baseUrl = CONFIG.BASE_URL;
    return url.startsWith(baseUrl) || url.startsWith('/');
  }

  private async checkLinkStatus(link: {url: string, text: string}): Promise<LinkReport> {
    try {
      // Intentar HEAD primero (más rápido)
      let response = await fetch(link.url, { 
        method: 'HEAD',
        redirect: 'follow'
      }).catch(() => null);

      // Si HEAD falla, intentar GET
      if (!response) {
        response = await fetch(link.url, { 
          method: 'GET',
          redirect: 'follow'
        });
      }

      return {
        url: link.url,
        text: link.text,
        status: response.status,
        statusText: response.statusText,
        sourcePage: this.page.url(),
        timestamp: new Date().toISOString()
      };

    } catch (error) {
      return {
        url: link.url,
        text: link.text,
        status: 0,
        statusText: 'Connection Error',
        sourcePage: this.page.url(),
        error: String(error),
        timestamp: new Date().toISOString()
      };
    }
  }

  private async generateDesktopReport() {
    const totalLinks = this.foundLinks.length;
    const brokenLinks = this.foundLinks.filter(l => l.status >= 400 || l.status === 0);
    const workingLinks = this.foundLinks.filter(l => l.status >= 200 && l.status < 400);
    const redirectLinks = this.foundLinks.filter(l => l.status >= 300 && l.status < 400);

    const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Torque Studio - Reporte Completo de Links</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #0f1419; 
      color: #e8eaf2; 
      padding: 20px;
      line-height: 1.6;
    }
    .header { 
      background: linear-gradient(135deg, rgba(138,180,248,0.1), rgba(77,142,255,0.1));
      padding: 30px; 
      border-radius: 16px; 
      margin-bottom: 30px;
      border: 1px solid rgba(138,180,248,0.2);
    }
    .header h1 { 
      background: linear-gradient(135deg, #8ab4f8, #4d8eff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-size: 2rem;
      margin-bottom: 10px;
    }
    .summary { 
      display: grid; 
      grid-template-columns: repeat(4, 1fr); 
      gap: 20px; 
      margin-bottom: 30px;
    }
    .stat-card { 
      background: rgba(255,255,255,0.05); 
      padding: 20px; 
      border-radius: 12px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .stat-value { font-size: 2.5rem; font-weight: 700; }
    .stat-label { color: #9aa3b2; font-size: 0.9rem; }
    .success { color: #22c55e; }
    .warning { color: #f59e0b; }
    .error { color: #ef4444; }
    .info { color: #3b82f6; }
    
    .filters {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filter-btn {
      padding: 10px 20px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.2);
      color: #e8eaf2;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .filter-btn:hover, .filter-btn.active {
      background: rgba(77,142,255,0.3);
      border-color: #4d8eff;
    }
    
    .search-box {
      width: 100%;
      padding: 12px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      color: #e8eaf2;
      margin-bottom: 20px;
      font-size: 1rem;
    }
    
    .link-list { margin-top: 20px; }
    .link-item { 
      background: rgba(255,255,255,0.03); 
      padding: 15px; 
      margin-bottom: 10px; 
      border-radius: 8px;
      border-left: 4px solid;
      transition: all 0.2s;
    }
    .link-item:hover { 
      background: rgba(255,255,255,0.05);
      transform: translateX(4px);
    }
    .link-item.success { border-color: #22c55e; }
    .link-item.warning { border-color: #f59e0b; }
    .link-item.error { border-color: #ef4444; }
    
    .link-url { 
      color: #8ab4f8; 
      font-family: monospace; 
      font-size: 0.9rem;
      word-break: break-all;
    }
    .link-text { color: #e8eaf2; margin-top: 5px; }
    .link-meta { 
      color: #9aa3b2; 
      font-size: 0.85rem; 
      margin-top: 8px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    .badge {
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    .badge-success { background: rgba(34,197,94,0.2); color: #86efac; }
    .badge-warning { background: rgba(245,158,11,0.2); color: #fcd34d; }
    .badge-error { background: rgba(239,68,68,0.2); color: #fca5a5; }
    
    .export-btn {
      padding: 12px 24px;
      background: linear-gradient(135deg, #4d8eff, #6b8cff);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
      .summary { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>🔗 Torque Studio - Reporte de Links</h1>
    <p>Generado: ${new Date().toLocaleString('es-CL')} | Total: ${totalLinks} links analizados</p>
  </div>
  
  <div class="summary">
    <div class="stat-card">
      <div class="stat-value success">${workingLinks.length}</div>
      <div class="stat-label">✅ Funcionando</div>
    </div>
    <div class="stat-card">
      <div class="stat-value warning">${redirectLinks.length}</div>
      <div class="stat-label">⚠️ Redirecciones</div>
    </div>
    <div class="stat-card">
      <div class="stat-value error">${brokenLinks.length}</div>
      <div class="stat-label">❌ Rotos</div>
    </div>
    <div class="stat-card">
      <div class="stat-value info">${totalLinks}</div>
      <div class="stat-label">📊 Total</div>
    </div>
  </div>
  
  <button class="export-btn" onclick="exportCSV()">📥 Exportar CSV</button>
  
  <div class="filters">
    <button class="filter-btn active" onclick="filterLinks('all')">Todos (${totalLinks})</button>
    <button class="filter-btn" onclick="filterLinks('working')">Funcionando (${workingLinks.length})</button>
    <button class="filter-btn" onclick="filterLinks('broken')">Rotos (${brokenLinks.length})</button>
    <button class="filter-btn" onclick="filterLinks('redirect')">Redirecciones (${redirectLinks.length})</button>
  </div>
  
  <input type="text" class="search-box" placeholder="🔍 Buscar URLs..." onkeyup="searchLinks(this.value)">
  
  <div class="link-list" id="linkList">
    ${this.generateLinkListHTML()}
  </div>
  
  <script>
    const allLinks = ${JSON.stringify(this.foundLinks)};
    
    function getLinkClass(status) {
      if (status >= 200 && status < 300) return 'success';
      if (status >= 300 && status < 400) return 'warning';
      return 'error';
    }
    
    function getBadgeClass(status) {
      if (status >= 200 && status < 300) return 'badge-success';
      if (status >= 300 && status < 400) return 'badge-warning';
      return 'badge-error';
    }
    
    function filterLinks(type) {
      document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
      
      let filtered = allLinks;
      if (type === 'working') {
        filtered = allLinks.filter(l => l.status >= 200 && l.status < 400);
      } else if (type === 'broken') {
        filtered = allLinks.filter(l => l.status >= 400 || l.status === 0);
      } else if (type === 'redirect') {
        filtered = allLinks.filter(l => l.status >= 300 && l.status < 400);
      }
      
      renderLinks(filtered);
    }
    
    function searchLinks(query) {
      const filtered = allLinks.filter(l => 
        l.url.toLowerCase().includes(query.toLowerCase()) ||
        l.text.toLowerCase().includes(query.toLowerCase())
      );
      renderLinks(filtered);
    }
    
    function renderLinks(links) {
      const list = document.getElementById('linkList');
      list.innerHTML = links.map(link => \`
        <div class="link-item \${getLinkClass(link.status)}">
          <div class="link-url">\${link.url}</div>
          <div class="link-text">\${link.text || 'Sin texto'}</div>
          <div class="link-meta">
            <span class="badge \${getBadgeClass(link.status)}">HTTP \${link.status}</span>
            <span>📄 \${link.sourcePage}</span>
            <span>🕐 \${new Date(link.timestamp).toLocaleString()}</span>
            \${link.error ? '<span style="color: #ef4444;">❌ ' + link.error + '</span>' : ''}
          </div>
        </div>
      \`).join('');
    }
    
    function exportCSV() {
      const csv = [
        'URL,Texto,Status,Status Text,Pagina Origen,Error,Timestamp',
        ...allLinks.map(l => \`"\${l.url}","\${l.text}",\${l.status},"\${l.statusText}","\${l.sourcePage}","\${l.error || ''}","\${l.timestamp}"\`)
      ].join('\\n');
      
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'torque-links-report-\${new Date().toISOString().split('T')[0]}.csv';
      a.click();
    }
  </script>
</body>
</html>`;

    fs.writeFileSync(this.desktopPath, html);
    this.logger.log('info', `📄 Reporte guardado en: ${this.desktopPath}`);
  }

  private generateLinkListHTML(): string {
    if (this.foundLinks.length === 0) {
      return '<div style="text-align: center; padding: 40px;">No se encontraron links</div>';
    }

    return this.foundLinks.map(link => {
      const statusClass = link.status >= 200 && link.status < 300 ? 'success' : 
                         link.status >= 300 && link.status < 400 ? 'warning' : 'error';
      const badgeClass = statusClass === 'success' ? 'badge-success' : 
                        statusClass === 'warning' ? 'badge-warning' : 'badge-error';

      return `
        <div class="link-item ${statusClass}">
          <div class="link-url">${link.url}</div>
          <div class="link-text">${link.text || 'Sin texto'}</div>
          <div class="link-meta">
            <span class="badge ${badgeClass}">HTTP ${link.status}</span>
            <span>📄 ${link.sourcePage}</span>
            <span>🕐 ${new Date(link.timestamp).toLocaleString()}</span>
            ${link.error ? `<span style="color: #ef4444;">❌ ${link.error}</span>` : ''}
          </div>
        </div>
      `;
    }).join('');
  }
}

test.describe('🔗 Link Crawler - Revisión Completa de Links', () => {
  
  test('Crawler - Revisar TODOS los links del sistema', async ({ page }) => {
    test.setTimeout(30 * 60 * 1000); // 30 minutos para crawler completo
    
    const logger = new AuditLogger('link-crawler-test');
    
    // Login primero
    await robustLogin(page);
    
    // Iniciar crawler desde el dashboard
    const crawler = new LinkCrawler(page);
    await crawler.crawl(`${CONFIG.BASE_URL}/dashboard`, 150); // Máximo 150 páginas
    
    logger.log('info', '✅ Crawler completado exitosamente');
    logger.saveReport();
    
    // El reporte se guarda automáticamente en el escritorio
  });

  test('Quick Link Check - Solo links principales', async ({ page, context }) => {
    test.setTimeout(5 * 60 * 1000); // 5 minutos
    
    const logger = new AuditLogger('quick-link-check');
    const reports: LinkReport[] = [];
    
    await robustLogin(page);
    
    // Lista de páginas principales
    const mainPages = [
      '/dashboard',
      '/clients',
      '/clients/create',
      '/vehicles',
      '/work-orders',
      '/services',
      '/parts',
      '/reports',
      '/users',
      '/settings',
    ];
    
    for (const pagePath of mainPages) {
      try {
        await page.goto(`${CONFIG.BASE_URL}${pagePath}`, { timeout: 15000 });
        await page.waitForTimeout(1000);
        
        // Extraer links de esta página
        const links = await page.evaluate((baseUrl) => {
          const found: Array<{url: string, text: string}> = [];
          document.querySelectorAll('a[href]').forEach((el) => {
            const a = el as HTMLAnchorElement;
            const href = a.getAttribute('href') || '';
            if (href && !href.startsWith('#') && !href.startsWith('javascript')) {
              try {
                const absoluteUrl = new URL(href, baseUrl).href;
                if (absoluteUrl.includes(baseUrl)) {
                  found.push({
                    url: absoluteUrl,
                    text: a.textContent?.trim().substring(0, 30) || 'Link'
                  });
                }
              } catch (e) {}
            }
          });
          return found.slice(0, 20); // Máximo 20 por página
        }, CONFIG.BASE_URL);
        
        // Verificar cada link
        for (const link of links) {
          try {
            const newPage = await context.newPage();
            const response = await newPage.goto(link.url, { timeout: 10000, waitUntil: 'domcontentloaded' });
            
            reports.push({
              url: link.url,
              text: link.text,
              status: response?.status() || 0,
              statusText: response?.statusText() || 'Error',
              sourcePage: pagePath,
              timestamp: new Date().toISOString()
            });
            
            await newPage.close();
          } catch (error) {
            reports.push({
              url: link.url,
              text: link.text,
              status: 0,
              statusText: 'Error',
              sourcePage: pagePath,
              error: String(error),
              timestamp: new Date().toISOString()
            });
          }
        }
        
      } catch (e) {
        logger.log('error', `Error en ${pagePath}: ${e}`);
      }
    }
    
    // Generar reporte simple
    const desktopPath = path.join(process.env.USERPROFILE || process.env.HOME || '', 'Desktop', 'torque-quick-links.html');
    const brokenLinks = reports.filter(r => r.status >= 400 || r.status === 0);
    
    const html = `<h1>Quick Links Report</h1>
<p>Total: ${reports.length} | Rotos: ${brokenLinks.length}</p>
<ul>
${reports.map(r => `<li style="color: ${r.status >= 400 || r.status === 0 ? 'red' : 'green'}">${r.status} - ${r.url}</li>`).join('')}
</ul>`;
    
    fs.writeFileSync(desktopPath, html);
    
    logger.log('info', `Quick check completado. Reporte: ${desktopPath}`);
    logger.saveReport();
  });

});
