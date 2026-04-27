# 🚀 Guía de Despliegue en cPanel

## 📋 Requisitos del Hosting cPanel

- PHP >= 7.4 (preferible 8.0+)
- MySQL >= 5.7
- Extensiones PHP: `pdo`, `pdo_mysql`, `mysqli`
- `mod_rewrite` habilitado
- Acceso a: File Manager, phpMyAdmin, MySQL Databases

---

## 📁 Archivos para Subir

### 1. Preparar Archivos

Primero, crea un ZIP del proyecto (sin las carpetas innecesarias):

```bash
# Excluir: tests/, node_modules/, .git/, test-results/
# Incluir TODO el código fuente
```

**Estructura a subir:**
```
/public_html/ (o directorio de tu dominio)
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── config/
├── database/
├── public/
│   ├── .htaccess
│   └── index.php
├── routes/
├── storage/
├── vendor/ (si usas composer)
└── ...
```

---

## 🗄️ Paso 1: Crear Base de Datos MySQL

### En cPanel:

1. **Ir a:** "MySQL® Database Wizard"

2. **Crear Base de Datos:**
   - Database Name: `tuestudio_torque` (o tu_prefijo_torque)
   - Anota el nombre completo (incluye prefijo de cPanel)

3. **Crear Usuario:**
   - Username: `tuestudio_user`
   - Password: Genera uno seguro (guarda este password)
   - Anota el nombre completo del usuario

4. **Agregar Privilegios:**
   - Marcar **"ALL PRIVILEGES"**
   - Click "Next Step"

---

## 📤 Paso 2: Subir Archivos

### Opción A: File Manager (Recomendado)

1. **cPanel → File Manager**
2. **Navega a:** `public_html/` (o tu directorio del dominio)
3. **Click "Upload"**
4. **Selecciona el ZIP del proyecto**
5. **Extraer el ZIP:**
   - Click derecho en el archivo → "Extract"
   - Asegúrate que los archivos queden en `public_html/`, no en `public_html/torque/`

### Opción B: FTP (FileZilla)

```
Host: tu-dominio.com (o IP del servidor)
Username: tu_usuario_cpanel
Password: tu_password_cpanel
Port: 21
```

**Subir a:** `/public_html/`

---

## 🔧 Paso 3: Configurar .htaccess

### Crear/Editar: `public/.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirigir todo a index.php excepto archivos existentes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Proteger archivos sensibles
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Deshabilitar listado de directorios
Options -Indexes

# Configuración PHP (opcional)
<IfModule php8_module>
    php_value upload_max_filesize 64M
    php_value post_max_size 64M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>
```

---

## ⚙️ Paso 4: Configurar Conexión a BD

### Editar: `app/config/database.php`

```php
<?php
namespace App\Config;

class Database {
    private static $host = 'localhost';  // Generalmente 'localhost' en cPanel
    private static $dbname = 'tuestudio_torque';  // Nombre completo de tu BD
    private static $user = 'tuestudio_user';      // Nombre completo de usuario
    private static $password = 'TU_PASSWORD_SEGURO'; // Password que generaste
    
    public static function getConnection() {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            return new PDO($dsn, self::$user, self::$password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \Exception("Error de conexión a la base de datos");
        }
    }
}
```

---

## 🗃️ Paso 5: Importar Base de Datos

### En cPanel → phpMyAdmin:

1. **Selecciona tu base de datos** (ej: `tuestudio_torque`)
2. **Click "Import"** (pestaña superior)
3. **Click "Choose File"**
4. **Selecciona:** `database/schema.sql`
5. **Dejar todo por defecto** (UTF-8, SQL)
6. **Click "Go"**

### Verificar Importación:

Deberías ver tablas:
- `users`
- `clients`
- `vehicles`
- `work_orders`
- `parts`
- `services`
- etc.

---

## 🧪 Paso 6: Ejecutar Fix SQL (si es necesario)

Si hay errores de columnas faltantes, importa también:

1. **cPanel → phpMyAdmin**
2. **Selecciona tu BD**
3. **Click "Import"**
4. **Selecciona:** `database/fix_purchase_alerts.sql`
5. **Click "Go"**

---

## 🚀 Paso 7: Configurar Punto de Entrada

### Estructura Correcta para cPanel:

```
/public_html/
├── app/
├── database/
├── public/
│   ├── .htaccess    ← Configuración de rewrites
│   └── index.php    ← Punto de entrada
├── routes/
├── storage/
└── ...
```

**Si tu hosting requiere que `index.php` esté en la raíz:**

Mueve el contenido de `/public/` a `/public_html/`:

```
/public_html/
├── .htaccess
├── index.php       ← Aquí
├── assets/         ← CSS, JS, imágenes
├── app/            ← (mover desde fuera)
├── database/       ← (mover desde fuera)
├── routes/         ← (mover desde fuera)
└── storage/        ← (mover desde fuera)
```

**Y actualiza rutas en `index.php`:**

```php
<?php
session_start();

// Ajustar rutas para estructura en raíz
require_once __DIR__ . '/../vendor/autoload.php';  // Cambiar según tu estructura
require_once __DIR__ . '/../app/helpers.php';
```

---

## 🔒 Paso 8: Configurar Permisos

### En File Manager:

1. **Selecciona carpeta `storage/`**
2. **Click "Permissions"**
3. **Establecer:** `755` (rwxr-xr-x)
4. **Click "Change Permissions"**

### Carpetas que necesitan escritura:
- `storage/logs/` (si existe)
- `storage/uploads/` (si existe)
- `storage/cache/` (si existe)

---

## ✅ Paso 9: Verificar Instalación

### Accede a tu dominio:

```
https://tu-dominio.com
```

**Deberías ver:**
- ✅ Página de login
- ✅ Sin errores de conexión a BD
- ✅ Estilos cargando correctamente

### Credenciales de Prueba:
```
Email: admin@torque.com
Password: admin123
```

---

## 🐛 Solución de Problemas Comunes

### Error 500 - Internal Server Error

1. **Verificar .htaccess:**
   - ¿Está en la carpeta correcta?
   - ¿mod_rewrite está habilitado?

2. **Ver logs en cPanel:**
   - cPanel → Errors (últimos errores)
   - cPanel → File Manager → error_log

### Error de Conexión a BD

1. **Verificar credenciales** en `app/config/database.php`
2. **Verificar que la BD existe** en phpMyAdmin
3. **Verificar hostname:** Generalmente `localhost` en cPanel

### Página en blanco

1. **Habilitar display_errors en PHP:**
   - cPanel → MultiPHP INI Editor
   - `display_errors = On`
   - `error_reporting = E_ALL`

2. **Ver error_log en la raíz**

### Archivos CSS/JS no cargan

1. **Verificar que `public/assets/` existe**
2. **Verificar rutas en .htaccess:**
   ```apache
   RewriteCond %{REQUEST_FILENAME} !-f
   ```

---

## 📞 Soporte

Si tienes problemas:

1. **Revisar logs:** cPanel → Errors
2. **Verificar configuración PHP:** cPanel → MultiPHP Manager
3. **Contactar soporte de hosting** con el error específico

---

## 🎉 ¡Listo!

Tu aplicación Torque Studio ERP debería estar funcionando en cPanel.

**URL de acceso:** `https://tu-dominio.com`

**Panel de administración:** `https://tu-dominio.com/dashboard`
