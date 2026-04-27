# 🔒 REPORTE DE AUDITORÍA DE SEGURIDAD - TORQUE STUDIO

> **Tipo:** Pentesting Ético  
> **Fecha:** Abril 2026  
> **Alcance:** Aplicación Web Torque Studio  
> **Metodología:** OWASP Top 10, SANS Top 25

---

## 📊 RESUMEN EJECUTIVO

| Severidad | Hallazgos |
|-----------|-----------|
| 🔴 **CRÍTICO** | 2 |
| 🟠 **ALTO** | 4 |
| 🟡 **MEDIO** | 6 |
| 🟢 **BAJO** | 8 |

---

## 🔴 VULNERABILIDADES CRÍTICAS

### 1. **Missing Authentication on Multiple Routes** (CRÍTICO)

**Ubicación:** `routes/web.php`, Múltiples controladores

**Descripción:** Varios controladores no implementan `checkAuth()` o tienen rutas expuestas sin verificación de sesión.

**Rutas potencialmente expuestas:**
```php
/dashboard        → Sin checkAuth() explícito
/work-orders      → Sin checkAuth() explícito  
/clients          → Sin checkAuth() explícito
/vehicles         → Sin checkAuth() explícito
/services         → Sin checkAuth() explícito
/parts            → Sin checkAuth() explícito
/tools            → Sin checkAuth() explícito
/reports          → Sin checkAuth() explícito
/workshop-ops     → Sin checkAuth() explícito
/api/*            → Potencialmente expuesto
```

**Prueba de concepto:**
```bash
curl -I http://localhost/torque/dashboard    # Sin cookie de sesión
curl -I http://localhost/torque/work-orders  # Acceso directo
```

**Impacto:** Acceso no autorizado a datos sensibles del taller.

**Solución:** Implementar `checkAuth()` global en `web.php` para todas las rutas protegidas.

---

### 2. **Mass Assignment Vulnerabilities** (CRÍTICO)

**Ubicación:** Todos los controladores `store()` y `update()`

**Descripción:** Los métodos aceptan `$_POST` completo sin filtrar campos permitidos.

**Ejemplo vulnerable (WorkOrderController.php:59):**
```php
public function store() {
    $data = $_POST;  // ← TODOS los campos
    $data['user_id'] = $_SESSION['user_id'];
    $workOrderId = $woModel->create($data);  // ← Mass assignment
}
```

**Ataque potencial:**
```http
POST /work-orders/create HTTP/1.1
Content-Type: application/x-www-form-urlencoded

csrf_token=xxx&status=completed&total_price=0&is_paid=1&...
```

**Impacto:** Manipulación de datos, escalada de privilegios, bypass de validaciones.

**Solución:** Lista blanca de campos permitidos:
```php
$allowed = ['client_id', 'vehicle_id', 'description', 'priority'];
$data = array_intersect_key($_POST, array_flip($allowed));
```

---

## 🟠 VULNERABILIDADES ALTA

### 3. **IDOR - Insecure Direct Object Reference** (ALTO)

**Ubicación:** Todos los métodos `edit()`, `update()`, `delete()`

**Descripción:** No se verifica que el usuario tenga permiso sobre el recurso.

**Ejemplo vulnerable:**
```php
// UserController.php:67-79
public function edit($id) {
    $this->checkAuth();  // Solo verifica login, no ownership
    $user = $userModel->find($id);  // ← Cualquier ID accesible
    // No verifica: $_SESSION['user_id'] == $user['id']
}
```

**Ataque:**
```
/users/edit/1      → Editar admin
/users/edit/2      → Editar otro usuario  
/work-orders/edit/123 → Ver orden de otro cliente
```

**Impacto:** Acceso/modificación de datos de otros usuarios.

**Solución:**
```php
$user = $userModel->find($id);
if (!$user || $user['id'] != $_SESSION['user_id'] && getUserRole() != 1) {
    die("Acceso denegado");
}
```

---

### 4. **SQL Injection Potential** (ALTO)

**Ubicación:** Múltiples modelos

**Descripción:** Aunque se usa PDO con prepared statements en la mayoría, hay riesgos:

**Patrón encontrado:**
```php
// Algunos modelos usan query() directo
$stmt = $this->db->query("SELECT * FROM {$this->table} WHERE...");
```

**Tablas dinámicas sin sanitización:**
```php
protected $table = 'users';  // ¿Qué pasa si se modifica?
```

**Impacto:** Exfiltración de base de datos, RCE potencial.

**Verificación:** Todas las queries usar `prepare()` + `execute()` con parámetros.

---

### 5. **XSS Reflejado y Almacenado** (ALTO)

**Ubicación:** Vistas que no usan `esc()`

**Descripción:** Algunas salidas no están escapadas:

**Ejemplo potencial:**
```php
// Si la vista hace:
echo $client['name'];  // ← Sin esc()
// Y el nombre es: <script>alert('XSS')</script>
```

**Campos de riesgo:**
- Nombres de clientes
- Descripciones de órdenes
- Nombres de repuestos
- Comentarios/mensajes

**Impacto:** Robo de sesiones, defacement, redirección maliciosa.

**Solución:** Usar `esc()` en TODAS las salidas de usuario.

---

### 6. **CSRF Bypass Potential** (ALTO)

**Ubicación:** `routes/web.php:49-51`

**Descripción:** La verificación CSRF solo ocurre en POST, pero hay métodos sensibles que podrían usar otros verbos.

**Código actual:**
```php
if ($method === 'POST') {
    verify_csrf();  // Solo POST
}
```

**Problema:** ¿Qué pasa con PUT, PATCH, DELETE via `_method`?

**Solución:** Verificar CSRF en TODOS los métodos de escritura.

---

## 🟡 VULNERABILIDADES MEDIO

### 7. **Information Disclosure** (MEDIO)

**Ubicación:** Múltiples archivos

**Hallazgos:**
```php
// UserController.php:17
if (getUserRole() != 1) {
    die("Acceso denegado. Solo administradores pueden gestionar usuarios.");
}

// WorkshopOpsController.php:51, 207, 214, etc.
die('Orden no encontrada');
die("Usuario no encontrado.");
```

**Problema:** Los mensajes de error revelan información del sistema.

**Solución:** Mensajes genéricos + logging interno.

---

### 8. **Session Management Issues** (MEDIO)

**Ubicación:** `AuthController.php:32`

**Hallazgo:** Solo se usa `session_regenerate_id(true)` en login, pero:
- No hay timeout de sesión configurado
- No hay invalidación de sesiones viejas
- No hay protección contra session fixation completa

**Solución:**
```php
// Configurar en bootstrap
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.gc_maxlifetime', 3600);  // 1 hora
```

---

### 9. **Password Policy Weakness** (MEDIO)

**Ubicación:** `UserController.php`

**Descripción:** No hay validación de fortaleza de contraseña:

```php
$data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
// Sin validar: longitud mínima, complejidad, etc.
```

**Impacto:** Contraseñas débiles permitidas.

---

### 10. **Debug Information Exposure** (MEDIO)

**Ubicación:** Múltiples controladores

**Hallazgos:**
```php
// ReportController.php, ApiController.php, etc.
// Uso de var_dump(), print_r() potencial
```

**Riesgo:** En modo debug, información sensible expuesta.

---

### 11. **Missing Rate Limiting** (MEDIO)

**Ubicación:** `AuthController.php`

**Descripción:** No hay limitación de intentos de login:

```php
// Login sin rate limiting
if ($user && password_verify($password, $user['password'])) {
    // Éxito - pero no cuenta intentos fallidos
}
```

**Impacto:** Ataques de fuerza bruta.

**Solución:** Implementar throttling de intentos.

---

### 12. **Path Traversal Potential** (MEDIO)

**Ubicación:** `ManualController.php`, uploads

**Descripción:** Si hay carga de archivos, no se verifica:
```php
// Verificar que no exista:
$file = $_GET['file'];
include("uploads/$file");  // Peligroso
```

---

## 🟢 VULNERABILIDADES BAJA

### 13-20. **Prácticas de Seguridad Débiles**

| # | Problema | Ubicación | Impacto |
|---|----------|-----------|---------|
| 13 | Missing Security Headers | Global | Clickjacking, XSS |
| 14 | No Content Security Policy | Global | XSS mitigation |
| 15 | Error Messages Verbose | Controllers | Info disclosure |
| 16 | Hardcoded paths | Múltiples | Mantenimiento |
| 17 | No Input Validation | Formularios | Data integrity |
| 18 | Missing Logging | Actions | Audit trail gaps |
| 19 | No HTTPS Enforcement | Global | MITM |
| 20 | Weak Session ID | Config | Session prediction |

---

## 🧪 PRUEBAS MANUALES RECOMENDADAS

### 1. Test de Autenticación Bypass
```bash
# Sin autenticación
curl -s http://localhost/torque/dashboard | head -20
curl -s http://localhost/torque/work-orders | head -20
curl -s http://localhost/torque/clients | head -20
curl -s http://localhost/torque/parts | head -20

# Verificar redirección a login o acceso directo
```

### 2. Test de IDOR
```bash
# Con cookie de sesión válida
# Probar acceso a IDs que no pertenecen al usuario
curl -b "PHPSESSID=xxx" http://localhost/torque/work-orders/edit/999
curl -b "PHPSESSID=xxx" http://localhost/torque/clients/edit/5
```

### 3. Test de Mass Assignment
```bash
# Agregar campos no permitidos
curl -X POST http://localhost/torque/work-orders/create \
  -d "csrf_token=xxx" \
  -d "client_id=1" \
  -d "vehicle_id=1" \
  -d "status=completed" \
  -d "total=0"
```

### 4. Test de XSS
```bash
# Crear registro con payload XSS
curl -X POST http://localhost/torque/clients/create \
  -d "csrf_token=xxx" \
  -d "name=<script>alert('XSS')</script>" \
  -d "email=test@test.com"
```

### 5. Test de CSRF
```bash
# Request sin token
curl -X POST http://localhost/torque/clients/create \
  -d "name=Test" \
  -d "email=test@test.com"
```

---

## 🛠️ RECOMENDACIONES DE MITIGACIÓN

### Inmediatas (CRÍTICO):

1. **Implementar Middleware de Autenticación Global**
```php
// routes/web.php al inicio
$protectedRoutes = ['/dashboard', '/work-orders', '/clients', ...];
if (in_array($uri, $protectedRoutes) && !isset($_SESSION['user_id'])) {
    redirect('/login');
}
```

2. **Whitelist de Campos en Todos los Controladores**
```php
$allowedFields = ['name', 'email', 'phone', 'address'];
$data = array_intersect_key($_POST, array_flip($allowedFields));
```

3. **Verificar Ownership en IDOR**
```php
// En cada método edit/update/delete
if (!$resource || $resource['user_id'] != $_SESSION['user_id']) {
    abort(403);
}
```

### A Corto Plazo (ALTO):

4. Auditar todas las queries SQL
5. Implementar CSP headers
6. Agregar rate limiting en login
7. Mejorar mensajes de error

### A Mediano Plazo (MEDIO/BAJO):

8. Implementar WAF
9. Configurar HTTPS forzado
10. Logging de seguridad
11. Password policy enforcement

---

## 📋 CHECKLIST DE VERIFICACIÓN

- [ ] Todas las rutas verifican autenticación
- [ ] Todos los inputs usan whitelist
- [ ] Todos los outputs usan `esc()`
- [ ] Verificación de ownership en recursos
- [ ] CSRF tokens en todos los forms
- [ ] Rate limiting en login/API
- [ ] Security headers configurados
- [ ] Error messages genéricos
- [ ] Session timeout configurado
- [ ] HTTPS forzado
- [ ] WAF implementado
- [ ] Logging de seguridad activo

---

## 🚨 PRIORIDADES DE REMEDIACIÓN

| Prioridad | Tiempo | Acciones |
|-----------|--------|----------|
| **P0** | 24 horas | Fix auth bypass + mass assignment |
| **P1** | 1 semana | Fix IDOR + XSS |
| **P2** | 2 semanas | CSRF + Rate limiting |
| **P3** | 1 mes | Headers + Logging |

---

**Nota:** Este reporte es para fines educativos y de mejora de seguridad. No realizar pruebas en producción sin autorización.
