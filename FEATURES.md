# ✅ Torque Studio ERP - Funcionalidades Completas

## 🗄️ Base de Datos - 13 Tablas

| Tabla | Descripción |
|-------|-------------|
| `roles` | Roles de usuario (Admin, Mecánico, Recepcionista) |
| `users` | Usuarios del sistema |
| `clients` | Clientes del taller |
| `vehicles` | Vehículos registrados |
| `services` | Servicios ofrecidos |
| `work_orders` | Órdenes de trabajo |
| `work_order_services` | Servicios en cada orden |
| **✨ parts** | Inventario de repuestos |
| **✨ work_order_parts** | Repuestos usados en órdenes |
| **✨ work_order_assignments** | Asignación de mecánicos |
| **✨ notifications** | Notificaciones por usuario |
| **✨ activity_logs** | Logs de actividad del sistema |
| **✨ settings** | Configuraciones del sistema |

---

## 🌐 URLs del Sistema

### Dashboards
- `/dashboard` - Dashboard Principal (Admin/Recepcionista)
- `/mechanic/dashboard` - **Dashboard exclusivo para mecánicos**
- `/mechanic/orders` - Órdenes asignadas al mecánico

### Módulos CRUD
- `/users` - Gestión de usuarios
- `/clients` - Clientes
- `/vehicles` - Vehículos
- `/work-orders` - Órdenes de trabajo
- `/services` - Servicios
- `/parts` - **Inventario de repuestos** ⭐

### Funcionalidades Avanzadas
- `/search` - **Búsqueda global** (clientes, órdenes, repuestos)
- `/notifications` - **Centro de notificaciones**
- `/reminders` - **Sistema de recordatorios**
- `/reports` - **Reportes y estadísticas** con gráficos
- `/reports/invoice/{id}` - **Factura imprimible**
- `/reports/activity` - **Logs de actividad**

### API REST (JSON)
- `/api/work-orders` - Lista de órdenes
- `/api/work-orders/{id}` - Detalle de orden
- `/api/work-orders/{id}/status` - Actualizar estado
- `/api/clients` - Lista de clientes
- `/api/vehicles` - Lista de vehículos
- `/api/parts` - Lista de repuestos
- `/api/stats` - Estadísticas del sistema
- `/api/mechanic/{id}/orders` - Órdenes de un mecánico

---

## ✨ Funcionalidades Implementadas

### 1. **Sistema de Inventario (Parts)**
- ✅ CRUD completo de repuestos
- ✅ Control de stock (cantidad y mínimo)
- ✅ Categorías de repuestos
- ✅ Precios de costo y venta
- ✅ Ubicación en almacén
- ✅ Proveedor
- ✅ Alertas de stock bajo (en dashboard)
- ✅ Búsqueda por código o nombre
- ✅ Ajuste rápido de stock (+/-)

### 2. **Asignación de Mecánicos**
- ✅ Asignar mecánicos a órdenes
- ✅ Múltiples mecánicos por orden
- ✅ Notas de asignación
- ✅ Vista exclusiva del mecánico
- ✅ Mecánicos ven solo sus órdenes
- ✅ Cambio de estado desde el panel mecánico

### 3. **Repuestos en Órdenes**
- ✅ Agregar repuestos a órdenes
- ✅ Stock se descuenta automáticamente
- ✅ Stock se restaura al quitar
- ✅ Total se recalcula automáticamente
- ✅ Ver repuestos usados en cada orden

### 4. **Facturas y Reportes**
- ✅ Factura imprimible para cada orden
- ✅ Formato profesional con ITBIS
- ✅ Reportes con gráficos (Chart.js)
- ✅ Ingresos por mes
- ✅ Servicios más solicitados
- ✅ Ranking de clientes
- ✅ Distribución por estado
- ✅ Exportación a Excel (preparado)
- ✅ Logs de actividad completos

### 5. **Búsqueda Global**
- ✅ Buscar en clientes
- ✅ Buscar en órdenes
- ✅ Buscar en repuestos
- ✅ Resultados por categoría
- ✅ Links directos

### 6. **Notificaciones**
- ✅ Centro de notificaciones
- ✅ Notificaciones por usuario
- ✅ Tipos: info, warning, success, error
- ✅ Links en notificaciones
- ✅ Contador en dashboard
- ✅ Marcar como leído
- ✅ Eliminar notificaciones

### 7. **Recordatorios**
- ✅ Crear recordatorios
- ✅ Recordatorios vencidos (rojo)
- ✅ Próximos recordatorios (amarillo)
- ✅ Completar recordatorios
- ✅ Vista por pestañas

### 8. **API REST**
- ✅ Endpoints JSON
- ✅ Autenticación simple
- ✅ Datos con meta información
- ✅ Links HATEOAS

### 9. **Dashboard Mejorado**
- ✅ Estadísticas en tiempo real
- ✅ Alertas de stock bajo
- ✅ Notificaciones recientes
- ✅ Órdenes recientes
- ✅ Buscador global
- ✅ Acceso rápido a secciones

### 10. **Seguridad**
- ✅ CSRF tokens en todos los formularios
- ✅ Control de acceso por roles
- ✅ Logs de actividad
- ✅ Escapado de output (XSS protection)
- ✅ Prepared statements (SQL injection protection)

---

## 📊 Seeders de Datos

### 10 Repuestos incluidos:
- Aceite Motor 5W-30
- Filtro de Aceite
- Filtro de Aire
- Bujía NGK
- Pastillas de Freno
- Discos de Freno
- Amortiguador Trasero
- Batería 55AH
- Correa Distribución
- Refrigerante 1L

### 4 Notificaciones de ejemplo:
- Orden en diagnóstico
- Alerta de stock bajo
- Nueva orden asignada
- Orden terminada

### Configuraciones del sistema:
- Nombre del taller
- Teléfono y dirección
- Moneda (DOP)
- Tasa ITBIS (18%)
- Prefijo de órdenes

---

## 🔑 Credenciales de Prueba

| Rol | Email | Password |
|-----|-------|----------|
| Admin | admin@torque.com | admin123 |
| Mecánico | mechanic@torque.com | mech123 |
| Recepcionista | reception@torque.com | recep123 |

---

## 🚀 Servidor

**URL:** http://127.0.0.1:8000

**Para reiniciar:**
```powershell
Get-Process php | Stop-Process -Force; C:\xampp\php\php.exe -S 127.0.0.1:8000 -t "c:\Users\victuspc\Desktop\Nueva carpeta\public"
```

---

## 📁 Estructura de Archivos

```
app/
├── controllers/
│   ├── ApiController.php          ← API REST
│   ├── MechanicController.php     ← Panel mecánico
│   ├── NotificationController.php ← Notificaciones
│   ├── PartController.php         ← Inventario
│   ├── ReminderController.php     ← Recordatorios
│   ├── ReportController.php       ← Reportes
│   └── ...
├── models/
│   ├── ActivityLog.php            ← Logs
│   ├── Notification.php           ← Notificaciones
│   ├── Part.php                   ← Repuestos
│   ├── Reminder.php               ← Recordatorios
│   └── ...
└── views/
    ├── mechanics/                 ← Vistas de mecánico
    ├── notifications/             ← Centro de notificaciones
    ├── parts/                     ← Inventario
    ├── reminders/                 ← Recordatorios
    ├── reports/                   ← Reportes y logs
    └── ...
```

---

## ✅ TODO Completado

- ✅ Base de datos con 13 tablas
- ✅ Dashboard de mecánico exclusivo
- ✅ Reportes avanzados con gráficos
- ✅ API REST completa
- ✅ Botones de exportación preparados
- ✅ Código QR preparado
- ✅ Histórico de cambios (logs)
- ✅ Sistema de recordatorios
