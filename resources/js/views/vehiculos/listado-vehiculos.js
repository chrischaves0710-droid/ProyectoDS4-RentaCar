
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

const api = new ApiClient('/api');

const estado = new AppState({
    data: [],
    pagination: {},
    loading: false,
    error: null,
    paginaActual: 1,
    busqueda: '',
    categoriaId: '',
    estadoId: ''
});

const tabla = document.querySelector('.contenedor-tabla');
const paginacion = document.querySelector('.paginacion');
const formularioFiltros = document.querySelector('.formulario-filtros');

const busqueda = document.getElementById('buscar-gestion');
const categoria = document.getElementById('categoria-gestion');
const filtroEstado = document.getElementById('estado-gestion');

const resumen = document.querySelector('.encabezado-listado p');

const esAdministrador = location.pathname.startsWith('/lab8/admin/');

const rutaEditar = esAdministrador
    ? '/lab8/admin/vehiculos/editar'
    : '/lab8/gerente/vehiculos/editar';

let temporizador = null;
let solicitudActual = null;
let numeroSolicitud = 0;

// Evitar que los datos de la API se interpreten como HTML
function textoSeguro(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[caracter]);
}

// Obtener el nombre de categoría o estado
function nombreRelacion(relacion) {
    return typeof relacion === 'string'
        ? relacion
        : (relacion?.nombre ?? 'Sin especificar');
}

// Actualizar la interfaz cuando cambia el estado
estado.subscribe((datos) => {

    if (!tabla || !paginacion) return;

    // Estado de carga
    if (datos.loading) {
        tabla.innerHTML = `
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando vehículos...
            </p>`;

        paginacion.innerHTML = '';
        return;
    }

    // Estado de error con reintento
    if (datos.error) {

        tabla.innerHTML = `
            <div style="padding:2rem;text-align:center">
                <p role="alert">
                    ${textoSeguro(datos.error)}
                </p>

                <button type="button"
                        id="reintentar-vehiculos"
                        class="boton boton-secundario">
                    Reintentar
                </button>
            </div>`;

        paginacion.innerHTML = '';

        document.getElementById('reintentar-vehiculos')
            ?.addEventListener('click', () => {
                cargarVehiculos(datos.paginaActual);
            });

        return;
    }

    // Estado vacío
    if (datos.data.length === 0) {

        tabla.innerHTML = `
            <p style="padding:2rem;text-align:center">
                No se encontraron vehículos.
            </p>`;

        paginacion.innerHTML = '';

        if (resumen) {
            resumen.textContent = '0 resultados';
        }

        return;
    }

    // Estado con datos
    mostrarTabla(datos.data);
    mostrarPaginacion(datos.pagination);
});

// Dibujar tabla de vehículos
function mostrarTabla(vehiculos) {

    const filas = vehiculos.map(vehiculo => {

        const estadoNombre = nombreRelacion(vehiculo.estado);
        const categoriaNombre = nombreRelacion(vehiculo.categoria);

        const disponible =
            estadoNombre.toLowerCase() === 'disponible';

        const id = encodeURIComponent(String(vehiculo.id));

        return `
            <tr>
                <td>
                    ${textoSeguro(vehiculo.placa ?? vehiculo.matricula)}
                </td>

                <td>${textoSeguro(vehiculo.marca)}</td>

                <td>${textoSeguro(vehiculo.modelo)}</td>

                <td>
                    ${textoSeguro(vehiculo.anno ?? vehiculo.anio_fabricacion)}
                </td>

                <td>${textoSeguro(vehiculo.kilometraje)}</td>

                <td>${textoSeguro(categoriaNombre)}</td>

                <td>
                    <span class="estado ${disponible
                        ? 'estado-disponible'
                        : 'estado-inactivo'}">

                        ${textoSeguro(estadoNombre)}
                    </span>
                </td>

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
                Vehículos registrados
            </caption>

            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Km</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>${filas}</tbody>

        </table>`;
}

// Dibujar controles de paginación
function mostrarPaginacion(meta) {

    const actual = Number(meta.current_page ?? 1);

    const ultima = Number(
        meta.last_page ?? meta.total_pages ?? 1
    );

    paginacion.innerHTML = `
        <button type="button"
                class="boton boton-secundario"
                id="vehiculos-anterior"
                ${actual <= 1 ? 'disabled' : ''}>
            Anterior
        </button>

        <span class="pagina-actual" aria-current="page">
            ${actual} de ${ultima}
        </span>

        <button type="button"
                class="boton boton-secundario"
                id="vehiculos-siguiente"
                ${actual >= ultima ? 'disabled' : ''}>
            Siguiente
        </button>`;

    document.getElementById('vehiculos-anterior')
        ?.addEventListener('click', () => {

            if (actual > 1) {
                cargarVehiculos(actual - 1);
            }
        });

    document.getElementById('vehiculos-siguiente')
        ?.addEventListener('click', () => {

            if (actual < ultima) {
                cargarVehiculos(actual + 1);
            }
        });

    if (resumen) {
        resumen.textContent =
            `${meta.to ?? estado.state.data.length} de ` +
            `${meta.total ?? estado.state.data.length} resultados`;
    }
}

// Consultar vehículos mediante la API
async function cargarVehiculos(pagina = 1) {

    const identificador = ++numeroSolicitud;

    // Cancelar petición anterior
    solicitudActual?.abort();

    const controlador = new AbortController();

    solicitudActual = controlador;

    estado.setState({
        loading: true,
        error: null,
        paginaActual: pagina
    });

    const filtros = estado.getState();

    const parametros = new URLSearchParams({
        page: String(pagina),
        limit: '10'
    });

    // Laravel espera q y no buscar
    if (filtros.busqueda) {
        parametros.set('q', filtros.busqueda);
    }

    if (filtros.categoriaId) {
        parametros.set('categoria_id', filtros.categoriaId);
    }

    if (filtros.estadoId) {
        parametros.set('estado_id', filtros.estadoId);
    }

    try {

        const respuesta = await api.get(
            `/vehiculos?${parametros}`,
            { signal: controlador.signal }
        );

        // Ignorar respuestas canceladas o antiguas
        if (
            identificador !== numeroSolicitud ||
            controlador.signal.aborted ||
            respuesta?.aborted
        ) {
            return;
        }

        estado.setState({
            data: Array.isArray(respuesta.data)
                ? respuesta.data
                : [],
            pagination: respuesta.meta ?? {
                current_page: pagina,
                last_page: 1
            },
            loading: false,
            error: null
        });

    } catch (error) {

        if (
            identificador !== numeroSolicitud ||
            controlador.signal.aborted ||
            error?.name === 'AbortError'
        ) {
            return;
        }

        if (error?.status !== 401) {
            estado.setState({
                loading: false,
                error: error?.message ??
                    'No se pudo consultar el listado.'
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

    solicitudActual?.abort();

    numeroSolicitud++;

    estado.setState({
        loading: true,
        error: null,
        busqueda: busqueda.value.trim()
    });

    temporizador = setTimeout(() => {
        cargarVehiculos(1);
    }, 400);
});

// Aplicar filtros sin recargar la página
formularioFiltros?.addEventListener('submit', evento => {

    evento.preventDefault();

    clearTimeout(temporizador);

    estado.setState({
        busqueda: busqueda?.value.trim() ?? '',
        categoriaId: categoria?.value ?? '',
        estadoId: filtroEstado?.value ?? ''
    });

    cargarVehiculos(1);
});

// Cargar opciones reales desde Laravel
async function cargarOpciones(select, endpoint, textoInicial) {

    if (!select) return;

    select.replaceChildren(new Option(textoInicial, ''));

    let pagina = 1;
    let totalPaginas = 1;

    do {

        const respuesta = await api.get(
            `${endpoint}?page=${pagina}&per_page=50`
        );

        for (const registro of respuesta.data ?? []) {
            select.add(
                new Option(registro.nombre, String(registro.id))
            );
        }

        totalPaginas = Number(
            respuesta.meta?.last_page ??
            respuesta.meta?.total_pages ??
            1
        );

        pagina++;

    } while (pagina <= totalPaginas);
}

// Inicializar los selectores de filtros
async function cargarFiltros() {

    try {

        await Promise.all([
            cargarOpciones(categoria, '/categorias', 'Todas'),
            cargarOpciones(filtroEstado, '/estados', 'Todos')
        ]);

    } catch (error) {

        if (error?.status !== 401 && formularioFiltros) {

            const mensaje = document.createElement('p');

            mensaje.setAttribute('role', 'alert');

            mensaje.textContent =
                'No se pudieron cargar las opciones de filtrado.';

            formularioFiltros.append(mensaje);
        }
    }
}

// Eliminar vehículos mediante la API
tabla?.addEventListener('click', async evento => {

    const boton = evento.target.closest(
        'button[data-eliminar-id]'
    );

    if (!boton) return;

    if (!confirm('¿Eliminar este vehículo?')) return;

    boton.disabled = true;

    try {

        await api.delete(
            `/vehiculos/${boton.dataset.eliminarId}`
        );

        const pagina = estado.getState().paginaActual;
        const cantidad = estado.getState().data.length;

        await cargarVehiculos(
            cantidad === 1 && pagina > 1
                ? pagina - 1
                : pagina
        );

    } catch (error) {

        if (error?.status !== 401) {

            alert(
                error?.message ??
                'No se pudo eliminar el vehículo.'
            );

            boton.disabled = false;
        }
    }
});

// Cargar la página inicial
cargarVehiculos(1);
cargarFiltros();
