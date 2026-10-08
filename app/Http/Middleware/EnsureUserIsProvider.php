<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isProvider()) {
            abort(403, 'Accès réservé aux prestataires approuvés.');
        }

        if (! $user->serviceProvider || $user->serviceProvider->status !== 'approved') {
            abort(403, 'Votre profil prestataire doit être approuvé pour accéder à cette section.');
        }

        return $next($request);
    }
}
