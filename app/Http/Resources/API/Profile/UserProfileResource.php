<?php

namespace App\Http\Resources\API\Profile;

use App\Models\Company;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "avatar" => $this->avatar,
            "country_code" => $this->country_code,
            "country_calling_code" => $this->country_calling_code,
            "phone_number" => $this->phone_number,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            'role_name' => $this->roles()->first()?->name,
            "profile_id" => $this->profile_id,
            "status" => $this->status,
            "lat" => $this->lat,
            "lng" => $this->lng,
            "last_login_at" => $this->last_login_at,
            "created_from_dashboard" => $this->created_from_dashboard,
            "is_login_before" => $this->is_login_before,
            'profile' => $this->getProfileData(),
        ];
    }

    /**
     * Get profile data based on the user's profile type.
     *
     * @return mixed
     */
    protected function getProfileData()
    {
        switch ($this->profile_type) {
            case Company::class:
                return CompanyProfileResource::make($this->resource->profile);
            case Member::class:
                return MemberProfileResource::make($this->resource->profile);
            default:
                return null;
        }
    }
}
