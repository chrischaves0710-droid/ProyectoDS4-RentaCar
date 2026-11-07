
import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';
import { restoreForm } from '../../ui/FormRecovery.js';

const formulario = document.querySelector('.secciones-formulario');

if (formulario) {
    const api = new ApiClient('/api');

    const campoNombre = formulario.elements.namedItem('nombre');
    const campoPrecio = formulario.elements.namedItem('precio_unitario');
    const botonGuardar = formulario.querySelector('button[type="submit"]');

    const rutaActual = window.location.pathname.replace(/\/+$/, '');
    const esEdicion = rutaActual.endsWith('/editar');

    const idAccesorio = new URLSearchParams(
        window.location.search
    ).get('id');

    const rutaListado = rutaActual.startsWith('/lab8/admin/')
        ? '/lab8/admin/accesorios'
        : '/lab8/gerente/accesorios';

    const textoBoton = botonGuardar?.textContent ?? 'Guardar accesorio';

    const estado = new AppState({
        cargando: true,
        guardando: false,
        listo: false,
        mensaje: 'Cargando formulario...',
        errores: {}
    });

    const avisoGeneral = document.createElement('p');
    avisoGeneral.id = 'mensaje-formulario-accesorio';
    avisoGeneral.setAttribute('role', 'status');
    avisoGeneral.style.marginBottom = '1rem';

    formulario.prepend(avisoGeneral);

    function avisoCampo(campo) {
        const id = `error-${campo.name}`;
        let aviso = document.getElementById(id);

        if (!aviso) {
            aviso = document.createElement('span');
            aviso.id = id;
            aviso.className = 'mensaje-error';
            aviso.style.color = '#dc2626';
            aviso.style.display = 'block';
            aviso.style.fontSize = '0.875rem';

            campo.insertAdjacentElement('afterend', aviso);
        }

        campo.setAttribute('aria-describedby', id);

        return aviso;
    }

    [campoNombre, campoPrecio].filter(Boolean).forEach(avisoCampo);

    estado.subscribe((datos) => {
        const bloqueado =
            !datos.listo || datos.cargando || datos.guardando;

        if (botonGuardar) {
            botonGuardar.disabled = bloqueado;

            botonGuardar.textContent = datos.guardando
                ? 'Guardando...'
                : textoBoton;
        }

        for (const campo of [campoNombre, campoPrecio]) {
            if (!campo) continue;

            campo.disabled =
                datos.cargando || (!datos.listo && !datos.guardando);

            const mensajes = datos.errores[campo.name];

            campo.setAttribute(
                'aria-invalid',
                mensajes ? 'true' : 'false'
            );

            const aviso = document.getElementById(
                `error-${campo.name}`
            );

            if (aviso) {
                aviso.textContent = Array.isArray(mensajes)
                    ? String(mensajes[0] ?? '')
                    : String(mensajes ?? '');
            }
        }

        avisoGeneral.textContent = datos.mensaje;

        avisoGeneral.setAttribute(
            'role',
            Object.keys(datos.errores).length ? 'alert' : 'status'
        );
    });

    estado.notify();

    function recuperarDatos() {
        return restoreForm(formulario);
    }

    async function iniciar() {
        if (!campoNombre || !campoPrecio || !botonGuardar) return;

        if (esEdicion && !idAccesorio) {
            estado.setState({
                cargando: false,
                mensaje: 'Falta el identificador del accesorio en la URL.'
            });
            return;
        }

        try {
            if (esEdicion) {
                const respuesta = await api.get(
                    `/accesorios/${encodeURIComponent(idAccesorio)}`
                );

                const accesorio = respuesta.data ?? respuesta;

                campoNombre.value = accesorio.nombre ?? '';
                campoPrecio.value = accesorio.precio_unitario ?? '';
            }

            const restaurado = recuperarDatos();

            estado.setState({
                cargando: false,
                listo: true,
                mensaje: restaurado
                    ? 'Se recuperaron los datos guardados antes de la expiración de sesión.'
                    : ''
            });

        } catch (error) {
            if (error?.status !== 401) {
                estado.setState({
                    cargando: false,
                    listo: false,
                    mensaje: error?.message ??
                        'No fue posible cargar el accesorio.'
                });
            }
        }
    }

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!estado.getState().listo ||
            estado.getState().guardando) {
            return;
        }

        estado.setState({
            guardando: true,
            errores: {},
            mensaje: ''
        });

        const datos = {
            nombre: campoNombre.value.trim(),
            precio_unitario: campoPrecio.value
        };

        try {
            if (esEdicion) {
                await api.put(
                    `/accesorios/${encodeURIComponent(idAccesorio)}`,
                    datos
                );
            } else {
                await api.post('/accesorios', datos);
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
                    mensaje: error?.message ??
                        'No se pudo guardar el accesorio.'
                });
            }

        } finally {
            estado.setState({
                guardando: false
            });
        }
    });

    iniciar();
}
