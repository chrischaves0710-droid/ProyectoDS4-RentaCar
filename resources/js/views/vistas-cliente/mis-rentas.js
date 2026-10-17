import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

// Se instancian los módulos base para la conexión a la API y el manejo de estado reactivo
const api = new ApiClient('/api');
const store = new AppState({
    data: [],
    pagination: {},
    loading: false,
    error: null,
    paginaActual: 1
});

let currentAbortController = null;

// Se seleccionan los elementos del DOM donde se inyectará el contenido
const contenedorTabla = document.querySelector('.contenedor-tabla');
const navPaginacion = document.querySelector('.paginacion');

// Se suscribe la vista a los cambios de estado
store.subscribe((state) => {
    
    // Se maneja el estado de carga
    if (state.loading) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `<div style="text-align: center; padding: 2.5rem; color: #64748b;">⏳ Cargando historial de rentas...</div>`;
        }
        return;
    }

    // Se maneja el estado de error
    if (state.error) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `<div style="text-align: center; padding: 2.5rem; color: #dc2626;">⚠️ ${state.error}</div>`;
        }
        return;
    }

    // Se maneja el estado vacío (cuando el cliente no tiene rentas registradas)
    if (!state.data || state.data.length === 0) {
        if (contenedorTabla) {
            contenedorTabla.innerHTML = `<div style="text-align: center; padding: 2.5rem; color: #64748b;">No se encontraron contratos de alquiler registrados a su nombre.</div>`;
        }
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    // Se inyectan las filas de la tabla y la paginación
    renderTabla(state.data);
    renderPaginacion(state.pagination);
});

// Se renderiza la tabla de historial en modo solo lectura
function renderTabla(rentas) {
    if (!contenedorTabla) return;

    const filasHTML = rentas.map(r => {
        // Se determina la clase visual según el estado general de la renta
        let claseEstado = 'estado-inactivo';
        let textoEstado = r.estado || 'Finalizada';
        
        if (textoEstado.toLowerCase().includes('curso') || textoEstado.toLowerCase() === 'activa') {
            claseEstado = 'estado-disponible';
            textoEstado = 'En curso';
        } else if (textoEstado.toLowerCase().includes('cancelada')) {
            claseEstado = 'estado-mantenimiento';
            textoEstado = 'Cancelada';
        }

        const vehiculo = r.vehiculo ? `${r.vehiculo.marca} ${r.vehiculo.modelo}` : 'Vehículo no especificado';
        const fechaInicio = r.fecha_inicio ? new Date(r.fecha_inicio).toLocaleDateString('es-CR') : 'N/A';
        const fechaFin = r.fecha_fin ? new Date(r.fecha_fin).toLocaleDateString('es-CR') : 'N/A';

        return `
            <tr>
                <td><strong>${r.numero_contrato || r.id}</strong></td>
                <td>${vehiculo}</td>
                <td>${fechaInicio} - ${fechaFin}</td>
                <td>$${parseFloat(r.total_pagar || 0).toFixed(2)}</td>
                <td>
                    <span class="estado ${claseEstado}">${textoEstado}</span>
                </td>
            </tr>
        `;
    }).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">
            <caption class="visualmente-oculto">Registro histórico de alquileres asociados a su cuenta.</caption>
            <thead>
                <tr>
                    <th scope="col">N° Contrato</th>
                    <th scope="col">Vehículo</th>
                    <th scope="col">Período</th>
                    <th scope="col">Total Abonado</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                ${filasHTML}
            </tbody>
        </table>`;
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
        if (current_page > 1) cargarHistorial(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarHistorial(current_page + 1);
    });
}

// Se realiza la petición a la API para obtener las rentas del usuario logueado
async function cargarHistorial(pagina = 1) {
    store.setState({ loading: true, error: null });

    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    try {
        // La política del backend (Policy) se encargará de filtrar y devolver únicamente las rentas del cliente
        const respuesta = await api.get(`/rentas?page=${pagina}`, { signal: currentAbortController.signal });

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

// Se inicializa la consulta del historial al cargar el script
cargarHistorial(1);