import { mountListing, escapeHtml as e } from '../../ui/PaginatedListing.js';

mountListing({
    endpoint: '/accesorios',
    body: document.getElementById('cuerpo-tabla-accesorios'),
    pagination: document.querySelector('.paginacion'),
    search: document.getElementById('buscar-accesorio'),
    form: document.querySelector('.formulario-filtros'),
    columns: 3,
    label: 'accesorios',
    emptyMessage: 'No se encontraron accesorios.',
    pageSize: {'per_page': '10'},
    row: v => `<tr><td>${e(`ACC-${v.id}`)}</td><td>${e(v.nombre)}</td><td>$${e(Number(v.precio_unitario ?? 0).toFixed(2))}</td></tr>`
});
