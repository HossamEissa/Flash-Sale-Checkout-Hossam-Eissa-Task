<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'status' => $this->status->value,
            'payment_status' => $this->payment_status->value,
            'total' => $this->total,
            'created_at' => $this->created_at->toISOString(),
            'consumed_at' => $this->when($this->consumed_at, fn() => $this->consumed_at->toISOString()),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
