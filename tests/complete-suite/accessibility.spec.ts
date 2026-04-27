import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, BugReporter, robustLogin } from './utils/test-helpers';
import AxeBuilder from '@axe-core/playwright';

const bugReporter = new BugReporter();

test.describe('♿ Módulo Accesibilidad (A11y)', () => {
  
  test('A11Y.1 - Login - Violaciones de accesibilidad', async ({ page }) => {
    const logger = new AuditLogger('a11y-login');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    await page.waitForTimeout(1000);
    
    try {
      const accessibilityScanResults = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
      
      const violations = accessibilityScanResults.violations;
      
      logger.log('info', `Violaciones encontradas: ${violations.length}`);
      
      for (const violation of violations) {
        bugReporter.addBug(
          violation.impact === 'critical' ? 'critical' : 'medium',
          'Accessibility',
          `A11y: ${violation.description}`,
          `Impacto: ${violation.impact} | Ayuda: ${violation.helpUrl}`
        );
      }
      
    } catch (e) {
      logger.log('warning', 'Error ejecutando axe-core');
    }
    
    logger.saveReport();
  });

  test('A11Y.2 - Dashboard - Accesibilidad', async ({ page }) => {
    const logger = new AuditLogger('a11y-dashboard');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(1000);
    
    try {
      const accessibilityScanResults = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
      
      const violations = accessibilityScanResults.violations;
      
      if (violations.length > 0) {
        logger.log('warning', `Dashboard: ${violations.length} violaciones de accesibilidad`);
        
        for (const violation of violations.slice(0, 5)) {
          bugReporter.addBug(
            'medium',
            'Accessibility',
            `Dashboard: ${violation.description.substring(0, 50)}...`,
            `Elementos afectados: ${violation.nodes.length}`
          );
        }
      } else {
        logger.log('info', '✅ Dashboard sin violaciones de accesibilidad');
      }
      
    } catch (e) {
      logger.log('warning', 'axe-core no disponible');
    }
    
    logger.saveReport();
  });

  test('A11Y.3 - Contraste de colores', async ({ page }) => {
    const logger = new AuditLogger('a11y-contrast');
    
    await robustLogin(page);
    
    const pages = [
      '/dashboard',
      '/clients',
      '/work-orders',
    ];
    
    for (const path of pages) {
      await page.goto(`${CONFIG.BASE_URL}${path}`);
      await page.waitForTimeout(1000);
      
      try {
        const accessibilityScanResults = await new AxeBuilder({ page })
          .withRules(['color-contrast'])
          .analyze();
        
        const contrastViolations = accessibilityScanResults.violations.filter(
          v => v.id === 'color-contrast'
        );
        
        if (contrastViolations.length > 0) {
          bugReporter.addBug(
            'medium',
            'Accessibility',
            `Contraste insuficiente en ${path}`,
            `${contrastViolations[0].nodes.length} elementos con contraste bajo`
          );
        }
        
      } catch (e) {
        logger.log('info', `Saltando contraste en ${path}`);
      }
    }
    
    logger.saveReport();
  });

  test('A11Y.4 - Navegación por teclado', async ({ page }) => {
    const logger = new AuditLogger('a11y-keyboard');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(1000);
    
    // Verificar elementos focusables
    const focusableElements = await page.locator('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])').all();
    
    logger.log('info', `Elementos focusables: ${focusableElements.length}`);
    
    // Intentar navegar con Tab
    await page.keyboard.press('Tab');
    await page.waitForTimeout(200);
    
    const activeElement = await page.evaluate(() => document.activeElement?.tagName);
    
    if (activeElement === 'BODY') {
      bugReporter.addBug('medium', 'Accessibility', 'Navegación Teclado Fallida', 'Tab no enfoca elementos');
    }
    
    logger.saveReport();
  });

  test('A11Y.5 - Atributos ARIA', async ({ page }) => {
    const logger = new AuditLogger('a11y-aria');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(1000);
    
    // Verificar elementos con ARIA
    const ariaElements = await page.locator('[role], [aria-label], [aria-describedby]').all();
    
    logger.log('info', `Elementos con ARIA: ${ariaElements.length}`);
    
    // Verificar imágenes sin alt
    const imagesWithoutAlt = await page.locator('img:not([alt])').count();
    
    if (imagesWithoutAlt > 0) {
      bugReporter.addBug(
        'medium',
        'Accessibility',
        'Imágenes Sin Alt',
        `${imagesWithoutAlt} imágenes sin texto alternativo`
      );
    }
    
    // Verificar botones sin texto
    const buttonsWithoutText = await page.locator('button:not(:has-text("")), button:not([aria-label])').count();
    
    logger.log('info', `Botones sin texto/label: ${buttonsWithoutText}`);
    
    logger.saveReport();
  });

  test('A11Y.6 - Formularios - Labels y errores', async ({ page }) => {
    const logger = new AuditLogger('a11y-forms');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/clients/create`);
    await page.waitForTimeout(1000);
    
    // Verificar inputs con label
    const inputs = await page.locator('input, select, textarea').all();
    let inputsWithoutLabel = 0;
    
    for (const input of inputs.slice(0, 10)) {
      try {
        const hasLabel = await input.evaluate(el => {
          const id = el.id;
          const ariaLabel = el.getAttribute('aria-label');
          const ariaLabelledBy = el.getAttribute('aria-labelledby');
          const hasLabelElement = id && !!document.querySelector(`label[for="${id}"]`);
          const parentLabel = el.closest('label');
          
          return hasLabelElement || !!parentLabel || !!ariaLabel || !!ariaLabelledBy;
        });
        
        if (!hasLabel) {
          inputsWithoutLabel++;
        }
      } catch (e) {
        // Ignorar
      }
    }
    
    if (inputsWithoutLabel > 0) {
      bugReporter.addBug(
        'medium',
        'Accessibility',
        'Inputs Sin Label',
        `${inputsWithoutLabel} campos sin label asociado`
      );
    }
    
    logger.saveReport();
  });

  test('A11Y.7 - Skip links', async ({ page }) => {
    const logger = new AuditLogger('a11y-skip-links');
    
    await page.goto(`${CONFIG.BASE_URL}/login`);
    
    const skipLink = await page.locator('a[href^="#"]:has-text("Saltar"), a.skip-link, .skip-link').first();
    
    if (await skipLink.isVisible().catch(() => false)) {
      logger.log('info', '✅ Skip link encontrado');
    } else {
      logger.log('info', '⚠️ No se encontró skip link (recomendado para a11y)');
    }
    
    logger.saveReport();
  });

  test('A11Y.8 - Headings estructurados', async ({ page }) => {
    const logger = new AuditLogger('a11y-headings');
    
    await robustLogin(page);
    await page.goto(`${CONFIG.BASE_URL}/dashboard`);
    await page.waitForTimeout(1000);
    
    const h1Count = await page.locator('h1').count();
    const h2Count = await page.locator('h2').count();
    
    logger.log('info', `Headings: ${h1Count} H1, ${h2Count} H2`);
    
    if (h1Count === 0) {
      bugReporter.addBug('medium', 'Accessibility', 'Sin H1', 'La página no tiene título principal H1');
    } else if (h1Count > 1) {
      bugReporter.addBug('low', 'Accessibility', 'Múltiples H1', `Hay ${h1Count} elementos H1 (debe haber solo 1)`);
    }
    
    logger.saveReport();
  });

});
