<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // FIXME: should these accounts be specified in the .env?

        User::create([
            "name" => "Viewer Receiving Emails",
            "email" => "records.retention.form.test.1@gmail.com",
            "is_receiving_emails" => true,
            "role_id" => 1
        ]);

        User::create([
            "name" => "Viewer Not Receiving Emails",
            "email" => "records.retention.form.test.2@gmail.com",
            "is_receiving_emails" => false,
            "role_id" => 1
        ]);

        User::create([
            "name" => "Authorizer Receiving Emails",
            "email" => "records.retention.form.test.3@gmail.com",
            "is_receiving_emails" => true,
            "role_id" => 2
        ]);

        User::create([
            "name" => "Authorizer Not Receiving Emails",
            "email" => "records.retention.form.test.4@gmail.com",
            "is_receiving_emails" => false,
            "role_id" => 2
        ]);

        User::create([
            "name" => "Admin Receiving Emails",
            "email" => "records.retention.form.test.5@gmail.com",
            "is_receiving_emails" => true,
            "role_id" => 3
        ]);

        User::create([
            "name" => "Admin Not Receiving Emails",
            "email" => "records.retention.form.test.6@gmail.com",
            "is_receiving_emails" => false,
            "role_id" => 3
        ]);
    }
}
