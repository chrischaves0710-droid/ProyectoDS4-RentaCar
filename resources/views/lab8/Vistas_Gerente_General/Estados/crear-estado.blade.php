<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar estado | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/estados/formulario-estado.js'])
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

        <nav class="migas-pan" aria-label="Migas de pan">
            <a href="{{ route('gerente.gestion-estados') }}">Estados</a>
            <span aria-hidden="true">/</span>
            <span>Registrar estado</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Registrar nuevo estado</h1>
            <p>Ingrese el nombre del nuevo estado de disponibilidad para los vehículos (Ej: Disponible, En Taller).</p>
        </header>

        <form action="#" method="post" class="secciones-formulario">
            <fieldset class="tarjeta-formulario">
                <legend>Datos del Estado</legend>
                <div class="campo">
                    <label for="nombre">Nombre del Estado</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: En Mantenimiento, Reservado" required>
                    <span id="error-nombre" class="mensaje-error" style="display: none;"></span>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <button type="button" class="boton boton-secundario" onclick="window.history.back();">Cancelar</button>
                <button type="submit" class="boton boton-primario">Guardar estado</button>
            </div>
        </form>

    </main>

</body>
</html>