# Evidencia técnica del comparativo

La carpeta historico-integrante3 contiene los 40 archivos de la entrega ZIP, conservados byte por byte. MANIFIESTO_SHA256.csv enumera los otros 39 archivos. Los datos se registraron el 10 de noviembre de 2026. Los archivos de entrada originales permanecen en su ubicación de entrega.

| Evidencia histórica | Laravel | NestJS | ASP.NET Core |
|---|---:|---:|---:|
| Coincidencias HTTP con contrato de 9 comprobaciones | 7 | 8 | 8 |
| Solicitudes HTTP 200 en las tres corridas principales | 902 | 902 | 901 |
| Media de los p95 por corrida, ms | 75.59 | 4.88 | 4.53 |
| Working Set medio en reposo, MB | 18.26 | 12.96 | 6.68 |
| Working Set medio bajo carga, MB | 46.99 | 38.77 | 64.39 |
| Líneas no vacías en archivos seleccionados | 542 | 257 | 396 |

Los JSON originales permiten recuperar el rps efectivo de cada corrida mediante metrics.http_reqs.rate. Se usó una carga fija de 10 solicitudes/s durante 30 s. MySQL se utilizó en Laravel y NestJS, y SQLite en ASP.NET Core. Los resultados describen esos entornos y no establecen capacidad máxima ni un efecto causal del framework. Los conteos seleccionan archivos y pueden incluir comentarios. La memoria es Working Set del proceso servidor y excluye servicios de base de datos.

La adenda técnica identifica las diferencias con el contrato congelado y los requisitos de la siguiente medición. Los ocho criterios requieren completar puesta en marcha comparable, alcance uniforme de código y esfuerzo, pruebas equivalentes y tamaños desplegables. Las nuevas corridas deben almacenarse por separado con el commit, versiones, parámetros y verificación de la base inicial.
