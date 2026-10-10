<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar cliente | RentaCar</title>
    @vite('resources/css/lab8.css')
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#" aria-current="page">Clientes</a></li>
                    <li><a href="#">Rentas</a></li>
                    <li><a href="#">Vehículos</a></li>
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
            <a href="#">Clientes</a>
            <span aria-hidden="true">/</span>
            <span>Registrar cliente</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Registrar nuevo cliente</h1>
            <p>
                Complete los datos personales y de contacto para dar de alta a un cliente en el sistema.
            </p>
        </header>

        <form action="#" method="post" class="secciones-formulario">

            <fieldset class="tarjeta-formulario">
                <legend>Datos Personales</legend>

                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="cedula">Cédula</label>
                        <input type="text" id="cedula" name="cedula" placeholder="Ej: 109870654" required>
                    </div>

                    <div class="campo">
                        <label for="anno-nacimiento">Año de Nacimiento</label>
                        <input type="number" id="anno-nacimiento" name="anno_nacimiento" placeholder="Ej: 1992" min="1900" max="2008" required>
                    </div>
                </div>

                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="nombre1">Primer Nombre</label>
                        <input type="text" id="nombre1" name="nombre1" placeholder="Ej: Ana" required>
                    </div>

                    <div class="campo">
                        <label for="nombre2">Segundo Nombre (Opcional)</label>
                        <input type="text" id="nombre2" name="nombre2" placeholder="Ej: María">
                    </div>
                </div>

                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="apellido1">Primer Apellido</label>
                        <input type="text" id="apellido1" name="apellido1" placeholder="Ej: Rojas" required>
                    </div>

                    <div class="campo">
                        <label for="apellido2">Segundo Apellido (Opcional)</label>
                        <input type="text" id="apellido2" name="apellido2" placeholder="Ej: Vargas">
                    </div>
                </div>
            </fieldset>

            <fieldset class="tarjeta-formulario">
                <legend>Datos de Contacto</legend>

                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.cr" required>
                    </div>

                    <div class="campo">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Ej: 88887777" required>
                    </div>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <button type="button" class="boton boton-secundario">Cancelar</button>
                <button type="submit" class="boton boton-primario">Guardar cliente</button>
            </div>

        </form>

    </main>

</body>
</html>