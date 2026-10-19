import { mountListing, escapeHtml as e } from '../../ui/PaginatedListing.js';

mountListing({
    endpoint: '/vehiculos',
    body: document.getElementById('cuerpo-tabla-vehiculos'),
    pagination: document.querySelector('.paginacion'),
    search: document.getElementById('buscar-vehiculo'),
    form: document.querySelector('.formulario-filtros'),
    columns: 5,
    label: 'vehiculos',
    emptyMessage: 'No se encontraron vehículos.',
    pageSize: {'limit': '10'},
    row: v => `<tr><td>${e(v.placa)}</td><td>${e(`${v.marca} ${v.modelo}`)}</td><td>${e(v.categoria)}</td><td>${e(Number(v.kilometraje ?? 0).toLocaleString('es-CR'))} km</td><td>${e(v.estado)}</td></tr>`
});
