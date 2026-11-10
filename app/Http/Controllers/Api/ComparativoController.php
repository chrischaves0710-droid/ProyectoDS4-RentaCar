<?php

namespace App\Http\Controllers\Api;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class ComparativoController
{
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:128'],
        ]);
        $writer = config('comparativo.writer');
        $reader = config('comparativo.reader');
        $password = config('comparativo.password');
        abort_unless(is_string($password) && $password !== ''
            && hash_equals($password, $input['password'])
            && in_array($input['email'], [$writer, $reader], true), 401);

        $token = bin2hex(random_bytes(32));
        DB::table('benchmark_tokens')->insert([
            'hash' => hash('sha256', $token),
            'role' => $input['email'] === $writer ? 'Admin_Inventarios' : 'Cliente',
            'expires' => time() + 1800,
        ]);

        return ['token' => $token];
    }

    public function index(Request $request)
    {
        $this->authorizeToken($request);
        $input = $request->validate([
            'page' => ['sometimes', 'required', 'integer', 'between:1,2147483647'],
            'per_page' => ['sometimes', 'required', 'integer', 'between:1,100'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $page = (int) ($input['page'] ?? 1);
        $perPage = (int) ($input['per_page'] ?? 10);
        $query = DB::table('vehiculos')->orderBy('id');
        if (filled($input['q'] ?? null)) {
            $term = trim($input['q']);
            $query->where(function ($query) use ($term) {
                foreach (['placa', 'marca', 'modelo'] as $field) {
                    $query->orWhereRaw("instr(upper($field), upper(?)) > 0", [$term]);
                }
            });
        }
        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return ['data' => $result->items(), 'meta' => [
            'current_page' => $page, 'per_page' => $perPage,
            'total' => $result->total(), 'last_page' => $result->lastPage(),
        ]];
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeToken($request);
        return ['data' => $this->find($id)];
    }

    public function store(Request $request)
    {
        $this->authorizeToken($request, true);
        $id = DB::table('vehiculos')->insertGetId($this->vehicleInput($request));
        return response()->json(['data' => $this->find($id)], 201)
            ->header('Location', url("/api/vehiculos/$id"));
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeToken($request, true);
        $this->find($id);
        DB::table('vehiculos')->where('id', $id)->update($this->vehicleInput($request, $id));
        return ['data' => $this->find($id)];
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeToken($request, true);
        $this->find($id);
        try {
            DB::table('vehiculos')->where('id', $id)->delete();
        } catch (QueryException $error) {
            if (!str_contains($error->getMessage(), 'FOREIGN KEY constraint failed')) {
                throw $error;
            }
            return response()->json(['message' => 'El vehículo tiene rentas asociadas.'], 409);
        }
        return response()->noContent();
    }

    private function find(int $id): object
    {
        $item = DB::table('vehiculos')->find($id);
        abort_unless($item, 404);
        return $item;
    }

    private function authorizeToken(Request $request, bool $write = false): void
    {
        $token = $request->bearerToken();
        abort_unless($token, 401);
        $record = DB::table('benchmark_tokens')->where('hash', hash('sha256', $token))
            ->where('expires', '>', time())->first();
        abort_unless($record, 401);
        abort_if($write && $record->role !== 'Admin_Inventarios', 403);
    }

    private function vehicleInput(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos')->ignore($id)],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'anno' => ['required', 'integer', 'between:1900,'.(gmdate('Y') + 1)],
            'kilometraje' => ['required', 'integer', 'between:0,2147483647'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
        ]);
    }
}
