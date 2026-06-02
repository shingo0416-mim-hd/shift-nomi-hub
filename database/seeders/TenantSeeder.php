<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'id' => 1,
                'name' => '株式会社MIM',
                'path' => 'mim-hd',
            ],
        ];

        foreach ($tenants as $tenant) {
            DB::table('tenants')->updateOrInsert(
                ['id' => $tenant['id']],
                [
                    'name' => $tenant['name'],
                    'data' => json_encode(['path' => $tenant['path']]),
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            info("テナント登録完了: {$tenant['name']} ({$tenant['path']})");
        }
    }
}
