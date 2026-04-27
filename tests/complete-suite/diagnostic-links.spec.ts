import { test, expect } from '@playwright/test';
import { CONFIG, robustLogin } from './utils/test-helpers';
import * as fs from 'fs';
import * as path from 'path';

test('Diagnostic - Listar todos los links visibles', async ({ page }) => {
  await robustLogin(page);
  
  console.log('\n🔍 DIAGNÓSTICO DE LINKS EN DASHBOARD\n');
  
  // Ir al dashboard
  await page.goto(`${CONFIG.BASE_URL}/dashboard`);
  await page.waitForTimeout(3000);
  
  // Método 1: Contar elementos <a>
  const linkCount = await page.locator('a[href]').count();
  console.log(`📊 Método 1 - Total etiquetas <a>: ${linkCount}`);
  
  // Método 2: Extraer información básica
  const basicLinks = await page.locator('a[href]').evaluateAll(anchors => {
    return anchors.slice(0, 20).map(a => {
      const el = a as HTMLElement;
      return {
        href: a.getAttribute('href'),
        text: a.textContent?.trim().substring(0, 30) || 'Sin texto',
        visible: el.offsetParent !== null
      };
    });
  });
  
  console.log('\n📋 Primeros 20 links encontrados:');
  basicLinks.forEach((link, i) => {
    console.log(`  ${i + 1}. ${link.visible ? '👁️' : '👻'} ${link.href}`);
    console.log(`     Texto: "${link.text}"`);
  });
  
  // Método 3: Usar Playwright locators
  const links = await page.getByRole('link').all();
  console.log(`\n📊 Método 2 - Links por rol: ${links.length}`);
  
  for (let i = 0; i < Math.min(links.length, 10); i++) {
    const href = await links[i].getAttribute('href');
    const text = await links[i].textContent();
    console.log(`  ${i + 1}. ${href} - "${text?.trim().substring(0, 30)}"`);
  }
  
  // Método 4: Buscar en sidebar específicamente
  console.log('\n📍 Buscando en sidebar...');
  const sidebarLinks = await page.locator('.sidebar a, nav a, [class*="sidebar"] a').count();
  console.log(`  Links en sidebar: ${sidebarLinks}`);
  
  // Generar reporte simple
  const reportContent = `
========================================
DIAGNÓSTICO DE LINKS - Torque Studio
========================================

Fecha: ${new Date().toLocaleString('es-CL')}
URL: ${CONFIG.BASE_URL}/dashboard

RESULTADOS:
-----------
Total etiquetas <a>: ${linkCount}
Links por rol: ${links.length}
Links en sidebar: ${sidebarLinks}

PRIMEROS 20 LINKS:
${basicLinks.map((l, i) => `${i + 1}. ${l.href}\n   Texto: "${l.text}"\n   Visible: ${l.visible ? 'Sí' : 'No'}`).join('\n\n')}

========================================
`;
  
  const reportPath = path.join(process.env.USERPROFILE || '', 'Desktop', 'diagnostic-links.txt');
  fs.writeFileSync(reportPath, reportContent);
  
  console.log(`\n📄 Reporte guardado en: ${reportPath}`);
  
  // Screenshot para verificar visualmente
  await page.screenshot({ 
    path: path.join(process.env.USERPROFILE || '', 'Desktop', 'dashboard-screenshot.png'),
    fullPage: true 
  });
  
  console.log('📸 Screenshot guardado en: dashboard-screenshot.png');
});
