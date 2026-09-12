<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Renta;
use App\Models\Vehiculo;
use App\Services\CategoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoriaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoriaService();
    }

    public function test_regla_1_no_permite_crear_categoria_reservada()
    {
        $this->expectException(BusinessRuleException::class);
        $this->service->crear(['nombre' => 'En Mantenimiento']);
    }

    public function test_regla_2_no_permite_modificar_categoria_con_vehiculo_en_renta_activa()
    {
        $categoria = Categoria::factory()->create();
        $vehiculo = Vehiculo::factory()->create(['categoria_id' => $categoria->id]);
        $cliente = Cliente::factory()->create();

        Renta::factory()->create([
            'vehiculo_id' => $vehiculo->id,
            'cliente_id'  => $cliente->id,
            'fecha_fin'   => now()->addDays(5),
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->service->actualizar($categoria, ['nombre' => 'Nuevo Nombre']);
    }

    public function test_regla_3_no_permite_eliminar_categoria_con_vehiculos()
    {
        $categoria = Categoria::factory()->create();
        Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

        $this->expectException(BusinessRuleException::class);
        $this->service->eliminar($categoria);
    }

    public function test_regla_4_no_permite_eliminar_categoria_base_id_1()
    {
        $categoria = Categoria::factory()->create(['id' => 1]);

        $this->expectException(BusinessRuleException::class);
        $this->service->eliminar($categoria);
    }
}