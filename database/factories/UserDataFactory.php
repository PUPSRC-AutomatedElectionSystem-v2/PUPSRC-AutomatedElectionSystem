<?php

namespace Database\Factories;

use App\Models\Central\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UserDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_id' => $this->faker->uuid(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'suffix' => $this->faker->optional()->randomElement(['Jr.', 'Sr.', 'III', 'IV']),
            'organizations' => json_encode([
                Organization::query()->inRandomOrder()->value('id') ?? Organization::factory()->create()->id,
            ]),
            'data' => json_encode([
                'year_level' => $this->faker->numberBetween(1, 4),
                'section' => strtoupper($this->faker->randomLetter()),
            ]),
        ];
    }
}
