<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Categoria;
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

    /** @test */
    public function regla_1_no_permite_crear_categorias_reservadas()
    {
        $this->expectException(BusinessRuleException::class);
        $this->service->crear(['nombre' => 'En Mantenimiento']);
    }

    /** @test */
    public function regla_2_no_permite_modificar_categoria_con_vehiculos_en_renta_activa()
    {
        $categoria = Categoria::factory()->create();
        
        $this->expectException(BusinessRuleException::class);
        $this->service->actualizar($categoria, ['nombre' => 'Nuevo Nombre']);
    }

    /** @test */
    public function regla_3_no_permite_eliminar_categoria_con_vehiculos_asociados()
    {
        $categoria = Categoria::factory()->create();
        Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

        $this->expectException(BusinessRuleException::class);
        $this->service->eliminar($categoria);
    }

    /** @test */
    public function regla_4_no_permite_eliminar_categoria_principal_id_1()
    {
        $categoria = Categoria::factory()->create(['id' => 1]);

        $this->expectException(BusinessRuleException::class);
        $this->service->eliminar($categoria);
    }
}