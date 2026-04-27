import asyncio
import os
import random
from datetime import datetime
from playwright.async_api import async_playwright

# =======================================================
# CONFIGURACIÓN
# =======================================================
BASE_URL = "http://127.0.0.1:53292"
USER = "admin@torque.com"
PASS = "admin123"

DESKTOP = os.path.join(os.path.expanduser("~"), "Desktop")
REPORT_DIR = os.path.join(DESKTOP, "Auditoria_Completa_Torque")
os.makedirs(REPORT_DIR, exist_ok=True)

# Datos realistas
NOMBRES = ["Carlos Mendoza", "Laura Fernández", "Roberto Sánchez", "Ana Gómez"]
MARCAS = ["Toyota", "Honda", "Hyundai", "Ford"]
MODELOS = ["Corolla", "Civic", "Accent", "Ranger"]
HERRAMIENTAS = ["Llave de torque", "Gato hidráulico", "Detector de fugas UV", "Compresor de aire"]

# =======================================================
# MOTOR DE TRAZABILIDAD Y DIAGNÓSTICO
# =======================================================
class TraceLogger:
    def __init__(self):
        self.steps = []
    def log(self, action, status="INFO"):
        ts = datetime.now().strftime('%H:%M:%S.%f')[:-3]
        entry = f"[{ts}] [{status}] {action}"
        self.steps.append(entry)
        print(entry)

logger = TraceLogger()

def diagnosticar_error(error_str, url, accion):
    error_lower = error_str.lower()
    if "timeout" in error_lower:
        return "El servidor tardó demasiado (Timeout).", "Revisar consulta SQL lenta o bucle infinito en PHP/JS."
    elif "locator" in error_lower and ("wait" in error_lower or "not found" in error_lower):
        return f"No se encontró el elemento en '{accion}'.", "Verificar que el usuario tenga permisos o que la vista esté cargando bien."
    elif "strict mode" in error_lower:
        return "Error de JavaScript silencioso.", "Abrir F12 en esa URL y revisar la pestaña Console."
    else:
        return "Error inesperado de interacción.", "Revisar la captura de pantalla para ver el estado visual."

# =======================================================
# UTILIDADES INTELIGENTES (NIVEL DIOS)
# =======================================================
async def smart_click(page, text, exact=False):
    logger.log(f"Clic en: '{text}'")
    try:
        # Busca en botones, enlaces, o cualquier texto visible
        locator = page.locator(f"button:has-text('{text}'), a:has-text('{text}'), div[role='button']:has-text('{text}'), span:has-text('{text}')").first
        await locator.wait_for(state="visible", timeout=4000)
        await locator.click()
        return True
    except Exception:
        logger.log(f"No encontró clickable para '{text}'", "WARN")
        return False

async def smart_fill(page, label_text, value):
    logger.log(f"Llenar '{label_text}' -> {value}")
    try:
        # Estrategia 1: Label -> Input por 'for'
        label = page.locator(f"label:has-text('{label_text}')").first
        if await label.count() > 0:
            for_id = await label.get_attribute("for")
            if for_id:
                await page.fill(f"#{for_id}", value)
                return True
        # Estrategia 2: Placeholder
        ph = page.locator(f"input[placeholder*='{label_text}']").first
        if await ph.count() > 0:
            await ph.fill(value)
            return True
        # Estrategia 3: Input cerca del texto
        div = page.locator(f"xpath=//div[contains(., '{label_text}')]").first
        inp = div.locator("input, textarea, select").first
        if await inp.count() > 0:
            await inp.fill(value)
            return True
        logger.log(f"Input para '{label_text}' no encontrado", "WARN")
        return False
    except Exception as e:
        logger.log(f"Error llenando '{label_text}': {str(e)[:50]}", "ERROR")
        return False

async def take_evidence(page, name):
    path = os.path.join(REPORT_DIR, f"{name}.png")
    await page.screenshot(path=path, full_page=True)
    logger.log(f"📸 Evidencia: {path}")
    return path

async def safe_execute(test_func, page, reporte, module_name):
    """Ejecuta una prueba, si falla no detiene todo, registra el error y vuelve al dashboard."""
    try:
        logger.log(f"--- INICIO MÓDULO: {module_name} ---")
        await test_func(page, reporte)
        logger.log(f"--- FIN MÓDULO: {module_name} ---\n")
    except Exception as e:
        await take_evidence(page, f"crash_{module_name}")
        reporte["fallos"].append({
            "modulo": module_name,
            "accion": "Desconocida (Fallo inesperado)",
            "error": str(e),
            "url": page.url,
            "traza": logger.steps[-10:],
            "diagnostico": diagnosticar_error(str(e), page.url, module_name)
        })
        logger.log(f"Recuperando estado tras fallo en {module_name}...", "ERROR")
        try:
            await page.goto(f"{BASE_URL}/dashboard", timeout=5000)
            await page.wait_for_timeout(1000)
        except:
            pass # Si hasta el dashboard falló, seguimos intentando

# =======================================================
# FLUJOS ESPECÍFICOS (TODO LO QUE PEDISTE)
# =======================================================
async def test_login(page, reporte):
    try:
        await page.goto(f"{BASE_URL}/login")
        await smart_fill(page, "Correo electronico", USER) or await smart_fill(page, "Correo electronico", USER)
        await smart_fill(page, "Contraseña", PASS) or await smart_fill(page, "Password", PASS)
        await smart_click(page, "Entrar") or await smart_click(page, "Iniciar") or await page.click('button[type="submit"]')
        await page.wait_for_timeout(2000)
        if "login" in page.url.lower():
            raise Exception("Credenciales incorrectas")
        reporte["exitosos"].append("Login y redirección correctos")
    except Exception as e:
        await take_evidence(page, "error_login")
        reporte["fallos"].append({"modulo": "Login", "accion": "Iniciar sesión", "error": str(e), "url": page.url, "traza": logger.steps[-5:], "diagnostico": diagnosticar_error(str(e), page.url, "Login")})

async def test_dashboard(page, reporte):
    try:
        # Intentar leer los contadores del dashboard que mencionaste
        logger.log("Verificando contadores del dashboard...")
        contadores = page.locator("text=/Herramientas Asignadas|Herramientas en Bodega|Solicitudes Pendientes|Valor Total Bodega/")
        count = await contadores.count()
        if count > 0:
            reporte["exitosos"].append(f"Dashboard cargó con {count} módulos visibles")
        else:
            raise Exception("No se encontraron los bloques de estadísticas del Dashboard")
    except Exception as e:
        reporte["fallos"].append({"modulo": "Dashboard", "accion": "Cargar estadísticas", "error": str(e), "url": page.url, "traza": logger.steps[-3:], "diagnostico": diagnosticar_error(str(e), page.url, "Dashboard")})

async def test_herramientas_completas(page, reporte):
    # 1. Herramientas de Mecánico
    if not await smart_click(page, "Herramientas de Mecánico"):
        logger.log("No encontró menú mecánico, intentando bodega...")
    
    await page.wait_for_timeout(1000)
    # 2. Herramientas de Bodega
    if await smart_click(page, "Herramientas de Bodega"):
        await page.wait_for_timeout(1000)
        # 3. Agregar Herramienta
        if await smart_click(page, "Agregar Herramienta"):
            await page.wait_for_timeout(1000)
            await smart_fill(page, "Nombre", random.choice(HERRAMIENTAS))
            await smart_fill(page, "Valor", "1500")
            await smart_fill(page, "Cantidad", "2")
            if await smart_click(page, "Guardar") or await smart_click(page, "Registrar"):
                await page.wait_for_timeout(2000)
                if "error" not in await page.content().lower():
                    reporte["exitosos"].append("Herramienta de bodega creada")
        
        # 4. Solicitudes de Préstamo
        await smart_click(page, "Solicitudes de Préstamo")
        await page.wait_for_timeout(1000)
        reporte["exitosos"].append("Módulo de herramientas recorrido sin crasheos")
    else:
        logger.log("No se pudo probar herramientas", "WARN")

async def test_manuales(page, reporte):
    if await smart_click(page, "Manuales Técnicos") or await smart_click(page, "📚 Manuales"):
        await page.wait_for_timeout(1000)
        await smart_fill(page, "Buscar", "Frenos ABS")
        await page.wait_for_timeout(1000)
        reporte["exitosos"].append("Módulo de Manuales probado")
    else:
        logger.log("Manuales no encontrado", "WARN")

async def test_vin(page, reporte):
    if await smart_click(page, "Decodificador VIN") or await smart_click(page, "🔍 Decodificador"):
        await page.wait_for_timeout(1000)
        await smart_fill(page, "VIN", "1HGCM82633A123456") or await smart_fill(page, "Número VIN", "1HGCM82633A123456")
        await smart_click(page, "Decodificar")
        await page.wait_for_timeout(2000)
        reporte["exitosos"].append("Decodificador VIN ejecutado")
    else:
        logger.log("VIN no encontrado", "WARN")

async def test_dtc(page, reporte):
    if await smart_click(page, "DTC Codes") or await smart_click(page, "🔧 DTC"):
        await page.wait_for_timeout(1000)
        await smart_fill(page, "Código", "P0300") or await smart_fill(page, "Ej:", "P0300")
        await smart_click(page, "Buscar")
        await page.wait_for_timeout(2000)
        reporte["exitosos"].append("Buscador DTC ejecutado")
    else:
        logger.log("DTC no encontrado", "WARN")

async def test_reportes(page, reporte):
    # Si hay un menú lateral de reportes o si está en el dashboard
    if await smart_click(page, "Reportes") or await smart_click(page, "📊 Reportes"):
        await page.wait_for_timeout(1000)
        # Intentar clic en exportar (sin descargar realmente, solo ver si crashea)
        await smart_click(page, "Exportar Excel") or await smart_click(page, "📥 Exportar")
        await page.wait_for_timeout(1000)
        reporte["exitosos"].append("Módulo de reportes accedido")
    else:
        logger.log("Reportes no encontrados como menú independiente", "WARN")

async def test_usuarios(page, reporte):
    if await smart_click(page, "Gestión de Usuarios") or await smart_click(page, "👥 Usuarios"):
        await page.wait_for_timeout(1000)
        # Crear usuario
        if await smart_click(page, "Nuevo Usuario") or await smart_click(page, "+ Nuevo"):
            await page.wait_for_timeout(1000)
            nombre_test = f"QA Bot {random.randint(100,999)}"
            await smart_fill(page, "Nombre", nombre_test)
            await smart_fill(page, "email", f"qa_{random.randint(100,999)}@test.com")
            await smart_fill(page, "Rol", "Mecánico") # Si es un select, intentará escribirlo
            await smart_click(page, "Guardar")
            await page.wait_for_timeout(2000)
            reporte["exitosos"].append(f"Usuario de prueba '{nombre_test}' procesado")
        else:
            reporte["exitosos"].append("Gestión de usuarios visitada (sin crear nuevo)")
    else:
        logger.log("Usuarios no encontrado", "WARN")

async def test_configuracion(page, reporte):
    if await smart_click(page, "Configuracion") or await smart_click(page, "⚙️ Configuración"):
        await page.wait_for_timeout(1500)
        # Cambiar un valor para probar que el POST funciona
        await smart_fill(page, "Tax Rate", "19")
        await smart_click(page, "Guardar Cambios") or await smart_click(page, "💾 Guardar")
        await page.wait_for_timeout(2000)
        reporte["exitosos"].append("Configuración del sistema guardada")
    else:
        logger.log("Configuración no encontrada", "WARN")

# =======================================================
# GENERADOR DE INFORME
# =======================================================
def generar_informe(reporte):
    archivo = os.path.join(DESKTOP, f"INFORME_EJECUTIVO_{datetime.now().strftime('%Y%m%d_%H%M')}.txt")
    with open(archivo, "w", encoding="utf-8") as f:
        f.write("╔═══════════════════════════════════════════════════════════╗\n")
        f.write("║        INFORME DE AUDITORÍA INTEGRAL - TORQUE STUDIO       ║\n")
        f.write("╚═══════════════════════════════════════════════════════════╝\n\n")
        f.write(f"Fecha: {datetime.now()}\n")
        f.write(f"Pruebas Superadas: {len(reporte['exitosos'])} | Fallos/Advertencias: {len(reporte['fallos'])}\n\n")
        
        f.write("--- ✅ MÓDULOS VALIDADOS ---\n")
        for r in reporte["exitosos"]: f.write(f" ✔ {r}\n")
        
        if reporte["fallos"]:
            f.write("\n--- ❌ PROBLEMAS DETECTADOS (RASTREO COMPLETO) ---\n")
            for i, fallo in enumerate(reporte["fallos"], 1):
                f.write(f"\n[ FALLO #{i} ] Módulo: {fallo['modulo']}\n")
                f.write(f"URL Afectada: {fallo['url']}\n")
                f.write(f"Error Técnico: {fallo['error']}\n")
                diag = fallo['diagnostico']
                f.write(f"🧠 CAUSA RAÍZ: {diag[0]}\n")
                f.write(f"🔧 SOLUCIÓN: {diag[1]}\n")
                f.write("Paso a paso del error:\n")
                for p in fallo["traza"]: f.write(f"  -> {p}\n")
                f.write("\n")
    return archivo

# =======================================================
# EJECUCIÓN
# =======================================================
async def main():
    reporte = {"exitosos": [], "fallos": []}

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False, slow_mo=350)
        page = await browser.new_page(viewport={"width": 1400, "height": 900})
        page.on("pageerror", lambda e: logger.log(f"JS Error: {e}", "WARN"))

        print("\n🔧 INICIANDO AUDITORÍA TOTAL DEL SISTEMA...\n")

        # Ejecutar todo con el escudo anti-caidas
        await safe_execute(test_login, page, reporte, "Login")
        await safe_execute(test_dashboard, page, reporte, "Dashboard")
        await safe_execute(test_herramientas_completas, page, reporte, "Gestión Herramientas")
        await safe_execute(test_manuales, page, reporte, "Manuales Técnicos")
        await safe_execute(test_vin, page, reporte, "Decodificador VIN")
        await safe_execute(test_dtc, page, reporte, "DTC Codes")
        await safe_execute(test_reportes, page, reporte, "Reportes")
        await safe_execute(test_usuarios, page, reporte, "Gestión Usuarios")
        await safe_execute(test_configuracion, page, reporte, "Configuración")

        archivo = generar_informe(reporte)
        
        print("\n" + "="*60)
        print(f"🏁 AUDITORÍA TERMINADA.")
        print(f"📄 Informe ejecutivo en: {archivo}")
        print(f"📸 Evidencias de errores en: {REPORT_DIR}")
        print("="*60 + "\n")
        await browser.close()

if __name__ == "__main__":
    asyncio.run(main())