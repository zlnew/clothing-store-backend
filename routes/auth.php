<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
  Route::post('/register', [RegisteredUserController::class, 'store']);
  
  Route::post('/login', [AuthenticatedSessionController::class, 'store']);
  
  Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
  
  Route::post('/reset-password', [NewPasswordController::class, 'store']);
  
  Route::middleware('auth:sanctum')->group(function () {
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class);
    
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store']);
    
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
  });
});
