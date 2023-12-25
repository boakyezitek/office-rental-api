<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::facotry(),
            'title' => $this->fake()->sentence(),
            'description' => $this->fake()->paragraph(),
            'lat' => $this->fake()->latitude(),
            'lng' => $this->fake()->longitude(),
            'address_line1' => $this->fake()->address(),
            'address_line2' => $this->fake()->address(),
            'approval_status' => Office::APPROVAL_APPROVED,
            'hidden' => false,
            'price_per_day' => $this->fake()->numberBetween(1_000, 2_000),
            'monthly_discount' => 0
        ];
    }
}
