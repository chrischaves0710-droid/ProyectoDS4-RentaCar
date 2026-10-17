<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Vehículos | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vistas-gestor/consultar-vehiculos.js'])
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
                    <li><a href="{{ route('gestor.consultar-vehiculos') }}" aria-current="page">Vehículos</a></li>
                    <li><a href="{{ route('gestor.consultar-accesorios') }}">Accesorios</a></li>
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
            <h1>Consulta de Vehículos</h1>
            <p>Directorio de la flota vehicular para consultar disponibilidad.</p>
        </header>

        <section class="seccion-filtros">
            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto auto;" action="#" method="get">
                <div class="campo">
                    <label for="buscar-vehiculo">Buscar por placa, marca o modelo</label>
                    <input type="search" id="buscar-vehiculo" name="buscar">
                </div>
                <!-- Aquí podrías agregar los selects de categoría y estado si lo deseas -->
                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">Filtrar</button>
            </form>
        </section>

        <section class="seccion-tabla-vehiculos">
            <div class="contenedor-tabla" tabindex="0">
                <table class="tabla-vehiculos">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Marca y Modelo</th>
                            <th>Categoría</th>
                            <th>Kilometraje</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-vehiculos">
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">Cargando vehículos...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        <nav class="paginacion paginacion-numerica"></nav>
    </main>
</body>
</html>