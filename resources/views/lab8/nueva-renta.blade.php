<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nueva renta | RentaCar</title>

    @vite('resources/css/lab8.css')
</head>

<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#">Clientes</a></li>
                    <li><a href="#">Vehículos</a></li>
                    <li><a href="#">Accesorios</a></li>
                    <li><a href="#" aria-current="page">Rentas</a></li>
                </ul>
            </nav>

            <div class="sesion">
                <span>Gestor de Rentas</span>
                <button type="button">Cerrar sesión</button>
            </div>

        </div>
    </header>

    <main class="contenido-principal">

        <nav class="migas-pan" aria-label="Migas de pan">
            <a href="#">Rentas</a>
            <span aria-hidden="true">/</span>
            <span>Nueva renta</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Nueva renta</h1>
            <p>
                Complete los pasos en orden. El monto final lo calcula el sistema.
            </p>
        </header>

        <form class="formulario-renta" action="#" method="post">

            <div class="secciones-formulario">

                <fieldset class="tarjeta-formulario">
                    <legend>
                        <span class="numero-paso">1</span>
                        Cliente
                    </legend>

                    <div class="campo">
                        <label for="buscar-cliente">Buscar cliente</label>

                        <input
                            type="search"
                            id="buscar-cliente"
                            name="buscar_cliente"
                            placeholder="Ej: Rojas"
                            required
                            aria-describedby="ayuda-cliente"
                        >

                        <small id="ayuda-cliente">
                            Ingrese el nombre o identificación del cliente.
                        </small>
                    </div>

                    <div class="seleccion-actual" role="status">
                        <strong>Seleccionado:</strong>
                        Ana María Rojas Vargas · Cédula 1-0987-0654 ·
                        ana.rojas@correo.cr
                    </div>
                </fieldset>


                <fieldset class="tarjeta-formulario">
                    <legend>
                        <span class="numero-paso">2</span>
                        Vehículo
                    </legend>

                    <div class="campo-checkbox">
                        <input
                            type="checkbox"
                            id="solo-disponibles"
                            name="solo_disponibles"
                            checked
                        >

                        <label for="solo-disponibles">
                            Mostrar solo vehículos disponibles
                        </label>
                    </div>

                    <p class="etiqueta-grupo">
                        Elija un vehículo
                    </p>

                    <div class="lista-opciones">

                        <label class="opcion-vehiculo">
                            <input
                                type="radio"
                                name="vehiculo"
                                value="corolla"
                                required
                                checked
                            >

                            <span class="datos-vehiculo">
                                <strong>Toyota Corolla 2024</strong>
                                <span>BCD-123 · Sedán · 12,450 km</span>
                            </span>

                            <span class="estado estado-disponible">
                                Disponible
                            </span>
                        </label>


                        <label class="opcion-vehiculo">
                            <input
                                type="radio"
                                name="vehiculo"
                                value="tucson"
                            >

                            <span class="datos-vehiculo">
                                <strong>Hyundai Tucson 2023</strong>
                                <span>EFG-456 · SUV · 28,300 km</span>
                            </span>

                            <span class="estado estado-disponible">
                                Disponible
                            </span>
                        </label>


                        <label class="opcion-vehiculo opcion-deshabilitada">
                            <input
                                type="radio"
                                name="vehiculo"
                                value="swift"
                                disabled
                            >

                            <span class="datos-vehiculo">
                                <strong>Suzuki Swift 2022</strong>
                                <span>HIJ-789 · Hatchback · 41,900 km</span>
                            </span>

                            <span class="estado estado-alquilado">
                                Alquilado
                            </span>

                            <span class="texto-no-disponible">
                                No disponible para rentar
                            </span>
                        </label>

                    </div>
                </fieldset>


                <fieldset class="tarjeta-formulario">
                    <legend>
                        <span class="numero-paso">3</span>
                        Accesorios
                        <small>(opcional)</small>
                    </legend>

                    <div class="lista-accesorios">

                        <div class="accesorio">
                            <div class="campo-checkbox">
                                <input
                                    type="checkbox"
                                    id="gps"
                                    name="accesorios[]"
                                    value="gps"
                                    checked
                                >

                                <label for="gps">
                                    GPS Navegador · $10.00 c/u
                                </label>
                            </div>

                            <div class="cantidad">
                                <label for="cantidad-gps">Cantidad</label>

                                <input
                                    type="number"
                                    id="cantidad-gps"
                                    name="cantidad_gps"
                                    min="1"
                                    value="1"
                                >
                            </div>
                        </div>


                        <div class="accesorio">
                            <div class="campo-checkbox">
                                <input
                                    type="checkbox"
                                    id="silla-bebe"
                                    name="accesorios[]"
                                    value="silla_bebe"
                                >

                                <label for="silla-bebe">
                                    Silla de bebé para auto · $15.00 c/u
                                </label>
                            </div>

                            <div class="cantidad">
                                <label for="cantidad-silla">Cantidad</label>

                                <input
                                    type="number"
                                    id="cantidad-silla"
                                    name="cantidad_silla"
                                    min="1"
                                    value="1"
                                >
                            </div>
                        </div>


                        <div class="accesorio">
                            <div class="campo-checkbox">
                                <input
                                    type="checkbox"
                                    id="portabicicletas"
                                    name="accesorios[]"
                                    value="portabicicletas"
                                >

                                <label for="portabicicletas">
                                    Portabicicletas · $20.00 c/u
                                </label>
                            </div>

                            <div class="cantidad">
                                <label for="cantidad-portabicicletas">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    id="cantidad-portabicicletas"
                                    name="cantidad_portabicicletas"
                                    min="1"
                                    value="1"
                                >
                            </div>
                        </div>

                    </div>
                </fieldset>


                <fieldset class="tarjeta-formulario">
                    <legend>
                        <span class="numero-paso">4</span>
                        Fechas y precio
                    </legend>

                    <div class="grupo-fechas">

                        <div class="campo">
                            <label for="fecha-inicio">
                                Fecha de inicio
                            </label>

                            <input
                                type="date"
                                id="fecha-inicio"
                                name="fecha_inicio"
                                required
                            >

                            <span class="mensaje-error" aria-live="polite">
                                Seleccione una fecha de inicio válida.
                            </span>
                        </div>


                        <div class="campo">
                            <label for="fecha-final">
                                Fecha de finalización
                            </label>

                            <input
                                type="date"
                                id="fecha-final"
                                name="fecha_final"
                                required
                            >

                            <span class="mensaje-error" aria-live="polite">
                                Seleccione una fecha de finalización válida.
                            </span>
                        </div>


                        <div class="campo">
                            <label for="precio-diario">
                                Precio diario
                            </label>

                            <input
                                type="number"
                                id="precio-diario"
                                name="precio_diario"
                                value="45.00"
                                min="0"
                                step="0.01"
                                required
                            >
                        </div>

                    </div>
                </fieldset>

            </div>


            <aside class="resumen-renta" aria-labelledby="titulo-resumen">

                <h2 id="titulo-resumen">
                    <span class="numero-paso">5</span>
                    Resumen
                </h2>

                <dl class="datos-resumen">
                    <div>
                        <dt>Cliente</dt>
                        <dd>Ana María Rojas Vargas</dd>
                    </div>

                    <div>
                        <dt>Vehículo</dt>
                        <dd>Toyota Corolla · BCD-123</dd>
                    </div>

                    <div>
                        <dt>Duración</dt>
                        <dd>5 días</dd>
                    </div>

                    <div>
                        <dt>Alquiler (5 × $45.00)</dt>
                        <dd>$225.00</dd>
                    </div>

                    <div>
                        <dt>Accesorios (GPS × 1)</dt>
                        <dd>$10.00</dd>
                    </div>
                </dl>

                <div class="total-renta">
                    <span>Monto estimado</span>
                    <strong>$235.00</strong>
                </div>

                <p class="nota-resumen">
                    Estimación preliminar; el sistema calcula el total al confirmar.
                </p>

                <div class="acciones-formulario">
                    <button
                        type="button"
                        class="boton boton-secundario"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="boton boton-primario"
                    >
                        Confirmar renta
                    </button>
                </div>

            </aside>

        </form>

    </main>

</body>
</html>