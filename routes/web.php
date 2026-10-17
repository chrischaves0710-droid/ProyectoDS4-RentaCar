<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// 1. Vistas del Gerente General (Admin_General)
// ==========================================

// Módulo de Vehículos (Carpeta: Vistas_Gerente_General/Vehiculos)
Route::view('/lab8/gerente/vehiculos', 'lab8.Vistas_Gerente_General.Vehiculos.gestion-vehiculos')->name('gerente.gestion-vehiculos');
Route::view('/lab8/gerente/vehiculos/crear', 'lab8.Vistas_Gerente_General.Vehiculos.crear-vehiculo')->name('gerente.crear-vehiculo');
Route::view('/lab8/gerente/vehiculos/editar', 'lab8.Vistas_Gerente_General.Vehiculos.editar-vehiculo')->name('gerente.editar-vehiculo');

// Módulo de Accesorios (Carpeta: Vistas_Gerente_General/Accesorios)
Route::view('/lab8/gerente/accesorios', 'lab8.Vistas_Gerente_General.Accesorios.gestion-accesorios')->name('gerente.gestion-accesorios');
Route::view('/lab8/gerente/accesorios/crear', 'lab8.Vistas_Gerente_General.Accesorios.crear-accesorio')->name('gerente.crear-accesorio');
Route::view('/lab8/gerente/accesorios/editar', 'lab8.Vistas_Gerente_General.Accesorios.editar-accesorio')->name('gerente.editar-accesorio');

// Módulo de Clientes (Carpeta: Vistas_Gerente_General/Clientes)
Route::view('/lab8/gerente/clientes', 'lab8.Vistas_Gerente_General.Clientes.gestion-clientes')->name('gerente.gestion-clientes');
Route::view('/lab8/gerente/clientes/registrar', 'lab8.Vistas_Gerente_General.Clientes.registrar-cliente')->name('gerente.registrar-cliente');
Route::view('/lab8/gerente/clientes/editar', 'lab8.Vistas_Gerente_General.Clientes.editar-cliente')->name('gerente.editar-cliente');

// Módulo de Rentas (Carpeta: Vistas_Gerente_General/Rentas y Vistas_Gestor_Rentas)
Route::view('/lab8/gerente/rentas', 'lab8.Vistas_Gerente_General.Rentas.gestion-rentas')->name('gerente.gestion-rentas');
Route::view('/lab8/gerente/rentas/consultar', 'lab8.Vistas_Gerente_General.Rentas.consultar-renta')->name('gerente.consultar-renta');
Route::view('/lab8/gerente/rentas/editar', 'lab8.Vistas_Gerente_General.Rentas.editar-renta')->name('gerente.editar-renta');
Route::view('/lab8/gerente/rentas/nueva', 'lab8.Vistas_Gerente_General.Rentas.nueva-renta')->name('gerente.nueva-renta');

// Módulo de Categorías (Carpeta: Vistas_Gerente_General/Categorias)
Route::view('/lab8/gerente/categorias', 'lab8.Vistas_Gerente_General.Categorias.gestion-categorias')->name('gerente.gestion-categorias');
Route::view('/lab8/gerente/categorias/crear', 'lab8.Vistas_Gerente_General.Categorias.crear-categoria')->name('gerente.crear-categoria');
Route::view('/lab8/gerente/categorias/editar', 'lab8.Vistas_Gerente_General.Categorias.editar-categoria')->name('gerente.editar-categoria');

// Módulo de Estados (Carpeta: Vistas_Gerente_General/Estados)
Route::view('/lab8/gerente/estados', 'lab8.Vistas_Gerente_General.Estados.gestion-estados')->name('gerente.gestion-estados');
Route::view('/lab8/gerente/estados/crear', 'lab8.Vistas_Gerente_General.Estados.crear-estado')->name('gerente.crear-estado');
Route::view('/lab8/gerente/estados/editar', 'lab8.Vistas_Gerente_General.Estados.editar-estado')->name('gerente.editar-estado');

// ==========================================
// 2. Vistas del Admin de Inventarios
// ==========================================

// Módulo de Vehículos (Carpeta: Vistas_Admin_Inventarios/Vehiculos)
Route::view('/lab8/admin/vehiculos', 'lab8.Vistas_Admin_Inventarios.Vehiculos.gestion-vehiculos')->name('admin.gestion-vehiculos');
Route::view('/lab8/admin/vehiculos/crear', 'lab8.Vistas_Admin_Inventarios.Vehiculos.crear-vehiculo')->name('admin.crear-vehiculo');
Route::view('/lab8/admin/vehiculos/editar', 'lab8.Vistas_Admin_Inventarios.Vehiculos.editar-vehiculo')->name('admin.editar-vehiculo');

// Módulo de Accesorios (Carpeta: Vistas_Admin_Inventarios/Accesorios)
Route::view('/lab8/admin/accesorios', 'lab8.Vistas_Admin_Inventarios.Accesorios.gestion-accesorios')->name('admin.gestion-accesorios');
Route::view('/lab8/admin/accesorios/crear', 'lab8.Vistas_Admin_Inventarios.Accesorios.crear-accesorio')->name('admin.crear-accesorio');
Route::view('/lab8/admin/accesorios/editar', 'lab8.Vistas_Admin_Inventarios.Accesorios.editar-accesorio')->name('admin.editar-accesorio');

// Módulo de Categorías (Carpeta: Vistas_Admin_Inventarios/Categorias)
Route::view('/lab8/admin/categorias', 'lab8.Vistas_Admin_Inventarios.Categorias.gestion-categorias')->name('admin.gestion-categorias');
Route::view('/lab8/admin/categorias/crear', 'lab8.Vistas_Admin_Inventarios.Categorias.crear-categoria')->name('admin.crear-categoria');
Route::view('/lab8/admin/categorias/editar', 'lab8.Vistas_Admin_Inventarios.Categorias.editar-categoria')->name('admin.editar-categoria');

// Módulo de Estados (Carpeta: Vistas_Admin_Inventarios/Estados)
Route::view('/lab8/admin/estados', 'lab8.Vistas_Admin_Inventarios.Estados.gestion-estados')->name('admin.gestion-estados');
Route::view('/lab8/admin/estados/crear', 'lab8.Vistas_Admin_Inventarios.Estados.crear-estado')->name('admin.crear-estado');
Route::view('/lab8/admin/estados/editar', 'lab8.Vistas_Admin_Inventarios.Estados.editar-estado')->name('admin.editar-estado');


// ==========================================
// 3. Vistas del Gestor de Rentas
// ==========================================

// Módulo de Rentas (CRUD Activo - Carpeta: Vistas_Gestor_Rentas)
Route::view('/lab8/gestor/rentas', 'lab8.Vistas_Gestor_Rentas.gestion-rentas')->name('gestor.gestion-rentas');
Route::view('/lab8/gestor/rentas/nueva', 'lab8.Vistas_Gestor_Rentas.nueva-renta')->name('gestor.nueva-renta');
Route::view('/lab8/gestor/rentas/consultar', 'lab8.Vistas_Gestor_Rentas.consultar-renta')->name('gestor.consultar-renta');
Route::view('/lab8/gestor/rentas/editar', 'lab8.Vistas_Gestor_Rentas.editar-renta')->name('gestor.editar-renta');

// Vistas de Consultas pasivas (Carpeta: Vistas_Gestor_Rentas/Vistas_Consultas)
Route::view('/lab8/gestor/consultar/vehiculos', 'lab8.Vistas_Gestor_Rentas.Vistas_Consultas.consultar-vehiculos')->name('gestor.consultar-vehiculos');
Route::view('/lab8/gestor/consultar/accesorios', 'lab8.Vistas_Gestor_Rentas.Vistas_Consultas.consultar-accesorios')->name('gestor.consultar-accesorios');
Route::view('/lab8/gestor/consultar/clientes', 'lab8.Vistas_Gestor_Rentas.Vistas_Consultas.consultar-clientes')->name('gestor.consultar-clientes');


// ==========================================
// 4. Vistas del Cliente
// ==========================================

// Vistas de solo lectura y perfil (Carpeta: Vistas_Cliente)
Route::view('/lab8/cliente/catalogo', 'lab8.Vistas_Cliente.catalogo')->name('cliente.catalogo');
Route::view('/lab8/cliente/mis-rentas', 'lab8.Vistas_Cliente.mis-rentas')->name('cliente.mis-rentas');
Route::view('/lab8/cliente/mi-perfil', 'lab8.Vistas_Cliente.mi-perfil')->name('cliente.mi-perfil');

// Documentación Swagger UI en el navegador
Route::view('/docs', 'docs.index');

// Entrega del archivo físico OpenAPI YAML
Route::get('/docs/openapi.yaml', function () {
    return response()->file(
        base_path('docs/openapi.yaml'),
        ['Content-Type' => 'application/yaml']
    );
});

// Rutas CRUD API
Route::resource('vehiculos', VehiculoController::class);
Route::resource('accesorios', AccesorioController::class);
Route::resource('rentas', RentaController::class);
Route::resource('estados', EstadoController::class);
Route::resource('categorias', CategoriaController::class);


// Rutas de reportes y consultas
Route::prefix('reportes')->group(function () {
    Route::get('/scopes', [ReporteController::class, 'obtenerRentasVigentesPorMonto']);
    Route::get('/group-by', [ReporteController::class, 'obtenerIngresosYTotalesPorVehiculo']);
    Route::get('/pivote', [ReporteController::class, 'obtenerEstadisticasAccesoriosRenta']);
});