import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

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

const tbodyAccesorios = document.getElementById('cuerpo-tabla-accesorios');
const inputBusqueda = document.getElementById('buscar-accesorio');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

store.subscribe((state) => {
    if (state.loading) {
        if (tbodyAccesorios) tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #64748b;">⏳ Cargando catálogo de accesorios...</td></tr>`;
        return;
    }
    if (state.error) {
        if (tbodyAccesorios) tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #dc2626;">⚠️ ${state.error}</td></tr>`;
        return;
    }
    if (!state.data || state.data.length === 0) {
        if (tbodyAccesorios) tbodyAccesorios.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 2.5rem; color: #64748b;">🔍 No se encontraron accesorios.</td></tr>`;
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

function renderTabla(accesorios) {
    if (!tbodyAccesorios) return;

    const filasHTML = accesorios.map(a => `
        <tr>
            <td><strong>ACC-${a.id}</strong></td>
            <td>${a.nombre || 'Accesorio genérico'}</td>
            <td><strong>$${parseFloat(a.precio_unitario || 0).toFixed(2)}</strong></td>
        </tr>
    `).join('');

    tbodyAccesorios.innerHTML = filasHTML;
}

function renderPaginacion(meta) {
    if (!navPaginacion || !meta || !meta.last_page) return;
    const { current_page, last_page } = meta;

    navPaginacion.innerHTML = `
        <button type="button" class="boton boton-secundario" id="btn-prev" ${current_page <= 1 ? 'disabled' : ''}>Anterior</button>
        <span class="pagina-actual">Página ${current_page} de ${last_page}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${current_page >= last_page ? 'disabled' : ''}>Siguiente</button>
    `;

    document.getElementById('btn-prev')?.addEventListener('click', () => { if (current_page > 1) cargarAccesorios(current_page - 1); });
    document.getElementById('btn-next')?.addEventListener('click', () => { if (current_page < last_page) cargarAccesorios(current_page + 1); });
}

async function cargarAccesorios(pagina = 1) {
    store.setState({ loading: true, error: null });
    if (currentAbortController) currentAbortController.abort();
    currentAbortController = new AbortController();

    try {
        const queryParams = new URLSearchParams({ page: pagina });
        if (store.state.busqueda) queryParams.append('buscar', store.state.busqueda);

        const respuesta = await api.get(`/accesorios?${queryParams.toString()}`, { signal: currentAbortController.signal });

        if (respuesta && !respuesta.aborted) {
            store.setState({
                data: respuesta.data || respuesta,
                pagination: respuesta.meta || { current_page: pagina, last_page: 1 },
                loading: false,
                paginaActual: pagina
            });
        }
    } catch (err) {
        if (err.status !== 401) store.setState({ loading: false, error: err.message || 'Error de conexión.' });
    }
}

inputBusqueda?.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { store.setState({ busqueda: e.target.value.trim() }); cargarAccesorios(1); }, 400);
});
formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault(); store.setState({ busqueda: inputBusqueda?.value.trim() || '' }); cargarAccesorios(1);
});
cargarAccesorios(1);