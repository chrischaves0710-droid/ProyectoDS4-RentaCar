# Documentación de Reglas de Negocio - Entidad Vehículos

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

--- 

# Documentación de Reglas de Negocio - Entidad Estados

Este documento describe la lógica de negocio, las validaciones de dominio y el comportamiento implementado en la capa de servicio (`EstadoService`) para el módulo de administración de estados.

## 1. Reglas de Negocio Implementadas

### Regla 1: Protección de Eliminación de Estados con Vehículos Asociados

* **Descripción:** No se permite eliminar físicamente un estado que se encuentre asociado a uno o más vehículos registrados en el sistema.
* **Comportamiento Esperado:** Antes de ejecutar la eliminación, la capa de servicio consulta si existen vehículos cuyo `estado_id` corresponda al estado seleccionado. Si existen vehículos asociados, la operación se cancela y se genera una `EstadoException` con el mensaje `No se puede eliminar el estado porque tiene vehículos asociados.`. El estado permanece almacenado en la base de datos.

## 2. Validaciones de Entrada

* **Nombre obligatorio:** El campo `nombre` es obligatorio y debe corresponder a una cadena de texto.
* **Longitud máxima:** El nombre del estado no puede superar los 255 caracteres.
* **Nombre único:** No se permiten dos estados con el mismo nombre. Durante una actualización, la validación ignora el registro que se está modificando para permitir conservar su propio nombre.
* **Paginación máxima:** El listado de estados limita el parámetro `per_page` a un máximo de 50 registros por página.
* **Ordenamiento:** El listado permite ordenar por `id` o `nombre`, utilizando únicamente las direcciones `asc` o `desc`.
* **Filtro por nombre:** El listado permite filtrar los estados mediante el parámetro `nombre`.

## 3. Comportamiento Transaccional (ACID)

* **Operaciones sobre una sola tabla:** Las operaciones de creación, actualización y eliminación de un estado afectan únicamente a la tabla `estados`, por lo que no requieren una transacción explícita mediante `DB::transaction()`.
* **Protección de integridad referencial:** La eliminación es controlada desde la capa de servicio mediante una consulta previa a la entidad `Vehiculo`, evitando eliminar estados que todavía se encuentren asociados a vehículos.

## 4. Códigos de Respuesta HTTP y Manejo de Excepciones

* **Operaciones Exitosas (`201 Created` / `200 OK`):**
  Cuando el sistema procesa correctamente una solicitud, responde con un código **`201 Created`** al registrar un nuevo estado y con **`200 OK`** al consultar, actualizar o eliminar un estado.

* **Errores de Validación de Entrada (`422 Unprocessable Content`):**
  Si la solicitud no cumple con las reglas definidas en `StoreEstadoRequest` o `UpdateEstadoRequest`, Laravel rechaza la petición y devuelve los errores de validación correspondientes al campo `nombre`.

* **Violaciones a las Reglas de Negocio:**
  Cuando se intenta eliminar un estado que tiene vehículos asociados, la capa de servicio genera una `EstadoException` con un mensaje explicativo del motivo por el cual la operación no puede realizarse.

---

# Documentación de Reglas de Negocio - Entidad Accesorios

## 1. Reglas de Negocio Implementadas

### Regla 1: Protección de Eliminación con Rentas Asociadas

* **Descripción:** No se permite eliminar físicamente un accesorio que tenga registros asociados en la tabla intermedia `accesorio_renta`.
* **Comportamiento Esperado:** Antes de ejecutar la eliminación, la capa de servicio verifica si el accesorio posee rentas asociadas. Si existen registros relacionados, la operación se cancela y se genera una `AccesorioException` con el mensaje `No se puede eliminar el accesorio porque tiene rentas asociadas.`.

### Regla 2: Restricción de Accesorios en Rentas Finalizadas

* **Descripción:** No se permite agregar un accesorio a una renta cuya fecha de finalización ya haya pasado.
* **Comportamiento Esperado:** Antes de crear la asociación, la capa de servicio verifica la fecha `fecha_fin` de la renta. Si la renta ya se encuentra finalizada, se cancela la operación y se genera una `AccesorioException` con el mensaje `No se puede agregar un accesorio a una renta finalizada.`.

### Regla 3: No Duplicar Accesorios dentro de una Misma Renta

* **Descripción:** Un mismo accesorio no puede ser asociado más de una vez a una misma renta.
* **Comportamiento Esperado:** Antes de insertar el registro en `accesorio_renta`, la capa de servicio comprueba si el accesorio ya se encuentra asociado a la renta. Si la relación ya existe, se cancela la operación y se genera una `AccesorioException` con el mensaje `El accesorio ya está asociado a esta renta.`.

### Regla 4: Congelación del Precio y Actualización del Total de la Renta

* **Descripción:** Al agregar un accesorio a una renta, se debe conservar el precio que tenía el accesorio en el momento de realizar la asociación y actualizar el monto total de la renta.
* **Comportamiento Esperado:** La capa de servicio copia el valor actual de `precio_unitario` a `precio_diario`, calcula el `subtotal` mediante la operación `cantidad * precio_diario` y suma dicho subtotal al `monto_total` de la renta.

## 2. Validaciones de Entrada, Filtros y Paginación

* **Nombre obligatorio:** El campo `nombre` es obligatorio y debe ser una cadena de texto.
* **Longitud máxima:** El nombre del accesorio no puede superar los 100 caracteres.
* **Nombre único:** No se permiten accesorios con nombres duplicados.
* **Precio obligatorio:** El campo `precio_unitario` es obligatorio y debe ser numérico.
* **Precio positivo:** El `precio_unitario` debe ser mayor que 0.
* **Cantidad válida:** Para agregar un accesorio a una renta, la cantidad debe ser un entero mayor o igual a 1.
* **Filtros combinables:** El listado permite combinar filtros por nombre y por rango de `precio_unitario`.
* **Ordenamiento:** El listado permite ordenar por `id`, `nombre`, `precio_unitario` o `created_at`, utilizando las direcciones `asc` o `desc`.
* **Paginación máxima:** El tamaño máximo de página es de 25 registros, aunque el cliente solicite una cantidad superior.

## 3. Comportamiento Transaccional y Auditoría (ACID)

* **Atomicidad en la Asociación a una Renta:** La operación de agregar un accesorio a una renta modifica tanto la tabla intermedia `accesorio_renta` como el campo `monto_total` de la tabla `rentas`. Ambas operaciones se ejecutan dentro de un bloque `DB::transaction()`.

* **Congelación del Precio:** Dentro de la transacción se almacena el precio actual del accesorio en el campo `precio_diario` y se calcula el `subtotal`, evitando que modificaciones posteriores al precio del catálogo alteren el valor histórico de la renta.

* **Comportamiento ante Fallos (Rollback):** Si ocurre una excepción después de insertar la relación en `accesorio_renta` y antes de completar la actualización de la renta, la transacción realiza un `ROLLBACK`. De esta forma, se elimina la asociación insertada y el `monto_total` de la renta conserva su valor anterior.


## 4. Códigos de Respuesta HTTP y Manejo de Excepciones

* **Operaciones Exitosas (`201 Created` / `200 OK`):**
  Cuando el sistema procesa correctamente una solicitud, responde con **`201 Created`** al registrar un nuevo accesorio y con **`200 OK`** al consultar, actualizar, eliminar o agregar un accesorio a una renta.

* **Errores de Validación de Entrada (`422 Unprocessable Content`):**
  Si la solicitud no cumple con las reglas definidas en los `FormRequest` o con la validación de `cantidad`, Laravel rechaza la petición con código **`422`**, indicando el campo que presenta el error.

* **Violaciones a las Reglas de Negocio:**
  Cuando una petición intenta eliminar un accesorio con rentas asociadas, agregarlo a una renta finalizada o duplicarlo dentro de una misma renta, la capa de servicio genera una `AccesorioException` con un mensaje explicativo del motivo del rechazo.


## 5. Pruebas de las Reglas de Negocio

* Se incluyen pruebas Pest para verificar que no sea posible eliminar accesorios con rentas asociadas.
* Se verifica que no sea posible agregar accesorios a rentas finalizadas.
* Se comprueba que un accesorio no pueda ser agregado dos veces a la misma renta.
* Se valida el cálculo del `precio_diario`, `subtotal` y `monto_total`.
* Se incluye una prueba de rollback para comprobar que una falla durante la operación transaccional no deje registros parciales en `accesorio_renta` ni modificaciones incompletas en `rentas`.