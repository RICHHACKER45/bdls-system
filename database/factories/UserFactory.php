<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // THE FIX: Itinugma sa BDLS Database Schema ang fake data!
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'sex' => fake()->randomElement(['Male', 'Female']),
            'date_of_birth' => fake()->date('Y-m-d', '2005-01-01'), // Fake age around 20+
            'house_number' => fake()->buildingNumber(),
            'purok_street' => 'Purok ' . fake()->numberBetween(1, 7),
            'contact_number' => '09' . fake()->numerify('#########'), // Fake 11-digit number
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => (static::$password ??= Hash::make('password123')), // Default DB test password
            'role' => 'resident',
            'is_verified' => 1,
            'contact_verified_at' => now(),
            'terms_accepted_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'contact_verified_at' => null,
            'is_verified' => 0,
        ]);
    }
}