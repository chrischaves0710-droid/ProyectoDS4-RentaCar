<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis rentas | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vistas-cliente/mis-rentas.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ route('cliente.catalogo') }}">Catálogo</a></li>
                    <li><a href="#">Accesorios</a></li>
                    <li><a href="{{ route('cliente.mis-rentas') }}" aria-current="page">Mis Rentas</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <a href="{{ route('cliente.mi-perfil') }}" class="perfil-usuario">
                    Mi perfil · Ana Rojas
                </a>
                <button type="button">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">

        <header class="encabezado-pagina">
            <h1>Mis Rentas</h1>
            <p>Historial de sus contratos de alquiler vigentes y pasados.</p>
        </header>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-rentas">
            <h2 id="titulo-listado-rentas" class="visualmente-oculto">Listado de rentas personales</h2>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de mis rentas.">
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">
                        Registro histórico de alquileres asociados a su cuenta.
                    </caption>
                    
                    <thead>
                        <tr>
                            <th scope="col">N° Contrato</th>
                            <th scope="col">Vehículo</th>
                            <th scope="col">Período</th>
                            <th scope="col">Total Abonado</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <!-- Esta tabla será poblada por mis-rentas.js usando ApiClient -->
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Cargando historial...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de rentas">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <span class="pagina-actual" aria-current="page">1 de 1</span>
            <button type="button" class="boton boton-secundario" disabled>Siguiente</button>
        </nav>

    </main>

</body>
</html>