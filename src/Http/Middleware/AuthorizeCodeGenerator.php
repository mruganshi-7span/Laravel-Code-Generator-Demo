<?php

namespace Mruganshi\CodeGenerator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeCodeGenerator
{
    /**
     * Handle an incoming request.
     *
     * This middleware restricts access to the code generator in the production environment
     * based on the 'require_auth_in_production' config flag.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            $requireAuth = config('code_generator.require_auth_in_production', false);

            // If authorization is required in production but not enabled, abort with 403 Forbidden.
            if (! $requireAuth) {
                abort(403, 'Access to the code generator is forbidden in production.');
            }
        }

        return $next($request);
    }
}
