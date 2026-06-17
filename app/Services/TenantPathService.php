<?php

namespace App\Services;

use App\Models\Tenant;

class TenantPathService
{
    public function findByPath(string $path): ?Tenant
    {
        return Tenant::query()
            ->where('data->path', $path)
            ->first();
    }

    public function pathFor(Tenant $tenant): ?string
    {
        $path = data_get($tenant->data, 'path');

        return is_string($path) && $path !== '' ? $path : null;
    }
}
