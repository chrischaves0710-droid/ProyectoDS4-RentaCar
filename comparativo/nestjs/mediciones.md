# Puesta en marcha (NestJS)
Inicio y estructuración base inicial.
Tiempo oficial registrado: **3 minutos y 15 segundos**.
Comandos utilizados: `nest new rentacar-nestjs`, `npm install @nestjs/typeorm typeorm mysql2 @nestjs/jwt`, y los generadores del CLI (`nest g resource vehiculos`, `nest g module auth`, etc.).
La medición incluye la generación de la plantilla base con el CLI, la instalación de dependencias principales para base de datos y JWT, y la limpieza de archivos de prueba innecesarios.

## Conteos medidos de código propio
Se generaron y editaron los archivos TypeScript estrictamente necesarios para cumplir con el contrato de la entidad `Vehiculo` y la autenticación. 
Archivos principales modificados/creados:
* **Entidad y DTOs:** `vehiculo.entity.ts`, `create-vehiculo.dto.ts`, `update-vehiculo.dto.ts` (Definición de los 8 campos y validaciones).
* **Controladores y Servicios:** `vehiculos.controller.ts`, `vehiculos.service.ts` (CRUD de 5 endpoints).
* **Autenticación:** `auth.controller.ts`, `auth.service.ts`, `auth.module.ts` (Generación de token JWT con usuario hardcodeado).
* **Pruebas:** `vehiculos.service.spec.ts` (Mock del repositorio).

## Estimaciones de esfuerzo por área

| Área | Estrategia en NestJS | Alcance |
|---|---|---|
| **Validación** | Uso de `ValidationPipe` global y `class-validator` en los DTOs. | Se validaron los tipos de datos en la entrada del POST/PUT automáticamente a través de los decoradores en los DTOs. |
| **Autenticación** | `JwtModule` de `@nestjs/jwt`. | Implementación directa del endpoint `POST /auth/login` validando credenciales estáticas (`admin@rentacar.com`) y devolviendo un `access_token` firmado, cumpliendo el requerimiento sin CRUD extra de usuarios. |
| **Pruebas Unitarias** | `Jest` (incluido por defecto en el CLI de Nest). | 1 archivo de pruebas (`vehiculos.service.spec.ts`) con 3 casos de prueba (findAll, findOne, create) utilizando un Mock manual del repositorio de TypeORM para simular la base de datos sin latencia. |
| **Base de Datos** | `TypeORM` y `mysql2`. | Conexión a MySQL local, generación automática de la tabla a través de la sincronización de la entidad `Vehiculo`. |

## Resultado de la verificación final
* El servidor levanta sin errores de inyección de dependencias mediante `npm run start:dev`.
* La suite de pruebas de Jest ejecutó 4 pruebas (incluyendo el AppController base), con **4 aprobadas y 0 fallidas** en ~2.48 segundos.
* Se validaron los 6 endpoints requeridos mediante una colección de **Postman** (exportada y adjunta en el repositorio), confirmando la creación exitosa en MySQL, las respuestas JSON correctas y la generación del JWT.
* Queda pendiente que el Integrante 3 realice las pruebas de estrés (latencia, peticiones por segundo y consumo de RAM).