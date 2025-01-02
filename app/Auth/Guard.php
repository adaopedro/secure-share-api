<?php

namespace App\Auth;

abstract class Guard
{

    public static function protect(callable $middleware): ProtectedRoute
    {
        return new ProtectedRoute($middleware);
    }
}
