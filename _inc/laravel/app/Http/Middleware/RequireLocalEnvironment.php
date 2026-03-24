<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to routes unless the application is running in the local environment.
 * Used to guard installer/updater wizard routes that hang in non-local contexts.
 */
class RequireLocalEnvironment
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->isLocal(), 403, 'This route is only available in the local environment.');
        return $next($request);
    }
}
