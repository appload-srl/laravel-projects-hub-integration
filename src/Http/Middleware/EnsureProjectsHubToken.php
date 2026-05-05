<?php

namespace Appload\ProjectsHub\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectsHubToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('projects-hub.route.auth.enabled')) {
            return $next($request);
        }

        $expectedToken = config('projects-hub.route.auth.x-api-key');

        if (! $expectedToken) {
            abort(403, 'Projects Hub token is not configured.');
        }

        $providedToken = $request->header('X-Api-Key');

        if (! hash_equals((string) $expectedToken, (string) $providedToken)) {
            abort(403, 'Invalid Projects Hub token.');
        }

        return $next($request);
    }
}