# 🚀 Guía de Despliegue en InfinityFree

## 📋 Información de tu Cuenta

```
Cuenta: if0_41764302
Panel: https://dash.infinityfree.com/accounts/if0_41764302
Tipo: Hosting Gratuito con cPanel
```

---

## 🔗 **Paso 1: Acceder a tu cPanel en InfinityFree**

1. Ve a: `https://dash.infinityfree.com/accounts/if0_41764302`
2. Click en **"Control Panel"** (se abre cPanel)

---

## 🗄️ **Paso 2: Crear Base de Datos MySQL**

### En cPanel de InfinityFree:

1. Busca: **"MySQL Databases"** o **"Bases de Datos MySQL"**
2. **Crear Base de Datos:**
   - Nombre de BD: `torque` (el sistema agrega prefijo automático)
   - **Nombre completo será:** `if0_41764302_torque`
   - Click **"Create Database"**

3. **Crear Usuario MySQL:**
   - Nombre de usuario: `torqueuser`
   - **Nombre completo será:** `if0_41764302_torqueuser`
   - Password: Genera uno seguro (anótalo)
   - Click **"Create User"**

4. **Agregar Usuario a la BD:**
   - Selecciona usuario: `if0_41764302_torqueuser`
   - Selecciona BD: `if0_41764302_torque`
   - Marca: **"ALL PRIVILEGES"** (todos los privilegios)
   - Click **"Make Changes"**

### 📋 Anota estos datos:
```
Host: sqlXXX.epizy.com (o localhost - verificar en cPanel)
Database: if0_41764302_torque
Username: if0_41764302_torqueuser
Password: [tu_password]
```

**Nota:** El host puede variar, verifica en la sección "Remote MySQL" o en la info de la BD.

---

## 📤 **Paso 3: Subir Archivos vía FTP**

### Conexión FTP (FileZilla):

```
Host: ftpupload.net
Username: if0_41764302
Password: [tu_password_de_FTP]
Port: 21 (o 22 para SFTP)
```

**O usa el File Manager de cPanel:**

1. En cPanel → **"File Manager"**
2. Navega a: `/htdocs/` (o `/public_html/`)
3. **IMPORTANTE:** InfinityFree usa `/htdocs/` no `/public_html/`

### Estructura correcta para InfinityFree:

```
/htdocs/
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── config/
├── database/
├── public/          ← Mover contenido a /htdocs/
│   ├── .htaccess
│   └── index.php
├── routes/
├── storage/
└── ...
```

**⚠️ IMPORTANTE:** En InfinityFree, el `index.php` debe estar en `/htdocs/`, NO en `/htdocs/public/`

---

## 🔧 **Paso 4: Configurar Estructura para InfinityFree**

### Opción A: Mover archivos (Recomendada)

Sube todo el contenido de tu carpeta `/public/` de forma que quede en `/htdocs/`:

```
/htdocs/
├── .htaccess          ← desde /public/
├── index.php          ← desde /public/
├── assets/            ← CSS, JS, imágenes
├── app/               ← (subir desde raíz)
├── database/          ← (subir desde raíz)
├── routes/            ← (subir desde raíz)
└── storage/           ← (subir desde raíz)
```

### Opción B: Modificar index.php

Si prefieres mantener la estructura con carpeta `public/`:

1. Crea en `/htdocs/` un archivo `index.php` que redirija:

```php
<?php
// /htdocs/index.php - Redirección para InfinityFree
require_once __DIR__ . '/public/index.php';
```

2. Y un `.htaccess` en `/htdocs/`:

```apache
RewriteEngine On
RewriteRule ^$ public/ [L]
RewriteRule (.*) public/$1 [L]
```

---

## ⚙️ **Paso 5: Configurar Base de Datos**

### Editar: `app/config/database.php`

```php
<?php
namespace App\Config;

class Database {
    // ============================================
    // CONFIGURACIÓN PARA INFINITYFREE
    // ============================================
    
    // Host: Usualmente 'localhost' o el que indica cPanel
    private static $host = 'localhost';
    
    // Base de datos: if0_41764302_torque (o el nombre que creaste)
    private static $dbname = 'if0_41764302_torque';
    
    // Usuario: if0_41764302_torqueuser (o el nombre que creaste)
    private static $user = 'if0_41764302_torqueuser';
    
    // Password: El que generaste en el paso 2
    private static $password = 'TU_PASSWORD_AQUI';
    
    // ============================================
    
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
            error_log("[DB ERROR] " . $e->getMessage());
            throw new \Exception("Error de conexión a la base de datos");
        }
    }
}
```

**Guarda el archivo y súbelo.**

---

## 🗃️ **Paso 6: Importar Base de Datos**

### En cPanel → phpMyAdmin:

1. Busca **"phpMyAdmin"** en cPanel
2. Selecciona tu BD: `if0_41764302_torque`
3. Click **"Import"** (pestaña superior)
4. **Choose File** → Selecciona `database/schema.sql`
5. Deja todo por defecto (UTF-8)
6. Click **"Go"**

### Si el archivo es muy grande:

**InfinityFree tiene límites de subida.** Si `schema.sql` es muy grande:

1. Divide el archivo en partes más pequeñas
2. O usa la opción **"Remote MySQL"** para conectar desde tu PC

---

## 🔒 **Paso 7: Configurar .htaccess para InfinityFree**

### Archivo: `/htdocs/.htaccess`

```apache
# Activar rewrites
RewriteEngine On

# Redirigir todo a index.php excepto archivos existentes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Proteger archivos sensibles
<FilesMatch "\.(sql|log|ini|md|env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Deshabilitar listado de directorios
Options -Indexes

# Configurar charset
AddDefaultCharset UTF-8

# Límites de PHP (InfinityFree tiene restricciones)
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 60
```

**Nota:** Algunas configuraciones PHP pueden estar deshabilitadas en InfinityFree.

---

## 🚀 **Paso 8: Verificar Instalación**

### Accede a tu dominio:

```
https://tu-dominio.epizy.com
```

O si tienes dominio propio:
```
https://www.tudominio.com
```

### Credenciales por defecto:

```
Email: admin@torque.com
Password: admin123
```

---

## ⚠️ **Limitaciones de InfinityFree (Importante)**

| Característica | InfinityFree | Solución |
|---------------|--------------|----------|
| **Tiempo de inactividad** | 1 hora máximo | Usar cron job para pings |
| **Espacio** | 5 GB | Optimizar imágenes/BD |
| **Ancho de banda** | Ilimitado (teórico) | - |
| **Base de datos** | 400 MB máximo | Limpiar logs regularmente |
| **PHP Mail()** | Limitado | Usar SMTP externo |
| **Cron Jobs** | Disponible | Configurar en cPanel |

### 🔄 Mantener la web activa:

InfinityFree "duerme" sitios inactivos. Para evitarlo:

1. Ve a cPanel → **"Cron Jobs"**
2. Crea un cron que haga ping cada 30 minutos:

```bash
*/30 * * * * curl -s https://tu-dominio.epizy.com/ > /dev/null
```

---

## 🐛 **Solución de Problemas en InfinityFree**

### Error 500:

1. Ve a cPanel → **"Error Logs"**
2. Revisa el último error
3. Común: Problema de permisos o sintaxis en .htaccess

### "Cannot connect to database":

1. Verifica que el host sea correcto (puede ser `sqlXXX.epizy.com`)
2. Verifica credenciales en `app/config/database.php`
3. Asegúrate de que la BD existe en phpMyAdmin

### "403 Forbidden":

1. Verifica permisos de archivos (deben ser 644 o 755)
2. En File Manager → click derecho → "Change Permissions"

### Página en blanco:

1. Activa errores PHP temporalmente en `index.php`:

```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ... resto del código
```

2. Recarga la página y ver el error
3. **Recuerda desactivarlo en producción**

---

## ✅ **Checklist Final**

- [ ] Base de datos creada en cPanel
- [ ] Usuario MySQL creado y asignado a BD
- [ ] Archivos subidos a `/htdocs/`
- [ ] `index.php` está en `/htdocs/` (no en subcarpeta)
- [ ] `.htaccess` configurado
- [ ] `database.php` actualizado con credenciales correctas
- [ ] SQL importado en phpMyAdmin
- [ ] Probado acceso al dominio
- [ ] Login funciona con admin@torque.com / admin123

---

## 📞 **Soporte**

Si tienes problemas:

1. **Foro de InfinityFree:** https://forum.infinityfree.net/
2. **Documentación:** https://docs.infinityfree.net/
3. **Estado del servicio:** https://status.infinityfree.net/

**Tu cuenta específica:**
- Panel: https://dash.infinityfree.com/accounts/if0_41764302
- cPanel: Click en "Control Panel" desde el dashboard

---

## 🎉 ¡Listo!

Tu aplicación Torque Studio ERP debería funcionar en InfinityFree.

**Recuerda:** El plan gratuito tiene limitaciones, considera actualizar si necesitas más recursos.
