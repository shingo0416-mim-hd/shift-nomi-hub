<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'id' => 1,
                'tenant_id' => 1,
                'name' => 'あたしンち 北千住店',
                'address' => '東京都足立区千住1-31-5 2F',
                'is_active' => false,
            ],
            [
                'id' => 2,
                'tenant_id' => 1,
                'name' => 'あたしンち 松戸店',
                'address' => '千葉県松戸市松戸１２９１−１ 古賀ビル １０２',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'tenant_id' => 1,
                'name' => 'ノマッズ 松戸店(NomadZ’)',
                'address' => '千葉県松戸市根本４６４−４ １Ｆ',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'tenant_id' => 1,
                'name' => 'デクリック 松戸店(Déclic)',
                'address' => '千葉県松戸市松戸1240-3 B1F ラーメン雷さん地下',
                'is_active' => true,
            ],
        ];

        foreach ($stores as $store) {
            $now = now();
            $exists = DB::table('stores')->where('id', $store['id'])->exists();
            $values = [
                'tenant_id' => $store['tenant_id'],
                'name' => $store['name'],
                'address' => $store['address'],
                'timezone' => 'Asia/Tokyo',
                'is_active' => $store['is_active'],
                'updated_at' => $now,
            ];

            if (! $exists) {
                $values['created_at'] = $now;
            }

            DB::table('stores')->updateOrInsert(
                ['id' => $store['id']],
                $values
            );

            info("店舗登録完了: {$store['name']}");
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('stores', 'id'), (SELECT MAX(id) FROM stores))");
        }
    }
}
