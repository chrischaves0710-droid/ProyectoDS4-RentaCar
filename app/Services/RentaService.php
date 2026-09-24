<?php

namespace App\Services;

use App\Exceptions\RentaException;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\User;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RentaService
{
    /**
     * Lista las rentas.
     *
     * Admin_General y Gestor_Rentas pueden listar todas.
     * Cliente solamente puede listar sus propias rentas.
     */
    public function listar(array $filtros)
    {
        $user = $this->usuarioAutenticado();

        $query = Renta::with([
            'cliente',
            'vehiculo',
            'accesorios',
        ]);

        /*
         * Admin_General y Gestor_Rentas pueden consultar
         * todas las rentas y utilizar los filtros recibidos.
         */
        if ($user->hasAnyRole(['Admin_General', 'Gestor_Rentas'])) {

            if (!empty($filtros['cliente_id'])) {
                $query->where(
                    'cliente_id',
                    $filtros['cliente_id']
                );
            }

            if (!empty($filtros['vehiculo_id'])) {
                $query->where(
                    'vehiculo_id',
                    $filtros['vehiculo_id']
                );
            }

            return $query->paginate(10);
        }

        /*
         * Un Cliente únicamente puede ver el historial
         * de rentas asociado a su propio registro.
         */
        if ($user->hasRole('Cliente')) {

            $cliente = Cliente::where(
                'correo',
                $user->email
            )->first();

            /*
             * Si el usuario no tiene un Cliente asociado,
             * no debe recibir rentas de otras personas.
             */
            if (!$cliente) {
                return $query
                    ->whereRaw('1 = 0')
                    ->paginate(10);
            }

            return $query
                ->where('cliente_id', $cliente->id)
                ->paginate(10);
        }

        throw new AuthorizationException(
            'No autorizado para consultar rentas.'
        );
    }

    /**
     * Obtiene una renta específica.
     *
     * Admin_General y Gestor_Rentas pueden ver cualquiera.
     * Cliente solamente puede ver una renta propia.
     */
    public function obtener($id): Renta
    {
        $renta = Renta::with([
            'cliente',
            'vehiculo',
            'accesorios',
        ])->find($id);

        if (!$renta) {
            throw new NotFoundHttpException(
                'Renta no encontrada.'
            );
        }

        $this->verificarAccesoRenta($renta);

        return $renta;
    }

    /**
     * Crea una renta.
     *
     * Solamente Admin_General y Gestor_Rentas.
     */
    public function crear(array $datos): Renta
    {
        $this->verificarGestionRentas();

        $vehiculo = Vehiculo::with('estado')
            ->findOrFail($datos['vehiculo_id']);

        $this->validarDisponibilidad($vehiculo);

        $inicio = Carbon::parse(
            $datos['fecha_inicio']
        );

        $fin = Carbon::parse(
            $datos['fecha_fin']
        );

        $dias = $inicio->diffInDays($fin);

        if ($dias < 1) {
            throw new RentaException(
                'La renta debe tener una duración mínima de un día.'
            );
        }

        $datos['monto_total'] =
            $dias * $datos['precio_diario'];

        return DB::transaction(
            function () use ($datos, $vehiculo) {

                $renta = Renta::create($datos);

                $estadoAlquilado = Estado::where(
                    'nombre',
                    'Alquilado'
                )->first();

                if (!$estadoAlquilado) {
                    throw new RentaException(
                        'No se encontró el estado Alquilado.'
                    );
                }

                $vehiculo->update([
                    'estado_id' => $estadoAlquilado->id,
                ]);

                return $renta->load([
                    'cliente',
                    'vehiculo',
                    'accesorios',
                ]);
            }
        );
    }

    /**
     * Actualiza una renta.
     *
     * Solamente Admin_General y Gestor_Rentas.
     */
    public function actualizar($id, array $datos): Renta
    {
        $this->verificarGestionRentas();

        $renta = $this->obtener($id);

        $fechaInicio = Carbon::parse(
            $datos['fecha_inicio']
                ?? $renta->fecha_inicio
        );

        $fechaFin = Carbon::parse(
            $datos['fecha_fin']
                ?? $renta->fecha_fin
        );

        if ($fechaFin->lte($fechaInicio)) {
            throw new RentaException(
                'La fecha final debe ser posterior a la fecha inicial.'
            );
        }

        $precio = $datos['precio_diario']
            ?? $renta->precio_diario;

        $dias = $fechaInicio->diffInDays(
            $fechaFin
        );

        $datos['monto_total'] =
            $dias * $precio;

        $renta->update($datos);

        return $renta->load([
            'cliente',
            'vehiculo',
            'accesorios',
        ]);
    }

    /**
     * Elimina una renta.
     *
     * Solamente Admin_General y Gestor_Rentas.
     */
    public function eliminar($id): void
    {
        $this->verificarGestionRentas();

        $renta = $this->obtener($id);

        $renta->delete();
    }
    /**
 * Finaliza una renta.
 *
 * Cambia el vehículo asociado nuevamente a Disponible.
 * Solamente Admin_General y Gestor_Rentas.
 */
public function finalizar($id): Renta
{
    $this->verificarGestionRentas();

    $renta = $this->obtener($id);

    $estadoDisponible = Estado::where(
        'nombre',
        'Disponible'
    )->first();

    if (!$estadoDisponible) {
        throw new RentaException(
            'No se encontró el estado Disponible.'
        );
    }

    return DB::transaction(function () use (
        $renta,
        $estadoDisponible
    ) {

        $renta->vehiculo->update([
            'estado_id' => $estadoDisponible->id,
        ]);

        return $renta->load([
            'cliente',
            'vehiculo',
            'accesorios',
        ]);
    });
}

    /**
     * Obtiene el usuario autenticado.
     *
     * También protege al Service cuando alguien intenta
     * invocarlo directamente sin pasar por api.php.
     */
    private function usuarioAutenticado(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            throw new AuthorizationException(
                'Usuario no autorizado.'
            );
        }

        return $user;
    }

    /**
     * Comprueba quién puede administrar Rentas.
     *
     * Esta es la segunda capa de seguridad:
     * Admin_General y Gestor_Rentas tienen CRUD.
     */
    private function verificarGestionRentas(): void
    {
        $user = $this->usuarioAutenticado();

        if (!$user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ])) {
            throw new AuthorizationException(
                'No autorizado para administrar rentas.'
            );
        }
    }

    /**
     * Comprueba el acceso a una renta específica.
     *
     * Admin_General y Gestor_Rentas pueden ver cualquiera.
     * Para Cliente se utiliza RentaPolicy para comprobar
     * que la renta realmente le pertenezca.
     */
    private function verificarAccesoRenta(
        Renta $renta
    ): void {
        $user = $this->usuarioAutenticado();

        if ($user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ])) {
            return;
        }

        if ($user->hasRole('Cliente')) {
            Gate::forUser($user)
                ->authorize('view', $renta);

            return;
        }

        throw new AuthorizationException(
            'No autorizado para consultar esta renta.'
        );
    }

    /**
     * Regla de negocio:
     * el vehículo debe estar Disponible.
     */
    private function validarDisponibilidad(
        Vehiculo $vehiculo
    ): void {
        if (!$vehiculo->estado) {
            throw new RentaException(
                "El vehículo {$vehiculo->placa} no tiene estado asignado."
            );
        }

        if ($vehiculo->estado->nombre !== 'Disponible') {
            throw new RentaException(
                "El vehículo {$vehiculo->placa} no está disponible " .
                "(Estado actual: {$vehiculo->estado->nombre})."
            );
        }
    }
}