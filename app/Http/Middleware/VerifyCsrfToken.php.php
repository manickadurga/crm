<?php


namespace App\Http\Middleware; 
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware; 
class VerifyCsrfToken extends Middleware { 
    protected $except = [
        'api/email-event-webhook',
        ]; 
}


// use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;


    // protected function shouldIgnore($request)
    // {
    //     foreach ($this->except as $except) {
    //         if ($request->fullUrlIs($except) || $request->is($except)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

