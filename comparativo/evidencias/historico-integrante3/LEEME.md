# Entrega definitiva del Integrante 3

Este paquete **ya incluye todas las evidencias finales**, verificadas y copiadas desde `pruebas-comparativo.zip`. No es necesario ejecutar PowerShell, repetir k6 ni volver a recopilar archivos para entregarlo.

## Contenido

- `INFORME_FINAL_Integrante3.md`: informe final, resultados, limitaciones y recomendaciones al grupo.
- `resumen/`: siete CSV consolidados y recalculados directamente a partir de los datos originales.
- `evidencias/rendimiento/`: 12 JSON válidos de k6 (nueve pruebas principales y tres junto con RAM).
- `evidencias/memoria/`: seis CSV originales, cada uno de 30 muestras.
- `evidencias/funcionales/`: seis CSV originales que suman 27 comprobaciones.
- `evidencias/codigo/`: dos CSV del conteo final, resumen y detalle (32 archivos seleccionados).
- `scripts/`: script k6, script de conteo original y `validar-evidencias-corregido.ps1` de PowerShell.
- `MANIFIESTO_SHA256.csv`: hash SHA256 de cada archivo incluido (excepto el propio manifiesto).
- `EXCLUSIONES_Y_CONTROL_DE_CALIDAD.md`: por qué no se incluyeron corridas antiguas.

## Verificación de k6 corregida

El error en el empaquetador anterior se debió a una ruta JSON equivocada: intentaba leer `metrics.http_req_failed.values.rate`, pero el JSON real usa `metrics.http_req_failed.value`. Este paquete ha sido comprobado en su totalidad. El nuevo script `scripts/validar-evidencias-corregido.ps1` realiza esa comprobación sin modificar evidencia alguna.

## Pendiente para entrega de equipo

- Comunicar las cuatro diferencias HTTP frente al contrato (tres respuestas DELETE 204 versus 200 y login incorrecto Laravel 422 versus 401).
- Asegurar que los cambios locales de NestJS están presentes en el repositorio/ZIP definitivo del grupo. **Este paquete de integrante 3 no contiene el proyecto fuente completo.**
