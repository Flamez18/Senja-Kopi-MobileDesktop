<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::create([
            'name' => 'Super Admin Senja',
            'email' => 'superadmin@kopisenja.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'super_admin',
            'branch_id' => null,
        ]);

        // Ambil cabang untuk admin cabang
        $branches = Branch::all();

        // 2. Admin Cabang Senopati (Branch ID 1)
        if (isset($branches[0])) {
            User::create([
                'name' => 'Admin Senopati',
                'email' => 'adminsenopati@kopisenja.com',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'role' => 'admin_branch',
                'branch_id' => $branches[0]->id,
            ]);
        }

        // 3. Admin Cabang Kemang (Branch ID 2)
        if (isset($branches[1])) {
            User::create([
                'name' => 'Admin Kemang',
                'email' => 'adminkemang@kopisenja.com',
                'password' => Hash::make('password'),
                'phone' => '081234567892',
                'role' => 'admin_branch',
                'branch_id' => $branches[1]->id,
            ]);
        }

        // 4. Customer Biasa
        User::create([
            'name' => 'Budi Setiawan',
            'email' => 'customer@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '081298765432',
            'role' => 'customer',
            'branch_id' => null,
            'avatar' => 'avatars/default.png',
        ]);
    }
}
