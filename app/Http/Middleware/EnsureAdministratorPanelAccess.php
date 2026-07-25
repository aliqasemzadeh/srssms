<?php

namespace App\Http\Middleware;

use App\Services\Permission\PermissionCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministratorPanelAccess
{
    public function __construct(
        private readonly PermissionCatalog $catalog,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->hasRole(PermissionCatalog::ROLE_NAME)) {
            return $next($request);
        }

        if ($user->hasAnyPermission($this->catalog->names())) {
            return $next($request);
        }

        abort(403);
    }
}
