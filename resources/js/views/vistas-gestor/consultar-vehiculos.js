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

const tbodyVehiculos = document.getElementById('cuerpo-tabla-vehiculos');
const inputBusqueda = document.getElementById('buscar-vehiculo');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

store.subscribe((state) => {
    if (state.loading) {
        if (tbodyVehiculos) tbodyVehiculos.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: #64748b;">⏳ Cargando directorio de vehículos...</td></tr>`;
        return;
    }

    if (state.error) {
        if (tbodyVehiculos) tbodyVehiculos.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: #dc2626;">⚠️ ${state.error}</td></tr>`;
        return;
    }

    if (!state.data || state.data.length === 0) {
        if (tbodyVehiculos) tbodyVehiculos.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: #64748b;">🔍 No se encontraron vehículos con esos criterios.</td></tr>`;
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

function renderTabla(vehiculos) {
    if (!tbodyVehiculos) return;

    const filasHTML = vehiculos.map(v => {
        let claseEstado = 'estado-disponible';
        let textoEstado = 'Disponible';
        
        if (v.estado && v.estado.nombre) {
            textoEstado = v.estado.nombre;
            const estadoMin = textoEstado.toLowerCase();
            if (estadoMin.includes('alquilado') || estadoMin.includes('curso')) claseEstado = 'estado-alquilado';
            if (estadoMin.includes('mantenimiento') || estadoMin.includes('inactivo')) claseEstado = 'estado-mantenimiento';
        }

        const categoriaNombre = v.categoria ? v.categoria.nombre : 'General';
        const kilometraje = v.kilometraje ? v.kilometraje.toLocaleString() : '0';

        return `
            <tr>
                <td><strong>${v.placa || v.id}</strong></td>
                <td>${v.marca || ''} ${v.modelo || ''} <span style="color:#64748b; font-size: 0.85em;">(${v.anio || '-'})</span></td>
                <td>${categoriaNombre}</td>
                <td>${kilometraje} km</td>
                <td><span class="estado ${claseEstado}">${textoEstado}</span></td>
            </tr>
        `;
    }).join('');

    tbodyVehiculos.innerHTML = filasHTML;
}

function renderPaginacion(meta) {
    if (!navPaginacion || !meta || !meta.last_page) return;
    const { current_page, last_page } = meta;

    navPaginacion.innerHTML = `
        <button type="button" class="boton boton-secundario" id="btn-prev" ${current_page <= 1 ? 'disabled' : ''}>Anterior</button>
        <span class="pagina-actual">Página ${current_page} de ${last_page}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${current_page >= last_page ? 'disabled' : ''}>Siguiente</button>
    `;

    document.getElementById('btn-prev')?.addEventListener('click', () => { if (current_page > 1) cargarVehiculos(current_page - 1); });
    document.getElementById('btn-next')?.addEventListener('click', () => { if (current_page < last_page) cargarVehiculos(current_page + 1); });
}

async function cargarVehiculos(pagina = 1) {
    store.setState({ loading: true, error: null });
    if (currentAbortController) currentAbortController.abort();
    currentAbortController = new AbortController();

    try {
        const queryParams = new URLSearchParams({ page: pagina });
        if (store.state.busqueda) queryParams.append('buscar', store.state.busqueda);

        const respuesta = await api.get(`/vehiculos?${queryParams.toString()}`, { signal: currentAbortController.signal });

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
    debounceTimer = setTimeout(() => { store.setState({ busqueda: e.target.value.trim() }); cargarVehiculos(1); }, 400);
});
formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault(); store.setState({ busqueda: inputBusqueda?.value.trim() || '' }); cargarVehiculos(1);
});
cargarVehiculos(1);