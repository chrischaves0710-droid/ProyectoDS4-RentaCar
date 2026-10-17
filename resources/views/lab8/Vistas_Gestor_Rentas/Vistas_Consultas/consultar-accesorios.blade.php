<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Accesorios | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vistas-gestor/consultar-accesorios.js'])
</head>
<body>
    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ url('/') }}">Inicio</a></li>
                    <li><a href="{{ route('gestor.gestion-rentas') }}">Rentas</a></li>
                    <li><a href="{{ route('gestor.consultar-clientes') }}">Clientes</a></li>
                    <li><a href="{{ route('gestor.consultar-vehiculos') }}">Vehículos</a></li>
                    <li><a href="{{ route('gestor.consultar-accesorios') }}" aria-current="page">Accesorios</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <span>Gestor de Rentas</span>
                <button type="button">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">
        <header class="encabezado-pagina">
            <h1>Catálogo de Accesorios</h1>
            <p>Lista de elementos adicionales disponibles para los contratos.</p>
        </header>

        <section class="seccion-filtros">
            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto;" action="#" method="get">
                <div class="campo">
                    <label for="buscar-accesorio">Buscar accesorio</label>
                    <input type="search" id="buscar-accesorio" name="buscar">
                </div>
                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">Filtrar</button>
            </form>
        </section>

        <section class="seccion-tabla-vehiculos">
            <div class="contenedor-tabla" tabindex="0">
                <table class="tabla-vehiculos">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Accesorio</th>
                            <th>Precio Diario ($)</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-accesorios">
                        <tr><td colspan="3" style="text-align: center; padding: 2rem;">Cargando accesorios...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        <nav class="paginacion paginacion-numerica"></nav>
    </main>
</body>
</html>