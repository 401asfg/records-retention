<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Box>
 */
abstract class LinkedFactory extends Factory
{
    protected function fakerForeignKey(int $maxId): int
    {
        return $this->faker->numberBetween(1, $maxId);
    }
}
