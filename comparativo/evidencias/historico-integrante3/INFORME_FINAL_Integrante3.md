# INFORME FINAL — INTEGRANTE 3

**Proyecto:** Comparación de API Vehículos de RentaCar: Laravel, NestJS y ASP.NET Core  
**Fecha de las evidencias:** 10 de noviembre de 2026  
**Procedencia:** archivo `pruebas-comparativo.zip` aportado por el integrante, verificado contra los JSON originales de k6 y los CSV obtenidos con PowerShell. No se repitieron ni simularon pruebas para elaborar este informe.

## 1. Objetivo y alcance

Documentar funcionalidad HTTP, latencias bajo carga fija, memoria residente (*Working Set*) y un conteo de líneas para los archivos seleccionados de las tres implementaciones de la API Vehículos. No permite inferir por sí solo rendimiento máximo, seguridad integral, consumo total de recursos ni calidad intrínseca de los frameworks.

## 2. Pruebas funcionales

Se registraron nueve comprobaciones por framework, **27 pruebas** en total. Para las operaciones de modificación se usaron vehículos temporales, posteriormente eliminados, sin efectuar esas operaciones sobre los tres vehículos semilla.

| Framework | Ejecutadas | Códigos coincidentes con contrato | Diferencias |
|---|---:|---:|---:|
| Laravel | 9 | 7 | 2 |
| NestJS | 9 | 8 | 1 |
| ASP.NET Core | 9 | 8 | 1 |
| **TOTAL** | **27** | **23** | **4** |

Las diferencias observadas son:

1. **Laravel, login incorrecto:** 422 observado frente a 401 esperado.
2. **Laravel, DELETE exitoso:** 204 observado frente a 200 esperado.
3. **NestJS, DELETE exitoso:** 204 observado frente a 200 esperado.
4. **ASP.NET Core, DELETE exitoso:** 204 observado frente a 200 esperado.

La eliminación funcionó en las tres implementaciones: el GET posterior del ID temporal devolvió 404. Las divergencias son frente al **contrato comparativo acordado**, no fallos de eliminación HTTP. Los inicios de sesión correctos y la obtención de tokens se comprobaron para acceder a los endpoints protegidos, pero no se contabilizan como filas adicionales dentro de estas 27 pruebas.

Los datos por prueba están en `resumen/03_resultados_funcionales_27_pruebas.csv`, respaldados por seis CSV originales en `evidencias/funcionales/`.

## 3. Rendimiento con k6

**Configuración:** `constant-arrival-rate`, 10 solicitudes por segundo, 30 segundos por prueba, cinco VUs preasignados (máximo 20), solicitud `GET /api/vehiculos` paginada. Se usó token Bearer donde era necesario. El script exacto utilizado se conserva en `scripts/k6-comparativo.js`.

Cada framework tiene tres ejecuciones principales válidas, nueve en total. Sus 2.705 solicitudes cumplen la comprobación de respuesta HTTP 200 y la métrica de fallos reporta tasa 0.

| Framework | Media de p95 (3 ejecuciones) | Media de latencias medias | Solicitudes HTTP 200 | Tasa de fallos |
|---|---:|---:|---:|---:|
| Laravel | 75.59 ms | 53.50 ms | 902/902 | 0 % |
| NestJS | 4.88 ms | 3.70 ms | 902/902 | 0 % |
| ASP.NET Core | 4.53 ms | 3.91 ms | 901/901 | 0 % |

**Cálculo:** el valor mostrado es la media aritmética de los p95 de las tres ejecuciones, **no** un p95 conjunto calculado con todas las solicitudes. Se calcularon los promedios directamente desde los JSON sin truncar los valores de cada ejecución, por lo que algunos centésimos varían respecto a los resúmenes preliminares redondeados. La tasa solicitada es fija; no se determinó el máximo rendimiento sostenible.

## 4. Memoria RAM en reposo y bajo carga

Se midió `WorkingSet64` del proceso servidor bajo Windows 11, en 30 muestras de aproximadamente un segundo por condición y framework. Los CSV originales incluyen marca de tiempo, proceso y MB. Bajo carga, k6 realizó una prueba adicional de 30 segundos con 0 % de errores.

| Framework | RAM media reposo | RAM máxima reposo | RAM media con carga | RAM máxima con carga |
|---|---:|---:|---:|---:|
| Laravel (`php -S`) | 18.26 MB | 18.26 MB | 46.99 MB | 47.41 MB |
| NestJS (`node dist/main`) | 12.96 MB | 12.96 MB | 38.77 MB | 40.93 MB |
| ASP.NET Core (`Api.exe`) | 6.68 MB | 6.73 MB | 64.39 MB | 69.52 MB |

**k6 concurrente con muestreo:** Laravel 300/300 HTTP 200 (p95 253.24 ms); NestJS 301/301 HTTP 200 (p95 8.08 ms); ASP.NET Core 301/301 HTTP 200 (p95 16.18 ms). Estos resultados complementarios no se mezclaron con las nueve ejecuciones principales.

**Limitaciones:** el *Working Set* no representa necesariamente la memoria privada, el heap, la suma de procesos de soporte ni la memoria del motor de base de datos. No se incluyeron procesos padre, servicios MySQL/SQLite u otros procesos del sistema. Windows puede recortar páginas residentes cuando el proceso está inactivo; ello influye especialmente en el reposo de ASP.NET Core. La precisión temporal entre muestras y solicitudes es limitada.

## 5. Conteo de líneas de código

| Framework | Archivos seleccionados | Líneas físicas | Líneas no vacías |
|---|---:|---:|---:|
| Laravel | 11 | 690 | 542 |
| NestJS | 11 | 298 | 257 |
| ASP.NET Core | 10 | 443 | 396 |
| **TOTAL** | **32** | **1431** | **1195** |

Se verificó que los totales del CSV de resumen coinciden con las sumas del detalle por archivo. Se incluyen `scripts/contar-codigo-final.ps1` y los dos CSV originales. El conteo selecciona archivos explícitos, no todo el repositorio ni sus dependencias; las líneas no vacías pueden incluir comentarios. NestJS incluye los ajustes de código reflejados en la última medición local.

## 6. Condiciones de comparación

- Laravel y NestJS usaron MySQL; ASP.NET Core usó SQLite. La configuración de base de datos **no fue homogénea**.
- Los entornos, tratamiento de autenticación, código y tamaño de respuestas tienen diferencias; no es un experimento aislado sobre rendimiento de frameworks.
- Se utilizó una carga fija y acotada, no una prueba de capacidad máxima ni una prueba de saturación.
- Las correcciones de NestJS hechas en el equipo local durante las pruebas deben incorporarse al repositorio final del equipo; este ZIP contiene evidencias y scripts, no el proyecto fuente.
- El equipo debe corregir las cuatro diferencias de código HTTP o dejar documentado un acuerdo de modificación del contrato. Si cambia el código, convendría volver a ejecutar las pruebas relevantes.

## 7. Comprobación de integridad de los registros k6

Los JSON generados con `k6 --summary-export` de esta instalación contienen `metrics.http_req_failed.value` (igual a 0 para las 12 pruebas válidas) y `metrics.http_reqs.count`. El script anterior esperaba erróneamente `metrics.http_req_failed.values.rate`, campo ausente en esos archivos. La validez se comprobó también con `metrics.checks.passes == metrics.http_reqs.count` y `metrics.checks.fails == 0`. Se incluye un script corregido de validación para PowerShell.

Se excluyeron deliberadamente las pruebas históricas con errores o previas a la configuración definitiva. En particular, `aspnet-ram-carga-k6-error.json` presenta una tasa real de errores igual a 1 (100 %) y no se incorpora como ejecución válida. Se preservó intacto el archivo fuente recibido; solo se seleccionaron para la entrega los registros finales válidos.

## 8. Conclusión

Bajo las condiciones documentadas, las tres API respondieron correctamente las **2.705 solicitudes** de nueve pruebas principales. ASP.NET Core y NestJS obtuvieron p95 promedio por ejecución similares y menores que Laravel en esta prueba específica; sin embargo, las diferencias de base de datos, implementación y ambiente impiden atribuir esa diferencia al framework de manera causal. El análisis funcional dejó **23 coincidencias y cuatro discrepancias frente al contrato** sobre 27 comprobaciones; las operaciones CRUD medidas se ejecutaron correctamente. Las conclusiones quedan respaldadas por las evidencias originales adjuntas.
