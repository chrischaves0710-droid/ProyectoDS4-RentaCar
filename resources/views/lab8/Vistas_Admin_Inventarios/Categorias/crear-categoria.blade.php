<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar categoría | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/categorias/formulario-categoria.js'])
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

        <nav class="migas-pan" aria-label="Migas de pan">
            <a href="#">Categorías</a>
            <span aria-hidden="true">/</span>
            <span>Registrar categoría</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Registrar nueva categoría</h1>
            <p>Ingrese el nombre de la nueva clasificación para los vehículos.</p>
        </header>

        <form action="#" method="post" class="secciones-formulario">
            <fieldset class="tarjeta-formulario">
                <legend>Datos de la Categoría</legend>
                <div class="campo">
                    <label for="nombre">Nombre de la Categoría</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Compacto, Deportivo, Pick-up" required>
                    <span id="error-nombre" class="mensaje-error" style="display: none;"></span>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <button type="button" class="boton boton-secundario" onclick="window.history.back();">Cancelar</button>
                <button type="submit" class="boton boton-primario">Guardar categoría</button>
            </div>
        </form>

    </main>

</body>
</html>