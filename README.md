# Torque Studio ERP

Sistema ERP para gestión de taller automotriz desarrollado con PHP puro, MySQL y arquitectura MVC.

## Características

- **Autenticación segura** con `password_hash()` y `password_verify()`
- **Protección contra vulnerabilidades**: SQL Injection (PDO + Prepared Statements), XSS (`htmlspecialchars`), CSRF (tokens)
- **Sistema de Roles**: Admin, Mecánico, Recepcionista
- **Módulos completos**:
  - Clientes (CRUD)
  - Vehículos (CRUD)
  - Órdenes de Trabajo (CRUD + Kanban)
  - Servicios (CRUD)

## Requisitos

- PHP >= 7.4
- MySQL >= 5.7
- Servidor web (Apache/Nginx)

## Instalación

### 1. Configurar Base de Datos

1. Crear la base de datos `torque_erp` en MySQL
2. Importar el esquema:

```sql
mysql -u root -p torque_erp < database/schema.sql
```

O desde phpMyAdmin importar el archivo `database/schema.sql`.

### 2. Configurar Conexión a BD

Editar `config/database.php` con tus credenciales:

```php
private static $host = '127.0.0.1';
private static $dbname = 'torque_erp';
private static $user = 'root';
private static $password = '';
```

### 3. Configurar Servidor Web

**Apache (.htaccess en /public):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**O configurar virtual host apuntando a `/public`**

### 4. Credenciales por Defecto

| Usuario | Email | Contraseña | Rol |
|---------|-------|------------|-----|
| Administrador | admin@torque.com | admin123 | Admin |
| Juan Mecánico | juan@torque.com | admin123 | Mecánico |
| María Recepción | maria@torque.com | admin123 | Recepcionista |

### 5. Datos de Prueba Incluidos

El schema.sql incluye datos de ejemplo:
- **10 servicios** comunes de taller (aceite, frenos, alineación, etc.)
- **5 clientes** con información de contacto
- **6 vehículos** asociados a clientes
- **6 órdenes de trabajo** en diferentes estados

### 6. Ejecutar Script de Verificación

Opcionalmente, puede ejecutar el script de instalación para verificar requisitos:

```bash
php install.php
```

## Estructura del Proyecto

```
/app
  /controllers    # Controladores MVC
  /models         # Modelos MVC
  /views          # Vistas MVC
/config           # Configuración (database.php)
/database         # Schema SQL
/public           # Punto de entrada (index.php) + assets
/routes           # Definición de rutas (web.php)
/storage          # Archivos subidos (logs, uploads)
/vendor           # Dependencias (opcional)
```

## Roles y Permisos

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso total a todos los módulos |
| **Recepcionista** | Clientes, Vehículos, Órdenes de trabajo |
| **Mecánico** | Órdenes de trabajo asignadas |

## Seguridad Implementada

- **SQL Injection**: Uso obligatorio de PDO con Prepared Statements
- **XSS**: Escapado de salida con `htmlspecialchars()`
- **CSRF**: Tokens en todos los formularios POST
- **Contraseñas**: Hash seguro con `password_hash()`
- **Sesiones**: Regeneración de ID en login

## Flujo de Trabajo

1. **Recepción**: Crear cliente → Crear vehículo → Crear orden de trabajo
2. **Diagnóstico**: Actualizar estado y agregar servicios
3. **Reparación**: Seguimiento del progreso
4. **Terminado**: Entrega al cliente

## Kanban Board

Accede a la vista Kanban en: `/work-orders/kanban`

Permite visualizar y mover órdenes entre estados:
- Recepción
- Diagnóstico  
- Reparación
- Terminado

## Desarrollo

Para agregar nuevos módulos:

1. Crear controlador en `app/controllers/`
2. Crear modelo en `app/models/`
3. Crear vistas en `app/views/{modulo}/`
4. Agregar rutas en `routes/web.php`

## Licencia

MIT License - Torque Studio ERP
