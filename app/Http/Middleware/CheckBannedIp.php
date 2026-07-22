<?php

namespace App\Http\Middleware;

use App\Settings\SecuritySettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedIp
{
    /**
     * Block every request coming from an IP listed in the security settings.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bannedIps = rescue(fn (): array => app(SecuritySettings::class)->banned_ips, [], false);

        if (in_array($request->ip(), $bannedIps, true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
