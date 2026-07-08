<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $copi = Category::where('name', 'Copi')->first();
        $nonCopi = Category::where('name', 'Non-Copi')->first();
        $makanan = Category::where('name', 'Makanan')->first();

        $products = [
            // Copi
            [
                'category_id' => $copi->id,
                'name' => 'Kopi Senja Signature',
                'description' => 'Kopi susu khas Senja dengan gula aren premium dan racikan espresso ganda yang creamy.',
                'price' => 28000,
                'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'category_id' => $copi->id,
                'name' => 'Cappuccino Warmth',
                'description' => 'Espresso dengan susu foam tebal dan taburan bubuk cokelat di atasnya.',
                'price' => 32000,
                'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'category_id' => $copi->id,
                'name' => 'Caramel Macchiato',
                'description' => 'Espresso dengan susu creamy dan saus karamel manis. Paduan harmonis antara pahitnya kopi Arabika pilihan dengan tekstur susu yang lembut dan manisnya karamel yang autentik.',
                'price' => 35000,
                'image' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'category_id' => $copi->id,
                'name' => 'Iced Senja Latte',
                'description' => 'Espresso dingin dengan susu segar pilihan yang menyegarkan hari-harimu.',
                'price' => 30000,
                'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            
            // Non-Copi
            [
                'category_id' => $nonCopi->id,
                'name' => 'Uji Matcha Zen',
                'description' => 'Matcha premium dari Uji, Jepang, dipadukan dengan susu segar yang lembut dan manis yang pas.',
                'price' => 35000,
                'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'category_id' => $nonCopi->id,
                'name' => 'Choco Velvet',
                'description' => 'Cokelat Belgia pekat bertekstur lembut dipadukan dengan susu hangat.',
                'price' => 33000,
                'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],

            // Makanan
            [
                'category_id' => $makanan->id,
                'name' => 'Buttery Croissant',
                'description' => 'Croissant mentega klasik Perancis yang renyah di luar dan lembut berlapis di dalam.',
                'price' => 24000,
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
            [
                'category_id' => $makanan->id,
                'name' => 'Choco Danish',
                'description' => 'Pastry renyah isi cokelat leleh berkualitas tinggi.',
                'price' => 26000,
                'image' => 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
            ],
        ];

        $branches = Branch::all();

        foreach ($products as $prodData) {
            $product = Product::create($prodData);

            foreach ($branches as $branch) {
                $isAvailable = true;
                if ($branch->id === 3 && $product->name === 'Caramel Macchiato') {
                    $isAvailable = false;
                }

                ProductStock::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'is_available' => $isAvailable,
                ]);
            }
        }
    }
}
