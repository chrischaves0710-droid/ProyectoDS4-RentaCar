<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Renta | RentaCar</title>
    @vite(['resources/css/lab8.css', 'resources/js/views/rentas/formulario-renta.js'])
</head>
<body>

    <header class="encabezado-principal">
        <div class="barra-navegacion">
            <a href="{{ url('/') }}" class="marca">RentaCar</a>
            <nav aria-label="Navegación principal">
                <ul class="menu-principal">
                    <li><a href="{{ url('/') }}">Inicio</a></li>
                    <li><a href="{{ route('gestor.consultar-clientes') }}">Clientes</a></li>
                    <li><a href="{{ route('gestor.gestion-rentas') }}" aria-current="page">Rentas</a></li>
                    <li><a href="{{ route('gestor.consultar-vehiculos') }}">Vehículos</a></li>
                    <li><a href="{{ route('gestor.consultar-accesorios') }}">Accesorios</a></li>
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
            <a href="{{ route('gestor.gestion-rentas') }}">Rentas</a>
            <span aria-hidden="true">/</span>
            <span>Editar renta RN-1045</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Editar Contrato: RN-1045</h1>
            <p>Modifique el vehículo asignado o las fechas del contrato en curso.</p>
        </header>

        <form action="{{ route('gestor.gestion-rentas') }}" method="post" class="secciones-formulario">
            <!-- En Laravel, aquí irían @csrf y @method('PUT') -->

            <fieldset class="tarjeta-formulario">
                <legend>Asignación</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="cliente_id">Cliente Asociado</label>
                        <!-- El cliente no se puede editar una vez creado el contrato -->
                        <select id="cliente_id" name="cliente_id" disabled class="campo-deshabilitado" title="El cliente asociado no se puede modificar">
                            <option value="1" selected>109870654 - Ana María Rojas Vargas</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="vehiculo_id">Vehículo Asignado</label>
                        <select id="vehiculo_id" name="vehiculo_id" required>
                            <option value="1" selected>Toyota Yaris (Sedán) - Tarifa: $50/día</option>
                            <option value="2">Ford Escape (SUV) - Tarifa: $80/día</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="tarjeta-formulario">
                <legend>Período de Alquiler</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <!-- value en formato YYYY-MM-DDTHH:MM para inputs datetime-local -->
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="2026-10-12T08:00" required>
                    </div>

                    <div class="campo">
                        <label for="fecha_fin">Fecha de Devolución Estimada</label>
                        <input type="datetime-local" id="fecha_fin" name="fecha_fin" value="2026-10-15T17:00" required>
                    </div>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <a href="{{ route('gestor.gestion-rentas') }}" class="boton boton-secundario" style="text-align: center; text-decoration: none;">
                    Cancelar
                </a>
                <button type="submit" class="boton boton-primario">Actualizar Contrato</button>
            </div>

        </form>

    </main>

</body>
</html>