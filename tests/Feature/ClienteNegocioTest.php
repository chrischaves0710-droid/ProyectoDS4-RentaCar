<?php

namespace Tests\Feature;

use App\Exceptions\ClienteException;
use App\Models\Cliente;
use App\Models\Renta;
use App\Services\ClienteService;
use Tests\TestCase;

class ClienteNegocioTest extends TestCase
{
    private ClienteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ClienteService::class);
    }

    /** @test */
   public function test_impide_registrar_un_cliente_menor_de_edad(): void
    {
        $data = [
            'cedula' => '123456789',
            'nombre1' => 'Juan',
            'nombre2' => null,
            'apellido1' => 'Pérez',
            'apellido2' => null,
            'anno_nacimiento' => date('Y') - 17,
            'telefono' => '88888888',
            'correo' => 'juan@example.com',
        ];

        $this->expectException(ClienteException::class);
        $this->expectExceptionMessage(
            'El cliente debe ser mayor de edad.'
        );

        $this->service->crear($data);
    }

    /** @test */
   public function test_impide_eliminar_un_cliente_con_rentas_asociadas(): void
    {
        $cliente = Cliente::factory()->create();

        Renta::factory()->create([
            'cliente_id' => $cliente->id,
        ]);

        $this->expectException(ClienteException::class);
        $this->expectExceptionMessage(
            'No se puede eliminar el cliente porque tiene rentas asociadas.'
        );

        $this->service->eliminar($cliente);

        $this->assertNotNull(
            Cliente::find($cliente->id)
        );
    }

    /** @test */
public function test_permite_crear_un_cliente_correctamente(): void
    {
        $data = [
            'cedula' => '987654321',
            'nombre1' => 'Carlos',
            'nombre2' => null,
            'apellido1' => 'Rodríguez',
            'apellido2' => null,
            'anno_nacimiento' => 1995,
            'telefono' => '88888888',
            'correo' => 'carlos@example.com',
        ];

        $cliente = $this->service->crear($data);

        $this->assertInstanceOf(
            Cliente::class,
            $cliente
        );

        $this->assertDatabaseHas('clientes', [
            'cedula' => '987654321',
            'nombre1' => 'Carlos',
        ]);
    }

    /** @test */
public function test_permite_actualizar_un_cliente_correctamente(): void
    {
        $cliente = Cliente::factory()->create([
            'anno_nacimiento' => 1995,
        ]);

        $clienteActualizado = $this->service->actualizar(
            $cliente,
            [
                'cedula' => $cliente->cedula,
                'nombre1' => 'Carlos',
                'nombre2' => $cliente->nombre2,
                'apellido1' => $cliente->apellido1,
                'apellido2' => $cliente->apellido2,
                'anno_nacimiento' => $cliente->anno_nacimiento,
                'telefono' => '89999999',
                'correo' => $cliente->correo,
            ]
        );

        $this->assertEquals(
            '89999999',
            $clienteActualizado->telefono
        );

        $this->assertEquals(
            'Carlos',
            $clienteActualizado->nombre1
        );
    }

    /** @test */
  public function test_permite_eliminar_un_cliente_sin_rentas(): void
    {
        $cliente = Cliente::factory()->create();

        $resultado = $this->service->eliminar($cliente);

        $this->assertTrue($resultado);

        $this->assertDatabaseMissing('clientes', [
            'id' => $cliente->id,
        ]);
    }

    /** @test */
   public function test_permite_listar_clientes_con_paginacion_y_limite_maximo(): void
    {
        Cliente::factory()->count(10)->create();

        $resultado = $this->service->listar(
            null,
            'nombre1',
            'asc',
            100
        );

        $this->assertEquals(
            50,
            $resultado->perPage()
        );
    }
}