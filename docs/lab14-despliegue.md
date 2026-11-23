
# Laboratorio 14 — Configuración, seguridad y despliegue de RentaCar

## 1. Introducción

El objetivo de este laboratorio es preparar la aplicación RentaCar para su funcionamiento en un entorno de producción, aplicando medidas de seguridad, separación de configuraciones, empaquetado, automatización del despliegue y publicación móvil.

El trabajo se distribuyó entre cinco integrantes, con responsabilidades específicas para cada etapa.

## 2. Arquitectura general

La solución está compuesta por:

- Backend desarrollado con Laravel 13.
- Frontend web compilado mediante Vite.
- Base de datos MySQL alojada en Railway.
- Contenedor Docker basado en FrankenPHP y PHP 8.4.
- Automatización CI/CD mediante GitHub Actions.
- Aplicación móvil desarrollada con Ionic Vue y Capacitor.
- Comunicación con la API mediante HTTPS.

### Direcciones de producción

Aplicación web:
https://rentacar-web-production.up.railway.app

Repositorio backend:
https://github.com/chrischaves0710-droid/ProyectoDS4-RentaCar

Repositorio móvil:
https://github.com/alexlascarez0411/rentacar-movil

## 3. Separación de entornos

Se establecieron configuraciones independientes para desarrollo y producción.

En desarrollo se utilizan variables locales, conexión de pruebas y direcciones de localhost.

En producción se utilizan variables administradas por Railway, una dirección pública HTTPS y una conexión persistente a MySQL.

La aplicación móvil utiliza archivos de ejemplo independientes:

- .env.development.example
- .env.production.example

Los archivos con valores reales no deben almacenarse en los repositorios.

## 4. Configuración de variables de entorno

Laravel utiliza variables de entorno para administrar los parámetros de funcionamiento.

Entre las principales se encuentran:

| Variable | Función |
|---|---|
| APP_ENV | Identifica el entorno de ejecución. |
| APP_DEBUG | Controla la exposición de errores detallados. |
| APP_KEY | Clave de cifrado de Laravel. |
| APP_URL | Dirección principal de la aplicación. |
| DB_CONNECTION | Motor de base de datos. |
| DB_HOST | Servidor de base de datos. |
| DB_DATABASE | Base de datos utilizada. |
| DB_USERNAME | Usuario de conexión. |
| DB_PASSWORD | Contraseña de conexión. |
| CORS_ALLOWED_ORIGINS | Orígenes autorizados. |
| SESSION_SECURE_COOKIE | Protección de cookies mediante HTTPS. |

El archivo .env.example funciona como plantilla y no contiene credenciales reales.

Las variables de producción se administran desde Railway.

## 5. Protección de credenciales

Se reforzó el archivo .gitignore para impedir que se publiquen archivos sensibles.

Se contemplan:

- Archivos .env.
- Contraseñas y credenciales.
- Certificados y llaves privadas.
- Keystores de Android.
- Archivos de configuración locales.
- Dependencias y artefactos generados.

También se debe comprobar el historial del repositorio para detectar posibles credenciales publicadas anteriormente.

## 6. Configuración CORS

Se modificó config/cors.php para administrar los orígenes autorizados mediante CORS_ALLOWED_ORIGINS.

Se contemplan los orígenes necesarios para desarrollo local y para la aplicación móvil.

La configuración evita autorizar indiscriminadamente cualquier dominio.

Los orígenes de producción se administran mediante las variables configuradas en Railway.

## 7. Seguridad del entorno de producción

Se establecieron las siguientes medidas:

- APP_DEBUG desactivado en producción.
- APP_KEY obligatoria y persistente.
- Uso de HTTPS.
- Validación de la URL de producción.
- Cookies de sesión seguras.
- Validación de configuraciones obligatorias.
- Protección de las rutas privadas.
- Comprobación del endpoint /up.

Se incorporaron comprobaciones adicionales en AppServiceProvider.php.

También se utiliza docker/validate-runtime.php para validar la configuración necesaria antes del inicio del contenedor.

## 8. Empaquetado Docker

El Integrante 2 preparó el empaquetado de Laravel mediante un Dockerfile multietapa.

La solución utiliza FrankenPHP, PHP sobre Alpine, Composer y Node para compilar el frontend.

Se configuró un usuario sin privilegios para ejecutar la aplicación y se excluyeron archivos innecesarios mediante .dockerignore.

La documentación técnica se encuentra en:

docs/lab14-empaquetado.md

## 9. Despliegue en Railway

El Integrante 3 realizó el despliegue de RentaCar en Railway.

Se configuraron:

- Aplicación Laravel.
- Base de datos MySQL.
- Almacenamiento persistente.
- Variables de producción.
- HTTPS.
- Migraciones y datos iniciales.

La aplicación quedó publicada en:

https://rentacar-web-production.up.railway.app

Se reportó el funcionamiento del inicio y cierre de sesión, junto con las pruebas de seguridad.

## 10. Automatización CI/CD

El Integrante 4 configuró GitHub Actions para automatizar las pruebas y el despliegue.

El proceso incluye:

- Pruebas de Laravel.
- Pruebas de JavaScript.
- Compilación de Vite.
- Integración con Railway.
- Uso de secretos de GitHub.
- Despliegue condicionado al resultado satisfactorio de las pruebas.

Resultados reportados por el integrante:

- 134 pruebas de Laravel aprobadas.
- 7 pruebas de JavaScript aprobadas.
- Compilación de Vite exitosa.
- Despliegue automático ejecutado correctamente.

Las evidencias se encuentran en:

docs/evidencias/lab14-ci-cd/

## 11. Publicación móvil

El Integrante 5 preparó la aplicación móvil mediante Ionic Vue y Capacitor.

Se realizaron:

- Separación de entornos.
- Configuración de la API pública HTTPS.
- Protección de variables de entorno.
- Protección de keystores.
- Desactivación de HTTP sin cifrar en producción.
- Compilación de producción.
- Sincronización con Capacitor.
- Firma del APK.
- Instalación y pruebas en Android.

Se comprobó el inicio de sesión, el registro de vehículos, la consulta de detalles y la persistencia de los datos.

La documentación específica se encuentra en:

docs/Lab-14/publicacion-movil.md

## 12. Evidencias del Integrante 1

Se incorporarán las siguientes evidencias:

1. Archivo .env.example.
2. Protección mediante .gitignore.
3. Configuración CORS.
4. Validaciones de AppServiceProvider.
5. Variables de producción configuradas.
6. APP_DEBUG desactivado.
7. Certificado HTTPS.
8. Funcionamiento del endpoint /up.
9. Verificación de credenciales y archivos ignorados.
10. Pruebas de funcionamiento de las configuraciones.

## 13. Conclusiones

La implementación del Laboratorio 14 permitió preparar RentaCar para su funcionamiento en producción, integrando medidas de seguridad, administración de configuraciones, empaquetado Docker, despliegue en Railway, automatización CI/CD y publicación móvil.

La separación de entornos permite administrar independientemente las configuraciones locales y las de producción.

La protección de credenciales, el uso de HTTPS, la validación de parámetros obligatorios y la automatización de pruebas contribuyen a reducir errores y riesgos durante las actualizaciones de la aplicación.


