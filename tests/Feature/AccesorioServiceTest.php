<?php

use App\Exceptions\AccesorioException;
use App\Models\Accesorio;
use App\Models\Renta;
use App\Models\User;
use App\Services\AccesorioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::create([
        'name' => 'Admin Accesorio Test',
        'email' => 'admin_accesorio_' . uniqid() . '@correo.com',
        'password' => 'Password123',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Admin_General',
        'guard_name' => 'web',
    ]);

    $admin->assignRole($role);
    $this->actingAs($admin);
});


it('no permite eliminar un accesorio con rentas asociadas', function () {
    $accesorio = Accesorio::factory()->create();
    $renta = Renta::factory()->create();

    $renta->accesorios()->attach($accesorio->id, [
        'cantidad' => 1,
        'precio_diario' => $accesorio->precio_unitario,
        'subtotal' => $accesorio->precio_unitario,
    ]);

    expect(fn () => app(AccesorioService::class)->eliminar($accesorio))
        ->toThrow(AccesorioException::class);

    expect(Accesorio::find($accesorio->id))->not->toBeNull();
});

it('no permite agregar un accesorio a una renta finalizada', function () {
    $accesorio = Accesorio::factory()->create(['precio_unitario' => 1000]);
    $renta = Renta::factory()->create([
        'fecha_fin' => now()->subDay(),
        'monto_total' => 10000,
    ]);

    expect(fn () => app(AccesorioService::class)->agregarARenta($accesorio, $renta, 1))
        ->toThrow(AccesorioException::class);

    expect(DB::table('accesorio_renta')->count())->toBe(0);
});

it('no permite agregar dos veces el mismo accesorio a una renta', function () {
    $accesorio = Accesorio::factory()->create(['precio_unitario' => 1000]);
    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    $renta->accesorios()->attach($accesorio->id, [
        'cantidad' => 1,
        'precio_diario' => 1000,
        'subtotal' => 1000,
    ]);

    expect(fn () => app(AccesorioService::class)->agregarARenta($accesorio, $renta, 1))
        ->toThrow(AccesorioException::class);
});

it('congela el precio, calcula subtotal y actualiza la renta dentro de la operacion', function () {
    $accesorio = Accesorio::factory()->create(['precio_unitario' => 1500]);
    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    app(AccesorioService::class)->agregarARenta($accesorio, $renta, 2);

    $pivot = DB::table('accesorio_renta')->first();

    expect((float) $pivot->precio_diario)->toBe(1500.0);
    expect((float) $pivot->subtotal)->toBe(3000.0);
    expect((float) $renta->fresh()->monto_total)->toBe(13000.0);
});

it('revierte la transaccion si ocurre un fallo despues de insertar la pivote', function () {
    $accesorio = Accesorio::factory()->create(['precio_unitario' => 1500]);
    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    Renta::updating(function () {
        throw new RuntimeException('Fallo simulado durante la actualizacion de la renta.');
    });

    expect(fn () => app(AccesorioService::class)->agregarARenta($accesorio, $renta, 2))
        ->toThrow(RuntimeException::class);

    expect(DB::table('accesorio_renta')->count())->toBe(0);
    expect((float) $renta->fresh()->monto_total)->toBe(10000.0);

    Renta::flushEventListeners();
});