
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
const busqueda = document.getElementById('buscar-cliente');
const formularioFiltros = document.querySelector('.formulario-filtros');
const resumen = document.querySelector('.encabezado-listado p');

let temporizador = null;
let solicitudActual = null;
let versionSolicitud = 0;

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

// Construir el nombre completo del cliente
function nombreCompleto(cliente) {
    return [
        cliente.nombre1,
        cliente.nombre2,
        cliente.apellido1,
        cliente.apellido2
    ]
        .filter(parte =>
            parte !== null &&
            parte !== undefined &&
            parte !== ''
        )
        .join(' ');
}

// Cancelar solicitudes anteriores
function cancelarSolicitud() {
    versionSolicitud++;
    solicitudActual?.abort();
    solicitudActual = null;
}

// Mostrar la tabla de clientes
function mostrarTabla(clientes) {

    const filas = clientes.map(cliente => {

        const id = escaparHTML(cliente.id);

        return `
            <tr>
                <td>${escaparHTML(cliente.cedula)}</td>
                <td>${escaparHTML(nombreCompleto(cliente))}</td>
                <td>${escaparHTML(cliente.anno_nacimiento)}</td>
                <td>${escaparHTML(cliente.correo)}</td>
                <td>${escaparHTML(cliente.telefono)}</td>

                <td>
                    <div class="acciones-tabla">

                        <button type="button"
                                class="accion-tabla"
                                data-consultar-id="${id}">
                            Consultar
                        </button>

                        <a class="accion-tabla"
                           href="/lab8/gerente/clientes/editar?id=${encodeURIComponent(String(cliente.id))}">
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
                Directorio de clientes
            </caption>

            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre completo</th>
                    <th>Año Nac.</th>
                    <th>Correo electrónico</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>${filas}</tbody>

        </table>`;
}

// Mostrar la paginación
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
                Cargando clientes...
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = 'Cargando...';
        }

        return;
    }

    // Estado de error con opción de reintento
    if (estado.error) {

        tabla.innerHTML = `
            <div style="padding:2rem;text-align:center">

                <p role="alert">
                    ${escaparHTML(estado.error)}
                </p>

                <button type="button"
                        id="reintentar-clientes"
                        class="boton boton-secundario">
                    Reintentar
                </button>

            </div>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent =
                'No se pudo consultar el directorio';
        }

        document.getElementById('reintentar-clientes')
            ?.addEventListener('click', () => {

                cargarClientes(estado.paginaActual);
            });

        return;
    }

    // Estado vacío
    if (estado.data.length === 0) {

        tabla.innerHTML = `
            <p style="padding:2rem;text-align:center">
                No se encontraron clientes.
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = '0 clientes encontrados';
        }

        return;
    }

    // Estado con datos
    mostrarTabla(estado.data);
    mostrarPaginacion(estado.pagination);

    if (resumen) {

        const total = estado.pagination.total ??
            estado.data.length;

        resumen.textContent =
            `${total} clientes encontrados`;
    }
});

// Consultar clientes de forma asíncrona
async function cargarClientes(pagina = 1) {

    cancelarSolicitud();

    const miVersion = versionSolicitud;
    const controlador = new AbortController();

    solicitudActual = controlador;

    store.setState({
        loading: true,
        error: null,
        paginaActual: pagina
    });

    const parametros = new URLSearchParams({
        page: String(pagina),
        perPage: '10'
    });

    // Laravel espera q para la búsqueda
    const criterio = store.getState().busqueda;

    if (criterio) {
        parametros.set('q', criterio);
    }

    try {

        const respuesta = await api.get(
            `/clientes?${parametros.toString()}`,
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
                    'No se pudo consultar el directorio.'
            });
        }

    } finally {

        if (solicitudActual === controlador) {
            solicitudActual = null;
        }
    }
}

// Búsqueda con debounce y cancelación inmediata
busqueda?.addEventListener('input', () => {

    clearTimeout(temporizador);

    cancelarSolicitud();

    store.setState({
        busqueda: busqueda.value.trim(),
        paginaActual: 1,
        loading: true,
        error: null
    });

    temporizador = setTimeout(() => {
        cargarClientes(1);
    }, 400);
});

// Aplicar filtros sin recargar la página
formularioFiltros?.addEventListener('submit', evento => {

    evento.preventDefault();

    clearTimeout(temporizador);

    store.setState({
        busqueda: busqueda?.value.trim() ?? ''
    });

    cargarClientes(1);
});

// Cambiar de página
paginacion?.addEventListener('click', evento => {

    const boton = evento.target.closest(
        'button[data-pagina]'
    );

    if (boton && !boton.disabled) {
        cargarClientes(Number(boton.dataset.pagina));
    }
});

// Consultar los datos completos de un cliente
async function consultarCliente(id) {

    try {

        const respuesta = await api.get(
            `/clientes/${encodeURIComponent(id)}`
        );

        const cliente = respuesta.data ?? respuesta;

        const detalle = document.createElement('dialog');

        detalle.style.cssText =
            'max-width:450px;width:90%;padding:1.5rem;' +
            'border:1px solid #cbd5e1;border-radius:8px;';

        const titulo = document.createElement('h2');
        titulo.textContent = 'Datos del cliente';

        detalle.append(titulo);

        const campos = {
            'Cédula': cliente.cedula,
            'Nombre': nombreCompleto(cliente),
            'Año de nacimiento': cliente.anno_nacimiento,
            'Correo': cliente.correo,
            'Teléfono': cliente.telefono
        };

        for (const [etiqueta, valor] of Object.entries(campos)) {

            const parrafo = document.createElement('p');

            parrafo.textContent =
                `${etiqueta}: ${valor ?? 'N/A'}`;

            detalle.append(parrafo);
        }

        const cerrar = document.createElement('button');

        cerrar.type = 'button';
        cerrar.className = 'boton boton-secundario';
        cerrar.textContent = 'Cerrar';

        cerrar.addEventListener('click', () => {
            detalle.close();
        });

        detalle.append(cerrar);

        detalle.addEventListener('close', () => {
            detalle.remove();
        });

        document.body.append(detalle);

        detalle.showModal();

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo consultar el cliente.'
            );
        }
    }
}

// Acciones de consultar y eliminar
tabla?.addEventListener('click', async evento => {

    const consultar = evento.target.closest(
        'button[data-consultar-id]'
    );

    if (consultar) {

        await consultarCliente(
            consultar.dataset.consultarId
        );

        return;
    }

    const eliminar = evento.target.closest(
        'button[data-eliminar-id]'
    );

    if (!eliminar) return;

    if (!confirm(
        '¿Está seguro de que desea eliminar este cliente?'
    )) {
        return;
    }

    eliminar.disabled = true;

    try {

        await api.delete(
            `/clientes/${encodeURIComponent(eliminar.dataset.eliminarId)}`
        );

        const { paginaActual, data } = store.getState();

        const paginaDestino =
            data.length === 1 && paginaActual > 1
                ? paginaActual - 1
                : paginaActual;

        await cargarClientes(paginaDestino);

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo eliminar el cliente.'
            );

            eliminar.disabled = false;
        }
    }
});

// Corregir el enlace Registrar cliente
const enlaceRegistrar = document.querySelector('.boton-registrar');

if (enlaceRegistrar) {
    enlaceRegistrar.href =
        '/lab8/gerente/clientes/registrar';
}

// Mensaje de restricciones de eliminación
const advertencia = document.getElementById(
    'mensaje-eliminacion-cliente'
);

if (advertencia) {
    advertencia.textContent =
        'Los clientes con rentas asociadas no se pueden eliminar.';
}

// Cargar el listado inicial
if (tabla && paginacion) {
    cargarClientes(1);
}
