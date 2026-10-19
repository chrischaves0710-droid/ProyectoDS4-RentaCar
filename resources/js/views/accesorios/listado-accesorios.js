
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
const busqueda = document.getElementById('buscar-accesorio');
const filtros = document.querySelector('.formulario-filtros');
const resumen = document.querySelector('.encabezado-listado p');

const esAdmin = location.pathname.startsWith('/lab8/admin/');

const rutaEditar = esAdmin
    ? '/lab8/admin/accesorios/editar'
    : '/lab8/gerente/accesorios/editar';

let temporizador = null;
let solicitud = null;
let version = 0;

// Evitar que los datos recibidos se interpreten como HTML
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
    version++;
    solicitud?.abort();
    solicitud = null;
}

// Mostrar los accesorios en la tabla
function mostrarTabla(accesorios) {

    const filas = accesorios.map(accesorio => {

        const id = encodeURIComponent(String(accesorio.id));
        const precio = Number(accesorio.precio_unitario);

        const precioTexto = Number.isFinite(precio)
            ? precio.toFixed(2)
            : '0.00';

        return `
            <tr>
                <td>
                    <strong>${escaparHTML(accesorio.id)}</strong>
                </td>

                <td>${escaparHTML(accesorio.nombre)}</td>

                <td>$${precioTexto}</td>

                <td>
                    <div class="acciones-tabla">

                        <a class="accion-tabla"
                           href="${rutaEditar}?id=${id}">
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

    tabla.innerHTML = `
        <table class="tabla-vehiculos">

            <caption class="visualmente-oculto">
                Accesorios registrados
            </caption>

            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del accesorio</th>
                    <th scope="col">Precio unitario diario</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>

            <tbody>${filas}</tbody>

        </table>`;
}

// Mostrar los botones de paginación
function mostrarPaginacion(meta) {

    const actual = Math.max(
        1,
        Number(meta.current_page) || 1
    );

    const ultima = Math.max(
        1,
        Number(meta.last_page ?? meta.total_pages) || 1
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

// Sincronizar el estado con la interfaz
store.subscribe(estado => {

    if (!tabla || !paginacion) return;

    // Estado de carga
    if (estado.loading) {

        tabla.innerHTML = `
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando accesorios...
            </p>`;

        paginacion.innerHTML = '';
        return;
    }

    // Estado de error con botón de reintento
    if (estado.error) {

        tabla.innerHTML = `
            <div style="padding:2rem;text-align:center">

                <p role="alert">
                    ${escaparHTML(estado.error)}
                </p>

                <button type="button"
                        id="reintentar-accesorios"
                        class="boton boton-secundario">
                    Reintentar
                </button>

            </div>`;

        paginacion.innerHTML = '';

        document.getElementById('reintentar-accesorios')
            ?.addEventListener('click', () => {
                cargarAccesorios(estado.paginaActual);
            });

        return;
    }

    // Estado vacío
    if (estado.data.length === 0) {

        tabla.innerHTML = `
            <p style="padding:2rem;text-align:center">
                No se encontraron accesorios.
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = '0 accesorios encontrados';
        }

        return;
    }

    // Estado con registros
    mostrarTabla(estado.data);
    mostrarPaginacion(estado.pagination);

    if (resumen) {

        resumen.textContent =
            `${estado.pagination.total ?? estado.data.length} accesorios encontrados`;
    }
});

// Obtener los accesorios desde la API
async function cargarAccesorios(pagina = 1) {

    cancelarSolicitud();

    const miVersion = version;
    const controlador = new AbortController();

    solicitud = controlador;

    store.setState({
        loading: true,
        error: null,
        paginaActual: pagina
    });

    const parametros = new URLSearchParams({
        page: String(pagina),
        per_page: '10'
    });

    // Laravel espera q para buscar
    const texto = store.getState().busqueda;

    if (texto) {
        parametros.set('q', texto);
    }

    try {

        const respuesta = await api.get(
            `/accesorios?${parametros.toString()}`,
            {
                signal: controlador.signal
            }
        );

        // Ignorar respuestas antiguas o canceladas
        if (
            miVersion !== version ||
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
            miVersion !== version ||
            controlador.signal.aborted ||
            error?.name === 'AbortError'
        ) {
            return;
        }

        if (error?.status !== 401) {

            store.setState({
                loading: false,
                error: error?.message ??
                    'No se pudieron cargar los accesorios.'
            });
        }

    } finally {

        if (solicitud === controlador) {
            solicitud = null;
        }
    }
}

// Búsqueda con debounce de 400 milisegundos
busqueda?.addEventListener('input', () => {

    clearTimeout(temporizador);

    // Cancelar inmediatamente la solicitud activa
    cancelarSolicitud();

    store.setState({
        busqueda: busqueda.value.trim(),
        paginaActual: 1,
        loading: true,
        error: null
    });

    temporizador = setTimeout(() => {
        cargarAccesorios(1);
    }, 400);
});

// Aplicar búsqueda con el botón Filtrar
filtros?.addEventListener('submit', evento => {

    evento.preventDefault();

    clearTimeout(temporizador);

    store.setState({
        busqueda: busqueda?.value.trim() ?? ''
    });

    cargarAccesorios(1);
});

// Cambiar de página
paginacion?.addEventListener('click', evento => {

    const boton = evento.target.closest(
        'button[data-pagina]'
    );

    if (boton && !boton.disabled) {
        cargarAccesorios(Number(boton.dataset.pagina));
    }
});

// Eliminar un accesorio desde la API
tabla?.addEventListener('click', async evento => {

    const boton = evento.target.closest(
        'button[data-eliminar-id]'
    );

    if (!boton) return;

    if (!confirm(
        '¿Está seguro de que desea eliminar este accesorio?'
    )) {
        return;
    }

    boton.disabled = true;

    try {

        await api.delete(
            `/accesorios/${boton.dataset.eliminarId}`
        );

        const { paginaActual, data } = store.getState();

        const destino = data.length === 1 && paginaActual > 1
            ? paginaActual - 1
            : paginaActual;

        // Actualizar sin recargar toda la página
        await cargarAccesorios(destino);

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo eliminar el accesorio.'
            );

            boton.disabled = false;
        }
    }
});

// Cargar el listado inicial
if (tabla && paginacion) {
    cargarAccesorios(1);
}
