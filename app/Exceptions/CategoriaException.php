<?php

namespace App\Exceptions;

use Exception;

class BusinessRuleException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'error' => $this->getMessage()
        ], 422);
    }
}