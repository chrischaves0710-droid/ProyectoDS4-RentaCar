import { saveCurrentForm } from '../ui/FormRecovery.js';

export class ApiClient {
    static redirecting = false;

    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl.replace(/\/+$/, '');
    }

    getToken() {
        return localStorage.getItem('auth_token');
    }

    async request(endpoint, options = {}) {
        const { headers: customHeaders = {}, ...requestOptions } = options;
        const token = this.getToken();
        const headers = {
            Accept: 'application/json',
            ...(requestOptions.body !== undefined && !(requestOptions.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...customHeaders
        };

        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, { ...requestOptions, headers });
            if (response.status === 401) {
                if (location.pathname !== '/login') this.handleUnauthorized();
                throw { status: 401, message: 'La sesión ha expirado. Iniciá sesión nuevamente.', errors: {} };
            }
            if (response.status === 204) return null;
            const contentType = response.headers?.get('content-type') ?? '';
            const data = contentType.includes('application/json') ? await response.json().catch(() => ({})) : {};
            if (!response.ok) {
                throw {
                    status: response.status,
                    message: data.message ?? `La solicitud falló (${response.status}).`,
                    errors: data.errors ?? {}
                };
            }
            return data;
        } catch (error) {
            if (error?.name === 'AbortError') return { aborted: true };
            if (error instanceof TypeError) throw { status: 0, message: 'No fue posible conectar con el servidor.', errors: {} };
            throw error;
        }
    }

    handleUnauthorized() {
        if (ApiClient.redirecting) return;
        ApiClient.redirecting = true;
        const route = location.pathname + location.search + location.hash;
        if (location.pathname.startsWith('/lab8/')) {
            sessionStorage.setItem('return_url_401', route);
            saveCurrentForm();
        }
        localStorage.removeItem('auth_token');
        location.assign('/login');
    }

    get(endpoint, options = {}) {
        return this.request(endpoint, { ...options, method: 'GET' });
    }

    post(endpoint, body, options = {}) {
        return this.request(endpoint, { ...options, method: 'POST', body: JSON.stringify(body) });
    }

    put(endpoint, body, options = {}) {
        return this.request(endpoint, { ...options, method: 'PUT', body: JSON.stringify(body) });
    }

    delete(endpoint, options = {}) {
        return this.request(endpoint, { ...options, method: 'DELETE' });
    }
}
