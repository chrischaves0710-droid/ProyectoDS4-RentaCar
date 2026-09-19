<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class AccesorioException extends Exception
{
    public function render(Request $request)
    {
        return response()->json([
            'error' => 'Conflict',
            'message' => $this->getMessage()
        ], 409);
    }
}