<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de rentas | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/rentas/listado-rentas.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">

            <a href="{{ url('/') }}" class="marca">RentaCar</a>

            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ url('/') }}">Inicio</a></li>
                    <li><a href="{{ route('gerente.gestion-clientes') }}">Clientes</a></li>
                    <li><a href="{{ route('gerente.gestion-rentas') }}" aria-current="page">Rentas</a></li>
                    <li><a href="{{ route('gerente.gestion-vehiculos') }}">Vehículos</a></li>
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
                <h1>Gestión de Rentas</h1>
                <p>Monitoreo general de contratos de alquiler e historial de transacciones.</p>
            </header>

            <a href="{{ route('gerente.nueva-renta') }}" class="boton boton-primario boton-registrar">
                Nueva Renta
            </a>
        </div>

        <section class="seccion-filtros" aria-labelledby="titulo-filtros-rentas">
            <h2 id="titulo-filtros-rentas" class="visualmente-oculto">
                Filtros de búsqueda de rentas
            </h2>

            <form class="formulario-filtros" action="{{ route('gerente.gestion-rentas') }}" method="get">
                
                <div class="campo" style="grid-column: 1 / -2;">
                    <label for="buscar-renta">Buscar por N° Contrato o Cliente</label>
                    <input 
                        type="search" 
                        id="buscar-renta" 
                        name="buscar" 
                        placeholder="Ej: RN-1045 o Ana Rojas"
                    >
                </div>

                <div class="campo">
                    <label for="estado-renta">Estado del alquiler</label>
                    <select id="estado-renta" name="estado">
                        <option value="">Todas</option>
                        <option value="activa">En curso (Activas)</option>
                        <option value="finalizada">Finalizadas</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>

                <button type="submit" class="boton boton-primario boton-filtrar">
                    Filtrar
                </button>

            </form>
        </section>

        <section class="seccion-tabla-vehiculos" aria-labelledby="titulo-listado-rentas">
            
            <div class="encabezado-listado">
                <h2 id="titulo-listado-rentas">Listado de rentas</h2>
                <p>Mostrando 3 contratos</p>
            </div>

            <div class="contenedor-tabla" tabindex="0" aria-label="Tabla de rentas. Desplácese horizontalmente si es necesario.">
                
                <table class="tabla-vehiculos">
                    <caption class="visualmente-oculto">
                        Registro de contratos de alquiler del sistema RentaCar
                    </caption>
                    
                    <thead>
                        <tr>
                            <th scope="col">N° Contrato</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Vehículo</th>
                            <th scope="col">Período</th>
                            <th scope="col">Total Estimado</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <td><strong>RN-1045</strong></td>
                            <td>Ana María Rojas Vargas</td>
                            <td>Toyota Yaris (Sedán)</td>
                            <td>12/10/2026 - 15/10/2026</td>
                            <td>$150.00</td>
                            <td>
                                <span class="estado estado-disponible">En curso</span>
                            </td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="{{ route('gerente.consultar-renta') }}" class="accion-tabla">Consultar</a>
                                    <a href="{{ route('gerente.editar-renta') }}" class="accion-tabla">Editar</a>
                                    <button type="button" class="accion-tabla" style="color: #0056b3;">Finalizar</button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>RN-1044</strong></td>
                            <td>Carlos Alberto Mora</td>
                            <td>Ford Escape (SUV)</td>
                            <td>01/10/2026 - 05/10/2026</td>
                            <td>$320.00</td>
                            <td>
                                <span class="estado estado-inactivo" style="background-color: #e2e8f0; color: #475569;">Finalizada</span>
                            </td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="{{ route('gerente.consultar-renta') }}" class="accion-tabla">Consultar</a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><strong>RN-1043</strong></td>
                            <td>Laura Solano Quirós</td>
                            <td>Hyundai Tucson (SUV)</td>
                            <td>28/09/2026 - 30/09/2026</td>
                            <td>$0.00</td>
                            <td>
                                <span class="estado estado-inactivo">Cancelada</span>
                            </td>
                            <td>
                                <div class="acciones-tabla">
                                    <a href="{{ route('gerente.consultar-renta') }}" class="accion-tabla">Consultar</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <nav class="paginacion paginacion-numerica" aria-label="Paginación de rentas">
            <button type="button" class="boton boton-secundario" disabled>Anterior</button>
            <a href="#" class="pagina-actual" aria-current="page">1</a>
            <a href="#" class="pagina-enlace">2</a>
            <button type="button" class="boton boton-secundario">Siguiente</button>
        </nav>

    </main>

</body>
</html>