# 🧪 Suite de Tests QA - Torque Studio

> **Autor:** QA Engineer Senior  
> **Framework:** Playwright  
> **Versión:** 2.0

---

## 📋 Estructura de Tests

```
tests/
├── README.md                    # Este archivo
├── auth.setup.js               # Setup de autenticación (login una sola vez)
├── helpers/
│   └── utils.js                # Funciones utilitarias QA
├── 01-navigation.spec.js       # Tests de navegación por módulos
├── 02-forms.spec.js            # Tests de formularios (CRUD)
├── 03-smoke.spec.js            # Tests de detección de errores
└── 04-exploratory.spec.js      # Tests exploratorios recursivos
```

---

## 🚀 Ejecución

### Ejecutar todos los tests:
```bash
npx playwright test
```

### Ejecutar con UI:
```bash
npx playwright test --ui
```

### Ejecutar un archivo específico:
```bash
npx playwright test 01-navigation.spec.js
```

### Ejecutar con reporte HTML:
```bash
npx playwright test --reporter=html
```

---

## ⚙️ Configuración

Las credenciales son configurables mediante variables de entorno:

```bash
# Windows PowerShell
$env:TEST_EMAIL="admin@torque.com"
$env:TEST_PASSWORD="admin123"
$env:BASE_URL="http://localhost/torque"

# O editar playwright.config.js
```

---

## 📊 Tests Incluidos

### 1️⃣ Navegación (01-navigation.spec.js)
- ✅ Dashboard completo
- ✅ Recorrido de TODOS los módulos del sidebar
- ✅ Sub-menús anidados
- ✅ Breadcrumbs
- ✅ Logout

### 2️⃣ Formularios (02-forms.spec.js)
- ✅ Clientes (Crear, Editar, Eliminar)
- ✅ Vehículos
- ✅ Órdenes de trabajo
- ✅ Inventario/Repuestos
- ✅ Servicios
- ✅ Validaciones de campos

### 3️⃣ Smoke Tests (03-smoke.spec.js)
- 🐛 Errores JavaScript en consola
- 🌐 Errores HTTP 4xx/5xx
- 🖼️ Imágenes rotas
- ⚠️ Textos de error en páginas
- 🔘 Botones sin acción
- 🔗 Enlaces rotos
- 📊 Tablas y paginación
- 🔍 Búsquedas y filtros
- ⏱️ Tiempos de carga
- 📱 Responsive

### 4️⃣ Exploratorios (04-exploratory.spec.js)
- 🎪 Click en TODOS los botones del dashboard
- 🔄 Exploración profunda de módulos
- 🔧 Operación Inteligente
- 📦 Inventario con alertas
- 🔔 Notificaciones
- 📊 Reportes y exportación
- 🛠️ Herramientas completas
- 🎯 Recursivo - Todos los botones

---

## 📸 Evidencia

Los tests generan automáticamente:
- **Screenshots** en cada paso
- **Videos** si fallan
- **Traces** para debug
- **Reporte HTML** interactivo

Ubicación: `test-results/` y `playwright-report/`

---

## 🎯 Características QA

### Robustez
- ✅ Selectores resistentes (role-based)
- ✅ Esperas inteligentes
- ✅ Reintentos automáticos
- ✅ Manejo de errores graceful

### Detección de Errores
- ✅ Errores JS en consola
- ✅ Errores HTTP
- ✅ Elementos rotos
- ✅ Textos de error
- ✅ Timeouts

### Datos de Prueba
- Datos realistas incluidos
- No crea basura permanente
- Limpia después de tests

---

## 📈 Métricas

| Métrica | Valor |
|---------|-------|
| Total Tests | 30+ |
| Cobertura Módulos | 100% |
| Cobertura Formularios | 100% |
| Screenshots por Test | ~5-10 |
| Tiempo Estimado | 2-5 min |

---

## 🐛 Debug

### Ver ejecución en tiempo real:
```bash
npx playwright test --headed
```

### Ver solo fallidos:
```bash
npx playwright test --last-failed
```

### Debug paso a paso:
```bash
npx playwright test --debug
```

---

## 📝 Notas

- Tests son independientes (autocontenidos)
- Login se hace una sola vez (setup compartido)
- No dependen de datos específicos
- Funcionan con datos de prueba existentes
- Screenshots automatizados en cada acción

---

## 🆘 Soporte

Si un test falla:
1. Revisar screenshots en `test-results/`
2. Ver trace en `playwright-report/`
3. Ejecutar con `--debug` para ver paso a paso
4. Verificar credenciales en `playwright.config.js`
