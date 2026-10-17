<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de accesorios | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vistas-cliente/accesorios.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ route('cliente.catalogo') }}">Catálogo</a></li>
                    <li><a href="{{ route('cliente.accesorios') }}" aria-current="page">Accesorios</a></li>
                    <li><a href="{{ route('cliente.mis-rentas') }}">Mis Rentas</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <a href="{{ route('cliente.mi-perfil') }}" class="perfil-usuario">
                    Mi perfil
                </a>
                <button type="button">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">

        <header class="encabezado-pagina">
            <h1>Accesorios Adicionales</h1>
            <p>Catálogo de elementos extra disponibles para complementar su renta.</p>
        </header>

        <section class="aviso-informativo" aria-label="Información importante">
            <strong>Solo consulta.</strong>
            <span>Los accesorios se solicitan y asignan al momento de formalizar su contrato con el Gestor de Rentas.</span>
        </section>

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
            <h2 id="titulo-listado-accesorios" class="visualmente-oculto">Listado de accesorios</h2>
            
            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de accesorios.">
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">Accesorios registrados en el sistema</caption>
                    <thead>
                        <tr>
                            <th scope="col" style="width: 15%;">Código</th>
                            <th scope="col">Nombre del Accesorio</th>
                            <th scope="col" style="width: 25%;">Precio por Día</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-accesorios">
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem;">Cargando catálogo...</td>
                        </tr>
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