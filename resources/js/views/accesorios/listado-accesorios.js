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
const contenedorTabla = document.querySelector('.contenedor-tabla');
const inputBusqueda = document.getElementById('buscar-accesorio');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

// 2. Suscripción al estado (renderizado reactivo)
store.subscribe((state) => {
    
    // Se maneja el estado de carga
    if (state.loading) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>⏳ Cargando catálogo de accesorios...</p>
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
            document.getElementById('btn-reintentar')?.addEventListener('click', () => cargarAccesorios(state.paginaActual));
        }
        return;
    }

    // Se maneja el estado vacío
    if (!state.data || state.data.length === 0) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>🔍 No se encontraron accesorios que coincidan con la búsqueda.</p>
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
function renderTabla(accesorios) {
    if (!contenedorTabla) return;

    // Se determina dinámicamente la ruta de edición según el rol en la URL
    const baseUrl = window.location.pathname.includes('admin') 
        ? '/lab8/admin/accesorios/editar' 
        : '/lab8/gerente/accesorios/editar';

    const filasHTML = accesorios.map(a => `
        <tr>
            <td><strong>${a.id}</strong></td>
            <td>${a.nombre || 'N/A'}</td>
            <td>$${parseFloat(a.precio_unitario || 0).toFixed(2)}</td>
            <td>
                <div class="acciones-tabla">
                    <a href="${baseUrl}?id=${a.id}" class="accion-tabla">Editar</a>
                    <button 
                        type="button" 
                        class="accion-tabla accion-eliminar" 
                        onclick="eliminarAccesorio(${a.id})"
                    >
                        Eliminar
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">
            <caption class="visualmente-oculto">Accesorios registrados en el sistema</caption>
            <thead>
                <tr>
                    <th scope="col" style="width: 10%;">ID</th>
                    <th scope="col">Nombre del Accesorio</th>
                    <th scope="col">Precio Unitario Diario</th>
                    <th scope="col" style="width: 25%;">Acciones</th>
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
        if (current_page > 1) cargarAccesorios(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarAccesorios(current_page + 1);
    });
}

// 5. Se realiza la petición asíncrona con AbortController
async function cargarAccesorios(pagina = 1) {
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

        const respuesta = await api.get(`/accesorios?${query}`, {
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
        cargarAccesorios(1);
    }, 400);
});

formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault();
    store.setState({ busqueda: inputBusqueda?.value.trim() || '' });
    cargarAccesorios(1);
});

// Función auxiliar para eliminar accesorio
window.eliminarAccesorio = async function(id) {
    if (!confirm('¿Está seguro de que desea eliminar este accesorio?')) return;
    
    try {
        await api.delete(`/accesorios/${id}`);
        alert('Accesorio eliminado correctamente.');
        cargarAccesorios(store.state.paginaActual);
    } catch (error) {
        if (error.status !== 401) {
            alert('Error al eliminar: ' + (error.message || 'El accesorio no puede ser eliminado.'));
        }
    }
};

// Se inicializa la vista al cargar la página
cargarAccesorios(1);