<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Admin User',     'admin@example.com',    'admin',    null],
            ['Gate Security',  'security@example.com', 'security', null],
            ['Hardik Chavda',  'host@example.com',     'host',     'Computer Applications'],
            ['Riya Shah',      'host2@example.com',    'host',     'Administration'],
        ];

        foreach ($accounts as [$name, $email, $role, $department]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name, 'password' => 'password', 'role' => $role,
                'department' => $department, 'status' => true,
            ]);
        }
    }
}
