<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'api/*',
        'web/' ,// Add this line to exclude all routes with the /api prefix
    ];

    protected function shouldIgnore($request)
    {
        foreach ($this->except as $except) {
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
