
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

const tabla = document.querySelector('.contenedor-tabla');
const paginacion = document.querySelector('.paginacion');

if (tabla && paginacion) {
    const api = new ApiClient('/api');

    const store = new AppState({
        data: [],
        pagination: {},
        loading: false,
        error: null,
        paginaActual: 1,
        busqueda: ''
    });

    const inputBusqueda = document.getElementById('buscar-renta');
    const formFiltros = document.querySelector('.formulario-filtros');
    const selectEstado = document.getElementById('estado-renta');
    const resumen = document.querySelector('.encabezado-listado p');

    const esGestor = location.pathname.startsWith('/lab8/gestor/');

    const rutaBase = esGestor
        ? '/lab8/gestor/rentas'
        : '/lab8/gerente/rentas';

    let temporizador = null;
    let controladorActivo = null;
    let versionSolicitud = 0;

    // El modelo actual no guarda estados de renta.
    // Desactivar el filtro hasta que exista en la API.
    if (selectEstado) {
        selectEstado.value = '';
        selectEstado.disabled = true;
        selectEstado.title =
            'El filtro necesita un estado de renta en el modelo y la API.';
    }

    // Evitar interpretar datos recibidos como HTML
    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[caracter]);
    }

    // Cancelar solicitudes anteriores
    function cancelarSolicitud() {
        versionSolicitud++;
        controladorActivo?.abort();
        controladorActivo = null;
    }

    // Mostrar los datos recibidos de Laravel
    function mostrarTabla(rentas) {

        const filas = rentas.map(renta => {

            const id = encodeURIComponent(String(renta.id));

            const cliente = renta.cliente?.nombre_completo
                ?? 'Sin cliente';

            const vehiculo = [
                renta.vehiculo?.marca,
                renta.vehiculo?.modelo
            ].filter(Boolean).join(' ') || 'Sin vehículo';

            const monto = Number(renta.monto_total);

            const total = Number.isFinite(monto)
                ? monto.toFixed(2)
                : '0.00';

            const nombreEstado = typeof renta.estado === 'string'
                ? renta.estado
                : (renta.estado?.nombre ?? 'No registrado');

            return `
                <tr>
                    <td>
                        <strong>RN-${escaparHTML(renta.id)}</strong>
                    </td>

                    <td>${escaparHTML(cliente)}</td>

                    <td>${escaparHTML(vehiculo)}</td>

                    <td>
                        ${escaparHTML(renta.fecha_inicio)}
                        -
                        ${escaparHTML(renta.fecha_fin)}
                    </td>

                    <td>$${total}</td>

                    <td>${escaparHTML(nombreEstado)}</td>

                    <td>
                        <div class="acciones-tabla">

                            <a class="accion-tabla"
                               href="${rutaBase}/consultar?id=${id}">
                                Consultar
                            </a>

                            <a class="accion-tabla"
                               href="${rutaBase}/editar?id=${id}">
                                Editar
                            </a>

                        </div>
                    </td>
                </tr>`;
        }).join('');

        tabla.innerHTML = `
            <table class="tabla-vehiculos">

                <caption class="visualmente-oculto">
                    Contratos de alquiler
                </caption>

                <thead>
                    <tr>
                        <th>N° Contrato</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Período</th>
                        <th>Total Estimado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>${filas}</tbody>

            </table>`;
    }

    // Paginación del listado
    function mostrarPaginacion(meta) {

        const actual = Math.max(
            1,
            Number(meta.current_page) || 1
        );

        const ultima = Math.max(
            1,
            Number(meta.last_page) || 1
        );

        paginacion.innerHTML = `
            <button type="button"
                    class="boton boton-secundario"
                    data-pagina="${actual - 1}"
                    ${actual <= 1 ? 'disabled' : ''}>
                Anterior
            </button>

            <span class="pagina-actual" aria-current="page">
                ${actual} de ${ultima}
            </span>

            <button type="button"
                    class="boton boton-secundario"
                    data-pagina="${actual + 1}"
                    ${actual >= ultima ? 'disabled' : ''}>
                Siguiente
            </button>`;
    }

    // Sincronización entre estado y vista
    store.subscribe(estado => {

        // Estado de carga
        if (estado.loading) {

            tabla.innerHTML = `
                <p role="status"
                   style="padding:2rem;text-align:center">
                    Cargando rentas...
                </p>`;

            paginacion.innerHTML = '';

            if (resumen) {
                resumen.textContent = 'Cargando...';
            }

            return;
        }

        // Estado de error con reintento
        if (estado.error) {

            tabla.innerHTML = `
                <div style="padding:2rem;text-align:center">

                    <p role="alert">
                        ${escaparHTML(estado.error)}
                    </p>

                    <button type="button"
                            id="reintentar-rentas"
                            class="boton boton-secundario">
                        Reintentar
                    </button>

                </div>`;

            paginacion.innerHTML = '';

            if (resumen) {
                resumen.textContent = 'Error al consultar las rentas';
            }

            document.getElementById('reintentar-rentas')
                ?.addEventListener('click', () => {
                    cargarRentas(estado.paginaActual);
                });

            return;
        }

        // Estado vacío
        if (estado.data.length === 0) {

            tabla.innerHTML = `
                <p style="padding:2rem;text-align:center">
                    No se encontraron rentas.
                </p>`;

            paginacion.innerHTML = '';

            if (resumen) {
                resumen.textContent = '0 rentas encontradas';
            }

            return;
        }

        // Estado con datos
        mostrarTabla(estado.data);
        mostrarPaginacion(estado.pagination);

        if (resumen) {
            resumen.textContent =
                `${estado.pagination.total ?? estado.data.length} rentas encontradas`;
        }
    });

    // Consultar la API de manera asíncrona
    async function cargarRentas(pagina = 1) {

        cancelarSolicitud();

        const miVersion = versionSolicitud;
        const controlador = new AbortController();

        controladorActivo = controlador;

        store.setState({
            loading: true,
            error: null,
            paginaActual: pagina
        });

        const parametros = new URLSearchParams({
            page: String(pagina)
        });

        const busqueda = store.getState().busqueda;

        // El servicio espera q
        if (busqueda) {
            parametros.set('q', busqueda);
        }

        try {

            const respuesta = await api.get(
                `/rentas?${parametros.toString()}`,
                {
                    signal: controlador.signal
                }
            );

            if (
                miVersion !== versionSolicitud ||
                controlador.signal.aborted ||
                respuesta?.aborted
            ) {
                return;
            }

            if (!Array.isArray(respuesta?.data)) {
                throw new Error(
                    'La API devolvió un listado con formato inesperado.'
                );
            }

            store.setState({
                data: respuesta.data,

                pagination: respuesta.meta ?? {
                    current_page: pagina,
                    last_page: 1
                },

                loading: false,
                error: null
            });

        } catch (error) {

            if (
                miVersion !== versionSolicitud ||
                controlador.signal.aborted ||
                error?.name === 'AbortError'
            ) {
                return;
            }

            if (error?.status !== 401) {
                store.setState({
                    loading: false,
                    error: error?.message ??
                        'No se pudieron cargar las rentas.'
                });
            }

        } finally {

            if (controladorActivo === controlador) {
                controladorActivo = null;
            }
        }
    }

    // Búsqueda con debounce de 400 ms
    inputBusqueda?.addEventListener('input', () => {

        clearTimeout(temporizador);

        // Cancelar de inmediato la solicitud anterior
        cancelarSolicitud();

        store.setState({
            busqueda: inputBusqueda.value.trim(),
            paginaActual: 1,
            loading: true,
            error: null
        });

        temporizador = setTimeout(() => {
            cargarRentas(1);
        }, 400);
    });

    // Búsqueda desde el botón Filtrar
    formFiltros?.addEventListener('submit', evento => {

        evento.preventDefault();

        clearTimeout(temporizador);

        store.setState({
            busqueda: inputBusqueda?.value.trim() ?? ''
        });

        cargarRentas(1);
    });

    // Paginación sin recargar la página
    paginacion.addEventListener('click', evento => {

        const boton = evento.target.closest(
            'button[data-pagina]'
        );

        if (boton && !boton.disabled) {
            cargarRentas(Number(boton.dataset.pagina));
        }
    });

    // Cargar registros iniciales
    cargarRentas(1);
}
