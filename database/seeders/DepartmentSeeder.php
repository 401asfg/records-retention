<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create(['name' => 'Engineering Labs']);
        Department::create(['name' => 'Medical Testing']);
        Department::create(['name' => 'Xylophone Room']);
        Department::create(['name' => 'Admin Offices']);
        Department::create(['name' => 'Shipping and Receiving']);
        Department::create(['name' => 'Shipping or Receiving']);
        Department::create(['name' => 'Yellow Painting Room']);
        Department::create(['name' => 'History']);
        Department::create(['name' => 'Automotive Repair']);
    }
}
