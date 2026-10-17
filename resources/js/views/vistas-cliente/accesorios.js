import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

// Se instancian los módulos base para la conexión y el estado
const api = new ApiClient('/api');
const store = new AppState({
    data: [],
    pagination: {},
    loading: false,
    error: null,
    paginaActual: 1,
    busqueda: ''
});

let debounceTimer = null;
let currentAbortController = null;

// Se seleccionan los elementos del DOM
const tbodyAccesorios = document.getElementById('cuerpo-tabla-accesorios');
const inputBusqueda = document.getElementById('buscar-accesorio');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

// Se suscribe la vista a los cambios de estado
store.subscribe((state) => {
    
    // Se maneja el estado de carga
    if (state.loading) {
        if (tbodyAccesorios) {
            tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #64748b;">⏳ Cargando catálogo de accesorios...</td></tr>`;
        }
        return;
    }

    // Se maneja el estado de error
    if (state.error) {
        if (tbodyAccesorios) {
            tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #dc2626;">⚠️ ${state.error}</td></tr>`;
        }
        return;
    }

    // Se maneja el estado vacío
    if (!state.data || state.data.length === 0) {
        if (tbodyAccesorios) {
            tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #64748b;">🔍 No se encontraron accesorios con esos criterios de búsqueda.</td></tr>`;
        }
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    // Se inyectan las filas y la paginación
    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

// Se renderiza la tabla en modo solo lectura (sin acciones)
function renderTabla(accesorios) {
    if (!tbodyAccesorios) return;

    const filasHTML = accesorios.map(a => `
        <tr>
            <td><strong>ACC-${a.id}</strong></td>
            <td>${a.nombre || 'Accesorio sin nombre'}</td>
            <td><strong>$${parseFloat(a.precio_unitario || 0).toFixed(2)}</strong> <span style="color: #64748b; font-size: 0.875rem;">/ día</span></td>
        </tr>
    `).join('');

    tbodyAccesorios.innerHTML = filasHTML;
}

// Se actualizan los controles de paginación
function renderPaginacion(meta) {
    if (!navPaginacion || !meta || !meta.last_page) return;

    const { current_page, last_page } = meta;

    navPaginacion.innerHTML = `
        <button type="button" class="boton boton-secundario" id="btn-prev" ${current_page <= 1 ? 'disabled' : ''}>Anterior</button>
        <span class="pagina-actual">Página ${current_page} de ${last_page}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${current_page >= last_page ? 'disabled' : ''}>Siguiente</button>
    `;

    document.getElementById('btn-prev')?.addEventListener('click', () => {
        if (current_page > 1) cargarAccesorios(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarAccesorios(current_page + 1);
    });
}

// Se realiza la petición a la API para obtener los accesorios filtrados
async function cargarAccesorios(pagina = 1) {
    store.setState({ loading: true, error: null });

    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    try {
        const queryParams = new URLSearchParams({ page: pagina });
        if (store.state.busqueda) queryParams.append('buscar', store.state.busqueda);

        const respuesta = await api.get(`/accesorios?${queryParams.toString()}`, { 
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
            store.setState({ loading: false, error: err.message || 'Error al conectar con el servidor.' });
        }
    }
}

// Se configuran los escuchadores de eventos para la barra de búsqueda
inputBusqueda?.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        store.setState({ busqueda: e.target.value.trim() });
        cargarAccesorios(1);
    }, 400);
});

formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault();
    store.setState({ busqueda: inputBusqueda?.value.trim() || '' });
    cargarAccesorios(1);
});

// Se inicializa el listado al cargar la vista
cargarAccesorios(1);