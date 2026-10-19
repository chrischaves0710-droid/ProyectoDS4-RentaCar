import { ApiClient } from '../api/ApiClient.js';
import { AppState } from '../store/AppState.js';

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);
}

export function paginationOf(response, fallbackPage = 1) {
    const meta = response?.meta ?? {};
    return {
        current_page: Math.max(1, Number(meta.current_page ?? meta.page ?? fallbackPage) || 1),
        last_page: Math.max(1, Number(meta.last_page ?? meta.total_pages ?? 1) || 1),
        total: Math.max(0, Number(meta.total ?? meta.total_records ?? 0) || 0)
    };
}

export function mountListing({ endpoint, body, pagination, search, form, columns, row, query = () => ({}), pageSize = {}, emptyMessage = 'No se encontraron registros.', label = 'registros' }) {
    if (!body || !pagination) return null;

    const api = new ApiClient('/api');
    const state = new AppState({ data: [], pagination: {}, loading: false, error: null, page: 1, search: '' });
    let active = null;
    let requestNumber = 0;
    let debounceId = null;

    const renderMessage = (message, retry = false) => {
        body.innerHTML = `<tr><td colspan="${columns}"><div role="${retry ? 'alert' : 'status'}" style="padding:1.5rem;text-align:center">${escapeHtml(message)}${retry ? ' <button type="button" class="boton boton-secundario" data-retry>Reintentar</button>' : ''}</div></td></tr>`;
        pagination.replaceChildren();
    };

    state.subscribe(current => {
        if (current.loading) {
            renderMessage(`Cargando ${label}...`);
            return;
        }
        if (current.error) {
            renderMessage(current.error, true);
            body.querySelector('[data-retry]')?.addEventListener('click', () => load(current.page));
            return;
        }
        if (!current.data.length) {
            renderMessage(emptyMessage);
            return;
        }
        body.innerHTML = current.data.map(record => row(record)).join('');
        const meta = current.pagination;
        pagination.innerHTML = `<button type="button" class="boton boton-secundario" data-page="prev" ${meta.current_page <= 1 ? 'disabled' : ''}>Anterior</button><span class="pagina-actual" aria-current="page">Página ${meta.current_page} de ${meta.last_page}</span><button type="button" class="boton boton-secundario" data-page="next" ${meta.current_page >= meta.last_page ? 'disabled' : ''}>Siguiente</button>`;
    });

    async function load(page = 1) {
        active?.abort();
        const request = new AbortController();
        active = request;
        const identifier = ++requestNumber;
        state.setState({ loading: true, error: null, page });
        const params = new URLSearchParams({ page: String(page), ...pageSize, ...query() });
        if (state.state.search) params.set('q', state.state.search);
        try {
            const result = await api.get(`${endpoint}?${params}`, { signal: request.signal });
            if (identifier !== requestNumber || request.signal.aborted || result?.aborted) return;
            if (!Array.isArray(result?.data)) throw new Error('La API devolvió un listado inválido.');
            state.setState({ data: result.data, pagination: paginationOf(result, page), loading: false });
        } catch (error) {
            if (identifier !== requestNumber || request.signal.aborted || error?.name === 'AbortError') return;
            if (error?.status !== 401) state.setState({ loading: false, error: error?.message ?? 'No se pudo cargar el listado.' });
        } finally {
            if (active === request) active = null;
        }
    }

    pagination.addEventListener('click', event => {
        const action = event.target.closest('button[data-page]')?.dataset.page;
        if (!action) return;
        const { current_page, last_page } = state.state.pagination;
        const next = action === 'prev' ? current_page - 1 : current_page + 1;
        if (next >= 1 && next <= last_page) load(next);
    });

    search?.addEventListener('input', () => {
        clearTimeout(debounceId);
        active?.abort();
        requestNumber++;
        state.setState({ search: search.value.trim(), loading: true, error: null, page: 1 });
        debounceId = setTimeout(() => load(1), 400);
    });

    form?.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(debounceId);
        state.setState({ search: search?.value.trim() ?? '' });
        load(1);
    });

    load(1);
    return { state, load };
}
