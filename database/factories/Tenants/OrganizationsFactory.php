<?php

namespace Database\Factories\Tenants;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenants\Organizations>
 */
class OrganizationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => $this->faker->uuid(),
            'short_name' => strtoupper($this->faker->unique()->lexify('ORG???')),
            'contact_id' => null,
            'name' => $this->faker->company(),
            'category_id' => null,
            'should_copy_from_other_org' => $this->faker->boolean(20),
            'allow_cross_membership' => $this->faker->boolean(50),
            'theme' => json_encode([
                'primary_color' => $this->faker->hexColor(),
                'secondary_color' => $this->faker->hexColor(),
                'logo_url' => $this->faker->imageUrl(200, 200, 'business', true, 'Logo'),
            ]),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
