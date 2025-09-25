<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\ConstantEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Resources\API\Auth\UserAuthResource;
use App\Http\Resources\API\Profile\UserProfileResource;
use App\Models\DeviceToken;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use ApiResponder;


    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::query()
            ->when($request->post('email'), function ($query) use ($data) {
                $query->where('email', $data['email']);
            })->when($request->post('phone_number'), function ($query) use ($data) {
                $query->where('phone_number', $data['phone_number']);
            })->first();

        if (!$user) return $this->errorNotFound(__("These credentials do not match our records."));

        if (!$user->hasVerifiedEmail()) {
            if (isset($data['phone_number'])) {
                $message = __("Your phone number needs to be verified.");
            } else {
                $message = __("Your email address needs to be verified.");
            }
            return $this->errorForbidden($message);
        }

        if (!Hash::check($data['password'], $user->password)) {
            return $this->errorNotFound(__("These credentials do not match our records."));
        }

        if (!($user->status == ConstantEnum::STATUS_ACTIVE)) {
            if ($user->profile_type == Supplier::class) {
                $message = __("Your supplier account is pending activation.");
            } else {
                $message = __("Your account is currently inactive.");
            }
            return $this->errorForbidden($message);
        }

        $deviceName = $request->post('device_name', $request->userAgent());
        $token = $user->createToken($deviceName)->plainTextToken;

        DeviceToken::firstOrCreate([
            'user_id' => $user->id,
            'token' => $data['device_token'],
        ]);

        $user->loadSafeProfile();
        $finalData = [
            'access_token' => $token,
            'user' => new UserProfileResource($user),
        ];

        $user->update(['last_login_at' => now()]);

        return $this->respondWithItem($finalData, __("Login successful."));

    }
}
