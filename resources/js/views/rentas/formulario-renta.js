
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';
import { restoreForm } from '../../ui/FormRecovery.js';

const formulario = document.querySelector(
    'form.formulario-renta, form.secciones-formulario'
);

if (formulario && location.pathname.includes('/rentas/')) {

    const api = new ApiClient('/api');

    const esEdicion = location.pathname
        .replace(/\/$/, '')
        .endsWith('/editar');

    const idRenta = new URLSearchParams(
        location.search
    ).get('id');

    const rutaListado = location.pathname.startsWith('/lab8/gestor/')
        ? '/lab8/gestor/rentas'
        : '/lab8/gerente/rentas';

    const boton = formulario.querySelector(
        'button[type="submit"]'
    );

    const textoBoton = boton?.textContent.trim() || 'Guardar';

    const estado = new AppState({
        listo: false,
        guardando: false,
        errores: {},
        mensaje: 'Cargando datos...'
    });

    const aviso = document.createElement('p');
    aviso.setAttribute('role', 'status');
    formulario.prepend(aviso);

    formulario.noValidate = true;

    let clientes = [];
    let vehiculos = [];
    let accesorios = [];

    let temporizador = null;
    let controladorClientes = null;
    let versionClientes = 0;
    let rentaCreada = false;

    formulario.querySelectorAll('.mensaje-error').forEach(elemento => {
        elemento.textContent = '';
        elemento.style.display = 'none';
    });

    function campo(nombre) {

        if (nombre === 'fecha_fin') {
            return formulario.querySelector(
                '[name="fecha_fin"], [name="fecha_final"]'
            );
        }

        if (nombre === 'vehiculo_id') {
            return formulario.querySelector(
                '[name="vehiculo_id"], [name="vehiculo"]'
            );
        }

        return formulario.elements.namedItem(nombre);
    }

    function errorPorCampo(errores) {

        formulario.querySelectorAll('[data-error-lab9]')
            .forEach(elemento => elemento.remove());

        formulario.querySelectorAll('[aria-invalid="true"]')
            .forEach(elemento => {
                elemento.removeAttribute('aria-invalid');
            });

        formulario.querySelectorAll('.mensaje-error')
            .forEach(elemento => {
                elemento.textContent = '';
                elemento.style.display = 'none';
            });

        for (const [clave, mensajes] of Object.entries(errores || {})) {

            const nombre = clave.split('.')[0];
            const entrada = campo(nombre);

            const nodo = entrada instanceof Element
                ? entrada
                : entrada?.[0];

            if (!nodo) continue;

            nodo.setAttribute('aria-invalid', 'true');

            const elemento = document.createElement('span');

            elemento.className = 'mensaje-error';
            elemento.dataset.errorLab9 = nombre;
            elemento.setAttribute('role', 'alert');

            elemento.textContent = Array.isArray(mensajes)
                ? mensajes[0]
                : String(mensajes);

            elemento.style.cssText =
                'display:block;color:#b91c1c;margin-top:6px';

            const contenedor =
                nodo.closest('.campo, .lista-opciones') ||
                nodo.parentElement;

            contenedor.append(elemento);
        }
    }

    estado.subscribe(datos => {

        if (boton) {

            boton.disabled =
                !datos.listo ||
                datos.guardando ||
                rentaCreada;

            boton.textContent = datos.guardando
                ? 'Procesando...'
                : textoBoton;
        }

        aviso.textContent = datos.mensaje;

        aviso.setAttribute(
            'role',
            Object.keys(datos.errores).length
                ? 'alert'
                : 'status'
        );

        errorPorCampo(datos.errores);
    });

    estado.notify();

    async function consultarPaginas(ruta, parametro, cantidad) {

        const resultado = [];

        let pagina = 1;
        let ultima = 1;

        do {

            const respuesta = await api.get(
                `${ruta}?${parametro}=${cantidad}&page=${pagina}`
            );

            if (!Array.isArray(respuesta?.data)) {
                throw new Error(
                    `Formato de respuesta inesperado: ${ruta}`
                );
            }

            resultado.push(...respuesta.data);

            ultima = Math.max(
                1,
                Number(
                    respuesta.meta?.last_page ??
                    respuesta.meta?.total_pages
                ) || 1
            );

            pagina++;

        } while (pagina <= ultima);

        return resultado;
    }

    function nombreCliente(cliente) {

        return [
            cliente.nombre1,
            cliente.nombre2,
            cliente.apellido1,
            cliente.apellido2
        ]
            .filter(Boolean)
            .join(' ');
    }

    function selectorCliente() {

        const buscar = formulario.querySelector('#buscar-cliente');

        const select = document.createElement('select');

        select.id = 'cliente_id';
        select.name = 'cliente_id';
        select.required = true;

        select.setAttribute(
            'aria-label',
            'Seleccione un cliente de los resultados'
        );

        buscar.insertAdjacentElement('afterend', select);

        return { buscar, select };
    }

    async function consultarClientes(texto, select) {

        controladorClientes?.abort();

        const controlador = new AbortController();

        controladorClientes = controlador;

        const version = ++versionClientes;

        select.disabled = true;

        select.replaceChildren(
            new Option('Buscando clientes...', '')
        );

        const parametros = new URLSearchParams({
            q: texto,
            perPage: '25'
        });

        try {

            const respuesta = await api.get(
                `/clientes?${parametros}`,
                {
                    signal: controlador.signal
                }
            );

            if (
                version !== versionClientes ||
                controlador.signal.aborted ||
                respuesta?.aborted
            ) {
                return;
            }

            clientes = respuesta.data ?? [];

            select.replaceChildren(
                new Option('Seleccione un cliente', '')
            );

            for (const cliente of clientes) {

                select.add(
                    new Option(
                        `${nombreCliente(cliente)} — ${cliente.cedula}`,
                        cliente.id
                    )
                );
            }

        } catch (error) {

            if (
                version !== versionClientes ||
                controlador.signal.aborted ||
                error?.name === 'AbortError'
            ) {
                return;
            }

            if (error?.status !== 401) {

                select.replaceChildren(
                    new Option('Error al buscar clientes', '')
                );

                estado.setState({
                    mensaje: error?.message ||
                        'Error al consultar clientes.'
                });
            }

        } finally {

            if (
                version === versionClientes &&
                !controlador.signal.aborted
            ) {
                select.disabled = false;
            }
        }
    }

    function mostrarVehiculos(lista, soloDisponibles) {

        const contenedor = formulario.querySelector(
            '.lista-opciones'
        );

        contenedor.replaceChildren();

        for (const vehiculo of lista) {

            const nombreEstado =
                typeof vehiculo.estado === 'string'
                    ? vehiculo.estado
                    : vehiculo.estado?.nombre;

            const disponible =
                nombreEstado === 'Disponible';

            if (soloDisponibles && !disponible) {
                continue;
            }

            const etiqueta = document.createElement('label');

            etiqueta.className = 'opcion-vehiculo';

            const radio = document.createElement('input');

            radio.type = 'radio';
            radio.name = 'vehiculo_id';
            radio.value = String(vehiculo.id);
            radio.disabled = !disponible;

            const descripcion = document.createElement('span');

            descripcion.className = 'datos-vehiculo';

            descripcion.textContent =
                `${vehiculo.marca} ${vehiculo.modelo} — ` +
                `${vehiculo.placa ?? vehiculo.matricula}`;

            const estadoVisual = document.createElement('span');

            estadoVisual.className = disponible
                ? 'estado estado-disponible'
                : 'estado estado-alquilado';

            estadoVisual.textContent =
                nombreEstado || 'Sin estado';

            etiqueta.append(
                radio,
                descripcion,
                estadoVisual
            );

            contenedor.append(etiqueta);
        }

        if (!contenedor.children.length) {
            contenedor.textContent =
                'No hay vehículos disponibles.';
        }
    }

    function mostrarAccesorios(lista) {

        const contenedor = formulario.querySelector(
            '.lista-accesorios'
        );

        contenedor.replaceChildren();

        for (const accesorio of lista) {

            const fila = document.createElement('div');

            fila.className = 'accesorio';

            const etiqueta = document.createElement('label');

            etiqueta.className = 'campo-checkbox';

            const check = document.createElement('input');

            check.type = 'checkbox';
            check.name = 'accesorios[]';
            check.value = String(accesorio.id);

            const texto = document.createElement('span');

            texto.textContent =
                `${accesorio.nombre} — ` +
                `$${Number(accesorio.precio_unitario).toFixed(2)}`;

            etiqueta.append(check, texto);

            const cantidad = document.createElement('input');

            cantidad.type = 'number';
            cantidad.min = '1';
            cantidad.step = '1';
            cantidad.value = '1';

            cantidad.name = `cantidad_${accesorio.id}`;

            cantidad.setAttribute(
                'aria-label',
                `Cantidad de ${accesorio.nombre}`
            );

            cantidad.disabled = true;

            check.addEventListener('change', () => {

                cantidad.disabled = !check.checked;

                actualizarResumen();
            });

            cantidad.addEventListener(
                'input',
                actualizarResumen
            );

            fila.append(etiqueta, cantidad);

            contenedor.append(fila);
        }
    }

    function seleccionAccesorios() {

        return accesorios
            .filter(accesorio => {

                return formulario.querySelector(
                    `input[name="accesorios[]"][value="${accesorio.id}"]`
                )?.checked;

            })
            .map(accesorio => ({

                id: accesorio.id,
                nombre: accesorio.nombre,
                precio: Number(accesorio.precio_unitario),

                cantidad: Number(
                    formulario.elements.namedItem(
                        `cantidad_${accesorio.id}`
                    )?.value
                )
            }));
    }

    function actualizarResumen() {

        const detalles = formulario.querySelectorAll(
            '.datos-resumen dd'
        );

        if (!detalles.length) return;

        const idCliente = formulario.elements.namedItem(
            'cliente_id'
        )?.value;

        const cliente = clientes.find(
            item => String(item.id) === idCliente
        );

        const radio = formulario.querySelector(
            '[name="vehiculo_id"]:checked'
        );

        const vehiculo = vehiculos.find(
            item => String(item.id) === radio?.value
        );

        const inicio = formulario.elements.namedItem(
            'fecha_inicio'
        )?.value;

        const fin = campo('fecha_fin')?.value;

        const dias = inicio && fin
            ? Math.max(
                0,
                Math.round(
                    (Date.parse(fin) - Date.parse(inicio)) /
                    86400000
                )
            )
            : 0;

        const precio = Number(
            formulario.elements.namedItem('precio_diario')?.value || 0
        );

        const extras = seleccionAccesorios().reduce(
            (total, accesorio) =>
                total + accesorio.precio * accesorio.cantidad,
            0
        );

        detalles[0].textContent = cliente
            ? nombreCliente(cliente)
            : 'Seleccione un cliente';

        detalles[1].textContent = vehiculo
            ? `${vehiculo.marca} ${vehiculo.modelo}`
            : 'Seleccione un vehículo';

        detalles[2].textContent = `${dias} días`;

        detalles[3].textContent =
            `$${(dias * precio).toFixed(2)}`;

        detalles[4].textContent =
            `$${extras.toFixed(2)}`;

        const total = formulario.querySelector(
            '.total-renta strong'
        );

        if (total) {
            total.textContent =
                `$${(dias * precio + extras).toFixed(2)}`;
        }
    }

    function restaurar() {
        const recovered = restoreForm(formulario);
        actualizarResumen();
        return recovered;
    }

    async function iniciarCreacion() {

        const { buscar, select } = selectorCliente();

        const disponibles = formulario.querySelector(
            '#solo-disponibles'
        );

        const listaVehiculos = formulario.querySelector(
            '.lista-opciones'
        );

        const listaAccesorios = formulario.querySelector(
            '.lista-accesorios'
        );

        listaVehiculos.textContent = 'Cargando vehículos...';
        listaAccesorios.textContent = 'Cargando accesorios...';

        formulario.querySelector('.seleccion-actual').textContent =
            'Seleccione un cliente de la lista.';

        const [datosVehiculos, datosAccesorios] =
            await Promise.all([

                consultarPaginas('/vehiculos', 'limit', 50),

                consultarPaginas('/accesorios', 'per_page', 25)
            ]);

        vehiculos = datosVehiculos;
        accesorios = datosAccesorios;

        mostrarVehiculos(
            vehiculos,
            disponibles?.checked ?? true
        );

        mostrarAccesorios(accesorios);

        await consultarClientes('', select);

        buscar.addEventListener('input', () => {

            clearTimeout(temporizador);

            controladorClientes?.abort();

            select.value = '';

            formulario.querySelector('.seleccion-actual').textContent =
                'Seleccione un cliente de la lista.';

            temporizador = setTimeout(() => {

                consultarClientes(
                    buscar.value.trim(),
                    select
                );

            }, 350);
        });

        select.addEventListener('change', () => {

            const cliente = clientes.find(
                item => String(item.id) === select.value
            );

            formulario.querySelector('.seleccion-actual').textContent =
                cliente
                    ? `${nombreCliente(cliente)} — ${cliente.cedula}`
                    : 'Seleccione un cliente de la lista.';

            actualizarResumen();
        });

        disponibles?.addEventListener('change', () => {

            mostrarVehiculos(
                vehiculos,
                disponibles.checked
            );

            actualizarResumen();
        });

        formulario.addEventListener(
            'change',
            actualizarResumen
        );

        formulario.addEventListener(
            'input',
            actualizarResumen
        );

        const recuperado = restaurar();

        actualizarResumen();

        estado.setState({
            listo: true,
            mensaje: recuperado
                ? 'Se recuperaron los datos del formulario.'
                : ''
        });
    }

    async function iniciarEdicion() {

        if (!idRenta) {
            throw new Error(
                'No se indicó el número de la renta a editar.'
            );
        }

        const respuesta = await api.get(
            `/rentas/${encodeURIComponent(idRenta)}`
        );

        const renta = respuesta.data ?? respuesta;

        const cliente = formulario.querySelector('#cliente_id');
        const vehiculo = formulario.querySelector('#vehiculo_id');

        cliente.replaceChildren(
            new Option(
                renta.cliente?.nombre_completo || 'Cliente',
                renta.cliente?.id
            )
        );

        vehiculo.replaceChildren(
            new Option(
                `${renta.vehiculo?.marca ?? ''} ` +
                `${renta.vehiculo?.modelo ?? ''}`,
                renta.vehiculo?.id
            )
        );

        cliente.disabled = true;
        vehiculo.disabled = true;

        formulario.elements.namedItem('fecha_inicio').value =
            `${renta.fecha_inicio}T00:00`;

        formulario.elements.namedItem('fecha_fin').value =
            `${renta.fecha_fin}T00:00`;

        const contenedor = formulario.elements.namedItem(
            'fecha_fin'
        ).closest('.grupo-fechas');

        const seccion = document.createElement('div');

        seccion.className = 'campo';

        const etiqueta = document.createElement('label');

        etiqueta.htmlFor = 'precio_diario';
        etiqueta.textContent = 'Precio diario';

        const precio = document.createElement('input');

        precio.id = 'precio_diario';
        precio.name = 'precio_diario';
        precio.type = 'number';
        precio.min = '0';
        precio.step = '0.01';
        precio.required = true;
        precio.value = renta.precio_diario;

        seccion.append(etiqueta, precio);

        contenedor.append(seccion);

        const titulo = document.querySelector(
            '.encabezado-pagina h1'
        );

        if (titulo) {
            titulo.textContent =
                `Editar contrato: RN-${renta.id}`;
        }

        const recuperado = restaurar();

        estado.setState({
            listo: true,
            mensaje: recuperado
                ? 'Se recuperaron los datos del formulario.'
                : ''
        });
    }

    formulario.addEventListener('submit', async evento => {

        evento.preventDefault();

        if (
            !estado.getState().listo ||
            estado.getState().guardando ||
            rentaCreada
        ) {
            return;
        }

        if (
            !esEdicion &&
            seleccionAccesorios().some(
                accesorio =>
                    !Number.isInteger(accesorio.cantidad) ||
                    accesorio.cantidad < 1
            )
        ) {

            estado.setState({
                mensaje:
                    'Las cantidades de accesorios deben ser enteros mayores que cero.'
            });

            return;
        }

        const payload = {

            fecha_inicio: formulario.elements.namedItem(
                'fecha_inicio'
            )?.value,

            fecha_fin: campo('fecha_fin')?.value,

            precio_diario: formulario.elements.namedItem(
                'precio_diario'
            )?.value
        };

        if (!esEdicion) {

            payload.cliente_id = formulario.elements.namedItem(
                'cliente_id'
            )?.value;

            payload.vehiculo_id = formulario.querySelector(
                '[name="vehiculo_id"]:checked'
            )?.value || '';
        }

        estado.setState({
            guardando: true,
            errores: {},
            mensaje: ''
        });

        try {

            if (esEdicion) {

                await api.put(
                    `/rentas/${encodeURIComponent(idRenta)}`,
                    payload
                );

            } else {

                const respuesta = await api.post(
                    '/rentas',
                    payload
                );

                const creada = respuesta.data ?? respuesta;

                if (!creada?.id) {

                    throw new Error(
                        'La API no devolvió el identificador del contrato creado.'
                    );
                }

                rentaCreada = true;

                for (const accesorio of seleccionAccesorios()) {

                    await api.post(
                        `/rentas/${creada.id}/accesorios/${accesorio.id}`,
                        {
                            cantidad: accesorio.cantidad
                        }
                    );
                }
            }

            window.location.assign(rutaListado);

        } catch (error) {

            if (error?.status === 422) {

                estado.setState({
                    errores: error.errors ?? {},
                    mensaje: 'Revisa los campos marcados.'
                });

                formulario.querySelector(
                    '[aria-invalid="true"]'
                )?.focus();

            } else if (error?.status !== 401) {

                estado.setState({

                    mensaje: rentaCreada
                        ? 'La renta se creó, pero hubo un error al agregar accesorios. Revisa el contrato desde el listado; no vuelvas a enviarlo.'
                        : (
                            error?.message ||
                            'No se pudo guardar la renta.'
                        )
                });
            }

        } finally {

            estado.setState({
                guardando: false
            });
        }
    });

    (async () => {

        try {

            if (esEdicion) {

                await iniciarEdicion();

            } else {

                await iniciarCreacion();
            }

        } catch (error) {

            if (error?.status !== 401) {

                estado.setState({
                    listo: false,
                    mensaje: error?.message ||
                        'No fue posible cargar el formulario.'
                });
            }
        }

    })();
}
