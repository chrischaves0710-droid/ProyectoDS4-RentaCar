<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de clientes | RentaCar</title>
    @vite('resources/css/lab8.css')
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="#" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="#">Inicio</a></li>
                    <li>
                        <a href="#" aria-current="page">Clientes</a>
                    </li>
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

        <div class="cabecera-gestion">
            <header class="encabezado-pagina">
                <h1>Gestión de Clientes</h1>
                <p>Directorio de clientes registrados en el sistema.</p>
            </header>

            <a href="#" class="boton boton-primario boton-registrar">
                Registrar cliente
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-clientes">
            <h2 id="titulo-filtros-clientes" class="visualmente-oculto">
                Filtros de búsqueda de clientes
            </h2>

            <!-- Formulario ajustado sin el select de estado -->
            <form class="formulario-filtros" style="grid-template-columns: minmax(0, 1fr) auto;" action="#" method="get">
                
                <div class="campo">
                    <label for="buscar-cliente">Buscar por nombre, cédula o correo</label>
                    <input 
                        type="search" 
                        id="buscar-cliente" 
                        name="buscar" 
                        placeholder="Ej: Ana Rojas o 109870654"
                    >
                </div>

                <button type="submit" class="boton boton-primario boton-filtrar" style="align-self: end;">
                    Filtrar
                </button>

            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-clientes">
            
            <div class="encabezado-listado">
                <h2 id="titulo-listado-clientes">Listado de clientes</h2>
                <p>3 de 145</p>
            </div>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de clientes. Desplácese horizontalmente si es necesario.">
                
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">
                        Clientes registrados en el sistema RentaCar
                    </caption>
                    
                    <thead>
                        <tr>
                            <th scope="col">Cédula</th>
                            <th scope="col">Nombre Completo</th>
                            <th scope="col">Año Nac.</th>
                            <th scope="col">Correo Electrónico</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <td>109870654</td>
                            <td>Ana María Rojas Vargas</td>
                            <td>1992</td>
                            <td>ana.rojas@correo.cr</td>
                            <td>88887777</td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla">Consultar</a>
                                    <a href="#" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>204560123</td>
                            <td>Carlos Alberto Mora</td>
                            <td>1985</td>
                            <td>carlos.mora@empresa.com</td>
                            <td>89990000</td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla">Consultar</a>
                                    <a href="#" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla accion-eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>301110222</td>
                            <td>Laura Solano Quirós</td>
                            <td>1998</td>
                            <td>laura.solano@correo.cr</td>
                            <td>60001111</td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="#" class="accion-tabla">Consultar</a>
                                    <a href="#" class="accion-tabla">Editar</a>
                                    <button 
                                        type="button" 
                                        class="accion-tabla accion-eliminar" 
                                        disabled
                                        aria-describedby="mensaje-eliminacion-cliente"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p id="mensaje-eliminacion-cliente" class="mensaje-advertencia">
                Eliminar deshabilitado: el cliente tiene contratos de alquiler en su historial.
            </p>

        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de clientes">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <a href="#" class="pagina-actual" aria-current="page">1</a>
            <a href="#" class="pagina-enlace">2</a>
            <a href="#" class="pagina-enlace">3</a>
            <button type="button" class="boton boton-secundario">Siguiente</button>
        </nav>

    </main>

</body>
</html>