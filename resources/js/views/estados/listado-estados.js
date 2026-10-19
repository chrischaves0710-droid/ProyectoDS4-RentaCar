
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

const tabla = document.querySelector('.contenedor-tabla');
const paginacion = document.querySelector('.paginacion');
const inputBusqueda = document.getElementById('buscar-estado');
const formularioFiltros = document.querySelector('.formulario-filtros');
const resumen = document.querySelector('.encabezado-listado p');

const esAdmin = window.location.pathname.startsWith('/lab8/admin/');

const rutaEditar = esAdmin
    ? '/lab8/admin/estados/editar'
    : '/lab8/gerente/estados/editar';

let temporizador = null;
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

// Mostrar los registros en la tabla
function mostrarTabla(estados) {
    const filas = estados.map(estado => {

        const id = String(estado.id);
        const nombre = String(estado.nombre ?? 'Sin nombre');

        const claseEstado = nombre.toLowerCase().includes('disponible')
            ? 'estado-disponible'
            : 'estado-inactivo';

        return `
            <tr>
                <td>
                    <strong>${escaparHTML(id)}</strong>
                </td>
                <td>
                    <span class="estado ${claseEstado}">
                        ${escaparHTML(nombre)}
                    </span>
                </td>
                <td>
                    <div class="acciones-tabla">
                        <a class="accion-tabla"
                           href="${rutaEditar}?id=${encodeURIComponent(id)}">
                            Editar
                        </a>

                        <button type="button"
                                class="accion-tabla accion-eliminar"
                                data-eliminar-id="${escaparHTML(id)}">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>`;
    }).join('');

    tabla.innerHTML = `
        <table class="tabla-vehiculos">
            <caption class="visualmente-oculto">
                Estados registrados en el sistema
            </caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>${filas}</tbody>
        </table>`;
}

// Paginación compatible con la API de estados
function mostrarPaginacion(meta) {

    const actual = Math.max(
        1,
        Number(meta.current_page) || 1
    );

    const ultima = Math.max(
        1,
        Number(meta.total_pages ?? meta.last_page) || 1
    );

    paginacion.innerHTML = `
        <button type="button"
                class="boton boton-secundario"
                data-pagina="${actual - 1}"
                ${actual <= 1 ? 'disabled' : ''}>
            Anterior
        </button>

        <span class="pagina-actual" aria-current="page">
            ${actual} de ${ultima}
        </span>

        <button type="button"
                class="boton boton-secundario"
                data-pagina="${actual + 1}"
                ${actual >= ultima ? 'disabled' : ''}>
            Siguiente
        </button>`;
}

// Sincronización del estado con la vista
store.subscribe(datos => {

    if (!tabla || !paginacion) return;

    // Estado de carga
    if (datos.loading) {

        tabla.innerHTML = `
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando estados...
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = 'Cargando...';
        }

        return;
    }

    // Estado de error con reintento
    if (datos.error) {

        tabla.innerHTML = `
            <div style="padding:2rem;text-align:center">
                <p role="alert">
                    ${escaparHTML(datos.error)}
                </p>

                <button type="button"
                        id="reintentar-estados"
                        class="boton boton-secundario">
                    Reintentar
                </button>
            </div>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = 'No se pudo consultar el listado';
        }

        document.getElementById('reintentar-estados')
            ?.addEventListener('click', () => {
                cargarEstados(datos.paginaActual);
            });

        return;
    }

    // Estado vacío
    if (datos.data.length === 0) {

        tabla.innerHTML = `
            <p style="padding:2rem;text-align:center">
                No se encontraron estados.
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = '0 estados encontrados';
        }

        return;
    }

    // Estado con datos
    mostrarTabla(datos.data);
    mostrarPaginacion(datos.pagination);

    if (resumen) {

        const total = datos.pagination.total_records ??
            datos.data.length;

        resumen.textContent = `${total} estados encontrados`;
    }
});

// Consumir la API mediante async/await
async function cargarEstados(pagina = 1) {

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

    // Laravel espera el parámetro nombre
    if (busqueda) {
        parametros.set('nombre', busqueda);
    }

    try {

        const respuesta = await api.get(
            `/estados?${parametros.toString()}`,
            { signal: controlador.signal }
        );

        // Ignorar respuestas antiguas o canceladas
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
                total_pages: 1
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
                    'No se pudieron cargar los estados.'
            });
        }

    } finally {

        if (controladorActivo === controlador) {
            controladorActivo = null;
        }
    }
}

// Búsqueda con debounce y cancelación inmediata
inputBusqueda?.addEventListener('input', () => {

    clearTimeout(temporizador);

    cancelarSolicitud();

    store.setState({
        busqueda: inputBusqueda.value.trim(),
        paginaActual: 1,
        loading: true,
        error: null
    });

    // Esperar 400 milisegundos
    temporizador = setTimeout(() => {
        cargarEstados(1);
    }, 400);
});

// Botón Filtrar
formularioFiltros?.addEventListener('submit', evento => {

    evento.preventDefault();

    clearTimeout(temporizador);

    store.setState({
        busqueda: inputBusqueda?.value.trim() ?? ''
    });

    cargarEstados(1);
});

// Botones de paginación
paginacion?.addEventListener('click', evento => {

    const boton = evento.target.closest('button[data-pagina]');

    if (boton && !boton.disabled) {
        cargarEstados(Number(boton.dataset.pagina));
    }
});

// Eliminar un estado utilizando la API
tabla?.addEventListener('click', async evento => {

    const boton = evento.target.closest(
        'button[data-eliminar-id]'
    );

    if (!boton) return;

    if (!confirm('¿Está seguro de que desea eliminar este estado?')) {
        return;
    }

    boton.disabled = true;

    try {

        await api.delete(
            `/estados/${encodeURIComponent(boton.dataset.eliminarId)}`
        );

        const { paginaActual, data } = store.getState();

        const nuevaPagina =
            data.length === 1 && paginaActual > 1
                ? paginaActual - 1
                : paginaActual;

        await cargarEstados(nuevaPagina);

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo eliminar el estado.'
            );

            boton.disabled = false;
        }
    }
});

// Cargar registros al abrir la pantalla
if (tabla && paginacion) {
    cargarEstados(1);
}
