<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkUserMiddleware
{
    /**
     * Handle an incoming request.
     * Note the use of ...$roles (the spread operator) to accept multiple arguments.
     */
    // Use ...$roles to catch all roles passed from the controller
// app/Http/Middleware/checkUserMiddleware.php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (!in_array(auth()->user()->role, $roles)) {
        abort(403, 'Unauthorized Access');
    }

    return $next($request);
}
}