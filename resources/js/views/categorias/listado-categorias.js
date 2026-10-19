
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

const contenedorTabla = document.querySelector('.contenedor-tabla');
const navPaginacion = document.querySelector('.paginacion');
const inputBusqueda = document.getElementById('buscar-categoria');
const formFiltros = document.querySelector('.formulario-filtros');
const resumen = document.querySelector('.encabezado-listado p');

const esAdmin = window.location.pathname.startsWith('/lab8/admin/');

const rutaBase = esAdmin
    ? '/lab8/admin/categorias'
    : '/lab8/gerente/categorias';

let debounceTimer = null;
let controladorActivo = null;
let versionSolicitud = 0;

// Evitar interpretar los datos recibidos como HTML
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

// Mostrar las categorías
function mostrarTabla(categorias) {

    const filas = categorias.map(categoria => {

        const id = encodeURIComponent(String(categoria.id));

        return `
            <tr>
                <td>
                    <strong>${escaparHTML(categoria.id)}</strong>
                </td>

                <td>${escaparHTML(categoria.nombre)}</td>

                <td>
                    <div class="acciones-tabla">

                        <a class="accion-tabla"
                           href="${rutaBase}/editar?id=${id}">
                            Editar
                        </a>

                        <button type="button"
                                class="accion-tabla accion-eliminar"
                                data-eliminar-id="${id}">
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>`;
    }).join('');

    contenedorTabla.innerHTML = `
        <table class="tabla-vehiculos">

            <caption class="visualmente-oculto">
                Categorías registradas
            </caption>

            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre de la categoría</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>

            <tbody>${filas}</tbody>

        </table>`;
}

// Mostrar paginación
function mostrarPaginacion(meta) {

    const actual = Math.max(
        1,
        Number(meta.current_page) || 1
    );

    const ultima = Math.max(
        1,
        Number(meta.last_page ?? meta.total_pages) || 1
    );

    navPaginacion.innerHTML = `
        <button type="button"
                id="categoria-anterior"
                class="boton boton-secundario"
                ${actual <= 1 ? 'disabled' : ''}>
            Anterior
        </button>

        <span class="pagina-actual" aria-current="page">
            ${actual} de ${ultima}
        </span>

        <button type="button"
                id="categoria-siguiente"
                class="boton boton-secundario"
                ${actual >= ultima ? 'disabled' : ''}>
            Siguiente
        </button>`;

    document.getElementById('categoria-anterior')
        ?.addEventListener('click', () => {

            if (actual > 1) {
                cargarCategorias(actual - 1);
            }
        });

    document.getElementById('categoria-siguiente')
        ?.addEventListener('click', () => {

            if (actual < ultima) {
                cargarCategorias(actual + 1);
            }
        });
}

// Sincronizar el estado de la aplicación con la vista
store.subscribe(datos => {

    if (!contenedorTabla || !navPaginacion) return;

    // Estado de carga
    if (datos.loading) {

        contenedorTabla.innerHTML = `
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando categorías...
            </p>`;

        navPaginacion.innerHTML = '';
        return;
    }

    // Estado de error con reintento
    if (datos.error) {

        contenedorTabla.innerHTML = `
            <div style="padding:2rem;text-align:center">

                <p role="alert">
                    ${escaparHTML(datos.error)}
                </p>

                <button type="button"
                        id="categoria-reintentar"
                        class="boton boton-secundario">
                    Reintentar
                </button>

            </div>`;

        navPaginacion.innerHTML = '';

        document.getElementById('categoria-reintentar')
            ?.addEventListener('click', () => {

                cargarCategorias(datos.paginaActual);
            });

        return;
    }

    // Estado vacío
    if (datos.data.length === 0) {

        contenedorTabla.innerHTML = `
            <p style="padding:2rem;text-align:center">
                No se encontraron categorías.
            </p>`;

        navPaginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = '0 categorías encontradas';
        }

        return;
    }

    // Estado con datos
    mostrarTabla(datos.data);
    mostrarPaginacion(datos.pagination);

    if (resumen) {

        const total = datos.pagination.total ??
            datos.pagination.total_records ??
            datos.data.length;

        resumen.textContent = `${total} categorías encontradas`;
    }
});

// Consultar las categorías desde Laravel
async function cargarCategorias(pagina = 1) {

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
        page: String(pagina),
        per_page: '10'
    });

    const busqueda = store.getState().busqueda;

    // El controlador de Laravel espera q
    if (busqueda) {
        parametros.set('q', busqueda);
    }

    try {

        const respuesta = await api.get(
            `/categorias?${parametros.toString()}`,
            { signal: controlador.signal }
        );

        // Ignorar respuestas canceladas o antiguas
        if (
            miVersion !== versionSolicitud ||
            controlador.signal.aborted ||
            respuesta?.aborted
        ) {
            return;
        }

        store.setState({
            data: Array.isArray(respuesta?.data)
                ? respuesta.data
                : [],

            pagination: respuesta?.meta ?? {
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
                    'No se pudieron cargar las categorías.'
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

    clearTimeout(debounceTimer);

    // Cancelar inmediatamente la solicitud anterior
    cancelarSolicitud();

    store.setState({
        busqueda: inputBusqueda.value.trim(),
        loading: true,
        error: null
    });

    debounceTimer = setTimeout(() => {
        cargarCategorias(1);
    }, 400);
});

// Botón Filtrar
formFiltros?.addEventListener('submit', evento => {

    evento.preventDefault();
    clearTimeout(debounceTimer);

    store.setState({
        busqueda: inputBusqueda?.value.trim() ?? ''
    });

    cargarCategorias(1);
});

// Eliminar una categoría
contenedorTabla?.addEventListener('click', async evento => {

    const boton = evento.target.closest(
        'button[data-eliminar-id]'
    );

    if (!boton) return;

    if (!confirm('¿Está seguro de que desea eliminar esta categoría?')) {
        return;
    }

    boton.disabled = true;

    try {

        await api.delete(
            `/categorias/${boton.dataset.eliminarId}`
        );

        const { paginaActual, data } = store.getState();

        await cargarCategorias(
            data.length === 1 && paginaActual > 1
                ? paginaActual - 1
                : paginaActual
        );

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo eliminar la categoría.'
            );

            boton.disabled = false;
        }
    }
});

// Corregir el enlace para registrar categoría
const enlaceRegistrar = document.querySelector('.boton-registrar');

if (enlaceRegistrar) {
    enlaceRegistrar.href = `${rutaBase}/crear`;
}

// Cargar listado al abrir la página
if (contenedorTabla && navPaginacion) {
    cargarCategorias(1);
}
