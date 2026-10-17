<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar accesorio | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/accesorios/formulario-accesorio.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="#" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#">Categorías</a></li>
                    <li><a href="#">Estados</a></li>
                    <li><a href="#">Vehículos</a></li>
                    <li><a href="#" aria-current="page">Accesorios</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <span>Administrador de Inventarios</span>
                <button type="button">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">

        <nav class="migas-pan" aria-label="Migas de pan">
            <a href="{{ route('admin.gestion-accesorios') }}">Accesorios</a>
            <span aria-hidden="true">/</span>
            <span>Registrar accesorio</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Registrar nuevo accesorio</h1>
            <p>Agregue un nuevo elemento al catálogo de extras para las rentas.</p>
        </header>

        <form action="#" method="post" class="secciones-formulario">
            <fieldset class="tarjeta-formulario">
                <legend>Datos del Accesorio</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="nombre">Nombre del Accesorio</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: GPS" maxlength="100" required>
                    </div>
                    <div class="campo">
                        <label for="precio_unitario">Precio Unitario Diario ($)</label>
                        <input type="number" id="precio_unitario" name="precio_unitario" placeholder="Ej: 15.50" step="0.01" min="0" required>
                    </div>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <button type="button" class="boton boton-secundario" onclick="window.history.back();">Cancelar</button>
                <button type="submit" class="boton boton-primario">Guardar accesorio</button>
            </div>
        </form>

    </main>

</body>
</html>