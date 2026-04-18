<?php

use App\Http\Controllers\Auth\AdminPasswordResetController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OrtuPasswordResetController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('login-ortu', [AuthenticatedSessionController::class, 'storeOrtu'])
        ->name('login.ortu');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Routes for Orang Tua Password Reset
    Route::get('ortu-forgot-password', [OrtuPasswordResetController::class, 'create'])
        ->name('password.ortu.request');

    Route::post('ortu-forgot-password', [OrtuPasswordResetController::class, 'store'])
        ->name('password.ortu.email');

    Route::get('ortu-reset-password/{token}', [OrtuPasswordResetController::class, 'edit'])
        ->name('password.ortu.reset');

    Route::post('ortu-reset-password', [OrtuPasswordResetController::class, 'update'])
        ->name('password.ortu.update');

    // Routes for Admin (TU/Bendahara) Password Reset
    Route::get('admin-forgot-password', [AdminPasswordResetController::class, 'create'])
        ->name('password.admin.request');

    Route::post('admin-forgot-password', [AdminPasswordResetController::class, 'store'])
        ->name('password.admin.email');

    Route::get('admin-reset-password/{token}', [AdminPasswordResetController::class, 'edit'])
        ->name('password.admin.reset');

    Route::post('admin-reset-password', [AdminPasswordResetController::class, 'update'])
        ->name('password.admin.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});