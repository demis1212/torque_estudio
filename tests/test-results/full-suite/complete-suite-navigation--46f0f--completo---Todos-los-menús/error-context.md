# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: complete-suite\navigation.spec.ts >> 🧭 Módulo Navegación >> 2.1 - Sidebar completo - Todos los menús
- Location: complete-suite\navigation.spec.ts:8:7

# Error details

```
Test timeout of 30000ms exceeded.
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - heading " Torque Studio" [level=2] [ref=e3]:
      - generic [ref=e4]: 
      - text: Torque Studio
    - link " Dashboard" [ref=e5] [cursor=pointer]:
      - /url: /dashboard
      - generic [ref=e6]: 
      - text: Dashboard
    - link " Usuarios" [ref=e7] [cursor=pointer]:
      - /url: /users
      - generic [ref=e8]: 
      - text: Usuarios
    - link " Clientes" [ref=e9] [cursor=pointer]:
      - /url: /clients
      - generic [ref=e10]: 
      - text: Clientes
    - link " Vehículos" [ref=e11] [cursor=pointer]:
      - /url: /vehicles
      - generic [ref=e12]: 
      - text: Vehículos
    - link " Órdenes" [ref=e13] [cursor=pointer]:
      - /url: /work-orders
      - generic [ref=e14]: 
      - text: Órdenes
    - link " Servicios" [ref=e15] [cursor=pointer]:
      - /url: /services
      - generic [ref=e16]: 
      - text: Servicios
    - link " Operación Inteligente" [ref=e17] [cursor=pointer]:
      - /url: /workshop-ops
      - generic [ref=e18]: 
      - text: Operación Inteligente
    - link " Inventario" [ref=e19] [cursor=pointer]:
      - /url: /parts
      - generic [ref=e20]: 
      - text: Inventario
    - link " Reportes" [ref=e21] [cursor=pointer]:
      - /url: /reports
      - generic [ref=e22]: 
      - text: Reportes
    - link " Herramientas" [ref=e23] [cursor=pointer]:
      - /url: /tools
      - generic [ref=e24]: 
      - text: Herramientas
    - separator [ref=e25]
    - link " Manuales" [ref=e26] [cursor=pointer]:
      - /url: /manuals
      - generic [ref=e27]: 
      - text: Manuales
    - link " Decodificador VIN" [ref=e28] [cursor=pointer]:
      - /url: /vin-decoder
      - generic [ref=e29]: 
      - text: Decodificador VIN
    - link " DTC Codes" [ref=e30] [cursor=pointer]:
      - /url: /dtc
      - generic [ref=e31]: 
      - text: DTC Codes
  - generic [ref=e32]:
    - generic [ref=e33]:
      - heading "Gestión de Usuarios" [level=1] [ref=e34]
      - link "+ Nuevo Usuario" [ref=e35] [cursor=pointer]:
        - /url: /users/create
    - table [ref=e37]:
      - rowgroup [ref=e38]:
        - row "Nombre Email Rol Valor Hora Creado Acciones" [ref=e39]:
          - columnheader "Nombre" [ref=e40]
          - columnheader "Email" [ref=e41]
          - columnheader "Rol" [ref=e42]
          - columnheader "Valor Hora" [ref=e43]
          - columnheader "Creado" [ref=e44]
          - columnheader "Acciones" [ref=e45]
      - rowgroup [ref=e46]:
        - row "demis kurt meneses maira demismeneses7@gmail.com Mec??nico $20.000 26/04/2026 Editar Eliminar" [ref=e47]:
          - cell "demis kurt meneses maira" [ref=e48]
          - cell "demismeneses7@gmail.com" [ref=e49]
          - cell "Mec??nico" [ref=e50]
          - cell "$20.000" [ref=e51]
          - cell "26/04/2026" [ref=e52]
          - cell "Editar Eliminar" [ref=e53]:
            - link "Editar" [ref=e54] [cursor=pointer]:
              - /url: /users/edit/8
            - button "Eliminar" [ref=e56] [cursor=pointer]
        - row "Juan Mecánico juan@torque.com Mec??nico $25.000 26/04/2026 Editar Eliminar" [ref=e57]:
          - cell "Juan Mecánico" [ref=e58]
          - cell "juan@torque.com" [ref=e59]
          - cell "Mec??nico" [ref=e60]
          - cell "$25.000" [ref=e61]
          - cell "26/04/2026" [ref=e62]
          - cell "Editar Eliminar" [ref=e63]:
            - link "Editar" [ref=e64] [cursor=pointer]:
              - /url: /users/edit/6
            - button "Eliminar" [ref=e66] [cursor=pointer]
        - row "María Recepción maria@torque.com Recepcionista $0 26/04/2026 Editar Eliminar" [ref=e67]:
          - cell "María Recepción" [ref=e68]
          - cell "maria@torque.com" [ref=e69]
          - cell "Recepcionista" [ref=e70]
          - cell "$0" [ref=e71]
          - cell "26/04/2026" [ref=e72]
          - cell "Editar Eliminar" [ref=e73]:
            - link "Editar" [ref=e74] [cursor=pointer]:
              - /url: /users/edit/7
            - button "Eliminar" [ref=e76] [cursor=pointer]
        - row "Administrador admin@torque.com Admin $0 25/04/2026 Editar Eliminar" [ref=e77]:
          - cell "Administrador" [ref=e78]
          - cell "admin@torque.com" [ref=e79]
          - cell "Admin" [ref=e80]
          - cell "$0" [ref=e81]
          - cell "25/04/2026" [ref=e82]
          - cell "Editar Eliminar" [ref=e83]:
            - link "Editar" [ref=e84] [cursor=pointer]:
              - /url: /users/edit/1
            - button "Eliminar" [ref=e86] [cursor=pointer]
```