import test from 'node:test';
import assert from 'node:assert/strict';
import { ApiClient } from '../../resources/js/api/ApiClient.js';
import { AppState } from '../../resources/js/store/AppState.js';
import { paginationOf, escapeHtml } from '../../resources/js/ui/PaginatedListing.js';
import { saveCurrentForm, restoreForm } from '../../resources/js/ui/FormRecovery.js';

function storage() {
    const values = new Map();
    return {
        getItem: key => values.get(key) ?? null,
        setItem: (key, value) => values.set(key, String(value)),
        removeItem: key => values.delete(key)
    };
}

globalThis.localStorage = storage();
globalThis.sessionStorage = storage();
globalThis.location = { pathname: '/lab8/gestor/rentas/nueva', search: '', hash: '', assign: path => { location.assigned = path; } };
globalThis.document = { querySelector: () => null };

function reply(status, body = {}) {
    return { status, ok: status >= 200 && status < 300, headers: { get: () => 'application/json' }, json: async () => body };
}

test('agrega el token Bearer y devuelve datos del backend', async () => {
    localStorage.setItem('auth_token', 'ejemplo');
    globalThis.fetch = async (_, options) => {
        assert.equal(options.headers.Authorization, 'Bearer ejemplo');
        return reply(200, { data: [{ id: 1 }] });
    };
    assert.equal((await new ApiClient().get('/vehiculos')).data[0].id, 1);
});

test('preserva los errores de validación 422', async () => {
    globalThis.fetch = async () => reply(422, { message: 'Errores', errors: { placa: ['Obligatorio'] } });
    await assert.rejects(new ApiClient().post('/vehiculos', {}), error => error.status === 422 && error.errors.placa[0] === 'Obligatorio');
});

test('diferencia los errores de red de respuestas HTTP', async () => {
    globalThis.fetch = async () => { throw new TypeError('Conexión'); };
    await assert.rejects(new ApiClient().get('/vehiculos'), error => error.status === 0);
});

test('maneja peticiones canceladas sin publicar errores', async () => {
    globalThis.fetch = async () => { throw Object.assign(new Error('cancelada'), { name: 'AbortError' }); };
    assert.deepEqual(await new ApiClient().get('/vehiculos'), { aborted: true });
});

test('conserva los valores repetidos del formulario y restaura casillas', () => {
    const original = globalThis.FormData;
    const first = { name: 'accesorios[]', value: '1', type: 'checkbox', checked: true };
    const second = { name: 'accesorios[]', value: '3', type: 'checkbox', checked: true };
    const other = { name: 'fecha_inicio', value: '2026-10-18', type: 'date' };
    const form = {
        elements: [first, second, other],
        querySelectorAll: () => []
    };
    document.querySelector = () => form;
    globalThis.FormData = class {
        constructor() { return [['accesorios[]', '1'], ['accesorios[]', '3'], ['fecha_inicio', '2026-10-18']]; }
    };
    assert.equal(saveCurrentForm(), true);
    assert.deepEqual(JSON.parse(sessionStorage.getItem('form_backup_401')).values['accesorios[]'], ['1', '3']);
    first.checked = false;
    second.checked = false;
    other.value = '';
    assert.equal(restoreForm(form), true);
    assert.equal(first.checked, true);
    assert.equal(second.checked, true);
    assert.equal(other.value, '2026-10-18');
    assert.equal(sessionStorage.getItem('form_backup_401'), null);
    globalThis.FormData = original;
});

test('redirecciona tras un 401 sin borrar el formulario respaldado', async () => {
    ApiClient.redirecting = false;
    localStorage.setItem('auth_token', 'vencido');
    document.querySelector = () => null;
    globalThis.fetch = async () => reply(401);
    await assert.rejects(new ApiClient().get('/vehiculos'), error => error.status === 401);
    assert.equal(localStorage.getItem('auth_token'), null);
    assert.equal(sessionStorage.getItem('return_url_401'), '/lab8/gestor/rentas/nueva');
    assert.equal(location.assigned, '/login');
});

test('propaga cambios de estado y normaliza la paginación de Laravel', () => {
    const state = new AppState({ data: [] });
    let observed = 0;
    state.subscribe(current => { observed = current.data.length; });
    state.setState({ data: [1, 2] });
    assert.equal(observed, 2);
    assert.deepEqual(paginationOf({ meta: { current_page: 2, last_page: 5 } }), { current_page: 2, last_page: 5, total: 0 });
    assert.equal(paginationOf({ meta: { current_page: 1, total_pages: 3 } }).last_page, 3);
    assert.equal(escapeHtml('<script>'), '&lt;script&gt;');
});
