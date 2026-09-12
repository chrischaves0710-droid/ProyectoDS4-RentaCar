<?php

namespace Tests\Unit;

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\Vehiculo;
use App\Services\CategoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoriaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoriaService::class);
    }

    #[Test]
    public function regla_1_no_permite_crear_categoria_reservada()
    {
        $this->expectException(CategoriaException::class);
        $this->service->crear(['nombre' => 'En Mantenimiento']);
    }

    #[Test]
    public function regla_2_no_permite_modificar_categoria_con_vehiculo_en_renta_activa()
    {
        $categoria = Categoria::create(['nombre' => 'SUV Premium']);
        $estado = Estado::create(['nombre' => 'Disponible']);

        $cliente = Cliente::create([
            'cedula'          => '12345678',
            'nombre1'         => 'Juan',
            'apellido1'       => 'Perez',
            'correo'          => 'juan@example.com',
            'telefono'        => '88888888',
            'anno_nacimiento' => 1995,
        ]);

        $vehiculo = Vehiculo::create([
            'placa'        => 'ABC-123',
            'marca'        => 'Toyota',
            'modelo'       => 'Corolla',
            'anno'         => 2023,
            'kilometraje'  => 50000,
            'categoria_id' => $categoria->id,
            'estado_id'    => $estado->id,
        ]);

        Renta::create([
            'vehiculo_id'   => $vehiculo->id,
            'cliente_id'    => $cliente->id,
            'fecha_inicio'  => now(),
            'fecha_fin'     => now()->addDays(5),
            'precio_diario' => 50,
            'monto_total'   => 250,
        ]);

        $this->expectException(CategoriaException::class);
        $this->service->actualizar($categoria, ['nombre' => 'Nuevo Nombre']);
    }

    #[Test]
    public function regla_3_no_permite_eliminar_categoria_con_vehiculos()
    {
        $categoria = Categoria::create(['nombre' => 'Sedan']);
        $estado = Estado::create(['nombre' => 'Disponible']);

        Vehiculo::create([
            'placa'        => 'XYZ-789',
            'marca'        => 'Honda',
            'modelo'       => 'Civic',
            'anno'         => 2022,
            'kilometraje'  => 10000,
            'categoria_id' => $categoria->id,
            'estado_id'    => $estado->id,
        ]);

        $this->expectException(CategoriaException::class);
        $this->service->eliminar($categoria);
    }

    #[Test]
    public function regla_4_no_permite_eliminar_categoria_base_id_1()
    {
        $categoria = Categoria::create(['nombre' => 'Categoria Base']);
        $categoria->id = 1;

        $this->expectException(CategoriaException::class);
        $this->service->eliminar($categoria);
    }
}