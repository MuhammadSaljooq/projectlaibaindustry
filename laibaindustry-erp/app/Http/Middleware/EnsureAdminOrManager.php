<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['admin', 'manager'], true)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'You do not have permission to access user management.');
        }

        return $next($request);
    }
}
