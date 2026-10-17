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
    busqueda: '',
    categoria: '',
    disponibilidad: ''
});

let debounceTimer = null;
let currentAbortController = null;

// Se seleccionan los elementos del DOM
const contenedorCatalogo = document.querySelector('.catalogo-vehiculos');
const inputBusqueda = document.getElementById('buscar-vehiculo');
const selectCategoria = document.getElementById('categoria');
const selectDisponibilidad = document.getElementById('disponibilidad');
const navPaginacion = document.querySelector('.paginacion');

// Se suscribe la vista a los cambios de estado
store.subscribe((state) => {
    if (state.loading) {
        if (contenedorCatalogo) {
            contenedorCatalogo.innerHTML = `<div style="text-align: center; width: 100%; padding: 3rem; color: #64748b;">⏳ Cargando catálogo de vehículos...</div>`;
        }
        return;
    }

    if (state.error) {
        if (contenedorCatalogo) {
            contenedorCatalogo.innerHTML = `<div style="text-align: center; width: 100%; color: #dc2626; padding: 3rem;">⚠️ ${state.error}</div>`;
        }
        return;
    }

    if (!state.data || state.data.length === 0) {
        if (contenedorCatalogo) {
            contenedorCatalogo.innerHTML = `<div style="text-align: center; width: 100%; color: #64748b; padding: 3rem;">🔍 No se encontraron vehículos con estos filtros.</div>`;
        }
        if (navPaginacion) navPaginacion.innerHTML = '';
        return;
    }

    // Se inyectan las tarjetas y la paginación
    renderCatalogo(state.data);
    renderPaginacion(state.pagination);
});

// Se renderizan las tarjetas de los vehículos en modo consulta (solo lectura)
function renderCatalogo(vehiculos) {
    if (!contenedorCatalogo) return;

    const tituloHtml = `<h2 id="titulo-catalogo" class="visualmente-oculto">Listado de vehículos</h2>`;

    const tarjetasHTML = vehiculos.map(v => {
        let claseEstado = 'estado-disponible';
        let textoEstado = 'Disponible';
        
        if (v.estado && v.estado.nombre) {
            textoEstado = v.estado.nombre;
            const estadoMinuscula = textoEstado.toLowerCase();
            
            if (estadoMinuscula.includes('alquilado') || estadoMinuscula.includes('curso')) {
                claseEstado = 'estado-alquilado';
            } else if (estadoMinuscula.includes('mantenimiento') || estadoMinuscula.includes('inactivo')) {
                claseEstado = 'estado-mantenimiento';
            }
        }

        const categoriaNombre = v.categoria ? v.categoria.nombre : 'General';
        const kilometraje = v.kilometraje ? v.kilometraje.toLocaleString() : '0';

        return `
            <article class="tarjeta-vehiculo">
                <div class="imagen-vehiculo" aria-hidden="true">🚗</div>
                <div class="contenido-tarjeta-vehiculo">
                    <div class="encabezado-tarjeta-vehiculo">
                        <h3>${v.marca || ''} ${v.modelo || ''}</h3>
                        <span class="estado ${claseEstado}">${textoEstado}</span>
                    </div>
                    <p class="datos-catalogo">${v.anio || ''} · ${categoriaNombre} · ${kilometraje} km</p>
                    <a href="#" aria-label="Ver detalles de ${v.marca || ''} ${v.modelo || ''}" class="boton boton-secundario boton-detalle">Ver detalles</a>
                </div>
            </article>
        `;
    }).join('');

    contenedorCatalogo.innerHTML = tituloHtml + tarjetasHTML;
}

// Se actualizan los controles de paginación
function renderPaginacion(meta) {
    if (!navPaginacion || !meta || !meta.last_page) return;

    const { current_page, last_page } = meta;

    navPaginacion.innerHTML = `
        <button type="button" class="boton boton-secundario" id="btn-prev" ${current_page <= 1 ? 'disabled' : ''}>Anterior</button>
        <span aria-current="page">Página ${current_page} de ${last_page}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${current_page >= last_page ? 'disabled' : ''}>Siguiente</button>
    `;

    document.getElementById('btn-prev')?.addEventListener('click', () => {
        if (current_page > 1) cargarCatalogo(current_page - 1);
    });

    document.getElementById('btn-next')?.addEventListener('click', () => {
        if (current_page < last_page) cargarCatalogo(current_page + 1);
    });
}

// Se realiza la petición a la API para obtener los vehículos filtrados
async function cargarCatalogo(pagina = 1) {
    store.setState({ loading: true, error: null });

    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    try {
        const queryParams = { page: pagina };
        if (store.state.busqueda) queryParams.buscar = store.state.busqueda;
        if (store.state.categoria) queryParams.categoria_id = store.state.categoria;
        if (store.state.disponibilidad) queryParams.estado_id = store.state.disponibilidad;

        const query = new URLSearchParams(queryParams).toString();

        const respuesta = await api.get(`/vehiculos?${query}`, { signal: currentAbortController.signal });

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

// Se configuran los escuchadores de eventos para la barra de búsqueda y los selectores
inputBusqueda?.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        store.setState({ busqueda: e.target.value.trim() });
        cargarCatalogo(1);
    }, 400);
});

selectCategoria?.addEventListener('change', (e) => {
    store.setState({ categoria: e.target.value });
    cargarCatalogo(1);
});

selectDisponibilidad?.addEventListener('change', (e) => {
    store.setState({ disponibilidad: e.target.value });
    cargarCatalogo(1);
});

// Se inicializa el listado al cargar la vista
cargarCatalogo(1);