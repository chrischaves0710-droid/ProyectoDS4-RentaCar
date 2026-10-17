<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/vistas-cliente/mi-perfil.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ route('cliente.catalogo') }}">Catálogo</a></li>
                    <li><a href="#">Accesorios</a></li>
                    <li><a href="{{ route('cliente.mis-rentas') }}">Mis Rentas</a></li>
                </ul>
            </nav>
            <div class="sesion">
                <a href="{{ route('cliente.mi-perfil') }}" class="perfil-usuario" aria-current="page">
                    Mi perfil
                </a>
                <button type="button" id="btn-logout">Cerrar sesión</button>
            </div>
        </div>
    </header>

    <main class="contenido-principal">

        <header class="encabezado-pagina">
            <h1>Datos de Perfil</h1>
            <p>Consulta de la información registrada en su cuenta.</p>
        </header>

        <section class="aviso-informativo" aria-label="Información importante">
            <strong>Solo lectura.</strong>
            <span>Para actualizar su correo, teléfono o contraseña, comuníquese con el administrador del sistema.</span>
        </section>

        <div class="secciones-formulario">
            <fieldset class="tarjeta-formulario">
                <legend>Información Personal</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="identificacion">Cédula / Identificación</label>
                        <input type="text" id="identificacion" readonly class="campo-deshabilitado" placeholder="Cargando...">
                    </div>
                    <div class="campo">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" readonly class="campo-deshabilitado" placeholder="Cargando...">
                    </div>
                </div>
            </fieldset>

            <fieldset class="tarjeta-formulario">
                <legend>Datos de Contacto</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" readonly class="campo-deshabilitado" placeholder="Cargando...">
                    </div>
                    <div class="campo">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" readonly class="campo-deshabilitado" placeholder="Cargando...">
                    </div>
                </div>
            </fieldset>
        </div>

    </main>

</body>
</html>