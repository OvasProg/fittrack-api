<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a dedicated Admin User for the instructor to test with
        User::factory()->create([
            'name' => 'Instructor Admin',
            'email' => 'admin@fittrack.com',
            'password' => Hash::make('password123*'), // Simple password for testing
            'role' => UserRole::ADMIN, 
        ]);

        // 2. Call your existing domain seeders
        $this->call([
            FitnessDataSeeder::class,
        ]);
    }
}