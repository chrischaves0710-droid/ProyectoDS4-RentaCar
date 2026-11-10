# Archivos históricos no incorporados a los resultados válidos

El ZIP original contenía resultados de pruebas exploratorias, intentos con errores de autenticación y mediciones preliminares. Se dejaron fuera de los promedios y del directorio final de evidencias válidas por criterios de comparabilidad y calidad:

- `evidencias/rendimiento/aspnet-ram-carga-k6-error.json`: 300 solicitudes y tasa de errores `metrics.http_req_failed.value = 1` (100 %). No es una medición válida de carga HTTP 200.
- `evidencias/memoria/aspnet-ram-carga-error.csv`: toma de RAM asociada a esa corrida con errores; se sustituye por la toma final válida.
- `evidencias/memoria/ram-reposo-final.csv`: archivo preliminar de 90 filas con procesos/PID diferentes; para comparaciones se conservan los tres CSV específicos de reposo con 30 muestras válidas.
- Otros CSV exploratorios en la raíz y en `evidencias/memoria/`, y conteos `conteo-resumen.csv` o `preliminar`: solo se conservó la última medición por categoría.

No se modificaron ni eliminaron esos archivos del ZIP original que proporcionó el usuario. Este paquete únicamente selecciona los archivos finales necesarios.
