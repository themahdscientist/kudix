<?php

namespace App\Filament\Admin\Pages\Auth;

use App\Notifications\VerifyEmail;
use Exception;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    public function sendEmailVerificationNotification(MustVerifyEmail $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = app(VerifyEmail::class);
        $notification->url = filament()->getVerifyEmailUrl($user);

        $user->notify($notification);
    }
}
