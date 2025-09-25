<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    use ApiResponder;

    public function show(Request $request)
    {
        return $this->respondWithItem($request->user(), 'Profile retrieved successfully');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $request->user()->id,
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorWrongArgs($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            
            $user = $request->user();
            $user->update($request->only(['name', 'email', 'phone']));
            
            DB::commit();
            
            return $this->respondWithUpdated($user, 'Profile updated successfully');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorWrongArgs($validator->errors()->first());
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorWrongArgs('Current password is incorrect');
        }

        try {
            DB::beginTransaction();
            
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            DB::commit();
            
            return $this->respondWithMessage('Password changed successfully');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->errorDatabase($exception->getMessage());
        }
    }

    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorWrongArgs($validator->errors()->first());
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorWrongArgs('Password is incorrect');
        }

        try {
            DB::beginTransaction();
            
            // Delete all user tokens
            $user->tokens()->delete();
            
            // Delete user account
            $user->delete();
            
            DB::commit();
            
            return $this->respondWithMessage('Account deleted successfully');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->errorDatabase($exception->getMessage());
        }
    }
}