<?php

namespace Modules\OrganizationAdmin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\OrganizationAdmin\Models\Voter>
 */
class VoterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'year_level' => $this->faker->numberBetween(1, 4),
            'section' => $this->faker->randomLetter(),
            // 'voter_status_id' => $this->faker->numberBetween(1, 3),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
