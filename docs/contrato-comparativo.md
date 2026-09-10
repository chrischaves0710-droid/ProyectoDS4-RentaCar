# Contrato Comparativo — Hito 0

## 1. Entidad Seleccionada: Vehiculo
Entidad base que se replicará de forma idéntica en Laravel, NestJS y ASP.NET Core.

**Campos:**
1. `id` (integer, primary key)
2. `placa` (string, único)
3. `marca` (string)
4. `modelo` (string)
5. `anno` (integer)
6. `kilometraje` (integer)
7. `categoria_id` (integer, foreign key)
8. `estado_id` (integer, foreign key)

**Relación:**
- Un Vehículo tiene muchas Rentas (1:N).

---

## 2. Endpoints del Contrato Común
Las rutas y códigos de estado exactos que deben cumplir las tres implementaciones.

1. **Autenticación**
   - **Ruta:** `POST /api/login`
   - **Códigos:** `200 OK` (éxito), `401 Unauthorized` (credenciales inválidas)

2. **Listado Paginado**
   - **Ruta:** `GET /api/vehiculos`
   - **Códigos:** `200 OK`

3. **Detalle de Entidad**
   - **Ruta:** `GET /api/vehiculos/{id}`
   - **Códigos:** `200 OK` (encontrado), `404 Not Found` (no existe)

4. **Creación**
   - **Ruta:** `POST /api/vehiculos`
   - **Códigos:** `201 Created` (creado con éxito), `422 Unprocessable Entity` (error de validación)

5. **Actualización**
   - **Ruta:** `PUT /api/vehiculos/{id}`
   - **Códigos:** `200 OK` (actualizado), `404 Not Found` (no existe)

6. **Eliminación**
   - **Ruta:** `DELETE /api/vehiculos/{id}`
   - **Códigos:** `200 OK` (eliminado), `404 Not Found` (no existe)

---

## 3. Datos de Prueba (Carga Idéntica)
JSON estático para inicializar la base de datos (Seeders) en los tres proyectos, garantizando resultados comparables.

```json
[
  {
    "id": 1,
    "placa": "ABC-123",
    "marca": "Toyota",
    "modelo": "Corolla",
    "anno": 2022,
    "kilometraje": 45000,
    "categoria_id": 1,
    "estado_id": 1
  },
  {
    "id": 2,
    "placa": "XYZ-987",
    "marca": "Nissan",
    "modelo": "Sentra",
    "anno": 2020,
    "kilometraje": 60000,
    "categoria_id": 2,
    "estado_id": 1
  },
  {
    "id": 3,
    "placa": "LMN-456",
    "marca": "Honda",
    "modelo": "Civic",
    "anno": 2023,
    "kilometraje": 15000,
    "categoria_id": 1,
    "estado_id": 2
  }
]