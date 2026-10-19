
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';

const formulario = document.querySelector('form.secciones-formulario');

if (formulario) {
    const api = new ApiClient('/api');

    const rutaActual = window.location.pathname.replace(/\/+$/, '');
    const esEdicion = rutaActual.endsWith('/editar');

    const idCliente = new URLSearchParams(
        window.location.search
    ).get('id');

    const rutaListado = '/lab8/gerente/clientes';

    // Campos que utiliza la API de clientes
    const nombres = [
        'cedula',
        'anno_nacimiento',
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'correo',
        'telefono'
    ];

    const botonGuardar = formulario.querySelector(
        'button[type="submit"]'
    );

    const botonCancelar = formulario.querySelector(
        'button.boton-secundario[type="button"]'
    );

    const textoGuardar = botonGuardar?.textContent ?? 'Guardar cliente';

    // Permitir que Laravel devuelva los errores de validación
    formulario.noValidate = true;

    // Mensaje general del formulario
    const mensaje = document.createElement('p');
    mensaje.id = 'mensaje-formulario-cliente';
    mensaje.setAttribute('role', 'status');

    formulario.prepend(mensaje);

    // Estado de la aplicación
    const estado = new AppState({
        listo: false,
        cargando: true,
        guardando: false,
        errores: {},
        mensaje: 'Cargando formulario...'
    });

    // Obtener un campo por su nombre
    function obtenerCampo(nombre) {
        return formulario.elements.namedItem(nombre);
    }

    // Mostrar los errores debajo de cada campo
    function mostrarErrores(errores) {

        for (const nombre of nombres) {

            const campo = obtenerCampo(nombre);

            if (!campo) continue;

            let aviso = document.getElementById(`error-${nombre}`);

            if (!aviso) {
                aviso = document.createElement('span');
                aviso.id = `error-${nombre}`;
                aviso.className = 'mensaje-error';
                aviso.style.color = '#dc2626';
                aviso.style.display = 'block';

                campo.insertAdjacentElement('afterend', aviso);
            }

            const erroresCampo = errores[nombre];

            aviso.textContent = Array.isArray(erroresCampo)
                ? String(erroresCampo[0] ?? '')
                : String(erroresCampo ?? '');

            if (erroresCampo) {
                campo.setAttribute('aria-invalid', 'true');
            } else {
                campo.removeAttribute('aria-invalid');
            }
        }
    }

    // Sincronizar el estado con la interfaz
    estado.subscribe(datos => {

        if (botonGuardar) {
            botonGuardar.disabled =
                !datos.listo || datos.cargando || datos.guardando;

            botonGuardar.textContent = datos.guardando
                ? 'Guardando...'
                : textoGuardar;
        }

        for (const nombre of nombres) {

            const campo = obtenerCampo(nombre);

            if (campo) {
                campo.disabled = datos.cargando;
            }
        }

        mensaje.textContent = datos.mensaje;

        mensaje.setAttribute(
            'role',
            Object.keys(datos.errores).length ? 'alert' : 'status'
        );

        mostrarErrores(datos.errores);
    });

    estado.notify();

    // Rellenar los campos con datos existentes
    function rellenarCampos(cliente) {

        for (const nombre of nombres) {

            const campo = obtenerCampo(nombre);

            if (
                campo &&
                cliente[nombre] !== undefined &&
                cliente[nombre] !== null
            ) {
                campo.value = cliente[nombre];
            }
        }
    }

    // Recuperar datos guardados tras una respuesta 401
    function recuperarDatos() {

        const respaldo = sessionStorage.getItem('form_backup_401');

        if (!respaldo) return false;

        try {
            const datos = JSON.parse(respaldo);

            if (
                !datos ||
                typeof datos !== 'object' ||
                !('cedula' in datos) ||
                !('nombre1' in datos) ||
                !('apellido1' in datos)
            ) {
                return false;
            }

            rellenarCampos(datos);

            sessionStorage.removeItem('form_backup_401');

            return true;

        } catch {
            return false;
        }
    }

    // Cargar los datos al abrir el formulario
    async function iniciar() {

        if (
            !botonGuardar ||
            nombres.some(nombre => !obtenerCampo(nombre))
        ) {
            estado.setState({
                cargando: false,
                mensaje: 'Faltan campos del formulario.'
            });
            return;
        }

        if (esEdicion && !idCliente) {
            estado.setState({
                cargando: false,
                mensaje: 'Falta el ID del cliente en la dirección.'
            });
            return;
        }

        try {

            // Consultar el cliente cuando estamos editando
            if (esEdicion) {

                const respuesta = await api.get(
                    `/clientes/${encodeURIComponent(idCliente)}`
                );

                const cliente = respuesta.data ?? respuesta;

                rellenarCampos(cliente);
            }

            // Recuperar datos tras una sesión expirada
            const recuperado = recuperarDatos();

            estado.setState({
                listo: true,
                cargando: false,
                mensaje: recuperado
                    ? 'Se recuperaron los datos del formulario.'
                    : ''
            });

        } catch (error) {

            if (error?.status !== 401) {

                estado.setState({
                    cargando: false,
                    mensaje: error?.message ??
                        'No se pudo cargar el cliente.'
                });
            }
        }
    }

    // Registrar o actualizar un cliente
    formulario.addEventListener('submit', async evento => {

        evento.preventDefault();

        if (
            !estado.getState().listo ||
            estado.getState().guardando
        ) {
            return;
        }

        // Recopilar los datos del formulario
        const datos = Object.fromEntries(
            nombres.map(nombre => [
                nombre,
                obtenerCampo(nombre).value
            ])
        );

        estado.setState({
            guardando: true,
            errores: {},
            mensaje: ''
        });

        try {

            if (esEdicion) {

                // Actualizar un cliente existente
                await api.put(
                    `/clientes/${encodeURIComponent(idCliente)}`,
                    datos
                );

            } else {

                // Registrar un cliente nuevo
                await api.post('/clientes', datos);
            }

            // Regresar al directorio de clientes
            window.location.assign(rutaListado);

        } catch (error) {

            if (error?.status === 422) {

                // Mostrar errores devueltos por Laravel
                estado.setState({
                    errores: error.errors ?? {},
                    mensaje: 'Revisa los campos marcados.'
                });

                formulario.querySelector(
                    '[aria-invalid="true"]'
                )?.focus();

            } else if (error?.status !== 401) {

                estado.setState({
                    mensaje: error?.message ??
                        'No se pudo guardar el cliente.'
                });
            }

        } finally {

            estado.setState({
                guardando: false
            });
        }
    });

    // Funcionamiento del botón Cancelar
    botonCancelar?.addEventListener('click', () => {
        window.location.assign(rutaListado);
    });

    // Iniciar el formulario
    iniciar();
}
