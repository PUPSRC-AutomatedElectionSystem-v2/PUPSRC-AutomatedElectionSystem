<?php

namespace Modules\OrganizationAdmin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\OrganizationAdmin\Facades\MakeDefaultPassword;
use Modules\OrganizationAdmin\Models\Committee;
use Modules\OrganizationAdmin\Models\Voter;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\OrganizationAdmin\Models\User>
 */
class UserFactory extends Factory
{
    private Voter|Committee|null $accountModel = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountType = null;
        $accountId = null;

        if (! empty($this->accountModel)) {
            $accountType = strtolower(class_basename($this->accountModel));
            $accountId = $this->accountModel->id;
        } else {
            $choices = array_map(fn (string $class): string => strtolower(class_basename($class)), [Voter::class, Committee::class]);
            $accountType = $this->faker->randomElement($choices);
            $accountId = $this->faker->uuid();
        }

        return [
            'account_id' => $accountId,
            'account_type' => $accountType,
            'identity_id' => null,
            'email' => null,
            'email_verified_at' => null,
            'password' => MakeDefaultPassword::getDefaultPassword($accountType),
            'data' => null,
        ];
    }

    public function emailVerified() {}

    /**
     * Set the account model for this factory instance.
     */
    public function forAccount(Voter|Committee $accountModel): self
    {
        $this->accountModel = $accountModel;

        return $this;
    }
}
