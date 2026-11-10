# Adenda técnica del comparativo IF0009

Base de esta revisión: 3ecd17a1d0a875cca1b725b33820001f35706a25. Esta adenda registra un perfil de verificación propuesto. El contrato congelado en docs/contrato-comparativo.md sigue siendo la referencia histórica. La aceptación de las diferencias entre ese documento y las secciones 4 y 10 del PDF queda pendiente de confirmación.

| Caso | Contrato congelado | Perfil de verificación del PDF y convenciones propuestas |
|---|---|---|
| Login | 200 / 401 | 200 con token, 401 si las credenciales son incorrectas y 422 para entrada incompleta. |
| Listado | 200 paginado | page, per_page, q. data y meta con current_page, per_page, total y last_page. Orden por ID ascendente. |
| Detalle | 200 / 404 | 200 con data, 404 si falta el ID y 403 cuando no corresponde el acceso. |
| Alta | 201 / 422 | 201 con Location y data. Errores por campo en 422. |
| Actualización | 200 / 404 | 200 con data, 404 si no existe y 422 para entrada inválida. |
| Eliminación | 200 / 404 | 204 sin cuerpo, 404 si no existe y 409 cuando existe una dependencia. |

Las decisiones sobre alcance de autorización por recurso, estado inicial, reglas de kilometraje y bloqueo de rentas históricas deben confirmarse antes de declarar equivalencia. Los ocho campos del vehículo son id, placa, marca, modelo, anno, kilometraje, categoria_id y estado_id. Los casos históricos del Integrante 3 esperan 200 al eliminar y por eso registran tres diferencias con los 204 observados. El cuarto caso histórico es 422 en el login incorrecto de Laravel.

## Condición para repetir la medición

Usar SQLite en una copia de benchmark dedicada, cargar el fixture de ASP.NET, acordar un esquema común, verificar filas y relaciones y restaurar el mismo estado antes de cada corrida. Las APIs deben superar las mismas aserciones HTTP antes de ejecutar k6. El perfil propuesto y la evidencia histórica pueden coexistir mientras se confirma el criterio académico.
