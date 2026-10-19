
import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

const formLogin = document.getElementById('form-login');
const formRegistro = document.getElementById('form-registro');

const tabLogin = document.getElementById('tab-login');
const tabRegistro = document.getElementById('tab-registro');

const panelLogin = document.getElementById('panel-login');
const panelRegistro = document.getElementById('panel-registro');

if (formLogin && formRegistro && tabLogin && tabRegistro) {

    // Cambiar entre iniciar sesión y registrarse
    function cambiarPestana(tipo) {

        const iniciar = tipo === 'login';

        panelLogin.hidden = !iniciar;
        panelRegistro.hidden = iniciar;

        tabLogin.setAttribute(
            'aria-selected',
            String(iniciar)
        );

        tabRegistro.setAttribute(
            'aria-selected',
            String(!iniciar)
        );

        tabLogin.tabIndex = iniciar ? 0 : -1;
        tabRegistro.tabIndex = iniciar ? -1 : 0;

        document.title = iniciar
            ? 'Iniciar sesión | RentaCar'
            : 'Registrarse | RentaCar';
    }

    tabLogin.addEventListener('click', () => {
        cambiarPestana('login');
    });

    tabRegistro.addEventListener('click', () => {
        cambiarPestana('registro');
    });

    // Navegación por teclado
    [tabLogin, tabRegistro].forEach((tab, indice) => {

        tab.addEventListener('keydown', (evento) => {

            if (!['ArrowLeft', 'ArrowRight'].includes(evento.key)) {
                return;
            }

            evento.preventDefault();

            const siguiente = indice === 0
                ? 'registro'
                : 'login';

            cambiarPestana(siguiente);

            (siguiente === 'login'
                ? tabLogin
                : tabRegistro
            ).focus();
        });
    });

    // Limpiar mensajes anteriores
    function limpiarErrores(tipo, formulario) {

        formulario.querySelectorAll('.mensaje-error')
            .forEach(elemento => {
                elemento.textContent = '';
            });

        formulario.querySelectorAll('[aria-invalid]')
            .forEach(elemento => {
                elemento.removeAttribute('aria-invalid');
            });

        document.getElementById(
            `mensaje-${tipo}`
        ).textContent = '';
    }

    // Mostrar errores del backend debajo de los campos
    function mostrarErrores(tipo, formulario, errores) {

        let primerCampo = null;

        for (const [nombre, mensajes] of Object.entries(errores || {})) {

            const campo = formulario.elements.namedItem(nombre);

            const aviso = document.getElementById(
                `error-${tipo}-${nombre}`
            );

            if (campo) {

                campo.setAttribute('aria-invalid', 'true');

                primerCampo ??= campo;
            }

            if (aviso) {

                aviso.textContent = Array.isArray(mensajes)
                    ? String(mensajes[0] ?? '')
                    : String(mensajes);
            }
        }

        primerCampo?.focus();
    }

    // Comprobar que la ruta guardada es interna
    function rutaGuardadaSegura() {

        const guardada = sessionStorage.getItem(
            'return_url_401'
        );

        if (!guardada) return null;

        try {

            const url = new URL(
                guardada,
                window.location.origin
            );

            if (
                url.origin !== window.location.origin ||
                !url.pathname.startsWith('/lab8/')
            ) {
                return null;
            }

            return url.pathname + url.search + url.hash;

        } catch {

            return null;
        }
    }

    // Determinar la pantalla inicial según el rol
    async function rutaPorRol() {

        try {

            const respuesta = await api.get('/user');

            const usuario = respuesta?.data ?? respuesta;

            const roles = Array.isArray(usuario?.roles)
                ? usuario.roles.map(rol =>
                    typeof rol === 'string' ? rol : rol.name
                )
                : [];

            if (roles.includes('Admin_General')) {
                return '/lab8/gerente/vehiculos';
            }

            if (roles.includes('Admin_Inventarios')) {
                return '/lab8/admin/vehiculos';
            }

            if (roles.includes('Gestor_Rentas')) {
                return '/lab8/gestor/rentas';
            }

            if (roles.includes('Cliente')) {
                return '/lab8/cliente/catalogo';
            }

        } catch (error) {

            console.warn(
                'No fue posible obtener el rol del usuario.',
                error
            );
        }

        // Ruta predeterminada
        return '/';
    }

    // Enviar credenciales al servidor
    async function enviar(tipo, formulario) {

        const boton = formulario.querySelector(
            'button[type="submit"]'
        );

        const mensaje = document.getElementById(
            `mensaje-${tipo}`
        );

        const textoOriginal = boton.textContent;

        const esRegistro = tipo === 'registro';

        const datos = Object.fromEntries(
            new FormData(formulario).entries()
        );

        limpiarErrores(tipo, formulario);

        // Mostrar estado de carga
        boton.disabled = true;

        boton.textContent = esRegistro
            ? 'Creando cuenta...'
            : 'Iniciando sesión...';

        mensaje.textContent = esRegistro
            ? 'Registrando usuario...'
            : 'Validando credenciales...';

        try {

            // Consumir la API correspondiente
            const respuesta = await api.post(
                esRegistro ? '/register' : '/login',
                datos
            );

            // Comprobar el token recibido
            if (
                typeof respuesta?.token !== 'string' ||
                !respuesta.token
            ) {

                throw new Error(
                    'La API no devolvió un token de autenticación.'
                );
            }

            // Guardar token para las próximas peticiones
            localStorage.setItem(
                'auth_token',
                respuesta.token
            );

            // Si se registra una nueva cuenta
            if (esRegistro) {

                // La API asigna el rol Cliente
                sessionStorage.removeItem('return_url_401');
                sessionStorage.removeItem('form_backup_401');

                window.location.assign(
                    '/lab8/cliente/catalogo'
                );

                return;
            }

            // Si inició sesión después de un error 401
            const regresar = rutaGuardadaSegura();

            sessionStorage.removeItem(
                'return_url_401'
            );

            // Volver al formulario anterior si corresponde
            const destino = regresar ?? await rutaPorRol();

            window.location.assign(destino);

        } catch (error) {

            // Errores de validación de Laravel
            if (error?.status === 422 && error.errors) {

                mostrarErrores(
                    tipo,
                    formulario,
                    error.errors
                );
            }

            mensaje.textContent =
                error?.message ||
                'No se pudo completar la operación. Inténtalo nuevamente.';

        } finally {

            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    }

    // Evento: Iniciar sesión
    formLogin.addEventListener('submit', (evento) => {

        evento.preventDefault();

        void enviar('login', formLogin);
    });

    // Evento: Registrar usuario
    formRegistro.addEventListener('submit', (evento) => {

        evento.preventDefault();

        void enviar('registro', formRegistro);
    });

    // Mostrar login al abrir la página
    cambiarPestana('login');
}
