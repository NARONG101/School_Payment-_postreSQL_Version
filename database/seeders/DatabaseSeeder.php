<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        PaymentType::firstOrCreate(
            ['name' => 'Monthly Tuition'],
            [
                'description' => 'Regular monthly tuition fee',
                'amount' => 0,
                'is_active' => true
            ]
        );

        PaymentType::firstOrCreate(
            ['name' => 'Registration Fee'],
            [
                'description' => 'One-time registration fee',
                'amount' => 50,
                'is_active' => true
            ]
        );

        PaymentType::firstOrCreate(
            ['name' => 'Books & Materials'],
            [
                'description' => 'Books and learning materials',
                'amount' => 100,
                'is_active' => true
            ]
        );
    }
}
