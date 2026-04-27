# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: complete-suite\responsive.spec.ts >> 📱 Módulo Responsive >> 6.10 - Modal/Dialog en móvil
- Location: complete-suite\responsive.spec.ts:288:7

# Error details

```
Test timeout of 30000ms exceeded.
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - heading "Torque Studio" [level=2] [ref=e3]
    - link "Dashboard" [ref=e4] [cursor=pointer]:
      - /url: /dashboard
    - link "Clientes" [ref=e5] [cursor=pointer]:
      - /url: /clients
    - link "Vehículos" [ref=e6] [cursor=pointer]:
      - /url: /vehicles
    - link "Órdenes" [ref=e7] [cursor=pointer]:
      - /url: /work-orders
    - link "Servicios" [ref=e8] [cursor=pointer]:
      - /url: /services
    - link "Inventario" [ref=e9] [cursor=pointer]:
      - /url: /parts
    - link "📊 Reportes" [ref=e10] [cursor=pointer]:
      - /url: /reports
    - link "🔧 Herramientas" [ref=e11] [cursor=pointer]:
      - /url: /tools
    - separator [ref=e12]
    - link "📚 Manuales" [ref=e13] [cursor=pointer]:
      - /url: /manuals
    - link "🔍 Decodificador VIN" [ref=e14] [cursor=pointer]:
      - /url: /vin-decoder
    - link "🔧 DTC Codes" [ref=e15] [cursor=pointer]:
      - /url: /dtc
  - generic [ref=e16]:
    - heading "Nuevo Cliente" [level=1] [ref=e17]
    - generic [ref=e18]:
      - generic:
        - generic:
          - generic: Nombre Completo
          - textbox [ref=e19]
        - generic:
          - generic: Teléfono
          - textbox [ref=e20]
        - generic:
          - generic: Email
          - textbox [ref=e21]
        - generic:
          - generic: Dirección
          - textbox [ref=e22]
        - generic:
          - link "Cancelar" [ref=e23] [cursor=pointer]:
            - /url: /clients
          - button "Guardar Cliente" [ref=e24] [cursor=pointer]
```