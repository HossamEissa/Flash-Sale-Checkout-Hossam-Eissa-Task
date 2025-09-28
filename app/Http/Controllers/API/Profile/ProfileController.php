<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\API\Auth\RegistrationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Profile\ChangePasswordRequest;
use App\Http\Requests\API\Profile\UpdateProfileRequest;
use App\Http\Resources\API\Profile\UserProfileResource;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        $user->loadSafeProfile();

        return $this->respondWithRetrieved(UserProfileResource::make($user));
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $data = RegistrationController::uploadFiles($data);

            if (isset($data['profile'])) {
                $request->user()->profile->update($data['profile']);
            }

            $request->user()->update($data);

            DB::commit();

            return $this->respondWithUpdated();
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        $user->update(['password' => $request->new_password, 'created_from_dashboard' => false]);

        return $this->respondWithUpdated(message: 'Password changed successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respondWithUpdated(message: 'Logged out successfully.');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->delete();
        return $this->respondWithUpdated('Profile deleted successfully.');
    }

}
