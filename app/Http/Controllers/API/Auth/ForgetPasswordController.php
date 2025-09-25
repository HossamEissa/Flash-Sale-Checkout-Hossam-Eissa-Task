<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgetPasswordRequest;
use App\Mail\VerificationEmail;
use App\Models\User;
use App\Services\SMS\Eg;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ForgetPasswordController extends Controller
{
    use ApiResponder;

    /**
     * @throws ValidationException
     */
    public function forgotPassword(ForgetPasswordRequest $request)
    {
        $data = $request->validated();

        if (isset($data['phone_number'])) {
            $user = User::query()->where('phone_number', $data['phone_number'])->first();
        } else {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            return $this->respondWithMessage(__("An OTP has been already sent."));
        }
        $OTP = $user->generateOTPCode();

        if (isset($data['phone_number'])) {
            $smsEg = new Eg();
            $message = $OTP . ' كود التحقق الخاص بك هو :';
            $sms = $smsEg->sendMessage($data['phone_number'], $message);
            Log::error($sms);
        } else {
            Mail::to($user)->send(new VerificationEmail($OTP, __("Action Required: Verify Your Email Address")));
        }


        return $this->respondWithMessage(__("An OTP has been sent."));
    }
}
