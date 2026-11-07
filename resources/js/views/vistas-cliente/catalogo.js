import { ApiClient } from '../../api/ApiClient.js';
import { AppState } from '../../store/AppState.js';
import { escapeHtml as e, paginationOf } from '../../ui/PaginatedListing.js';

const cards = document.querySelector('.catalogo-vehiculos');
const pages = document.querySelector('.paginacion');
const search = document.getElementById('buscar-vehiculo');
const category = document.getElementById('categoria');
const availability = document.getElementById('disponibilidad');
const form = document.querySelector('.formulario-filtros');
const api = new ApiClient('/api');
const store = new AppState({ data: [], pagination: {}, loading: false, error: null, page: 1, search: '' });
let active = null;
let timer = null;
let generation = 0;

const dialog = document.createElement('dialog');
dialog.setAttribute('aria-label', 'Detalle del vehículo');
dialog.style.cssText = 'max-width:min(30rem,calc(100% - 2rem));border:1px solid #d1d5db;border-radius:1rem;padding:1.5rem';
document.body.append(dialog);

function render(current) {
    if (!cards || !pages) return;
    pages.replaceChildren();
    if (current.loading) {
        cards.innerHTML = '<p role="status" style="padding:2rem">Cargando vehículos...</p>';
        return;
    }
    if (current.error) {
        cards.innerHTML = `<div role="alert" style="padding:2rem"><p>${e(current.error)}</p><button type="button" class="boton boton-secundario" data-retry>Reintentar</button></div>`;
        cards.querySelector('[data-retry]')?.addEventListener('click', () => load(current.page));
        return;
    }
    if (!current.data.length) {
        cards.innerHTML = '<p role="status" style="padding:2rem">No se encontraron vehículos con estos filtros.</p>';
        return;
    }
    cards.innerHTML = '<h2 id="titulo-catalogo" class="visualmente-oculto">Listado de vehículos</h2>' + current.data.map(v => {
        const state = String(v.estado ?? 'Sin estado');
        const key = state.toLowerCase();
        const kind = key.includes('mantenimiento') ? 'estado-mantenimiento' : key.includes('disponible') ? 'estado-disponible' : 'estado-alquilado';
        return `<article class="tarjeta-vehiculo"><div class="imagen-vehiculo" aria-hidden="true">🚗</div><div class="contenido-tarjeta-vehiculo"><div class="encabezado-tarjeta-vehiculo"><h3>${e(v.marca)} ${e(v.modelo)}</h3><span class="estado ${kind}">${e(state)}</span></div><p class="datos-catalogo">${e(v.anno)} · ${e(v.categoria)} · ${e(Number(v.kilometraje ?? 0).toLocaleString('es-CR'))} km</p><button type="button" class="boton boton-secundario boton-detalle" data-detail="${e(v.id)}">Ver detalles</button></div></article>`;
    }).join('');
    const { current_page: page, last_page: last } = current.pagination;
    pages.innerHTML = `<button type="button" class="boton boton-secundario" data-nav="prev" ${page <= 1 ? 'disabled' : ''}>Anterior</button><span aria-current="page">Página ${page} de ${last}</span><button type="button" class="boton boton-secundario" data-nav="next" ${page >= last ? 'disabled' : ''}>Siguiente</button>`;
}

store.subscribe(render);

async function load(page = 1) {
    active?.abort();
    const controller = new AbortController();
    active = controller;
    const requestId = ++generation;
    store.setState({ loading: true, error: null, page });
    const params = new URLSearchParams({ page: String(page), limit: '10' });
    if (store.state.search) params.set('q', store.state.search);
    if (category?.value) params.set('categoria_id', category.value);
    if (availability?.value) params.set('estado_id', availability.value);
    try {
        const result = await api.get(`/vehiculos?${params}`, { signal: controller.signal });
        if (requestId !== generation || controller.signal.aborted || result?.aborted) return;
        if (!Array.isArray(result?.data)) throw new Error('La API devolvió datos inválidos.');
        store.setState({ data: result.data, pagination: paginationOf(result, page), loading: false });
    } catch (error) {
        if (requestId !== generation || controller.signal.aborted || error?.name === 'AbortError') return;
        if (error?.status !== 401) store.setState({ loading: false, error: error.message ?? 'No se pudo consultar el catálogo.' });
    } finally {
        if (active === controller) active = null;
    }
}

async function loadOptions() {
    const catalogues = await api.get('/catalogos/vehiculos');
    if (!Array.isArray(catalogues.categorias) || !Array.isArray(catalogues.estados)) throw new Error('No se pudieron cargar los filtros.');
    category.replaceChildren(new Option('Todas', ''));
    availability.replaceChildren(new Option('Todos', ''));
    for (const item of catalogues.categorias) category.add(new Option(item.nombre, item.id));
    for (const item of catalogues.estados) availability.add(new Option(item.nombre, item.id));
}

cards?.addEventListener('click', event => {
    const id = event.target.closest('[data-detail]')?.dataset.detail;
    if (!id) return;
    const v = store.state.data.find(item => String(item.id) === id);
    if (!v) return;
    dialog.innerHTML = `<h2>${e(v.marca)} ${e(v.modelo)}</h2><p>Placa: ${e(v.placa)}</p><p>Año: ${e(v.anno)}</p><p>Categoría: ${e(v.categoria)}</p><p>Estado: ${e(v.estado)}</p><p>Kilometraje: ${e(Number(v.kilometraje ?? 0).toLocaleString('es-CR'))} km</p><p>Tarifa diaria: $${e(Number(v.precio_diario ?? 0).toFixed(2))}</p><form method="dialog"><button class="boton boton-secundario">Cerrar</button></form>`;
    dialog.showModal();
});

pages?.addEventListener('click', event => {
    const direction = event.target.closest('[data-nav]')?.dataset.nav;
    if (!direction) return;
    const { current_page: current, last_page: last } = store.state.pagination;
    const page = direction === 'prev' ? current - 1 : current + 1;
    if (page >= 1 && page <= last) load(page);
});

search?.addEventListener('input', () => {
    clearTimeout(timer);
    active?.abort();
    generation++;
    store.setState({ search: search.value.trim(), loading: true, error: null, page: 1 });
    timer = setTimeout(() => load(1), 400);
});

[category, availability].forEach(select => select?.addEventListener('change', () => load(1)));
form?.addEventListener('submit', event => {
    event.preventDefault();
    clearTimeout(timer);
    store.setState({ search: search?.value.trim() ?? '' });
    load(1);
});

(async () => {
    try {
        await loadOptions();
        await load(1);
    } catch (error) {
        if (error?.status !== 401) store.setState({ loading: false, error: error?.message ?? 'No se pudieron cargar los filtros.' });
    }
})();
