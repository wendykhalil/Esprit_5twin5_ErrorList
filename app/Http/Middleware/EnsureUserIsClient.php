<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isClient()) {
            abort(403, 'Cette action est réservée aux comptes client.');
        }

        return $next($request);
    }
}
