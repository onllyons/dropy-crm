<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class SetTenantDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowed = array_keys(config('dropy.tenants.allowed', []));
        $default = config('dropy.tenants.default', 'onllyons_en');
        $tenantDb = $request->session()->get('tenant_db', $default);

        if (!in_array($tenantDb, $allowed, true)) {
            $tenantDb = $default;
            $request->session()->put('tenant_db', $tenantDb);
        }

        config(['database.connections.tenant.database' => $tenantDb]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        return $next($request);
    }
}
