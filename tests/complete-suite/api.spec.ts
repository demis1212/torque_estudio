import { test, expect } from '@playwright/test';
import { CONFIG, AuditLogger, BugReporter } from './utils/test-helpers';

const bugReporter = new BugReporter();

test.describe('🔌 Módulo API - Endpoints REST', () => {
  
  test('API.1 - Verificar endpoints básicos responden', async ({ request }) => {
    const logger = new AuditLogger('api-basic');
    
    const endpoints = [
      { url: '/api/clients', name: 'Clientes' },
      { url: '/api/vehicles', name: 'Vehículos' },
      { url: '/api/work-orders', name: 'Órdenes' },
      { url: '/api/parts', name: 'Repuestos' },
    ];
    
    for (const endpoint of endpoints) {
      try {
        const response = await request.get(`${CONFIG.BASE_URL}${endpoint.url}`);
        const status = response.status();
        
        logger.log('info', `${endpoint.name}: HTTP ${status}`);
        
        if (status === 404) {
          bugReporter.addBug('medium', 'API', `Endpoint No Existe: ${endpoint.url}`, 'Retorna 404');
        } else if (status === 500) {
          bugReporter.addBug('critical', 'API', `Error Servidor: ${endpoint.url}`, 'Retorna 500');
        }
        
      } catch (e) {
        logger.log('warning', `${endpoint.name}: Error - ${e}`);
      }
    }
    
    logger.saveReport();
  });

  test('API.2 - API autenticación requerida', async ({ request }) => {
    const logger = new AuditLogger('api-auth');
    
    const protectedEndpoints = [
      '/api/clients/create',
      '/api/work-orders/store',
      '/api/parts/update',
    ];
    
    for (const url of protectedEndpoints) {
      try {
        // Intentar POST sin autenticación
        const response = await request.post(`${CONFIG.BASE_URL}${url}`, {
          data: { test: 'data' }
        });
        
        const status = response.status();
        
        if (status === 200 || status === 201) {
          bugReporter.addBug('critical', 'API Security', `API Sin Protección: ${url}`, 'Permite acceso sin auth');
        } else if (status !== 401 && status !== 403) {
          logger.log('info', `${url}: HTTP ${status} (esperado 401/403)`);
        }
        
      } catch (e) {
        logger.log('info', `${url}: Error esperado`);
      }
    }
    
    logger.saveReport();
  });

  test('API.3 - Formato JSON válido', async ({ request }) => {
    const logger = new AuditLogger('api-json');
    
    try {
      const response = await request.get(`${CONFIG.BASE_URL}/api/clients`);
      const contentType = response.headers()['content-type'];
      
      if (contentType?.includes('application/json')) {
        const body = await response.json().catch(() => null);
        
        if (body === null) {
          bugReporter.addBug('high', 'API', 'JSON Inválido', 'Content-Type JSON pero cuerpo no parseable');
        }
      } else {
        bugReporter.addBug('medium', 'API', 'Content-Type Incorrecto', `Esperado JSON, recibido: ${contentType}`);
      }
      
    } catch (e) {
      logger.log('warning', 'Error verificando JSON');
    }
    
    logger.saveReport();
  });

  test('API.4 - Rate limiting en API', async ({ request }) => {
    const logger = new AuditLogger('api-rate-limit');
    
    const url = `${CONFIG.BASE_URL}/api/clients`;
    let rateLimited = false;
    
    for (let i = 0; i < 20; i++) {
      try {
        const response = await request.get(url);
        
        if (response.status() === 429) {
          rateLimited = true;
          logger.log('info', `Rate limit detectado después de ${i + 1} requests`);
          break;
        }
        
      } catch (e) {
        break;
      }
    }
    
    if (!rateLimited) {
      bugReporter.addBug('low', 'API Security', 'Sin Rate Limiting', 'No hay limitación de requests');
    }
    
    logger.saveReport();
  });

  test('API.5 - CORS headers', async ({ request }) => {
    const logger = new AuditLogger('api-cors');
    
    try {
      const response = await request.get(`${CONFIG.BASE_URL}/api/clients`, {
        headers: {
          'Origin': 'https://malicious-site.com'
        }
      });
      
      const corsHeader = response.headers()['access-control-allow-origin'];
      
      if (corsHeader === '*') {
        bugReporter.addBug('medium', 'API Security', 'CORS Permisivo', 'CORS permite cualquier origen (*)');
      }
      
    } catch (e) {
      logger.log('info', 'Error verificando CORS');
    }
    
    logger.saveReport();
  });

  test('API.6 - HTTP Methods permitidos', async ({ request }) => {
    const logger = new AuditLogger('api-methods');
    
    const methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    const url = `${CONFIG.BASE_URL}/api/clients`;
    
    for (const method of methods) {
      try {
        let response;
        
        switch (method) {
          case 'GET':
            response = await request.get(url);
            break;
          case 'POST':
            response = await request.post(url, { data: {} });
            break;
          case 'PUT':
            response = await request.put(url, { data: {} });
            break;
          case 'DELETE':
            response = await request.delete(url);
            break;
          case 'PATCH':
            response = await request.patch(url, { data: {} });
            break;
        }
        
        if (response) {
          const status = response.status();
          logger.log('info', `${method}: HTTP ${status}`);
          
          if (method === 'DELETE' && status === 200) {
            logger.log('warning', 'DELETE permitido sin auth');
          }
        }
        
      } catch (e) {
        logger.log('info', `${method}: Error`);
      }
    }
    
    logger.saveReport();
  });

});
