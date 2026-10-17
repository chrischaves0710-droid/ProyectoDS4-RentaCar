import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Estilos base y scripts principales
                'resources/css/app.css',
                'resources/css/lab8.css',
                'resources/js/app.js',
                
                // Módulos CRUD (Admin/Gerente/Gestor)
                'resources/js/views/categorias/listado-categorias.js',
                'resources/js/views/categorias/formulario-categoria.js',
                'resources/js/views/estados/listado-estados.js',
                'resources/js/views/estados/formulario-estado.js',
                'resources/js/views/accesorios/listado-accesorios.js',
                'resources/js/views/accesorios/formulario-accesorio.js',
                'resources/js/views/rentas/listado-rentas.js',
                
                // (Si tienes otros JS de vehículos o rentas separados, agrégalos aquí)
                
                // Vistas del Cliente (Solo lectura)
                'resources/js/views/vistas-cliente/catalogo.js',
                'resources/js/views/vistas-cliente/accesorios.js',
                'resources/js/views/vistas-cliente/mis-rentas.js',
                'resources/js/views/vistas-cliente/mi-perfil.js',

                // Vistas del Gestor (solo lectura)
                'resources/js/views/vistas-gestor/consultar-vehiculos.js',
                'resources/js/views/vistas-gestor/consultar-accesorios.js',
                'resources/js/views/vistas-gestor/consultar-clientes.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});