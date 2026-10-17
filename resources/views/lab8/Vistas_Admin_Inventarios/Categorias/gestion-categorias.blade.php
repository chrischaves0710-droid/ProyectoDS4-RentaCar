<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de categorías | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/categorias/listado-categorias.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#" aria-current="page">Categorías</a></li>
                    <li><a href="#">Estados</a></li>
                    <li><a href="#">Vehículos</a></li>
                    <li><a href="#">Accesorios</a></li>
                </ul>
            </nav>

            <div class="sesion">
                <span>Administrador de Inventarios</span>
                <button type="button">Cerrar sesión</button>
            </div>

        </div>
    </header>

    <main class="contenido-principal">

        <div class="cabecera-gestion">
            <header class="encabezado-pagina">
                <h1>Gestión de Categorías</h1>
                <p>Administración de las categorías y clasificaciones de los vehículos del sistema.</p>
            </header>

            <a href="#" class="boton boton-primario boton-registrar">
                Registrar categoría
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-categorias">
            <h2 id="titulo-filtros-categorias" class="visualmente-oculto">
                Filtros de búsqueda de categorías
            </h2>

            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto;" action="#" method="get">
                
                <div class="campo">
                    <label for="buscar-categoria">Buscar por nombre de categoría</label>
                    <input type="search" id="buscar-categoria" name="buscar" placeholder="Ej: Sedán, SUV, Compacto">
                </div>

                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">
                    Filtrar
                </button>

            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-categorias">
            
            <div class="encabezado-listado">
                <h2 id="titulo-listado-categorias">Listado de categorías</h2>
                <p>2 categorías registradas</p>
            </div>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de categorías.">
                
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">Categorías registradas en el sistema</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 15%;">ID</th>
                            <th scope="col">Nombre de la Categoría</th>
                            <th scope="col" style="width: 25%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1</strong></td>
                            <td>Sedán</td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de categorías">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <span class="pagina-actual" aria-current="page">1 de 1</span>
            <button type="button" class="boton boton-secundario" disabled>Siguiente</button>
        </nav>

    </main>

</body>
</html>