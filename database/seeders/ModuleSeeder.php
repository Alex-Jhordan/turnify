<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($moduleNumber = 1; $moduleNumber <= 50; $moduleNumber++) {
            Module::firstOrCreate(
                ['module_number' => $moduleNumber],
                ['is_active' => true]
            );
        }
    }
}
