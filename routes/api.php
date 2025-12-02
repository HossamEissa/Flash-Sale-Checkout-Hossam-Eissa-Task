<?php

use App\Http\Controllers\API\Auth\EmailVerificationController;
use App\Http\Controllers\API\Auth\ForgetPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegistrationController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\Profile\ProfileController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


###################################### Drop-Down ##########################################
Route::prefix('list')->group(function () {

});
###################################### End Drop-Down ##########################################

###################################### Products ##########################################
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
###################################### End Products ##########################################



################################# Authentication ##############################################
Route::middleware(['guest'])->group(function () {
    Route::post('register', [RegistrationController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
    Route::post('resend-code', [EmailVerificationController::class, 'resendEmailVerification']);
    Route::post('verify', [EmailVerificationController::class, 'verifyEmail']);
});
Route::post('reset-password', [ResetPasswordController::class, 'passwordReset']);

Route::get('review', function () {
    return [
        'data' => true,
    ];
});
################################# End Authentication ##############################################

######################################## Common API ##########################################
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [ProfileController::class, 'me']);
    Route::put('update-profile', [ProfileController::class, 'update']);
    Route::post('change-password', [ProfileController::class, 'changePassword']);
    Route::post('logout', [ProfileController::class, 'logout']);
    Route::delete('delete-profile', [ProfileController::class, 'destroy']);
});

######################################## End Common API #######################################
