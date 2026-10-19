import { mountListing, escapeHtml as e } from '../../ui/PaginatedListing.js';

mountListing({
    endpoint: '/clientes',
    body: document.getElementById('cuerpo-tabla-clientes'),
    pagination: document.querySelector('.paginacion'),
    search: document.getElementById('buscar-cliente'),
    form: document.querySelector('.formulario-filtros'),
    columns: 4,
    label: 'clientes',
    emptyMessage: 'No se encontraron clientes.',
    pageSize: {'perPage': '10'},
    row: v => `<tr><td>${e(v.cedula)}</td><td>${e([v.nombre1,v.nombre2,v.apellido1,v.apellido2].filter(Boolean).join(' '))}</td><td>${e(v.correo)}</td><td>${e(v.telefono)}</td></tr>`
});
