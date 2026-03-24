<?php

use App\Config\Constants\MiddlewaresConstants;
use App\Http\Controllers\Auth\{
  AuthenticatedSessionController,
  ConfirmablePasswordController,
  EmailVerificationNotificationController,
  EmailVerificationPromptController,
  NewPasswordController,
  PasswordResetLinkController,
  RegisteredUserController,
  VerifyEmailController
};
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;

// TEMP: $output = new ConsoleOutput();
$msg = 'Mapping auth main routes...';
// TEMP: app()->runningInConsole() ?
// TEMP:   $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);
Route::middleware([MiddlewaresConstants::WEB,])
  ->group(function () {
    Route::get('/login/{lang?}', [
      AuthenticatedSessionController::class,
      AuthenticatedSessionController::SHW_LG_FM
    ])
      // TODO REACTIVATE IN PRODUCTION
      // ->middleware(MiddlewaresConstants::GT) 
      ->name('login');
    Route::get('/register/{lang?}', [
      RegisteredUserController::class,
      RegisteredUserController::SHW_RG_FM
    ])->name('register');
    Route::get('/forgot-password/{lang?}', [
      AuthenticatedSessionController::class,
      AuthenticatedSessionController::SHW_LG_RQ
    ])->name('password.request');
    Route::get('/reset-password/{token}', [
      NewPasswordController::class,
      'create'
    ])->name('password.reset');
  });

Route::middleware([
  MiddlewaresConstants::WEB,
  MiddlewaresConstants::XSS,
  MiddlewaresConstants::TRT . ':10,1'
])
  ->group(function () {
    Route::post('/login', [
      AuthenticatedSessionController::class, 'store'
    ])->name('login.store');
    Route::post('/register', [
      RegisteredUserController::class, 'store'
    ])->name('register.store');
    Route::post('/forgot-password', [
      PasswordResetLinkController::class, 'store'
    ])->name('password.email');
    Route::post('/reset-password', [
      NewPasswordController::class, 'store'
    ])->name('password.update');
  });
Route::middleware([MiddlewaresConstants::WEB, MiddlewaresConstants::AUTH])
  ->group(function () {
    Route::get('/verify/{lang?}', [
      EmailVerificationPromptController::class, '__invoke'
    ])->name('verification.notice');
    // NOTE: GET /confirm-password removed — Fortify owns this route at
    // /user/confirm-password (fortify.php) with name 'password.confirm'.
    // Keeping both caused the 'password.confirm' name to resolve to
    // /confirm-password while Fortify middleware redirected to
    // /user/confirm-password, creating an infinite redirect loop.
  });

Route::middleware([
  MiddlewaresConstants::WEB,
  MiddlewaresConstants::AUTH,
  MiddlewaresConstants::TRT . ':6,1'
])
  ->group(function () {
    Route::post('/logout', [
      AuthenticatedSessionController::class, 'destroy'
    ])->name('logout');
    Route::post('/email/verification-notification', [
      EmailVerificationNotificationController::class, 'store'
    ])->name('verification.send');
    Route::get('/verify/{id}/{hash}', [
      VerifyEmailController::class, '__invoke'
    ])
      ->middleware(MiddlewaresConstants::SGN)
      ->name('verification.verify');
    Route::post('/confirm-password', [
      ConfirmablePasswordController::class, 'store'
    ]);
  });
