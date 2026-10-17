export class ApiClient {
    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl;
    }

    /**
     * Obtiene el token guardado en el navegador.
     */
    getToken() {
        return localStorage.getItem('auth_token');
    }

    /**
     * Petición HTTP centralizada con async/await.
     */
    async request(endpoint, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(this.getToken() && { 'Authorization': `Bearer ${this.getToken()}` }),
            ...options.headers
        };

        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                ...options,
                headers
            });

            // Manejo de expiración de token (401)
            if (response.status === 401) {
                this.handleUnauthorized();
                throw { status: 401, message: 'La sesión ha expirado.' };
            }

            // Manejo de errores de validación (422) o del servidor (500)
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw {
                    status: response.status,
                    errors: errorData.errors || {},
                    message: errorData.message || 'Ocurrió un error en la solicitud.'
                };
            }

            // Si la respuesta no tiene contenido (ej. DELETE 204)
            if (response.status === 204) {
                return null;
            }

            return await response.json();

        } catch (error) {
            // Si la petición fue cancelada intencionalmente por AbortController
            if (error.name === 'AbortError') {
                return { aborted: true };
            }
            throw error;
        }
    }

    /**
     * Respalda los datos digitados en el formulario antes de redirigir al Login.
     */
    handleUnauthorized() {
        const formularioActivo = document.querySelector('form');
        if (formularioActivo) {
            const formData = new FormData(formularioActivo);
            const backup = Object.fromEntries(formData.entries());
            sessionStorage.setItem('form_backup_401', JSON.stringify(backup));
        }

        alert('Su sesión ha expirado. Será redirigido al inicio de sesión.');
        window.location.href = '/login';
    }

    // Métodos auxiliares HTTP
    get(endpoint, options = {}) {
        return this.request(endpoint, { method: 'GET', ...options });
    }

    post(endpoint, body, options = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
            ...options
        });
    }

    put(endpoint, body, options = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body),
            ...options
        });
    }

    delete(endpoint, options = {}) {
        return this.request(endpoint, { method: 'DELETE', ...options });
    }
}