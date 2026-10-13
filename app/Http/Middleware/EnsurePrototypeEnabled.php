<?php

namespace App\Http\Middleware;

use App\Prototype\PrototypeManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrototypeEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! PrototypeManager::isEnabled()) {
            abort(404);
        }

        return $next($request);
    }
}
