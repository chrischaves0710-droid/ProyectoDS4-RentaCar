
export class ApiClient {

    static redirigiendo = false;

    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl.replace(/\/+$/, '');
    }

    // Obtener el token de autenticación
    getToken() {
        return localStorage.getItem('auth_token');
    }

    // Realizar peticiones HTTP de manera asíncrona
    async request(endpoint, options = {}) {

        const token = this.getToken();

        const headers = {
            'Accept': 'application/json',

            ...(options.body !== undefined
                ? { 'Content-Type': 'application/json' }
                : {}),

            ...(token
                ? { 'Authorization': `Bearer ${token}` }
                : {}),

            ...options.headers
        };

        try {

            const response = await fetch(
                `${this.baseUrl}${endpoint}`,
                {
                    ...options,
                    headers
                }
            );

            // Manejar sesión expirada (401)
            if (response.status === 401) {

                // Evitar redirecciones cuando ya estamos en login
                if (window.location.pathname !== '/login') {
                    this.handleUnauthorized();
                }

                throw {
                    status: 401,
                    message: 'La sesión ha expirado o el token no es válido.'
                };
            }

            // Respuesta sin contenido, por ejemplo DELETE
            if (response.status === 204) {
                return null;
            }

            const data = await response.json()
                .catch(() => ({}));

            // Manejo uniforme de errores HTTP
            if (!response.ok) {

                throw {
                    status: response.status,

                    message: data.message ||
                        'Ocurrió un error en la solicitud.',

                    errors: data.errors || {}
                };
            }

            return data;

        } catch (error) {

            // Solicitud cancelada con AbortController
            if (error?.name === 'AbortError') {
                return { aborted: true };
            }

            // Problemas de conexión
            if (error instanceof TypeError) {

                throw {
                    status: 0,
                    message: 'No se pudo conectar con el servidor.',
                    errors: {}
                };
            }

            throw error;
        }
    }

    // Manejar expiración del token y preservar el formulario
    handleUnauthorized() {

        // Evitar múltiples redirecciones simultáneas
        if (ApiClient.redirigiendo) {
            return;
        }

        ApiClient.redirigiendo = true;

        // Guardar la página actual para regresar después del login
        const urlActual =
            window.location.pathname +
            window.location.search +
            window.location.hash;

        sessionStorage.setItem(
            'return_url_401',
            urlActual
        );

        // Buscar el formulario activo de crear o editar
        const formulario = document.querySelector(
            'form[data-preserve-session], ' +
            'form.secciones-formulario, ' +
            'form.formulario-renta'
        );

        if (formulario) {

            // Obtener los valores introducidos
            const datos = [
                ...new FormData(formulario).entries()
            ].filter(([nombre, valor]) =>

                typeof valor === 'string' &&

                !/password|clave|token|csrf/i.test(nombre)
            );

            // Respaldar los datos temporalmente
            sessionStorage.setItem(
                'form_backup_401',
                JSON.stringify(
                    Object.fromEntries(datos)
                )
            );
        }

        // Eliminar el token expirado
        localStorage.removeItem('auth_token');

        // Redirigir al inicio de sesión
        window.location.assign('/login');
    }

    // Consultar registros
    get(endpoint, options = {}) {

        return this.request(endpoint, {
            ...options,
            method: 'GET'
        });
    }

    // Crear registros
    post(endpoint, body, options = {}) {

        return this.request(endpoint, {
            ...options,
            method: 'POST',
            body: JSON.stringify(body)
        });
    }

    // Actualizar registros
    put(endpoint, body, options = {}) {

        return this.request(endpoint, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(body)
        });
    }

    // Eliminar registros
    delete(endpoint, options = {}) {

        return this.request(endpoint, {
            ...options,
            method: 'DELETE'
        });
    }
}
