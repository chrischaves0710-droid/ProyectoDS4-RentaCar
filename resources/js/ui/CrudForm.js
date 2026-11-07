import { ApiClient } from '../api/ApiClient.js';
import { AppState } from '../store/AppState.js';
import { restoreForm } from './FormRecovery.js';

export function mountCrudForm(resource) {
    const form = document.querySelector('form.secciones-formulario');
    if (!form) return;
    const api = new ApiClient('/api');
    const id = new URLSearchParams(location.search).get('id');
    const edit = location.pathname.endsWith('/editar');
    const role = location.pathname.includes('/admin/') ? 'admin' : 'gerente';
    const button = form.querySelector('button[type="submit"]');
    const original = button?.textContent ?? 'Guardar';
    const message = document.createElement('p');
    message.setAttribute('role', 'status');
    form.prepend(message);
    const store = new AppState({ ready: !edit, saving: false, errors: {}, message: '' });
    form.noValidate = true;

    store.subscribe(current => {
        if (button) {
            button.disabled = !current.ready || current.saving;
            button.textContent = current.saving ? 'Guardando...' : original;
        }
        message.textContent = current.message;
        message.setAttribute('role', Object.keys(current.errors).length ? 'alert' : 'status');
        form.querySelectorAll('[data-server-error]').forEach(node => node.remove());
        form.querySelectorAll('[aria-invalid="true"]').forEach(node => node.removeAttribute('aria-invalid'));
        for (const [field, errors] of Object.entries(current.errors)) {
            const element = form.elements.namedItem(field.split('.')[0]);
            if (!element) continue;
            const hint = document.createElement('span');
            hint.dataset.serverError = field;
            hint.className = 'mensaje-error';
            hint.id = `server-${resource}-${field.replace(/[^a-z\d_-]/gi, '-')}`;
            hint.style.cssText = 'display:block;color:#b91c1c;margin-top:0.25rem';
            hint.textContent = Array.isArray(errors) ? errors[0] : String(errors);
            element.setAttribute('aria-invalid', 'true');
            element.setAttribute('aria-describedby', hint.id);
            element.insertAdjacentElement('afterend', hint);
        }
    });
    store.notify();

    async function initialize() {
        if (edit && !id) {
            store.setState({ ready: false, message: 'No se especificó el registro que se desea editar.' });
            return;
        }
        try {
            if (edit) {
                const response = await api.get(`/${resource}/${encodeURIComponent(id)}`);
                const data = response.data ?? response;
                for (const control of form.elements) {
                    if (control.name && data[control.name] !== undefined && data[control.name] !== null) control.value = data[control.name];
                }
            }
            const recovered = restoreForm(form);
            store.setState({ ready: true, message: recovered ? 'Se recuperaron los datos anteriores.' : '' });
        } catch (error) {
            if (error?.status !== 401) store.setState({ ready: false, message: error?.message ?? 'No se pudo cargar el registro.' });
        }
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!store.state.ready || store.state.saving) return;
        const body = Object.fromEntries(new FormData(form));
        store.setState({ saving: true, errors: {}, message: '' });
        try {
            if (edit) await api.put(`/${resource}/${encodeURIComponent(id)}`, body);
            else await api.post(`/${resource}`, body);
            location.assign(`/lab8/${role}/${resource}`);
        } catch (error) {
            if (error?.status === 422) {
                store.setState({ errors: error.errors ?? {}, message: 'Revisá los campos marcados.' });
                form.querySelector('[aria-invalid="true"]')?.focus();
            } else if (error?.status !== 401) store.setState({ message: error?.message ?? 'No fue posible guardar.' });
        } finally {
            store.setState({ saving: false });
        }
    });
    initialize();
}
