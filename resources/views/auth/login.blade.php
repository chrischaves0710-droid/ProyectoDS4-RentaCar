
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Acceso al sistema | RentaCar</title>

    @vite([
        'resources/css/lab8.css',
        'resources/js/views/auth/login.js'
    ])

    <style>
        .contenedor-auth {
            width: 100%;
            max-width: 32rem;
            margin: 2rem auto;
        }

        .opciones-auth {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .opcion-auth {
            padding: 0.8rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            cursor: pointer;
        }

        .opcion-auth[aria-selected="true"] {
            background: #1e40af;
            color: #ffffff;
            border-color: #1e40af;
        }

        .panel-auth[hidden] {
            display: none !important;
        }

        .campos-auth {
            display: grid;
            gap: 1rem;
        }

        .mensaje-auth {
            margin-top: 1rem;
            font-weight: 500;
        }

        .mensaje-auth:empty {
            display: none;
        }

        .contenedor-auth .acciones-formulario {
            margin-top: 1rem;
        }
    </style>
</head>

<body>

    <!-- Encabezado de RentaCar -->
    <header class="encabezado-principal">

        <div class="barra-navegacion">

            <a href="{{ url('/') }}" class="marca">
                RentaCar
            </a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">

                    <li>
                        <a href="{{ url('/') }}">
                            Inicio
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('login') }}"
                           aria-current="page">
                            Mi cuenta
                        </a>
                    </li>

                </ul>
            </nav>

        </div>

    </header>

    <main class="contenido-principal">

        <div class="contenedor-auth">

            <header class="encabezado-pagina">

                <h1>Bienvenido a RentaCar</h1>

                <p>
                    Inicie sesión o cree una cuenta
                    para utilizar nuestro sistema.
                </p>

            </header>

            <!-- Opciones de autenticación -->
            <div class="opciones-auth"
                 role="tablist"
                 aria-label="Opciones de acceso">

                <button type="button"
                        id="tab-login"
                        class="opcion-auth"
                        data-auth-tab="login"
                        role="tab"
                        aria-selected="true"
                        aria-controls="panel-login">

                    Iniciar sesión

                </button>

                <button type="button"
                        id="tab-registro"
                        class="opcion-auth"
                        data-auth-tab="registro"
                        role="tab"
                        aria-selected="false"
                        aria-controls="panel-registro">

                    Registrarse

                </button>

            </div>

            <!-- FORMULARIO DE INICIO DE SESIÓN -->
            <section id="panel-login"
                     class="panel-auth"
                     role="tabpanel"
                     aria-labelledby="tab-login">

                <form id="form-login"
                      class="secciones-formulario"
                      method="post"
                      novalidate>

                    <fieldset class="tarjeta-formulario">

                        <legend>
                            Iniciar sesión
                        </legend>

                        <div class="campos-auth">

                            <div class="campo">

                                <label for="login-email">
                                    Correo electrónico
                                </label>

                                <input type="email"
                                       id="login-email"
                                       name="email"
                                       placeholder="ejemplo@correo.com"
                                       autocomplete="username"
                                       required>

                                <span id="error-login-email"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                            <div class="campo">

                                <label for="login-password">
                                    Contraseña
                                </label>

                                <input type="password"
                                       id="login-password"
                                       name="password"
                                       autocomplete="current-password"
                                       required>

                                <span id="error-login-password"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                        </div>

                    </fieldset>

                    <div id="mensaje-login"
                         class="mensaje-auth"
                         role="status"
                         aria-live="polite"></div>

                    <div class="acciones-formulario">

                        <button type="submit"
                                id="btn-login"
                                class="boton boton-primario">

                            Iniciar sesión

                        </button>

                    </div>

                </form>

            </section>

            <!-- FORMULARIO DE REGISTRO -->
            <section id="panel-registro"
                     class="panel-auth"
                     role="tabpanel"
                     aria-labelledby="tab-registro"
                     hidden>

                <form id="form-registro"
                      class="secciones-formulario"
                      method="post"
                      novalidate>

                    <fieldset class="tarjeta-formulario">

                        <legend>
                            Crear cuenta
                        </legend>

                        <div class="campos-auth">

                            <div class="campo">

                                <label for="registro-name">
                                    Nombre completo
                                </label>

                                <input type="text"
                                       id="registro-name"
                                       name="name"
                                       maxlength="255"
                                       placeholder="Ingrese su nombre"
                                       autocomplete="name"
                                       required>

                                <span id="error-registro-name"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                            <div class="campo">

                                <label for="registro-email">
                                    Correo electrónico
                                </label>

                                <input type="email"
                                       id="registro-email"
                                       name="email"
                                       placeholder="ejemplo@correo.com"
                                       autocomplete="email"
                                       required>

                                <span id="error-registro-email"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                            <div class="campo">

                                <label for="registro-password">
                                    Contraseña
                                </label>

                                <input type="password"
                                       id="registro-password"
                                       name="password"
                                       minlength="12"
                                       autocomplete="new-password"
                                       required>

                                <small>
                                    Mínimo 12 caracteres, con
                                    mayúsculas, minúsculas,
                                    números y símbolos.
                                </small>

                                <span id="error-registro-password"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                            <div class="campo">

                                <label for="registro-confirmacion">
                                    Confirmar contraseña
                                </label>

                                <input type="password"
                                       id="registro-confirmacion"
                                       name="password_confirmation"
                                       minlength="12"
                                       autocomplete="new-password"
                                       required>

                                <span id="error-registro-password_confirmation"
                                      class="mensaje-error"
                                      role="alert"></span>

                            </div>

                        </div>

                    </fieldset>

                    <div id="mensaje-registro"
                         class="mensaje-auth"
                         role="status"
                         aria-live="polite"></div>

                    <div class="acciones-formulario">

                        <button type="submit"
                                id="btn-registro"
                                class="boton boton-primario">

                            Crear cuenta

                        </button>

                    </div>

                </form>

            </section>

        </div>

    </main>

</body>
</html>

