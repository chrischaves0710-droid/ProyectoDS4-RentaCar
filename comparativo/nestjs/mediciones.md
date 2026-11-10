# Puesta en marcha (NestJS)
Inicio y estructuración base inicial.
Tiempo oficial registrado: **3 minutos y 15 segundos**.
Comandos utilizados: `nest new rentacar-nestjs`, `npm install @nestjs/typeorm typeorm better-sqlite3 @nestjs/jwt`, y los generadores del CLI (`nest g resource vehiculos`, `nest g module auth`, etc.).
La medición incluye la generación de la plantilla base con el CLI, la instalación de dependencias principales para base de datos y JWT, y la limpieza de archivos de prueba innecesarios.

## Conteos medidos de código propio
Se generaron y editaron los archivos TypeScript estrictamente necesarios para cumplir con el contrato de la entidad `Vehiculo`, la paginación y la autenticación. 
Archivos principales modificados/creados:
- **Entidades y DTOs:** `vehiculo.entity.ts`, `renta.entity.ts` (para control de dependencias), `create-vehiculo.dto.ts`, `update-vehiculo.dto.ts` (sin datos parciales), `login.dto.ts` (Definición de los campos y validaciones).
- **Controladores y Servicios:** `vehiculos.controller.ts`, `vehiculos.service.ts` (CRUD ajustado al contrato con paginación, búsqueda, Location y 204/409).
- **Autenticación:** `auth.controller.ts`, `auth.service.ts`, `auth.module.ts`, `auth.guard.ts`, `roles.guard.ts` (Generación de token JWT y protección de rutas con respuestas 401/403).
- **Pruebas:** `vehiculos.service.spec.ts` (Mock del repositorio actualizado para TypeORM con `findAndCount`).

## Estimaciones de esfuerzo por área

| Área | Estrategia en NestJS | Alcance |
|---|---|---|
| **Validación** | Uso de `ValidationPipe` global y `class-validator` en los DTOs. | Se validaron los tipos de datos en la entrada del POST/PUT y Login automáticamente a través de los decoradores en los DTOs, diferenciando correctamente el error 422 de un 401. |
| **Autenticación** | `JwtModule` de `@nestjs/jwt` e implementación de Guards. | Implementación directa del endpoint `POST /api/login` validando credenciales estáticas (`admin@rentacar.com`) y devolviendo un `access_token` firmado. Se aplicó seguridad global a los endpoints mediante `AuthGuard` y simulación de roles con `RolesGuard`. |
| **Pruebas Unitarias** | `Jest` (incluido por defecto en el CLI de Nest). | 1 archivo de pruebas (`vehiculos.service.spec.ts`) con 3 casos de prueba (findAll, findOne, create) utilizando un Mock manual del repositorio de TypeORM ajustado para validar el formato de paginación contractual (`data` y `meta`). |
| **Base de Datos** | `TypeORM` y `better-sqlite3`. | Conexión a SQLite local (`rentacar.sqlite`), generación automática de las tablas a través de la sincronización de las entidades `Vehiculo` y `Renta`, asegurando la restricción de dependencias físicas para el error 409. |

## Resultado de la verificación final
- El servidor levanta sin errores de inyección de dependencias mediante `npm run start:dev`.
- La suite de pruebas de Jest ejecutó 4 pruebas (incluyendo el AppController base), con **4 aprobadas y 0 fallidas** en ~3.62 segundos.
- Se validaron los 6 endpoints requeridos mediante una colección de **Postman** (exportada y adjunta en el repositorio), confirmando la creación exitosa en SQLite, las respuestas JSON correctas y la generación del JWT.
- Queda pendiente que el Integrante 3 realice las pruebas de estrés (latencia, peticiones por segundo y consumo de RAM).
## Perfil reproducible actual

Los resultados anteriores describen una configuración histórica. El perfil vigente de comparación usa SQLite nueva y el fixture canónico. La configuración Jest usa ESM. Ver README.md y comparativo/evidencias/homologado para reproducir la verificación y distinguir las corridas nuevas.
