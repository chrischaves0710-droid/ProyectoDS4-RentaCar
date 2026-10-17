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
const inputBusqueda = document.getElementById('buscar-cliente');
const formFiltros = document.querySelector('.formulario-filtros');
const navPaginacion = document.querySelector('.paginacion');

// 2. Suscripción al estado (renderizado reactivo)
store.subscribe((state) => {
    
    // Se maneja el estado de carga
    if (state.loading) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>⏳ Cargando directorio de clientes...</p>
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
            document.getElementById('btn-reintentar')?.addEventListener('click', () => cargarClientes(state.paginaActual));
        }
        return;
    }

    // Se maneja el estado vacío
    if (!state.data || state.data.length === 0) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `
                <div style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <p>🔍 No se encontraron clientes que coincidan con la búsqueda.</p>
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
function renderTabla(clientes) {
    if (!contenedorTabla) return;

    const filasHTML = clientes.map(c => `
        <tr>
            <td>${c.cedula || 'N/A'}</td>
            <td>${c.nombre || c.nombre_completo || 'N/A'}</td>
            <td>${c.anio_nacimiento || c.anio_nac || 'N/A'}</td>
            <td>${c.correo || c.email || 'N/A'}</td>
            <td>${c.telefono || 'N/A'}</td>
            <td>
                <div class="acciones-tabla">
                    <a href="/lab8/gerente/clientes/consultar?id=${c.id}" class="accion-tabla">Consultar</a>
                    <a href="/lab8/gerente/clientes/editar?id=${c.id}" class="accion-tabla">Editar</a>
                    <button 
                        type="button" 
                        class="accion-tabla accion-eliminar" 
                        ${c.tiene_rentas ? 'disabled aria-describedby="mensaje-eliminacion-cliente"' : ''}
                        onclick="eliminarCliente(${c.id})"
                    >
                        Eliminar
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">
            <thead>
                <tr>
                    <th scope="col">Cédula</th>
                    <th scope="col">Nombre Completo</th>
                    <th scope="col">Año Nac.</th>
                    <th scope="col">Correo Electrónico</th>
                    <th scope="col">Teléfono</th>
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
        if (current_page > 1) cargarClientes(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarClientes(current_page + 1);
    });
}

// 5. Se realiza la petición asíncrona con AbortController
async function cargarClientes(pagina = 1) {
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

        const respuesta = await api.get(`/clientes?${query}`, {
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
        cargarClientes(1);
    }, 400); // Se aplica un retardo de 400 ms
});

formFiltros?.addEventListener('submit', (e) => {
    e.preventDefault();
    store.setState({ busqueda: inputBusqueda?.value.trim() || '' });
    cargarClientes(1);
});

// Función auxiliar para eliminar cliente
window.eliminarCliente = async function(id) {
    if (!confirm('¿Está seguro de que desea eliminar este cliente?')) return;
    
    try {
        await api.delete(`/clientes/${id}`);
        alert('Cliente eliminado correctamente.');
        cargarClientes(store.state.paginaActual);
    } catch (error) {
        if (error.status !== 401) {
            alert('Error al eliminar: ' + (error.message || 'El cliente no puede ser eliminado.'));
        }
    }
};

// Se inicializa la vista al cargar la página
cargarClientes(1);