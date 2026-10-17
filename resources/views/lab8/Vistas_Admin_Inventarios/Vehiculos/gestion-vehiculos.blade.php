<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de vehículos | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vehiculos/listado-vehiculos.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ url('/') }}">Inicio</a></li>
                    <li>
                        <a href="{{ route('admin.gestion-vehiculos') }}" aria-current="page">Vehículos</a>
                    </li>
                    <li><a href="{{ route('admin.gestion-categorias') }}">Categorías</a></li>
                    <li><a href="{{ route('admin.gestion-estados') }}">Estados</a></li>
                    <li><a href="{{ route('admin.gestion-accesorios') }}">Accesorios</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <span>Admin de Inventarios</span>
                <button type="button">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">

        <div class="cabecera-gestion">
            <header class="encabezado-pagina">
                <h1>Gestión de Vehículos</h1>
                <p>Flota registrada en el sistema.</p>
            </header>

            <a href="{{ route('admin.crear-vehiculo') }}" class="boton boton-primario boton-registrar">
                Registrar vehículo
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-vehiculos">
            <h2 id="titulo-filtros-vehiculos" class="visualmente-oculto">Filtros de vehículos</h2>
            
            <form class="formulario-filtros formulario-filtros-gestion" action="{{ route('admin.gestion-vehiculos') }}" method="get">
                <div class="campo">
                    <label for="buscar-gestion">Buscar por placa o modelo</label>
                    <input type="search" id="buscar-gestion" name="buscar" placeholder="Ej: BCD-123">
                </div>

                <div class="campo">
                    <label for="categoria-gestion">Categoría</label>
                    <select id="categoria-gestion" name="categoria">
                        <option value="">Todas</option>
                        <option value="sedan">Sedán</option>
                        <option value="suv">SUV</option>
                        <option value="hatchback">Hatchback</option>
                        <option value="pickup">Pickup</option>
                        <option value="hibrido">Eléctrico / Híbrido</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="estado-gestion">Estado</label>
                    <select id="estado-gestion" name="estado">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="alquilado">Alquilado</option>
                        <option value="mantenimiento">En Mantenimiento</option>
                        <option value="venta">Para Venta</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <button type="submit" class="boton boton-primario boton-filtrar">Filtrar</button>
            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-vehiculos">
            <div class="encabezado-listado">
                <h2 id="titulo-listado-vehiculos">Listado de vehículos</h2>
                <p>6 de 24</p>
            </div>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de vehículos. Desplácese horizontalmente si es necesario.">
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">Vehículos registrados en el sistema RentaCar</caption>
                    <thead>
                        <tr>
                            <th scope="col">Placa</th>
                            <th scope="col">Marca</th>
                            <th scope="col">Modelo</th>
                            <th scope="col">Año</th>
                            <th scope="col">Km</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>BCD-123</td>
                            <td>Toyota</td>
                            <td>Corolla</td>
                            <td>2024</td>
                            <td>12,450</td>
                            <td>Sedán</td>
                            <td><span class="estado estado-disponible">Disponible</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Toyota Corolla, placa BCD-123">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Toyota Corolla, placa BCD-123">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Toyota Corolla, placa BCD-123">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>EFG-456</td>
                            <td>Hyundai</td>
                            <td>Tucson</td>
                            <td>2023</td>
                            <td>28,300</td>
                            <td>SUV</td>
                            <td><span class="estado estado-disponible">Disponible</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Hyundai Tucson, placa EFG-456">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Hyundai Tucson, placa EFG-456">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Hyundai Tucson, placa EFG-456">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>HIJ-789</td>
                            <td>Suzuki</td>
                            <td>Swift</td>
                            <td>2022</td>
                            <td>41,900</td>
                            <td>Hatchback</td>
                            <td><span class="estado estado-alquilado">Alquilado</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Suzuki Swift, placa HIJ-789">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Suzuki Swift, placa HIJ-789">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Suzuki Swift, placa HIJ-789" disabled aria-describedby="mensaje-eliminacion">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>KLM-321</td>
                            <td>Kia</td>
                            <td>Niro</td>
                            <td>2023</td>
                            <td>15,700</td>
                            <td>Eléctrico / Híbrido</td>
                            <td><span class="estado estado-mantenimiento">En Mantenimiento</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Kia Niro, placa KLM-321">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Kia Niro, placa KLM-321">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Kia Niro, placa KLM-321">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>NOP-654</td>
                            <td>Nissan</td>
                            <td>Versa</td>
                            <td>2014</td>
                            <td>92,400</td>
                            <td>Sedán</td>
                            <td><span class="estado estado-venta">Para Venta</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Nissan Versa, placa NOP-654">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Nissan Versa, placa NOP-654">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Nissan Versa, placa NOP-654">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>QRS-987</td>
                            <td>Ford</td>
                            <td>Ranger</td>
                            <td>2020</td>
                            <td>60,200</td>
                            <td>Pickup</td>
                            <td><span class="estado estado-inactivo">Inactivo</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla" aria-label="Consultar Ford Ranger, placa QRS-987">Consultar</a>
                                    <a href="{{ route('admin.editar-vehiculo') }}" class="accion-tabla" aria-label="Editar Ford Ranger, placa QRS-987">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar" aria-label="Eliminar Ford Ranger, placa QRS-987">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            
            <p id="mensaje-eliminacion" class="mensaje-advertencia">
                Eliminar deshabilitado: el vehículo tiene un contrato de alquiler activo y no puede eliminarse.
            </p>

        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de vehículos">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <span class="pagina-actual" aria-current="page">1</span>
            <a href="#" class="pagina-enlace">2</a>
            <a href="#" class="pagina-enlace">3</a>
            <button type="button" class="boton boton-secundario">Siguiente</button>
        </nav>

    </main>

</body>
</html>