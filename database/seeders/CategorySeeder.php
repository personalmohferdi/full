<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'division' => 'TEFA'],
            ['name' => 'Alat Dapur', 'division' => 'Sarpras'],
            ['name' => 'ATK', 'division' => 'Tata Usaha'],
        ];

        foreach ($categories as $c) {
            Category::updateOrInsert(
                ['name' => $c['name']], // key unik (biar gak dobel)
                [
                    'division' => $c['division'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
