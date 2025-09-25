<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\EmailVerificationRequest;
use App\Http\Requests\API\Auth\ResendEmailVerificationRequest;
use App\Http\Resources\API\Auth\UserAuthResource;
use App\Mail\VerificationEmail;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    use ApiResponder;

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $data = $request->validated();

        if (isset($data['phone_number'])) {
            $user = User::query()->where('phone_number', $data['phone_number'])->first();
        } else {
            $user = User::where('email', $data['email'])->first();
        }

        if ($user->code != $data['code']) {
            return $this->errorNotFound(__("The OTP code you entered is incorrect ."));
        }

        if ($user->expire_at < now()->toDateTimeString()) {
            return $this->errorNotFound(__("The OTP code you entered is Expired."));
        }

        $user->load('profile');

        $deviceName = $request->post('device_name', $request->userAgent());
        $final = [
            'type' => $data['type'],
            'access_token' => $user->createToken($deviceName)->plainTextToken,
            'user' => new UserAuthResource($user),
        ];

        $user->markEmailAsVerified();

        return $this->respondWithItem($final, __("Your account has been successfully verified."));

    }

    public function resendEmailVerification(ResendEmailVerificationRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->respondWithMessage(__("An OTP has been sent to your email address."));
        }
        $OTP = $user->generateOTPCode();

        Mail::to($user)->send(new VerificationEmail($OTP, ("Action Required: Verify Your Email Address")));

        return $this->respondWithMessage(__("An OTP has been sent to your email address."));
    }
}
