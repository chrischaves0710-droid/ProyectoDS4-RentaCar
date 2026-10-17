<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de estados | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/estados/listado-estados.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#">Clientes</a></li>
                    <li><a href="#">Rentas</a></li>
                    <li><a href="#">Vehículos</a></li>
                    <li><a href="#">Categorías</a></li>
                    <li><a href="#" aria-current="page">Estados</a></li>
                    <li><a href="#">Reportes</a></li>
                </ul>
            </nav>

            <div class="sesion">
                <span>Gerente General</span>
                <button type="button">Cerrar sesión</button>
            </div>

        </div>
    </header>

    <main class="contenido-principal">

        <div class="cabecera-gestion">
            <header class="encabezado-pagina">
                <h1>Gestión de Estados</h1>
                <p>Administración de los estados disponibles para los vehículos del sistema.</p>
            </header>

            <a href="{{ route('gerente.crear-estado') }}" class="boton boton-primario boton-registrar">
                Registrar estado
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-estados">
            <h2 id="titulo-filtros-estados" class="visualmente-oculto">
                Filtros de búsqueda de estados
            </h2>

            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto;" action="#" method="get">
                
                <div class="campo">
                    <label for="buscar-estado">Buscar por nombre de estado</label>
                    <input type="search" id="buscar-estado" name="buscar" placeholder="Ej: Disponible, En Mantenimiento">
                </div>

                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">
                    Filtrar
                </button>

            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-estados">
            
            <div class="encabezado-listado">
                <h2 id="titulo-listado-estados">Listado de estados</h2>
                <p>2 estados registrados</p>
            </div>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de estados.">
                
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">Estados registrados en el sistema</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 15%;">ID</th>
                            <th scope="col">Nombre del Estado</th>
                            <th scope="col" style="width: 25%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1</strong></td>
                            <td><span class="estado estado-disponible">Disponible</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="{{ route('gerente.editar-estado', ['id' => 1]) }}" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>2</strong></td>
                            <td><span class="estado estado-inactivo">En Mantenimiento</span></td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="{{ route('gerente.editar-estado', ['id' => 2]) }}" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de estados">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <span class="pagina-actual" aria-current="page">1 de 1</span>
            <button type="button" class="boton boton-secundario" disabled>Siguiente</button>
        </nav>

    </main>

</body>
</html>