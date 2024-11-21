<?php

namespace Database\Seeders;

use App\Models\Box;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Box::create([
            'description' => "Test Box 1",
            'destroy_date' => "2022-01-01",
            'retention_request_id' => 1
        ]);

        Box::create([
            'description' => "Test Box 2",
            'destroy_date' => "2022-02-01",
            'retention_request_id' => 1
        ]);

        Box::create([
            'description' => "Test Box 3",
            'destroy_date' => "2022-03-01",
            'retention_request_id' => 2
        ]);

        Box::create([
            'description' => "Test Box 4",
            'destroy_date' => "2022-04-01",
            'retention_request_id' => 1
        ]);

        Box::create([
            'description' => "Test Box 5",
            'destroy_date' => "2022-05-01",
            'retention_request_id' => 3
        ]);

        Box::create([
            'description' => "Test Box 6",
            'destroy_date' => "2022-06-01",
            'retention_request_id' => 2
        ]);
    }
}
