# Laboratorio 8 - Lista de verificación de accesibilidad

Esta lista documenta la revisión de las tres pantallas principales del Laboratorio 8: Catálogo de vehículos, Nueva renta y Gestión de vehículos.

| Verificación | Catálogo | Nueva renta | Gestión de vehículos | Observación |
|---|---|---|---|---|
| Idioma del documento definido (`lang="es"`) | Cumple | Cumple | Cumple | Facilita la interpretación por tecnologías de asistencia. |
| Estructura semántica (`header`, `nav`, `main`, `section`, etc.) | Cumple | Cumple | Cumple | Se utilizan elementos semánticos según la función del contenido. |
| Un único `h1` por pantalla | Cumple | Cumple | Cumple | No se detectaron encabezados `h1` duplicados. |
| Jerarquía de encabezados sin saltos | Cumple | Cumple | Cumple | Los títulos secundarios utilizan `h2` y, cuando corresponde, `h3`. |
| Formularios con etiquetas asociadas | Cumple | Cumple | Cumple | Los controles visibles utilizan `label` con `for` e `id` coincidentes. |
| Campos relacionados agrupados | No aplica | Cumple | No aplica | Nueva renta utiliza `fieldset` y `legend` para Cliente, Vehículo, Accesorios y Fechas/precio. |
| Validación nativa de HTML | No aplica | Cumple | No aplica | Se utilizan `required`, `min`, `step` y tipos de entrada adecuados. |
| Mensajes de error asociados a sus campos | No aplica | Cumple | No aplica | Los mensajes tienen `id` y los campos correspondientes usan `aria-describedby`. |
| Navegación principal identificada | Cumple | Cumple | Cumple | Cada menú principal usa `nav` con `aria-label`. |
| Página actual identificada | Cumple | Cumple | Cumple | Se utiliza `aria-current="page"` donde corresponde. |
| Foco de teclado visible | Cumple | Cumple | Cumple | La hoja `lab8.css` define estilos `:focus-visible`. |
| Controles nativos accesibles con teclado | Cumple | Cumple | Cumple | Enlaces, botones, inputs, selects, radios y checkboxes utilizan elementos HTML nativos. |
| Contenido deshabilitado identificado | No aplica | Cumple | Cumple | Vehículos no disponibles y eliminación bloqueada usan controles `disabled` y texto explicativo. |
| Tabla con encabezados y `caption` | No aplica | No aplica | Cumple | La tabla usa `th scope="col"` y un `caption` accesible. |
| Tabla ancha accesible por teclado | No aplica | No aplica | Cumple | El contenedor con desplazamiento tiene `tabindex="0"` y descripción accesible. |
| Acciones repetidas con nombre accesible específico | Cumple | No aplica | Cumple | Los enlaces de detalle y acciones de tabla incluyen `aria-label` con el vehículo correspondiente. |
| Interfaz utilizable con texto al 200 % | Verificar con evidencia | Verificar con evidencia | Verificar con evidencia | La revisión final debe confirmarse en navegador con zoom/tamaño de fuente al 200 %. |
| Prueba exclusiva con teclado | Pendiente de evidencia manual | Pendiente de evidencia manual | Pendiente de evidencia manual | Recorrer con `Tab`/`Shift+Tab`, activar con `Enter` o `Espacio` y confirmar que no exista pérdida de foco. |
| Anchos 375 px, 768 px y 1280 px | Evidencia externa | Evidencia externa | Evidencia externa | Las capturas deben adjuntarse o quedar disponibles en la entrega final. |

## Prueba manual de teclado

1. Abrir cada una de las tres pantallas desde una recarga completa.
2. No utilizar el mouse durante la prueba.
3. Presionar `Tab` para avanzar por los elementos interactivos y `Shift + Tab` para retroceder.
4. Confirmar que el foco sea visible en todo momento.
5. Activar enlaces y botones con `Enter`; en botones, checkboxes y radios comprobar también `Espacio` cuando corresponda.
6. En Nueva renta, verificar que los campos obligatorios muestren la validación nativa al intentar confirmar el formulario sin completarlos.
7. En Gestión de vehículos, enfocar el contenedor de la tabla y comprobar que sea posible desplazarse horizontalmente con el teclado cuando el ancho no permita visualizarla completa.
8. Confirmar que ningún elemento interactivo quede inaccesible o atrapado durante el recorrido.

## Evidencias finales requeridas

- Capturas de cada pantalla a 375 px, 768 px y 1280 px.
- Capturas con el tamaño de fuente del navegador aumentado al 200 %.
- Confirmación manual del recorrido completo por teclado.
