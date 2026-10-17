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
    busqueda: ''
});

let debounceTimer = null;
let currentAbortController = null;

// Se seleccionan los elementos del DOM
const contenedorTabla = document.querySelector('.contenedor-tabla') || document.getElementById('contenedor-tabla');
const inputBusqueda = document.getElementById('buscar-vehiculo') || document.querySelector('input[type="search"]');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

// 2. Suscripción al estado (renderizado reactivo)
store.subscribe((state) => {
    
    // Se maneja el estado de carga
    if (state.loading) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>⏳ Cargando listado de vehículos...</p>
                </div>`;
        }
        return;
    }

    // Se maneja el estado de error con opción de reintento
    if (state.error) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #dc2626;">
                    <p>⚠️ ${state.error}</p>
                    <button type="button" id="btn-reintentar" class="boton boton-secundario" style="margin-top: 0.75rem;">
                        Reintentar
                    </button>
                </div>`;
            document.getElementById('btn-reintentar')?.addEventListener('click', () => cargarVehiculos(state.paginaActual));
        }
        return;
    }

    // Se maneja el estado vacío
    if (!state.data || state.data.length === 0) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>🔍 No se encontraron vehículos que coincidan con la búsqueda.</p>
                </div>`;
        }
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    // Se renderiza la lista poblada y la paginación
    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

// 3. Se renderizan las filas y la tabla
function renderTabla(vehiculos) {
    if (!contenedorTabla) return;

    const filasHTML = vehiculos.map(v => `
        <tr>
            <td><strong>${v.placa || 'N/A'}</strong></td>
            <td>${v.marca} ${v.modelo} (${v.anio || ''})</td>
            <td>${v.categoria?.nombre || v.categoria || 'Sedán'}</td>
            <td>$${parseFloat(v.precio_diario || 0).toFixed(2)} / día</td>
            <td>
                <span class="estado ${v.estado?.nombre === 'Disponible' ? 'estado-disponible' : 'estado-inactivo'}">
                    ${v.estado?.nombre || v.estado || 'Disponible'}
                </span>
            </td>
            <td>
                <div class="acciones-tabla">
                    <a href="/lab8/gerente/vehiculos/editar?id=${v.id}" class="accion-tabla">Editar</a>
                </div>
            </td>
        </tr>
    `).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">
            <thead>
                <tr>
                    <th scope="col">Placa</th>
                    <th scope="col">Vehículo</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Precio Diario</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                ${filasHTML}
            </tbody>
        </table>`;
}

// 4. Se renderiza la paginación funcional
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
        if (current_page > 1) cargarVehiculos(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarVehiculos(current_page + 1);
    });
}

// 5. Se realiza la petición asíncrona con AbortController
async function cargarVehiculos(pagina = 1) {
    store.setState({ loading: true, error: null });

    // Se cancela la petición anterior si aún está en curso
    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    try {
        const query = new URLSearchParams({
            page: pagina,
            buscar: store.state.busqueda
        }).toString();

        const respuesta = await api.get(`/vehiculos?${query}`, {
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

// 6. Se configuran los listeners para debounce y filtros
inputBusqueda?.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    
    debounceTimer = setTimeout(() => {
        store.setState({ busqueda: e.target.value.trim() });
        cargarVehiculos(1);
    }, 400); // Se aplica un retardo de 400 ms
});

formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault();
    store.setState({ busqueda: inputBusqueda?.value.trim() || '' });
    cargarVehiculos(1);
});

// Se inicializa la vista al cargar la página
cargarVehiculos(1);