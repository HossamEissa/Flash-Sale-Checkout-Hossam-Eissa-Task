<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $defaultProfile = $this->faker->randomElement([
            Company::class,
        ]);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'country_code' => $this->faker->countryCode(),
            'country_calling_code' => '+' . $this->faker->randomElement(['1', '44', '20', '91']),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'profile_type' => $defaultProfile,
            'profile_id' => $defaultProfile::factory()->create()->id,
            'disk' => 'public',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withProfileType(mixed $profileType): static
    {

        return $this->state([
            'profile_type' => $profileType,
            'profile_id' => $profileType::factory()->create()->id ?? $this->faker->randomNumber(1),
        ]);
    }
}
