<?php

namespace Database\Seeders;

use App\Models\RetentionRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RetentionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RetentionRequest::factory()->count(10)->create();
    }
}
