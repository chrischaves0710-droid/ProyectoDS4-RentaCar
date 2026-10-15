<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Renta | RentaCar</title>
    @vite('resources/css/lab8.css')
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
                    <li><a href="{{ route('gerente.gestion-accesorios') }}">Accesorios</a></li>
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
            <a href="{{ route('gerente.gestion-rentas') }}">Rentas</a>
            <span aria-hidden="true">/</span>
            <span>Consultar renta RN-1045</span>
        </nav>

        <header class="encabezado-pagina">
            <h1>Detalle del Contrato: RN-1045</h1>
            <p>
                <span class="estado estado-disponible">En curso</span>
            </p>
        </header>

        <div class="secciones-formulario">

            <fieldset class="tarjeta-formulario">
                <legend>Información General</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label>Cliente</label>
                        <input type="text" value="Ana María Rojas Vargas (109870654)" readonly class="campo-deshabilitado">
                    </div>
                    <div class="campo">
                        <label>Vehículo Asignado</label>
                        <input type="text" value="Toyota Yaris - Sedán (Placa: BC-1234)" readonly class="campo-deshabilitado">
                    </div>
                </div>
            </fieldset>

            <fieldset class="tarjeta-formulario">
                <legend>Período de Alquiler</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label>Fecha de Inicio</label>
                        <input type="text" value="12/10/2026 08:00 AM" readonly class="campo-deshabilitado">
                    </div>
                    <div class="campo">
                        <label>Fecha de Finalización (Estimada)</label>
                        <input type="text" value="15/10/2026 05:00 PM" readonly class="campo-deshabilitado">
                    </div>
                </div>
            </fieldset>

            <fieldset class="tarjeta-formulario">
                <legend>Desglose Económico</legend>
                <div class="grupo-fechas">
                    <div class="campo">
                        <label>Tarifa Diaria Vehículo</label>
                        <input type="text" value="$50.00" readonly class="campo-deshabilitado">
                    </div>
                    <div class="campo">
                        <label>Total Estimado (3 días)</label>
                        <input type="text" value="$150.00" readonly class="campo-deshabilitado">
                    </div>
                </div>
            </fieldset>

            <div class="acciones-formulario">
                <a href="{{ route('gerente.gestion-rentas') }}" class="boton boton-secundario" style="text-align: center; text-decoration: none;">
                    Volver al listado
                </a>
                <a href="{{ route('gerente.editar-renta') }}" class="boton boton-primario" style="text-align: center; text-decoration: none;">
                    Editar Renta
                </a>
            </div>

        </div>

    </main>

</body>
</html>