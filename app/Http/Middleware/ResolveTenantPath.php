<?php

namespace App\Http\Middleware;

use App\Services\TenantPathService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantPath = $request->route('tenant');

        if (! is_string($tenantPath) || $tenantPath === '') {
            abort(404);
        }

        $tenant = app(TenantPathService::class)->findByPath($tenantPath);

        if (! $tenant) {
            abort(404, '指定されたテナントが見つかりません。');
        }

        $request->attributes->set('tenantPath', $tenantPath);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
