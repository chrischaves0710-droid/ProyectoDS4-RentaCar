<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de accesorios | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/accesorios/listado-accesorios.js'])
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
                    <li><a href="#" aria-current="page">Accesorios</a></li>
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
                <h1>Gestión de Accesorios</h1>
                <p>Catálogo de elementos adicionales disponibles para las rentas.</p>
            </header>
            <a href="{{ route('gerente.crear-accesorio') }}" class="boton boton-primario boton-registrar">
                Registrar accesorio
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-accesorios">
            <h2 id="titulo-filtros-accesorios" class="visualmente-oculto">Filtros de búsqueda</h2>
            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto;" action="#" method="get">
                <div class="campo">
                    <label for="buscar-accesorio">Buscar por nombre</label>
                    <input type="search" id="buscar-accesorio" name="buscar" placeholder="Ej: Silla para bebé, GPS">
                </div>
                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">Filtrar</button>
            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-accesorios">
            <div class="encabezado-listado">
                <h2 id="titulo-listado-accesorios">Catálogo de accesorios</h2>
            </div>
            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de accesorios.">
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">Accesorios registrados en el sistema</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 10%;">ID</th>
                            <th scope="col">Nombre del Accesorio</th>
                            <th scope="col">Precio Unitario Diario</th>
                            <th scope="col" style="width: 25%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="4" style="text-align: center; padding: 2rem;">Cargando catálogo...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        <nav class="paginacion paginacion-numerica" aria-label="Paginación de accesorios">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <span class="pagina-actual" aria-current="page">1 de 1</span>
            <button type="button" class="boton boton-secundario" disabled>Siguiente</button>
        </nav>
    </main>
</body>
</html>