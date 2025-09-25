<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegistrationRequest;
use App\Mail\VerificationEmail;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    use ApiResponder;


    public function register(RegistrationRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $data = $this->uploadFiles($data);

            if (isset($data['profile'])) {
                $profile = $data['profile_type']::query()->create($data['profile']);
            } else {
                $profile = $data['profile_type']::query()->create();
            }

            $data['profile_id'] = $profile->id;

            $user = User::query()->create($data);

            $user->assignRole('user');

            $otp = $user->generateOTPCode();

            Mail::to($user)->send(new VerificationEmail($otp));

            DB::commit();

            return $this->respondWithMessage(__("You’re almost there! Please verify your email by checking the OTP we’ve sent to your inbox"));

        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->errorDatabase($exception->getMessage());
        }

    }


    public static function uploadFiles(mixed $data): mixed
    {
        if (isset($data['avatar'])) {
            $data['avatar'] = uploadFile($data['avatar'], 'users/avatars');
        }

        if (isset($data['profile']['cr_commercial_registration'])) {
            $data['profile']['cr_commercial_registration'] = uploadFile($data['profile']['cr_commercial_registration'], 'users/cr_commercial_registration');
        }

        if (isset($data['profile']['vat_certificate'])) {
            $data['profile']['vat_certificate'] = uploadFile($data['profile']['vat_certificate'], 'users/vat_certificate');
        }
        return $data;
    }

}
