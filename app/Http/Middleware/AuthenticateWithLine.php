<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithLine
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('line_id')) {
            return $next($request);
        }

        $tenantPath = $request->attributes->get('tenantPath');
        if (! is_string($tenantPath) || $tenantPath === '') {
            abort(404);
        }

        Session::put('line_intended_url', $request->fullUrl());

        if ($request->filled('registration_token')) {
            Session::put('line_registration_token', $request->string('registration_token')->toString());
        }

        return redirect()->route('line.login', [
            'tenant' => $tenantPath,
            ...($request->filled('registration_token')
                ? ['registration_token' => $request->string('registration_token')->toString()]
                : []),
        ]);
    }
}
