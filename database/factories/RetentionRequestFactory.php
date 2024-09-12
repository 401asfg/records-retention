<?php

namespace Database\Factories;

use App\Models\Department;
use Database\Factories\LinkedFactory;

/**
 * @extends \Database\Factories\LinkedFactory<\App\Models\RetentionRequest>
 */
class RetentionRequestFactory extends LinkedFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manager_name' => $this->faker->name(),
            'requestor_name' => $this->faker->name(),
            'requestor_email' => $this->faker->email(),
            'department_id' => self::fakerForeignKey(Department::count()),
            'authorizing_user_id' => null
        ];
    }
}
