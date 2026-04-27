# Workshop Intelligent Suite - Roadmap

## Fase 1 (implementada)
- Base de datos extendida para control avanzado de OT.
- Tarifas por hora configurables.
- Control de tiempo por mecánico (iniciar, pausar, reanudar, finalizar).
- Métricas cobrables/no cobrables y costos de labor.
- Checklist de calidad final por OT.
- Generación de documento de cobro (boleta/factura/cotización/presupuesto).
- Panel inicial `workshop-ops`.

## Fase 2
- Integrar datos faltantes en formularios de cliente/vehículo/OT:
  - RUT, WhatsApp y consentimiento legal.
  - VIN, color, kilometraje, prioridad y diagnóstico.
- Historial completo del vehículo (timeline consolidado por patente).
- Control de insumos consumibles por OT con descuento automático y alertas de compra.

## Fase 3
- Automatización WhatsApp:
  - Cola de recordatorios automáticos.
  - Plantillas por tipo de mantenimiento.
  - Envío manual desde OT/cliente.
- Firma digital de cliente y jefe de taller.
- Adjuntos de fotos antes/después y documentos por OT.

## Fase 4
- Productividad avanzada por mecánico:
  - Eficiencia, tiempos promedio, horas vendidas/perdidas, ranking mensual.
- Dashboard gerencial completo:
  - Ventas diarias/mensuales, utilidad, top mecánicos, top clientes, críticos.
- Caja diaria y reportes tributarios Chile (SII).

## Fase 5
- Multi-sucursal.
- Agenda de citas online.
- QR para OT.
- Backups automáticos programados.

## Integraciones externas recomendadas
- WhatsApp API (Meta Cloud API o proveedor oficial).
- PDF engine para documentos (dompdf o mPDF).
- Cron jobs para recordatorios y tareas automáticas.
