import { mountListing, escapeHtml as e } from '../../ui/PaginatedListing.js';

mountListing({
    endpoint: '/rentas',
    body: document.querySelector('.contenedor-tabla table tbody'),
    pagination: document.querySelector('.paginacion'),
    search: null,
    form: document.querySelector('.formulario-filtros'),
    columns: 5,
    label: 'rentas',
    emptyMessage: 'No hay rentas asociadas a la cuenta.',
    pageSize: {},
    row: v => `<tr><td>${e(`RN-${v.id}`)}</td><td>${e(v.vehiculo ? `${v.vehiculo.marca} ${v.vehiculo.modelo}` : 'No disponible')}</td><td>${e(v.fecha_inicio)} - ${e(v.fecha_fin)}</td><td>$${e(Number(v.monto_total ?? 0).toFixed(2))}</td><td>${e(new Date(`${v.fecha_fin}T23:59:59`).getTime() >= Date.now() ? 'En curso' : 'Finalizada')}</td></tr>`
});
