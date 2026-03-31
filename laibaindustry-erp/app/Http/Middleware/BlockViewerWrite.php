<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockViewerWrite
{
    /**
     * Block viewers from any write action (create, update, delete).
     * Viewers can only view: index, show, and read-only pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'viewer') {
            $name = $request->route()?->getName() ?? '';
            // Always allow logout so viewers can sign out
            if ($name === 'logout') {
                return $next($request);
            }
            $method = $request->method();
            // Block all write methods
            if (! in_array($method, ['GET', 'HEAD'], true)) {
                abort(403, 'You do not have permission to create, edit, or delete. Viewers can only view.');
            }
            // Block GET to create or edit pages (viewers must not see forms)
            if (str_ends_with($name, '.create') || str_ends_with($name, '.edit')) {
                // Supplier edit doubles as read-only supplier + account ledger for viewers.
                if ($name === 'suppliers.edit') {
                    return $next($request);
                }
                abort(403, 'You do not have permission to access this page. Viewers can only view.');
            }
        }

        return $next($request);
    }
}
