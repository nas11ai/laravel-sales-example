<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by'   => User::factory(),
            'kode'         => 'SL-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'tanggal'      => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status'       => SaleStatus::UNPAID,
            'total_amount' => fake()->numberBetween(10000, 1000000),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['status' => SaleStatus::UNPAID]);
    }

    public function partial(): static
    {
        return $this->state(['status' => SaleStatus::PARTIAL]);
    }

    public function paid(): static
    {
        return $this->state(['status' => SaleStatus::PAID]);
    }
}
