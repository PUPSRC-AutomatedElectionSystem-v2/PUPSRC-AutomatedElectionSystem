<?php

namespace Database\Factories\Tenants;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenants\OrganizationContacts>
 */
class OrganizationContactsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => null,
            'email' => $this->faker->unique()->safeEmail(),
            'website' => $this->faker->optional()->url(),
            'facebook' => $this->faker->optional()->url(),
            'twitter' => $this->faker->optional()->url(),
            'instagram' => $this->faker->optional()->url(),
            'threads' => $this->faker->optional()->url(),
            'discord' => $this->faker->optional()->url(),

        ];
    }
}
