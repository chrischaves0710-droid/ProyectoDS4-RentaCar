<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catálogo de vehículos | RentaCar</title>

    @vite('resources/css/lab8.css')
</head>

<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li>
                        <a href="#" aria-current="page">Catálogo</a>
                    </li>

                    <li>
                        <a href="#">Accesorios</a>
                    </li>

                    <li>
                        <a href="#">Mis Rentas</a>
                    </li>
                </ul>
            </nav>

            <div class="sesion">
                <a href="#" class="perfil-usuario">
                    Mi perfil · Ana Rojas
                </a>

                <button type="button">
                    Cerrar sesión
                </button>
            </div>

        </div>
    </header>


    <main class="contenido-principal">

        <header class="encabezado-pagina">
            <h1>Vehículos disponibles</h1>

            <p>
                Catálogo de consulta de la flota RentaCar.
            </p>
        </header>


        <section
            class="aviso-informativo"
            aria-label="Información importante"
        >
            <strong>Solo consulta.</strong>

            <span>
                Para gestionar una renta, comuníquese con un Gestor de Rentas.
            </span>
        </section>


        <section
            class="seccion-filtros"
            aria-labelledby="titulo-filtros"
        >

            <h2 id="titulo-filtros" class="visualmente-oculto">
                Filtros del catálogo
            </h2>

            <form class="formulario-filtros" action="#" method="get">

                <div class="campo">
                    <label for="buscar-vehiculo">
                        Buscar por placa o modelo
                    </label>

                    <input
                        type="search"
                        id="buscar-vehiculo"
                        name="buscar"
                        placeholder="Ej: Corolla"
                    >
                </div>


                <div class="campo">
                    <label for="categoria">
                        Categoría
                    </label>

                    <select id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <option value="sedan">Sedán</option>
                        <option value="suv">SUV</option>
                        <option value="hatchback">Hatchback</option>
                        <option value="pickup">Pickup</option>
                        <option value="hibrido">Eléctrico / Híbrido</option>
                    </select>
                </div>


                <div class="campo">
                    <label for="disponibilidad">
                        Disponibilidad
                    </label>

                    <select
                        id="disponibilidad"
                        name="disponibilidad"
                    >
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="alquilado">Alquilado</option>
                        <option value="mantenimiento">
                            En Mantenimiento
                        </option>
                    </select>
                </div>


                <button
                    type="submit"
                    class="boton boton-primario boton-filtrar"
                >
                    Aplicar filtros
                </button>

            </form>

        </section>


        <section
            class="catalogo-vehiculos"
            aria-labelledby="titulo-catalogo"
        >

            <h2 id="titulo-catalogo" class="visualmente-oculto">
                Listado de vehículos
            </h2>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Toyota Corolla</h3>

                        <span class="estado estado-disponible">
                            Disponible
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2024 · Sedán · 12,450 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Hyundai Tucson</h3>

                        <span class="estado estado-disponible">
                            Disponible
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2023 · SUV · 28,300 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Suzuki Swift</h3>

                        <span class="estado estado-alquilado">
                            Alquilado
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2022 · Hatchback · 41,900 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Toyota Hilux</h3>

                        <span class="estado estado-disponible">
                            Disponible
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2024 · Pickup · 8,200 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Kia Niro</h3>

                        <span class="estado estado-mantenimiento">
                            En Mantenimiento
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2023 · Eléctrico / Híbrido · 15,700 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>


            <article class="tarjeta-vehiculo">

                <div
                    class="imagen-vehiculo"
                    aria-hidden="true"
                >
                    🚗
                </div>

                <div class="contenido-tarjeta-vehiculo">

                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>Nissan Versa</h3>

                        <span class="estado estado-disponible">
                            Disponible
                        </span>
                    </div>

                    <p class="datos-catalogo">
                        2021 · Sedán · 36,100 km
                    </p>

                    <a
                        href="#"
                        class="boton boton-secundario boton-detalle"
                    >
                        Ver detalles
                    </a>

                </div>

            </article>

        </section>


        <nav
            class="paginacion"
            aria-label="Paginación del catálogo"
        >

            <button
                type="button"
                class="boton boton-secundario"
                disabled
            >
                Anterior
            </button>

            <span aria-current="page">
                Página 1 de 4
            </span>

            <button
                type="button"
                class="boton boton-secundario"
            >
                Siguiente
            </button>

        </nav>

    </main>

</body>
</html>