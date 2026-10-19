
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Estilos principales
                'resources/css/app.css',
                'resources/css/lab8.css',
                'resources/js/app.js',

                // Autenticación - Laboratorio 9
                'resources/js/views/auth/login.js',

                // Categorías
                'resources/js/views/categorias/listado-categorias.js',
                'resources/js/views/categorias/formulario-categoria.js',

                // Estados
                'resources/js/views/estados/listado-estados.js',
                'resources/js/views/estados/formulario-estado.js',

                // Accesorios
                'resources/js/views/accesorios/listado-accesorios.js',
                'resources/js/views/accesorios/formulario-accesorio.js',

                // Vehículos
                'resources/js/views/vehiculos/listado-vehiculos.js',
                'resources/js/views/vehiculos/formulario-vehiculo.js',

                // Clientes
                'resources/js/views/clientes/listado-clientes.js',
                'resources/js/views/clientes/formulario-cliente.js',

                // Rentas
                'resources/js/views/rentas/listado-rentas.js',
                'resources/js/views/rentas/formulario-renta.js',

                // Vistas del Cliente
                'resources/js/views/vistas-cliente/catalogo.js',
                'resources/js/views/vistas-cliente/accesorios.js',
                'resources/js/views/vistas-cliente/mis-rentas.js',
                'resources/js/views/vistas-cliente/mi-perfil.js',

                // Vistas del Gestor de Rentas
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
