<?php

namespace App\Services;

use App\Exceptions\VehiculoException;
use App\Models\Estado;
use App\Models\Vehiculo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehiculoService
{
    /**
     * CAPA 2 (Lectura): Permite el paso a Gestores y Administradores.
     */
    private function verificarPermisosLectura(): void
    {
        $user = Auth::user();
        
        if (!$user instanceof \App\Models\User || !$user->hasAnyRole(['Admin_General', 'Admin_Inventarios', 'Gestor_Rentas'])) {
            throw new AuthorizationException('No autorizado: Se requieren privilegios para consultar vehículos.');
        }
    }

    /**
     * CAPA 2 (Escritura): Restringe la creación, edición y eliminación solo a Administradores.
     */
    private function verificarPermisosEscritura(): void
    {
        $user = Auth::user();
        
        if (!$user instanceof \App\Models\User || !$user->hasAnyRole(['Admin_General', 'Admin_Inventarios'])) {
            throw new AuthorizationException('No autorizado: Se requieren privilegios de administración o inventario.');
        }
    }

    protected function getEstadoIdByName(string $nombre): int
    {
        $estado = Estado::where('nombre', $nombre)->first();

        if (!$estado) {
            throw new VehiculoException("El estado '{$nombre}' no existe en el catálogo de estados.");
        }

        return $estado->id;
    }

    public function listarConFiltros(array $filtros, string $sortBy = 'created_at', string $order = 'desc', int $limit = 10): LengthAwarePaginator
    {
        $this->verificarPermisosLectura();

        $limit = min(max($limit, 1), 50);
        
        $allowedSorts = ['marca', 'anno', 'precio_diario', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return Vehiculo::with(['categoria', 'estado']) 
            ->when($filtros['categoria_id'] ?? null, fn($q, $cat) => $q->where('categoria_id', $cat))
            ->when($filtros['q'] ?? null, fn($q, $search) => 
                $q->where('placa', 'like', "{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
            )
            ->orderBy($sortBy, $order)
            ->orderBy('id', 'desc')
            ->paginate($limit);
    }

    public function mostrar(Vehiculo $vehiculo): Vehiculo
    {
        $this->verificarPermisosLectura();
        
        return $vehiculo->load(['categoria', 'estado']);
    }

    public function crear(array $data): Vehiculo
    {
        $this->verificarPermisosEscritura();

        if ($data['kilometraje'] > 5000) {
            throw new VehiculoException('No se permite el registro inicial de vehículos con más de 5,000 km.');
        }

        $annoActual = (int) date('Y');

        if (($annoActual - $data['anno']) >= 10 || $data['kilometraje'] > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        } elseif (!isset($data['estado_id'])) {
            $data['estado_id'] = $this->getEstadoIdByName('Disponible');
        }

        return DB::transaction(function () use ($data) {
            $vehiculo = Vehiculo::create($data);

            DB::table('auditoria_vehiculos')->insert([
                'vehiculo_id' => $vehiculo->id,
                'accion' => 'REGISTRO_INICIAL',
                'created_at' => now(),
            ]);

            return $vehiculo->load(['categoria', 'estado']);
        });
    }

    public function actualizar(Vehiculo $vehiculo, array $data): Vehiculo
    {
        $this->verificarPermisosEscritura();

        $annoActual = (int) date('Y');
        $anno = $data['anno'] ?? $vehiculo->anno;

        if (isset($data['kilometraje'])) {
            if ($data['kilometraje'] < $vehiculo->kilometraje) {
                throw new VehiculoException("El kilometraje ({$data['kilometraje']} km) no puede ser menor al histórico registrado ({$vehiculo->kilometraje} km).");
            }
        }

        $kilometrajeFinal = $data['kilometraje'] ?? $vehiculo->kilometraje;

        if (($annoActual - $anno) >= 10 || $kilometrajeFinal > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        }

        $vehiculo->update($data);

        return $vehiculo->fresh(['categoria', 'estado']);
    }

    public function eliminar(Vehiculo $vehiculo): void
    {
        $this->verificarPermisosEscritura();

        if ($vehiculo->rentas()->where('fecha_fin', '>=', now()->toDateString())->exists()) {
            throw new VehiculoException('No se puede eliminar el vehículo porque tiene contratos de alquiler activos.');
        }

        $vehiculo->delete();
    }
}