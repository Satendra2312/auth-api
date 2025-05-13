<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed roles
        $this->call([
            RoleSeeder::class,
        ]);

        // Then create the user and assign a valid role_id (e.g., 1 for Admin)
        User::factory()->create([
            'name' => 'Rocky',
            'email' => 'rocky.rbl2312@gmil.com',
            'role_id' => 1, // Ensure this matches an existing role in roles table
        ]);
    }
}
