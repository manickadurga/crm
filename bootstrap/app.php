<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Configure the application
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware alias
        $middleware->alias([
           // 'check.user.auth' => \App\Http\Middleware\CheckUserAuthMiddleware::class,
        ]);
        // Register the middleware in the web middleware group $middleware->group('web', [ \jdavidbakr\MailTracker\Middleware\TrackEmail::class, ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configure exceptions handling if needed
    })
    ->create();

    // Register the MailTracker service provider 
    $app->register(jdavidbakr\MailTracker\MailTrackerServiceProvider::class);

    
