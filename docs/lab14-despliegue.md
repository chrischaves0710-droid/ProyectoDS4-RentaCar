# Laboratorio 14 — Configuración, Seguridad y Despliegue de RentaCar

## 1. Introducción

El objetivo de este laboratorio fue preparar el ecosistema de **RentaCar** (aplicación web, API REST y aplicación móvil) para su operación en un entorno de producción real[cite: 1]. Se aplicaron controles de seguridad, separación estricta de entornos, empaquetado optimizado mediante contenedores, despliegue continuo en la nube y firma criptográfica para distribución móvil[cite: 1].

---

## 2. Arquitectura general del sistema

La solución en producción está integrada por los siguientes componentes[cite: 1]:

- **Backend y API REST:** Desarrollados en Laravel 13 (PHP 8.4) con autenticación basada en sesiones web y tokens Laravel Sanctum (`expiration: 120` minutos), además de control de acceso basado en roles y permisos mediante Spatie Permission[cite: 1].
- **Frontend Web:** Vistas Blade y módulos JavaScript compilados y optimizados con Vite[cite: 1].
- **Servidor de Aplicaciones y Contenedor:** Imagen Docker multietapa basada en FrankenPHP sobre Alpine Linux, ejecutada bajo un usuario sin privilegios[cite: 1].
- **Base de Datos:** Instancia MySQL persistente alojada en Railway (con 14 migraciones ejecutadas y datos iniciales cargados)[cite: 1].
- **Integración y Despliegue Continuo (CI/CD):** Flujo automatizado en GitHub Actions (`.github/workflows/lab14-ci-cd.yml`) conectado a Railway[cite: 1].
- **Aplicación Móvil:** Cliente híbrido desarrollado en Ionic Vue + Capacitor para Android, compilado con firma de producción (`release`) y comunicación exclusiva por HTTPS[cite: 1].

### Enlaces y repositorios del proyecto

| Recurso | Ubicación |
|---|---|
| **Aplicación Web y API (Producción)** | https://rentacar-web-production.up.railway.app[cite: 1] |
| **Healthcheck del Servicio** | https://rentacar-web-production.up.railway.app/up[cite: 1] |
| **Repositorio Backend / Web / CI-CD** | https://github.com/chrischaves0710-droid/ProyectoDS4-RentaCar[cite: 1] |
| **Repositorio Aplicación Móvil** | https://github.com/alexlascarez0411/rentacar-movil[cite: 1] |

---

## 3. Configuración y seguridad

### 3.1 Separación de entornos (Desarrollo vs. Producción)

Se definieron perfiles independientes para evitar que configuraciones de depuración o bases de datos locales afecten el entorno productivo[cite: 1]:

- **Entorno de desarrollo (`local`):** Utiliza `.env` local (no rastreado en Git), `APP_DEBUG=true`, conexión `sqlite` (`database/database.sqlite`), cookies sin requerimiento de HTTPS (`SESSION_SECURE_COOKIE=false`) y orígenes locales (`http://localhost:8000`, `http://localhost:8100`)[cite: 1].
- **Entorno de producción (`production`):** Administrado mediante variables de entorno en Railway con `APP_ENV=production`, `APP_DEBUG=false`, URL pública `https://rentacar-web-production.up.railway.app`, base de datos MySQL persistente y cookies seguras (`SESSION_SECURE_COOKIE=true`)[cite: 1].
- **Aplicación móvil:** Utiliza plantillas separadas (`.env.development.example` y `.env.production.example`) sin exponer claves ni entornos reales en el repositorio[cite: 1].

### 3.2 Administración de variables de entorno

El archivo `.env.example` fue actualizado como plantilla limpia sin credenciales reales, documentando todas las variables críticas del sistema[cite: 1]:

| Variable | Propósito | Valor en Producción |
|---|---|---|
| `APP_ENV` | Define el entorno de ejecución de Laravel | `production`[cite: 1] |
| `APP_DEBUG` | Controla la exposición de trazas de error detalladas | `false`[cite: 1] |
| `APP_KEY` | Llave maestra de cifrado y firma de sesiones | Generada y persistente en Railway[cite: 1] |
| `APP_URL` | URL base pública de la aplicación | `https://rentacar-web-production.up.railway.app`[cite: 1] |
| `DB_CONNECTION` | Motor de base de datos activo | `mysql`[cite: 1] |
| `DB_HOST` / `DB_PORT` | Host y puerto del servidor MySQL | Proveídos por Railway[cite: 1] |
| `DB_DATABASE` | Nombre de la base de datos productiva | Proveído por Railway[cite: 1] |
| `DB_USERNAME` / `DB_PASSWORD` | Credenciales de acceso a MySQL | Secretos en Railway[cite: 1] |
| `CORS_ALLOWED_ORIGINS` | Lista blanca de orígenes autorizados | Dominio Railway + orígenes Capacitor[cite: 1] |
| `SESSION_SECURE_COOKIE` | Restringe el envío de cookies únicamente a HTTPS | `true`[cite: 1] |
| `SESSION_HTTP_ONLY` | Impide el acceso a cookies desde JavaScript (`XSS`) | `true`[cite: 1] |
| `SESSION_SAME_SITE` | Mitiga ataques de falsificación de peticiones (`CSRF`) | `lax`[cite: 1] |

### 3.3 Protección de credenciales y auditoría del repositorio

Se reforzó `.gitignore` para bloquear la subida accidental de secretos, archivos locales y artefactos compilados[cite: 1]:

- **Variables y respaldos:** `.env`, `.env.*` (exceptuando `.env.example` y `.env.testing.example`), `.env.local`, `.env.production`, `.env.backup`[cite: 1].
- **Llaves y certificados:** `*.pem`, `*.key`, `*.p12`, `*.pfx`, `*.jks`, `*.keystore`, `/storage/*.key`, `auth.json`, `credentials.json`, `secrets.json`, `service-account*.json`[cite: 1].
- **Bases locales y artefactos:** `/*.sqlite`, `/*.sqlite-journal`, `/vendor`, `/node_modules`, `/public/build`, `/public/hot`, `/bootstrap/cache/*.php` y `/storage/logs/*`[cite: 1].

Mediante `git check-ignore` y búsqueda de patrones de secretos en el código fuente (`app/`, `config/`, `database/`, `docker/`, `resources/`), se verificó que no existan credenciales expuestas[cite: 1].

### 3.4 Configuración CORS y seguridad de red

- **CORS (`config/cors.php`):** Se parametrizó `allowed_origins` mediante la variable `CORS_ALLOWED_ORIGINS` (eliminando configuraciones abiertas indiscriminadas) para autorizar exclusivamente el dominio de producción en Railway y los esquemas de Ionic/Capacitor (`https://localhost`, `capacitor://localhost`, `http://localhost:8100`)[cite: 1].
- **Proxy inverso y HTTPS (`bootstrap/app.php`):** Se configuró `trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_PROTO)` para que Laravel reconozca correctamente el cifrado TLS terminado en el balanceador de Railway[cite: 1].
- **Sesiones (`config/session.php`):** Se estableció serialización `json` (evitando vulnerabilidades de deserialización de objetos PHP) y fallback automático de `SESSION_SECURE_COOKIE` a `true` cuando `APP_ENV=production`[cite: 1].
- **Validaciones de arranque (`app/Providers/AppServiceProvider.php` y `docker/validate-runtime.php`):** Se implementaron verificaciones para forzar el esquema `https` en producción y bloquear el inicio del contenedor si faltan variables obligatorias (`APP_KEY`, `APP_URL`, parámetros de conexión `DB_*`) o si `APP_DEBUG` permanece activo en producción[cite: 1].

---

## 4. Docker y empaquetado

- **Construcción multietapa (`Dockerfile`):** Se diseñó una imagen optimizada que separa la compilación de activos frontend (Node + Vite) y la instalación de dependencias PHP (Composer sin paquetes de desarrollo) de la imagen final de ejecución basada en FrankenPHP y PHP 8.4 sobre Alpine Linux[cite: 1].
- **Seguridad y optimización:** Se configuró la ejecución mediante un usuario sin privilegios (`non-root`), se excluyeron archivos innecesarios mediante `.dockerignore` y se incorporaron scripts de inicialización y validación en `docker/` (`entrypoint.sh`, `validate-runtime.php`, `smoke-test.py`, `regression-test.sh`)[cite: 1].
- **Documentación y métricas:** Detalladas en `docs/lab14-empaquetado.md` y `docs/evidencias/lab14-empaquetado/`[cite: 1].

---

## 5. Despliegue en Railway

- **Infraestructura en Railway:** Se desplegó el contenedor Docker de Laravel junto con un servicio MySQL con almacenamiento persistente[cite: 1].
- **Inicialización de datos:** Se ejecutaron las 14 migraciones de esquema, los seeders iniciales y la creación del usuario administrador[cite: 1].
- **Verificación funcional y de seguridad:** Se validó el certificado HTTPS, el endpoint de salud `/up` (`200 OK`), el inicio y cierre de sesión web/API, la protección de rutas privadas y la aprobación de 128 pruebas generales más 6 pruebas de seguridad[cite: 1].

---

## 6. Automatización CI/CD

- **Flujo en GitHub Actions (`.github/workflows/lab14-ci-cd.yml`):** Automatiza la ejecución de pruebas y el despliegue hacia Railway utilizando secretos cifrados en GitHub[cite: 1].
- **Control de calidad (Quality Gate):** El despliegue automático hacia `main` solo se ejecuta cuando aprueban todas las verificaciones; ante cualquier fallo, el despliegue queda bloqueado[cite: 1].
- **Resultados y evidencias:** 134 pruebas de Laravel aprobadas, 7 pruebas de JavaScript aprobadas y compilación de Vite exitosa[cite: 1]. Capturas almacenadas en `docs/evidencias/lab14-ci-cd/`[cite: 1].

---

## 7. Publicación móvil

- **Configuración de entornos y API:** Se separaron los entornos de desarrollo y producción en `rentacar-movil`, apuntando la aplicación productiva exclusivamente a `https://rentacar-web-production.up.railway.app`[cite: 1].
- **Seguridad en Android:** Se desactivó el tráfico HTTP sin cifrar (`cleartext`) para producción y se configuró la firma `release` en `build.gradle` protegiendo el archivo `keystore` y sus credenciales fuera del control de versiones[cite: 1].
- **Compilación y pruebas:** Se compiló el proyecto Ionic Vue, se sincronizó con Capacitor Android, se generó el APK firmado y se comprobó su funcionamiento en dispositivo Android (inicio de sesión, registro de vehículos, consulta de detalles y persistencia en MySQL)[cite: 1]. Documentación detallada en `docs/Lab-14/publicacion-movil.md`[cite: 1].

---

## 8. Evidencias de configuración y seguridad

Las evidencias técnicas y capturas correspondientes a configuración y seguridad se encuentran en `docs/evidencias/lab14-seguridad/`[cite: 1]:

1. **Plantilla de entorno (`.env.example`):** Separación documentada entre desarrollo local y producción sin credenciales reales[cite: 1].
2. **Protección del repositorio (`.gitignore` y `verificacion-repositorio.txt`):** Confirmación mediante `git status` y `git check-ignore` de que `.env*`, bases SQLite, llaves y certificados están excluidos del repositorio, y auditoría de código sin secretos expuestos[cite: 1].
3. **Configuración CORS (`config/cors.php` y `verificacion-https-endpoint.txt`):** Prueba de petición `OPTIONS` verificando respuesta `204` y cabeceras `Vary: Origin, Access-Control-Request-Method`[cite: 1].
4. **Validaciones de arranque (`app/Providers/AppServiceProvider.php` y `verificacion-entorno-seguro.txt`):** Ejecución de `docker/validate-runtime.php` validando configuración correcta en producción y bloqueo explícito ante `APP_DEBUG=true`[cite: 1].
5. **Servicio activo en Railway (`docs/evidencias/lab14-ci-cd/Railway_Servicios_Online.png`):** Evidencia de los servicios web y MySQL operando en línea en producción[cite: 1].
6. **Modo debug desactivado (`docs/evidencias/lab14-seguridad/debug-desactivado.png`):** Captura en producción ante una ruta inexistente mostrando respuesta HTTP 404 limpia sin trazas internas ni variables expuestas[cite: 1].
7. **Certificado HTTPS válido (`docs/evidencias/lab14-seguridad/https-certificado.png`):** Captura del navegador verificando conexión cifrada TLS válida sobre `rentacar-web-production.up.railway.app`[cite: 1].
8. **Healthcheck `/up` operativo (`verificacion-https-endpoint.txt`):** Respuesta `StatusCode: 200 OK` desde `https://rentacar-web-production.up.railway.app/up`[cite: 1].
9. **Suite de pruebas automatizadas (`verificacion-validaciones-prod.txt`):** Ejecución completa de `php artisan test` con **134 pruebas aprobadas (371 aserciones)**[cite: 1].

---

## 9. Conclusiones

La ejecución del Laboratorio 14 permitió llevar **RentaCar** desde un entorno de desarrollo local hacia una arquitectura productiva segura, automatizada y multiplataforma[cite: 1]. La separación de variables de entorno, el endurecimiento de `.gitignore`, el control estricto de CORS, las cookies seguras sobre HTTPS y las validaciones de arranque eliminaron riesgos de exposición de credenciales o fallos de configuración silenciosos[cite: 1].

Asimismo, la integración entre el contenedor Docker basado en FrankenPHP, el despliegue persistente en Railway, el pipeline de CI/CD en GitHub Actions y el APK firmado en Android garantizan que cada actualización futura pase por pruebas automatizadas antes de llegar a los usuarios finales[cite: 1].