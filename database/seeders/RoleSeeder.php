<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            "name" => "Viewer",
            "permissions_code" => Role::encodePermissions(false, false, false)
        ]);

        Role::create([
            "name" => "Authorizer",
            "permissions_code" => Role::encodePermissions(true, true, false)
        ]);

        Role::create([
            "name" => "Admin",
            "permissions_code" => Role::encodePermissions(true, true, true)
        ]);
    }
}
