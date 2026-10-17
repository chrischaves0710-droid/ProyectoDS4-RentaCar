import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

// 1. Instancias de los módulos base
const api = new ApiClient('/api');
const store = new AppState({
    data: [],
    pagination: {},
    loading: false,
    error: null,
    paginaActual: 1,
    busqueda: '',
    estadoFiltro: ''
});

let debounceTimer = null;
let currentAbortController = null;

// Se seleccionan los elementos del DOM
const contenedorTabla = document.querySelector('.contenedor-tabla');
const inputBusqueda = document.getElementById('buscar-renta');
const selectEstado = document.getElementById('estado-renta');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

// 2. Suscripción al estado (renderizado reactivo)
store.subscribe((state) => {
    
    if (state.loading) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>⏳ Cargando listado de rentas...</p>
                </div>`;
        }
        return;
    }

    if (state.error) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #dc2626;">
                    <p>⚠️ ${state.error}</p>
                    <button type="button" id="btn-reintentar" class="boton boton-secundario" style="margin-top: 0.75rem;">
                        Reintentar
                    </button>
                </div>`;
            document.getElementById('btn-reintentar')?.addEventListener('click', () => cargarRentas(state.paginaActual));
        }
        return;
    }

    if (!state.data || state.data.length === 0) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>🔍 No se encontraron rentas que coincidan con los criterios de búsqueda.</p>
                </div>`;
        }
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

// 3. Se renderizan las filas y la tabla
function renderTabla(rentas) {
    if (!contenedorTabla) return;

    const filasHTML = rentas.map(renta => {
        // Se determina el color del estado
        let claseEstado = 'estado-inactivo';
        const estadoNombre = (renta.estado?.nombre || renta.estado || '').toLowerCase();
        if (estadoNombre.includes('activa') || estadoNombre.includes('curso')) {
            claseEstado = 'estado-disponible';
        }

        return `
        <tr>
            <td><strong>RN-${renta.id || 'N/A'}</strong></td>
            <td>${renta.cliente?.nombre || 'N/A'}</td>
            <td>${renta.vehiculo?.marca || ''} ${renta.vehiculo?.modelo || ''}</td>
            <td>${renta.fecha_inicio || 'N/A'} - ${renta.fecha_final || renta.fecha_fin || 'N/A'}</td>
            <td>$${parseFloat(renta.total || renta.monto_total || 0).toFixed(2)}</td>
            <td>
                <span class="estado ${claseEstado}">
                    ${renta.estado?.nombre || renta.estado || 'N/A'}
                </span>
            </td>
            <td>
                <div class="acciones-tabla">
                    <a href="/lab8/gerente/rentas/consultar?id=${renta.id}" class="accion-tabla">Consultar</a>
                    <a href="/lab8/gerente/rentas/editar?id=${renta.id}" class="accion-tabla">Editar</a>
                </div>
            </td>
        </tr>
    `}).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">
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
                ${filasHTML}
            </tbody>
        </table>`;
}

// 4. Se renderiza la paginación
function renderPaginacion(meta) {
    if (!navPaginacion || !meta || !meta.last_page) return;

    const { current_page, last_page } = meta;

    navPaginacion.innerHTML = `
        <button type="button" class="boton boton-secundario" id="btn-prev" ${current_page <= 1 ? 'disabled' : ''}>
            Anterior
        </button>
        <span class="pagina-actual">${current_page} de ${last_page}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${current_page >= last_page ? 'disabled' : ''}>
            Siguiente
        </button>
    `;

    document.getElementById('btn-prev')?.addEventListener('click', () => {
        if (current_page > 1) cargarRentas(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarRentas(current_page + 1);
    });
}

// 5. Se realiza la petición asíncrona
async function cargarRentas(pagina = 1) {
    store.setState({ loading: true, error: null });

    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    try {
        const query = new URLSearchParams({
            page: pagina,
            buscar: store.state.busqueda,
            estado: store.state.estadoFiltro
        }).toString();

        const respuesta = await api.get(`/rentas?${query}`, {
            signal: currentAbortController.signal
        });

        if (respuesta && !respuesta.aborted) {
            store.setState({
                data: respuesta.data || respuesta,
                pagination: respuesta.meta || { current_page: pagina, last_page: 1 },
                loading: false,
                paginaActual: pagina
            });
        }
    } catch (err) {
        if (err.status !== 401) {
            store.setState({
                loading: false,
                error: err.message || 'Error al conectar con el servidor.'
            });
        }
    }
}

// 6. Se configuran los listeners para filtros
inputBusqueda?.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        store.setState({ busqueda: e.target.value.trim() });
        cargarRentas(1);
    }, 400);
});

selectEstado?.addEventListener('change', (e) => {
    store.setState({ estadoFiltro: e.target.value });
    cargarRentas(1);
});

formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault();
    store.setState({
        busqueda: inputBusqueda?.value.trim() || '',
        estadoFiltro: selectEstado?.value || ''
    });
    cargarRentas(1);
});

// Se inicializa la vista
cargarRentas(1);