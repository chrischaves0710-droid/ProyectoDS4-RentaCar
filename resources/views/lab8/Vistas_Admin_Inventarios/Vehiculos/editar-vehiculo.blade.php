
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar vehículo | RentaCar</title>

    @vite([
        'resources/css/lab8.css',
        'resources/js/views/vehiculos/formulario-vehiculo.js'
    ])
</head>

<body>

    
<header class="encabezado-principal">
    <div class="barra-navegacion">
        <a href="{{ url('/') }}" class="marca">RentaCar</a>

        <nav aria-label="Navegación principal">
            <ul class="menu-principal">
                <li>
                    <a href="{{ url('/') }}">Inicio</a>
                </li>
                <li>
                    <a href="{{ route('admin.gestion-vehiculos') }}"
                       aria-current="page">Vehículos</a>
                </li>
                <li>
                    <a href="{{ route('admin.gestion-categorias') }}">
                        Categorías
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.gestion-estados') }}">
                        Estados
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.gestion-accesorios') }}">
                        Accesorios
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sesion">
            <span>Admin de Inventarios</span>
            <button type="button">Cerrar sesión</button>
        </div>
    </div>
</header>


    <main class="contenido-principal">

        <nav class="migas-pan">
            <a href="{{ route('admin.gestion-vehiculos') }}">
                Vehículos
            </a>

            <span>/</span>
            <span>Editar vehículo</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Editar vehículo</h1>
            <p>
                Modifique los datos del vehículo
                y guarde los cambios en el inventario.
            </p>
        </header>

        <form id="form-vehiculo"
              class="secciones-formulario"
              data-preserve-session
              method="post"
              novalidate>

            @csrf

            <input type="hidden"
                   id="id"
                   name="id"
                   value="{{ request('id') }}">

            <fieldset class="tarjeta-formulario">

                <legend>Información del vehículo</legend>

                <div class="grupo-fechas">

                    <div class="campo">
                        <label for="placa">Placa</label>

                        <input type="text"
                               id="placa"
                               name="placa"
                               maxlength="20"
                               required>

                        <span id="error-placa"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="marca">Marca</label>

                        <input type="text"
                               id="marca"
                               name="marca"
                               maxlength="50"
                               required>

                        <span id="error-marca"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="modelo">Modelo</label>

                        <input type="text"
                               id="modelo"
                               name="modelo"
                               maxlength="50"
                               required>

                        <span id="error-modelo"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="anno">Año</label>

                        <input type="number"
                               id="anno"
                               name="anno"
                               min="1900"
                               max="{{ date('Y') + 1 }}"
                               required>

                        <span id="error-anno"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="kilometraje">
                            Kilometraje
                        </label>

                        <input type="number"
                               id="kilometraje"
                               name="kilometraje"
                               min="0"
                               step="1"
                               required>

                        <span id="error-kilometraje"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="precio_diario">
                            Precio diario
                        </label>

                        <input type="number"
                               id="precio_diario"
                               name="precio_diario"
                               min="0"
                               step="0.01"
                               required>

                        <span id="error-precio_diario"
                              class="mensaje-error"></span>
                    </div>

                    <div class="campo">
                        <label for="categoria_id">
                            Categoría
                        </label>

                        <select id="categoria_id"
                                name="categoria_id"
                                required>

                            <option value="">
                                Seleccione una categoría
                            </option>

                        </select>

                        <span id="error-categoria_id"
                              class="mensaje-error"></span>
                    </div>

                </div>

            </fieldset>

            <div class="acciones-formulario">

                <a href="{{ route('admin.gestion-vehiculos') }}"
                   class="boton boton-secundario">
                    Cancelar
                </a>

                <button type="submit"
                        class="boton boton-primario">
                    Guardar cambios
                </button>

            </div>

        </form>

    </main>

</body>
</html>
