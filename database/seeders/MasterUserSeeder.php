<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'uuid'              => 'fdfd9562-a3c4-4d89-9a25-83e9b14f85e1',
                'name'              => 'Administrator',
                'department_id'     => 1,
                'email'             => 'reza.f@lintec.co.id',
                'email_verified_at' => now(),
                'password'          => Hash::make('mis32020'),
                'is_active'         => true,
                'last_login_at'     => null,
                'last_login_ip'     => null,
                'remember_token'    => null,
                'nrk'           => 'admin',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );
    }
}
