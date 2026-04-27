/**
 * @fileoverview Utilidades QA - Helper functions para tests
 * @author QA Engineer Senior
 * @description Funciones reutilizables para testing exhaustivo
 */

const { expect } = require('@playwright/test');

// ==================== CONFIGURACIÓN GLOBAL ====================
const CONFIG = {
  screenshotDir: 'test-results',
  defaultTimeout: 15000,
  retryAttempts: 2,
  waitAfterAction: 1000
};

// ==================== FUNCIONES DE UTILIDAD ====================

/**
 * Captura screenshot con nombre descriptivo
 * @param {Object} page - Instancia de página Playwright
 * @param {string} name - Nombre descriptivo de la captura
 * @param {Object} options - Opciones adicionales
 */
async function captureScreenshot(page, name, options = {}) {
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const filename = `${CONFIG.screenshotDir}/${name}-${timestamp}.png`;
  
  try {
    await page.screenshot({ 
      path: filename, 
      fullPage: options.fullPage !== false,
      timeout: 10000 
    });
    console.log(`📸 Screenshot: ${filename}`);
    return filename;
  } catch (e) {
    console.warn(`⚠️ Error capturando screenshot: ${e.message}`);
    return null;
  }
}

/**
 * Verifica errores en consola del navegador
 * @param {Object} page - Instancia de página Playwright
 * @returns {Array} Lista de errores encontrados
 */
async function checkConsoleErrors(page) {
  const errors = [];
  
  page.on('console', msg => {
    const type = msg.type();
    const text = msg.text().toLowerCase();
    
    if (type === 'error' || 
        text.includes('error') || 
        text.includes('exception') ||
        text.includes('fatal') ||
        text.includes('uncaught')) {
      errors.push({ type, text: msg.text() });
    }
  });
  
  page.on('pageerror', error => {
    errors.push({ type: 'pageerror', text: error.message });
  });
  
  return errors;
}

/**
 * Verifica errores HTTP (404, 500, etc.)
 * @param {Object} page - Instancia de página Playwright
 * @returns {Array} Lista de errores HTTP
 */
async function checkHttpErrors(page) {
  const httpErrors = [];
  
  page.on('response', response => {
    const status = response.status();
    const url = response.url();
    
    if (status >= 400) {
      httpErrors.push({ 
        status, 
        url,
        type: status >= 500 ? 'SERVER_ERROR' : 'CLIENT_ERROR'
      });
    }
  });
  
  return httpErrors;
}

/**
 * Obtiene todos los botones visibles de la página
 * @param {Object} page - Instancia de página Playwright
 * @returns {Array} Lista de botones
 */
async function getAllButtons(page) {
  const buttons = await page.locator('button, [role="button"], input[type="submit"], .btn, [class*="button"]').all();
  const buttonInfo = [];
  
  for (let i = 0; i < buttons.length; i++) {
    try {
      const btn = buttons[i];
      if (await btn.isVisible({ timeout: 2000 }).catch(() => false)) {
        const text = await btn.textContent().catch(() => '');
        const ariaLabel = await btn.getAttribute('aria-label').catch(() => '');
        const title = await btn.getAttribute('title').catch(() => '');
        
        buttonInfo.push({
          index: i,
          text: text?.trim() || ariaLabel || title || 'Sin texto',
          element: btn
        });
      }
    } catch (e) {
      // Ignorar botones que no se pueden procesar
    }
  }
  
  return buttonInfo;
}

/**
 * Obtiene todos los enlaces visibles
 * @param {Object} page - Instancia de página Playwright
 * @returns {Array} Lista de enlaces
 */
async function getAllLinks(page) {
  const links = await page.locator('a[href]').all();
  const linkInfo = [];
  
  for (let i = 0; i < links.length; i++) {
    try {
      const link = links[i];
      if (await link.isVisible({ timeout: 1000 }).catch(() => false)) {
        const text = await link.textContent().catch(() => '');
        const href = await link.getAttribute('href').catch(() => '');
        
        // Filtrar enlaces externos y anclas
        if (href && !href.startsWith('http') && !href.startsWith('#')) {
          linkInfo.push({
            index: i,
            text: text?.trim() || 'Sin texto',
            href,
            element: link
          });
        }
      }
    } catch (e) {
      // Ignorar
    }
  }
  
  return linkInfo;
}

/**
 * Obtiene todos los formularios de la página
 * @param {Object} page - Instancia de página Playwright
 * @returns {Array} Lista de formularios con sus campos
 */
async function getAllForms(page) {
  const forms = await page.locator('form').all();
  const formInfo = [];
  
  for (let i = 0; i < forms.length; i++) {
    try {
      const form = forms[i];
      const inputs = await form.locator('input, select, textarea').all();
      const inputInfo = [];
      
      for (const input of inputs) {
        const type = await input.getAttribute('type').catch(() => 'text');
        const name = await input.getAttribute('name').catch(() => '');
        const required = await input.getAttribute('required').catch(() => null);
        const placeholder = await input.getAttribute('placeholder').catch(() => '');
        
        inputInfo.push({ type, name, required: !!required, placeholder });
      }
      
      formInfo.push({
        index: i,
        inputs: inputInfo,
        element: form
      });
    } catch (e) {
      // Ignorar
    }
  }
  
  return formInfo;
}

/**
 * Llena un formulario con datos de prueba
 * @param {Object} page - Instancia de página Playwright
 * @param {Object} data - Objeto con { selector: valor }
 */
async function fillForm(page, data) {
  for (const [selector, value] of Object.entries(data)) {
    try {
      const field = page.locator(selector).first();
      
      if (await field.isVisible({ timeout: 5000 }).catch(() => false)) {
        const tagName = await field.evaluate(el => el.tagName.toLowerCase());
        const type = await field.getAttribute('type').catch(() => 'text');
        
        if (tagName === 'select') {
          await field.selectOption(value);
        } else if (type === 'checkbox' || type === 'radio') {
          if (value) await field.check();
          else await field.uncheck();
        } else if (type === 'date') {
          await field.fill(value);
        } else {
          await field.fill(value);
        }
        
        console.log(`📝 Campo ${selector} llenado con: ${value}`);
      }
    } catch (e) {
      console.warn(`⚠️ Error llenando campo ${selector}: ${e.message}`);
    }
  }
}

/**
 * Datos de prueba realistas
 */
const TEST_DATA = {
  client: {
    name: 'Juan Pérez Test QA',
    email: 'juan.perez.test@example.com',
    phone: '+56912345678',
    rut: '12.345.678-9',
    address: 'Av. Test 123, Santiago'
  },
  vehicle: {
    brand: 'Toyota',
    model: 'Corolla Test',
    year: '2023',
    plate: 'TEST-123',
    vin: '1HGCM82633A004352',
    mileage: '50000'
  },
  workOrder: {
    description: 'Revisión general de motor QA Test',
    diagnosis: 'Se detecta fuga de aceite QA',
    priority: 'alta'
  },
  part: {
    name: 'Filtro de Aceite Test QA',
    code: 'FA-QA-001',
    stock: '50',
    minStock: '10',
    price: '15000',
    category: 'Filtros'
  },
  service: {
    name: 'Cambio de Aceite QA',
    price: '25000',
    description: 'Cambio de aceite y revisión QA'
  }
};

/**
 * Navega a un módulo del sidebar
 * @param {Object} page - Instancia de página Playwright
 * @param {string|RegExp} moduleName - Nombre del módulo
 * @param {string} expectedUrl - URL esperada (pattern)
 */
async function navigateToModule(page, moduleName, expectedUrl) {
  console.log(`\n🧭 Navegando a: ${moduleName}`);
  
  // Buscar enlace del módulo
  const link = page.getByRole('link', { name: moduleName }).first();
  
  if (!(await link.isVisible({ timeout: 10000 }).catch(() => false))) {
    throw new Error(`Módulo "${moduleName}" no encontrado en el sidebar`);
  }
  
  // Click y esperar navegación
  await Promise.all([
    page.waitForURL(expectedUrl ? new RegExp(expectedUrl) : /.*/, { timeout: 20000 }),
    link.click()
  ]);
  
  // Esperar carga
  await page.waitForLoadState('networkidle', { timeout: 15000 });
  await page.waitForTimeout(CONFIG.waitAfterAction);
  
  console.log(`✅ Navegación exitosa a: ${page.url()}`);
}

/**
 * Verifica texto de error en la página
 * @param {Object} page - Instancia de página Playwright
 * @returns {boolean} True si hay errores visibles
 */
async function hasErrorText(page) {
  const errorPatterns = [
    /error/i, /exception/i, /fatal/i, /warning/i, 
    /fallido/i, /incorrecto/i, /no se pudo/i, /falló/i
  ];
  
  const bodyText = await page.locator('body').textContent().catch(() => '');
  
  for (const pattern of errorPatterns) {
    if (pattern.test(bodyText)) {
      return true;
    }
  }
  
  return false;
}

/**
 * Detecta elementos rotos (imágenes, enlaces)
 * @param {Object} page - Instancia de página Playwright
 * @returns {Object} Reporte de elementos rotos
 */
async function detectBrokenElements(page) {
  const broken = {
    images: [],
    links: [],
    scripts: []
  };
  
  // Imágenes rotas
  const images = await page.locator('img').all();
  for (const img of images) {
    const src = await img.getAttribute('src').catch(() => '');
    const naturalWidth = await img.evaluate(el => el.naturalWidth).catch(() => 0);
    
    if (naturalWidth === 0 && src) {
      broken.images.push(src);
    }
  }
  
  return broken;
}

/**
 * Espera inteligente para elementos dinámicos
 * @param {Object} page - Instancia de página Playwright
 * @param {string} selector - Selector del elemento
 * @param {Object} options - Opciones
 */
async function smartWait(page, selector, options = {}) {
  const { state = 'visible', timeout = CONFIG.defaultTimeout } = options;
  
  try {
    const locator = page.locator(selector).first();
    
    if (state === 'visible') {
      await locator.waitFor({ state: 'visible', timeout });
    } else if (state === 'hidden') {
      await locator.waitFor({ state: 'hidden', timeout });
    } else if (state === 'enabled') {
      await locator.waitFor({ state: 'visible', timeout });
      await expect(locator).toBeEnabled();
    }
    
    return true;
  } catch (e) {
    return false;
  }
}

/**
 * Ejecuta acción con reintentos
 * @param {Function} action - Función a ejecutar
 * @param {number} maxRetries - Máximo de reintentos
 */
async function retryAction(action, maxRetries = CONFIG.retryAttempts) {
  let lastError;
  
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await action();
    } catch (e) {
      lastError = e;
      console.log(`⚠️ Intento ${i + 1} fallido, reintentando...`);
      await new Promise(r => setTimeout(r, 2000));
    }
  }
  
  throw lastError;
}

/**
 * Genera reporte de test
 * @param {string} testName - Nombre del test
 * @param {Object} results - Resultados
 */
function generateReport(testName, results) {
  console.log(`\n📊 REPORTE: ${testName}`);
  console.log('='.repeat(50));
  
  for (const [key, value] of Object.entries(results)) {
    if (Array.isArray(value)) {
      console.log(`${key}: ${value.length} items`);
    } else {
      console.log(`${key}: ${value}`);
    }
  }
  
  console.log('='.repeat(50));
}

// ==================== EXPORTACIONES ====================
module.exports = {
  CONFIG,
  TEST_DATA,
  captureScreenshot,
  checkConsoleErrors,
  checkHttpErrors,
  getAllButtons,
  getAllLinks,
  getAllForms,
  fillForm,
  navigateToModule,
  hasErrorText,
  detectBrokenElements,
  smartWait,
  retryAction,
  generateReport
};
