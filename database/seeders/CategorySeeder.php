<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Financiera', 'prefix' => 'FIN'],
            ['name' => 'Legal', 'prefix' => 'LEG'],
            ['name' => 'Migraciones', 'prefix' => 'MIG'],
            ['name' => 'Tributaria', 'prefix' => 'TRI'],
            ['name' => 'General', 'prefix' => 'GEN'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['prefix' => $category['prefix'], 'is_active' => true]
            );
        }
    }
}
