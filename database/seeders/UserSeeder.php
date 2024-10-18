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
            "email" => env('USER_EMAIL_VIEWER_RECEIVING_EMAILS'),
            "is_receiving_emails" => true,
            "role_id" => 1
        ]);

        User::create([
            "name" => "Viewer Not Receiving Emails",
            "email" => env('USER_EMAIL_VIEWER_NOT_RECEIVING_EMAILS'),
            "is_receiving_emails" => false,
            "role_id" => 1
        ]);

        User::create([
            "name" => "Authorizer Receiving Emails",
            "email" => env('USER_EMAIL_AUTHORIZER_RECEIVING_EMAILS'),
            "is_receiving_emails" => true,
            "role_id" => 2
        ]);

        User::create([
            "name" => "Authorizer Not Receiving Emails",
            "email" => env('USER_EMAIL_AUTHORIZER_NOT_RECEIVING_EMAILS'),
            "is_receiving_emails" => false,
            "role_id" => 2
        ]);

        User::create([
            "name" => "Admin Receiving Emails",
            "email" => env('USER_EMAIL_ADMIN_RECEIVING_EMAILS'),
            "is_receiving_emails" => true,
            "role_id" => 3
        ]);

        User::create([
            "name" => "Admin Not Receiving Emails",
            "email" => env('USER_EMAIL_ADMIN_NOT_RECEIVING_EMAILS'),
            "is_receiving_emails" => false,
            "role_id" => 3
        ]);
    }
}
