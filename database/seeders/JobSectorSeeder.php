<?php

namespace Database\Seeders;

use App\Models\JobSector;
use Illuminate\Database\Seeder;

class JobSectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent job sectors
        $construction = JobSector::create([
            'name' => 'Construction',
            'description' => 'Construction related jobs',
        ]);

        // Construction sub-categories
        JobSector::create([
            'name' => 'A2A',
            'parent_id' => $construction->id,
            'description' => 'Construction A2A category',
        ]);

        JobSector::create([
            'name' => 'A2B',
            'parent_id' => $construction->id,
            'description' => 'Construction A2B category',
        ]);

        // Other job sectors (no sub-categories)
        $jobSectors = [
            ['name' => 'Agriculture', 'description' => 'Agricultural sector jobs'],
            ['name' => 'Plantation', 'description' => 'Plantation related work'],
            ['name' => 'Electrician', 'description' => 'Electrical work'],
            ['name' => 'Plumber', 'description' => 'Plumbing services'],
            ['name' => 'Supermarket', 'description' => 'Supermarket jobs'],
            ['name' => 'Cleaner', 'description' => 'Cleaning services'],
            ['name' => 'Restaurant', 'description' => 'Restaurant and hospitality'],
            ['name' => 'Service Sector', 'description' => 'General service sector'],
            ['name' => 'Factory', 'description' => 'Factory work'],
            ['name' => 'Furniture Factory', 'description' => 'Furniture manufacturing'],
            ['name' => 'BRSK', 'description' => 'BRSK category'],
            ['name' => 'Category - 3', 'description' => 'Category 3'],
            ['name' => 'Student', 'description' => 'Student category'],
        ];

        foreach ($jobSectors as $sector) {
            JobSector::create($sector);
        }
    }
}
