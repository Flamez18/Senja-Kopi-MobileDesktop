<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Kopi Senja – Senopati',
                'address' => 'Jl. Senopati No. 42, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'phone' => '021-5551234',
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Kopi Senja – Kemang',
                'address' => 'Jl. Kemang Raya No. 17, Mampang Prapatan',
                'city' => 'Jakarta Selatan',
                'phone' => '021-5555678',
                'open_time' => '09:00:00',
                'close_time' => '23:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Kopi Senja – Sudirman',
                'address' => 'Sudirman Central Business District (SCBD) Lot 21',
                'city' => 'Jakarta Pusat',
                'phone' => '021-5559012',
                'open_time' => '07:00:00',
                'close_time' => '21:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
