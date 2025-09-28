<?php

namespace App\Http\Resources\API\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tax_number' => $this->tax_number,
            'tax_card' => FileUrl($this->tax_card),
        ];
    }
}
