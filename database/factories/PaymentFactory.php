<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'idempotency_key' => $this->faker->unique()->uuid(),
            'order_id' => Order::factory(),
            'status' => PaymentStatusEnum::Pending,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'EGP',
            'external_transaction_id' => null,
            'payload' => [],
            'error_message' => null,
            'retry_count' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatusEnum::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatusEnum::Failed,
            'error_message' => $this->faker->sentence(),
        ]);
    }
}