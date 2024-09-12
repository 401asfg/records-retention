<?php

namespace Database\Factories;

use App\Models\RetentionRequest;
use Database\Factories\LinkedFactory;

/**
 * @extends \Database\Factories\LinkedFactory<\App\Models\Box>
 */
class BoxFactory extends LinkedFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->paragraph(),
            'destroy_date' => $this->faker->date(),
            'tracking_number' => self::fakerNullableUniqueNumber(),
            'retention_request_id' => self::fakerForeignKey(RetentionRequest::count())
        ];
    }

    private function fakerNullableUniqueNumber(): int|null
    {
        if ($this->faker->boolean)
            return null;

        return $this->faker->unique(true)->numberBetween();
    }
}
