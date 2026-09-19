<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class CategoriaException extends Exception
{
  
    public function render(Request $request)
    {
        return response()->json([
            'error' => 'Conflict',
            'message' => $this->getMessage()
        ], 409); 
    }
}