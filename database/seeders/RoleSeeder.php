<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => Role::ADMIN,
                'name' => 'admin',
                'description' => 'Administrator',
            ],
            [
                'id' => Role::CASHIER,
                'name' => 'cashier',
                'description' => 'Cashier/Salesperson',
            ],
            [
                'id' => Role::DOCTOR,
                'name' => 'doctor',
                'description' => 'Doctor/Medical Personnel',
            ],
            [
                'id' => Role::CLIENT,
                'name' => 'client',
                'description' => 'Client/Customer/Patient',
            ],
        ];

        foreach ($roles as $role) {
            Role::factory()->create($role);
        }
    }
}
