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
        RetentionRequest::create([
            'manager_name' => "Test Manager 1",
            'requestor_name' => "Test Requestor 1",
            'requestor_email' => "test_requestor_one@gmail.com",
            'department_id' => 1
        ]);

        RetentionRequest::create([
            'manager_name' => "Test Manager 2",
            'requestor_name' => "Test Requestor 2",
            'requestor_email' => "test_requestor_two@gmail.com",
            'department_id' => 2
        ]);

        RetentionRequest::create([
            'manager_name' => "Test Manager 3",
            'requestor_name' => "Test Requestor 3",
            'requestor_email' => "test_requestor_three@gmail.com",
            'department_id' => 3
        ]);
    }
}
