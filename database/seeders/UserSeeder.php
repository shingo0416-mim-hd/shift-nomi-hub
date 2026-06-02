<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'tenant_id' => 1,
                'name' => '問い合わせ 管理',
                'last_name' => '問い合わせ',
                'first_name' => '管理',
                'email' => 'contact@mim-hd.co.jp',
                'password' => Hash::make('mimn1203'),
                'role' => User::ROLE_SUPER_ADMIN,
            ],
        ];

        foreach ($users as $user) {
            $fullName = $user['name'] ?? trim(
                implode(' ', array_filter([
                    $user['last_name'] ?? null,
                    $user['first_name'] ?? null,
                ]))
            );

            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'tenant_id' => $user['tenant_id'],
                    'name' => $fullName,
                    'last_name' => $user['last_name'] ?? null,
                    'first_name' => $user['first_name'] ?? null,
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            info("ユーザー登録完了: {$user['email']} ({$fullName})");
        }
    }
}
