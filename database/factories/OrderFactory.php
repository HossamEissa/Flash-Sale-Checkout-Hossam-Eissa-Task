<?php

namespace Database\Factories;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'is_hold' => false,
            'expires_at' => null,
            'consumed_at' => null,
        ];
    }

    public function asHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hold' => true,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function withOnlinePayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::OnlinePayment,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
            'consumed_at' => now(),
        ]);
    }
}