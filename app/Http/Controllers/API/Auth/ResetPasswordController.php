<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use ApiResponder;

    public function passwordReset(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::query()->when(isset($data['email']), function ($query) use ($data) {
            return $query->where('email', $data['email']);
        })->when(isset($data['phone_number']), function ($query) use ($data) {
            return $query->where('phone_number', $data['phone_number']);
        })->first();

        if (!$user) {
            return $this->errorForbidden(message: __('User not found.'));
        }

        if ($user->code != $data['code']) {
            return $this->errorNotFound(__("The OTP code you entered is incorrect ."));
        }

        if ($user->expire_at < now()->toDateTimeString()) {
            return $this->errorNotFound(__("The OTP code you entered is Expired."));
        }
        $user->markEmailAsVerified();
        $user->resetOTPCode();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return $this->respondWithMessage(__("Your password has been reset successfully."));
    }
}
