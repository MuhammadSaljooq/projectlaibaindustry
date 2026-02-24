<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Use same host so redirect always loads (avoids connection errors)
            $loginPath = route('login', absolute: false);
            return $request->getSchemeAndHttpHost() . $loginPath;
        }

        return null;
    }
}
