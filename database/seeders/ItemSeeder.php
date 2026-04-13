<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua category yang ada
        $categories = Category::query()->orderBy('id')->get();

        if ($categories->count() === 0) {
            // Kalau belum ada category, seeder item tidak bisa jalan karena FK
            return;
        }

        // Helper: ambil category secara aman berdasarkan index
        $cat = fn(int $i) => $categories[$i % $categories->count()]->id;

        $items = [
            ['category_id' => $cat(0), 'name' => 'Piring',          'stock' => 100, 'repair_count' => 0],
            ['category_id' => $cat(0), 'name' => 'Gelas',           'stock' => 80,  'repair_count' => 0],
            ['category_id' => $cat(1), 'name' => 'Laptop',          'stock' => 25,  'repair_count' => 0],
            ['category_id' => $cat(1), 'name' => 'Proyektor',       'stock' => 10,  'repair_count' => 0],
            ['category_id' => $cat(2), 'name' => 'Kursi',           'stock' => 120, 'repair_count' => 0],
            ['category_id' => $cat(2), 'name' => 'Meja',            'stock' => 60,  'repair_count' => 0],
            ['category_id' => $cat(0), 'name' => 'Sendok & Garpu',  'stock' => 150, 'repair_count' => 0],
        ];

        foreach ($items as $data) {
            // updateOrCreate biar tidak dobel saat seeder dijalankan ulang
            Item::updateOrCreate(
                ['name' => $data['name']], // key
                $data
            );
        }
    }
}
