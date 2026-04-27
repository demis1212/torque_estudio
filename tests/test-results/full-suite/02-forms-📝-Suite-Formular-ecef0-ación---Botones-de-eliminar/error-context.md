# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 02-forms.spec.js >> 📝 Suite Formularios - CRUD Completo >> 🗑️ Eliminación - Botones de eliminar
- Location: 02-forms.spec.js:268:3

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/dashboard", waiting until "networkidle"

```

# Test source

```ts
  1   | /**
  2   |  * @fileoverview Tests de Formularios - Testing exhaustivo de CRUD
  3   |  * @description Crea, edita y elimina registros de prueba
  4   |  */
  5   | 
  6   | const { test, expect } = require('@playwright/test');
  7   | const { captureScreenshot, TEST_DATA, fillForm } = require('./helpers/utils');
  8   | 
  9   | test.describe('📝 Suite Formularios - CRUD Completo', () => {
  10  |   
  11  |   test.beforeEach(async ({ page }) => {
> 12  |     await page.goto('/dashboard', { waitUntil: 'networkidle' });
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  13  |     await expect(page).toHaveURL(/dashboard/, { timeout: 15000 });
  14  |   });
  15  | 
  16  |   test('👤 Formulario Clientes - Crear, verificar, eliminar', async ({ page }) => {
  17  |     console.log('\n👤 Probando CRUD de Clientes...');
  18  |     
  19  |     // 1. Ir a clientes
  20  |     await page.getByRole('link', { name: /Clientes$/i }).click();
  21  |     await page.waitForURL('**/clients', { timeout: 15000 });
  22  |     await captureScreenshot(page, '10-clientes-lista');
  23  |     
  24  |     // 2. Click en nuevo cliente
  25  |     const btnNuevo = page.getByRole('link', { name: /Nuevo Cliente/i });
  26  |     if (!(await btnNuevo.isVisible().catch(() => false))) {
  27  |       console.log('⚠️ Botón "Nuevo Cliente" no encontrado');
  28  |       return;
  29  |     }
  30  |     
  31  |     await btnNuevo.click();
  32  |     
  33  |     // Esperar navegación al formulario de creación
  34  |     await page.waitForURL('**/clients/create', { timeout: 15000 });
  35  |     await page.waitForLoadState('networkidle');
  36  |     await page.waitForTimeout(1000);
  37  |     await captureScreenshot(page, '11-cliente-form-vacio');
  38  |     
  39  |     // 3. Verificar campos del formulario (con verificación de que el formulario existe)
  40  |     await expect(page.locator('form[action*="clients/create"]').first(), 'Formulario de cliente debe existir')
  41  |       .toBeVisible({ timeout: 10000 });
  42  |     
  43  |     const camposRequeridos = [
  44  |       { selector: 'input[name="name"]', nombre: 'Nombre' },
  45  |       { selector: 'input[name="email"]', nombre: 'Email' },
  46  |       { selector: 'input[name="phone"]', nombre: 'Teléfono' },
  47  |       { selector: 'input[name="rut"]', nombre: 'RUT' }
  48  |     ];
  49  |     
  50  |     for (const campo of camposRequeridos) {
  51  |       const input = page.locator(campo.selector).first();
  52  |       // Verificar si existe antes de hacer expect
  53  |       const existe = await input.isVisible().catch(() => false);
  54  |       if (existe) {
  55  |         await expect(input, `Campo ${campo.nombre} debe estar visible`).toBeVisible();
  56  |         console.log(`✅ Campo ${campo.nombre} encontrado`);
  57  |       } else {
  58  |         console.log(`⚠️ Campo ${campo.nombre} no encontrado, saltando...`);
  59  |       }
  60  |     }
  61  |     
  62  |     // 4. Llenar formulario con datos de prueba
  63  |     await fillForm(page, {
  64  |       'input[name="name"]': TEST_DATA.client.name,
  65  |       'input[name="email"]': TEST_DATA.client.email,
  66  |       'input[name="phone"]': TEST_DATA.client.phone,
  67  |       'input[name="rut"]': TEST_DATA.client.rut,
  68  |       'input[name="address"]': TEST_DATA.client.address
  69  |     });
  70  |     
  71  |     await captureScreenshot(page, '12-cliente-form-lleno');
  72  |     
  73  |     // 5. Guardar (simulado - no enviamos para no crear basura)
  74  |     console.log('✅ Formulario de cliente verificado correctamente');
  75  |   });
  76  | 
  77  |   test('🚗 Formulario Vehículos - Crear con validaciones', async ({ page }) => {
  78  |     console.log('\n🚗 Probando formulario de Vehículos...');
  79  |     
  80  |     await page.getByRole('link', { name: /Vehículos$/i }).click();
  81  |     await page.waitForURL('**/vehicles', { timeout: 15000 });
  82  |     
  83  |     const btnNuevo = page.getByRole('link', { name: /Nuevo Vehículo/i });
  84  |     if (!(await btnNuevo.isVisible().catch(() => false))) {
  85  |       console.log('⚠️ Botón "Nuevo Vehículo" no encontrado');
  86  |       return;
  87  |     }
  88  |     
  89  |     await btnNuevo.click();
  90  |     await page.waitForTimeout(2000);
  91  |     await captureScreenshot(page, '20-vehiculo-form');
  92  |     
  93  |     // Verificar campos
  94  |     const campos = ['brand', 'model', 'year', 'plate', 'vin', 'mileage'];
  95  |     for (const campo of campos) {
  96  |       const input = page.locator(`[name="${campo}"]`).first();
  97  |       if (await input.isVisible().catch(() => false)) {
  98  |         console.log(`✅ Campo "${campo}" encontrado`);
  99  |       }
  100 |     }
  101 |     
  102 |     // Llenar datos
  103 |     await fillForm(page, {
  104 |       'input[name="brand"]': TEST_DATA.vehicle.brand,
  105 |       'input[name="model"]': TEST_DATA.vehicle.model,
  106 |       'input[name="year"]': TEST_DATA.vehicle.year,
  107 |       'input[name="plate"]': TEST_DATA.vehicle.plate,
  108 |       'input[name="vin"]': TEST_DATA.vehicle.vin,
  109 |       'input[name="mileage"]': TEST_DATA.vehicle.mileage
  110 |     });
  111 |     
  112 |     await captureScreenshot(page, '21-vehiculo-form-lleno');
```