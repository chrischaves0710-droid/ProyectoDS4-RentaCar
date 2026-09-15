# Documentación de Reglas de Negocio - Entidad Vehículos

Este documento describe la lógica de negocio, las validaciones de dominio y el comportamiento transaccional (ACID) implementados en la capa de servicio (`VehiculoService`) para el módulo de administración de vehículos.

---

## 1. Reglas de Negocio Implementadas

### Regla 1: Límite de Kilometraje en Registro Inicial
* **Descripción:** No se permite ingresar en el sistema vehículos cuya lectura inicial de kilometraje sea mayor a 5,000 km.
* **Comportamiento Esperado:** Si el valor del campo `kilometraje` supera los 5,000 km durante la creación, la capa de servicio cancela el registro y lanza una excepción `VehiculoException`, la cual retorna una respuesta HTTP `422 Unprocessable Content`.

### Regla 2: Clasificación Automática "Para Venta" por Antigüedad o Uso
* **Descripción:** Todo vehículo que posea 10 o más años de antigüedad (calculado según el año actual contra la propiedad `anno`) o un kilometraje superior a los 80,000 km se asigna de forma automática al estado "Para Venta".
* **Comportamiento Esperado:** El sistema ignora cualquier `estado_id` enviado externamente en la petición y asigna la clave foránea correspondiente a "Para Venta" antes de persistir los datos.

### Regla 3: Estado Inicial "Disponible" por Defecto
* **Descripción:** Si el vehículo a registrar no cumple con los criterios de antigüedad o kilometraje de la Regla 2 y no especifica una condición inicial, se clasifica por defecto como un vehículo apto para rentarlo.
* **Comportamiento Esperado:** La capa de servicio asigna automáticamente el `estado_id` asociado a "Disponible" antes de la inserción en la base de datos.

### Regla 4: Invariabilidad Histórica del Kilometraje (No Reducción)
* **Descripción:** El kilometraje es un valor estrictamente acumulativo. Bajo ninguna circunstancia se permite actualizar un vehículo reduciendo la cantidad de kilómetros registrados previamente.
* **Comportamiento Esperado:** Al procesar un `PUT` o `PATCH`, el servicio compara el `kilometraje` enviado contra el valor existente en el modelo. Si la nueva cifra es menor a la registrada en la base de datos, se detiene la actualización y se lanza una `VehiculoException`.

### Regla 5: Protección de Eliminación con Contratos Activos
* **Descripción:** Se restringe la eliminación física de un vehículo si este cuenta con contratos de alquiler (rentas) activos o vigentes en el sistema.
* **Comportamiento Esperado:** Antes de ejecutar el borrado, la capa de servicio consulta la relación con la entidad `Renta`. Si existen registros con fechas activas asociados al vehículo, la acción se aborta y se genera una `VehiculoException`.

---

## 2. Comportamiento Transaccional y Auditoría (ACID)

* **Atomicidad en Creación:** El proceso de registro de un vehículo involucra la inserción en la tabla `vehiculos` y el registro correspondiente en la tabla `auditoria_vehiculos` con la acción `REGISTRO_INICIAL`. Ambas operaciones se encuentran empaquetadas dentro de un bloque `DB::transaction()`.
* **Comportamiento ante Fallos (Rollback):** Si ocurre alguna interrupción o excepción dentro de la transacción, la base de datos deshace por completo las operaciones previas (`ROLLBACK`), garantizando que no existan vehículos registrados sin su correspondiente auditoría o datos incompletos en el sistema.

---

## 3. Códigos de Respuesta HTTP y Manejo de Excepciones

* **Operaciones Exitosas (`201 Created` / `200 OK` / `204 No Content`):**
  Cuando el sistema procesa una solicitud correctamente, responde con un código **`201 Created`** al registrar un nuevo vehículo, **`200 OK`** al consultar la lista paginada, ver el detalle o actualizar un registro existente, y **`204 No Content`** al completar la eliminación física de un vehículo sin contratos activos.

* **Errores de Validación de Entrada (`422 Unprocessable Content`):**
  Si la solicitud enviada no cumple con las reglas sintácticas o tipos de datos requeridos por la capa de validación (`FormRequest`), Laravel intercepta de forma automática la `ValidationException`, retornando un estado HTTP **`422`** con el detalle de los campos faltantes o inválidos.

* **Violaciones a las Reglas de Negocio (`422 Unprocessable Content`):**
  Cuando una petición incumple una regla propia del dominio (como intentar registrar un auto con más de 5,000 km, intentar reducir el kilometraje histórico o borrar un vehículo con contratos vigentes), la capa de servicio lanza una `VehiculoException`. Esta excepción personalizada responde de forma estructurada con un estado HTTP **`422`** y un mensaje JSON explicativo del motivo del rechazo.

* **Fallos Inesperados del Servidor (`500 Internal Server Error`):**
  Si se presenta un error no controlado durante la ejecución del sistema o se interrumpe la persistencia dentro de la transacción, el servidor responde con un estado **`500`**. Esto confirma que la base de datos abortó la operación mediante un rollback automático para preservar la integridad global de los datos.

  ---

  # Documentación de Reglas de Negocio - Entidad Rentas
  
  ### Regla 1: Validación de Disponibilidad del Vehículo

* **Descripción:** Se impide registrar una nueva renta cuando el vehículo seleccionado no se encuentra en estado `Disponible`.

* **Comportamiento Esperado:** Antes de crear la renta, la capa de servicio consulta el estado actual del vehículo. Si el vehículo no posee un estado asignado o su estado es diferente de `Disponible`, la operación se cancela y se genera una `RentaException`, indicando la placa y el estado actual del vehículo.


### Regla 2: Duración Mínima de la Renta

* **Descripción:** Toda renta debe tener una duración mínima de un día, evitando contratos cuya fecha de inicio y fecha de finalización representen una duración de cero días.

* **Comportamiento Esperado:** La capa de servicio calcula la diferencia entre `fecha_inicio` y `fecha_fin`. Si la duración calculada es menor a un día, se cancela la creación de la renta y se genera una `RentaException` con el mensaje `La renta debe tener una duración mínima de un día.`


### Regla 3: Cálculo Automático del Monto Total

* **Descripción:** El monto total de una renta no es ingresado manualmente, sino que se calcula automáticamente utilizando la duración del alquiler y el precio diario establecido.

* **Comportamiento Esperado:** La capa de servicio calcula la cantidad de días entre `fecha_inicio` y `fecha_fin` y multiplica el resultado por `precio_diario`. El valor obtenido se almacena automáticamente en el campo `monto_total`.


### Regla 4: Cambio Automático del Estado del Vehículo

* **Descripción:** Cuando una renta se registra correctamente, el vehículo asociado deja de estar disponible y pasa automáticamente al estado `Alquilado`.

* **Comportamiento Esperado:** Después de crear la renta, la capa de servicio busca el estado `Alquilado` y actualiza el `estado_id` del vehículo. Si dicho estado no existe en el sistema, se genera una `RentaException` y la operación no puede completarse.


### Regla 5: Creación Transaccional de la Renta

* **Descripción:** La creación de una renta y el cambio de estado del vehículo deben realizarse como una única operación transaccional para mantener la consistencia de los datos.

* **Comportamiento Esperado:** La creación de la renta y la actualización del vehículo al estado `Alquilado` se ejecutan dentro de una `DB::transaction`. Si alguna de las operaciones falla, se realiza un rollback y ninguno de los cambios efectuados dentro de la transacción queda almacenado.


### Regla 6: Validación de Fechas durante la Actualización

* **Descripción:** No se permite actualizar una renta de manera que la fecha de finalización sea igual o anterior a la fecha de inicio.

* **Comportamiento Esperado:** Antes de actualizar la renta, la capa de servicio compara `fecha_inicio` con `fecha_fin`. Si la fecha final es menor o igual a la inicial, la actualización se cancela y se genera una `RentaException` con el mensaje `La fecha final debe ser posterior a la fecha inicial.`


### Regla 7: Recálculo del Monto Total durante la Actualización

* **Descripción:** Cuando se modifican las fechas o el precio diario de una renta, el monto total debe actualizarse automáticamente para reflejar los nuevos valores.

* **Comportamiento Esperado:** La capa de servicio toma las fechas y el precio diario actualizados, calcula nuevamente la duración de la renta y establece `monto_total` como el resultado de multiplicar la cantidad de días por el precio diario.