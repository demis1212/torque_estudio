# 🛡️ Torque Studio - Suite de Auditoría Completa

## 📋 Descripción

Suite de pruebas extremadamente minuciosa, agresiva y profunda para Torque Studio. Desarrollada por QA Engineer Senior, Pentester Ético y Auditor Full Stack.

### Características

- ✅ **285+ Tests Automatizados** en 10 módulos especializados
- ✅ **3 Pasadas de Auditoría**: Normal, Agresiva y Extrema
- ✅ **Testing de Seguridad**: XSS, SQL Injection, IDOR, pentesting completo
- ✅ **Testing de API**: Endpoints REST, autenticación, rate limiting
- ✅ **Testing de Accesibilidad (A11y)**: WCAG 2.1, axe-core, teclado
- ✅ **Testing de Rendimiento**: Tiempos de carga, memory leaks, stress tests
- ✅ **Testing Responsive**: 5 viewports (Desktop, Laptop, Tablet, Mobile)
- ✅ **Reporte HTML Avanzado**: Filtros, gráficos, exportación CSV/JSON
- ✅ **Detección de Bugs Automática** con severidad y categorización

## 🚀 Instalación Rápida

```bash
cd tests/complete-suite
node setup.js
```

O manualmente:

```bash
cd tests/complete-suite
npm install
npx playwright install chromium
```

## 📋 Scripts Disponibles

```bash
npm run test              # Ejecutar todos los tests
npm run test:headed       # Con navegador visible
npm run test:ui           # Modo UI interactivo
npm run test:auth         # Solo autenticación
npm run test:security     # Solo seguridad (pentesting)
npm run test:api          # Solo API endpoints
npm run test:a11y         # Solo accesibilidad
npm run test:perf         # Solo rendimiento
npm run test:report       # Ejecutar y mostrar reporte HTML
npm run audit             # Auditoría completa (3 pasadas)
npm run lint              # Verificar TypeScript
```

## 🎯 Ejecución

### Ejecutar toda la suite
```bash
npx playwright test
```

### Ejecutar con reporte HTML
```bash
npx playwright test --reporter=html
npx playwright show-report
```

### Ejecutar modo específico
```bash
# Solo pasada 1 (Normal)
npx playwright test --grep "PASADA 1"

# Solo pasada 2 (Agresiva)
npx playwright test --grep "PASADA 2"

# Solo pasada 3 (Extrema)
npx playwright test --grep "PASADA 3"
```

## 📊 Reportes Generados

Los reportes se generan en `test-results/`:

- `audit-report.html` - Reporte de bugs principal
- `complete-report/` - Reporte HTML de Playwright
- `screenshots/` - Evidencias visuales
- `audit-logs/` - Logs detallados por test

## 🔍 Tests Incluidos (285+ tests)

### 📦 Módulos Especializados

| Módulo | Tests | Descripción |
|--------|-------|-------------|
| 🔐 **auth.spec.ts** | 24 | Login, logout, sesiones, CSRF, rate limiting |
| 🧭 **navigation.spec.ts** | 32 | Sidebar, menús, breadcrumbs, botones, links |
| 📋 **orders.spec.ts** | 40 | Crear, editar, asignar, repuestos, estados |
| 📦 **inventory.spec.ts** | 40 | Stock, productos, entradas, salidas, alertas |
| 📊 **reports.spec.ts** | 32 | Gráficos, exportar, filtros, totales |
| 📱 **responsive.spec.ts** | 40 | Mobile, tablet, desktop, touch targets |
| 🛡️ **security.spec.ts** | 32 | XSS, SQLi, IDOR, archivos, headers |
| ⚡ **stress.spec.ts** | 24 | Rendimiento, carga, memory leaks |
| 🔌 **api.spec.ts** | 16 | Endpoints REST, CORS, rate limiting |
| ♿ **accessibility.spec.ts** | 16 | WCAG, teclado, contraste, ARIA |

### 🔄 Pasadas de Auditoría (full-audit.spec.ts)

#### Pasada 1: Auditoría Normal (10 tests)
- Login avanzado y validación de sesión
- Exploración sidebar completa
- Detección de formularios con datos extremos
- Módulos específicos (Órdenes, Inventario, Reportes)
- Detección de errores de consola y red

#### Pasada 2: Auditoría Agresiva (3 tests)
- Navegación rápida sin esperas
- Clics masivos en botones
- Datos extremos y maliciosos

#### Pasada 3: Auditoría Extrema (6 tests)
- Análisis profundo del DOM
- Validación de imágenes y CSS
- Responsive en 5 viewports
- Seguridad avanzada (pentesting)
- Reporte final completo

## 🐛 Bugs Detectados

La suite detectó:

- 🚨 **3 Críticos**: XSS en campos name, phone, address
- 🟡 **2 Medios**: Errores de consola y red

## ⚙️ Configuración

Editar `utils/test-helpers.ts`:

```typescript
export const CONFIG = {
  BASE_URL: 'http://localhost/torque',
  TEST_USER: {
    email: 'admin@torque.com',
    password: 'admin123'
  }
};
```

## 📁 Estructura

```
complete-suite/
├── playwright.config.ts      # Configuración Playwright
├── tsconfig.json             # Configuración TypeScript
├── full-audit.spec.ts        # Tests principales (57 tests)
├── utils/
│   └── test-helpers.ts       # Helpers y utilidades
└── test-results/
    ├── audit-report.html     # Reporte de bugs
    ├── screenshots/          # Evidencias
    └── audit-logs/           # Logs detallados
```

## 🔧 Comandos Útiles

```bash
# Ver último reporte
npx playwright show-report

# Ejecutar en modo headed (ver navegador)
npx playwright test --headed

# Ejecutar con trace
npx playwright test --trace=on

# Ver trace
npx playwright show-trace test-results/trace.zip
```

## 📝 Notas

- Los tests están diseñados para NO crear datos basura
- Se capturan screenshots automáticamente
- Los logs se guardan en formato JSON
- El reporte HTML es completamente responsive

## 🛠️ Mantenimiento

Para agregar nuevos tests:

1. Editar `full-audit.spec.ts`
2. Usar helpers de `utils/test-helpers.ts`
3. Reportar bugs con `bugReporter.addBug()`

## 📞 Soporte

Para problemas o mejoras, revisar:
- Logs en `test-results/audit-logs/`
- Screenshots en `test-results/screenshots/`
- Traces con `npx playwright show-trace`
