<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Facades\Activity;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogowanieNotification;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener(event: Login::class)]
class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        $logDetails = [
            'user_id'     => $user->id,
            'email'       => $user->email,
            'rola'        => $user->role ?? 'brak roli',
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'url'         => request()->fullUrl(),
            'http_method' => request()->method(),
            'session_id'  => session()->getId(),
            'login_time'  => now()->toDateTimeString(),
            'locale'      => app()->getLocale(),
        ];

        // // log do bazy (Spatie)
        // Activity::useLog('Logowanie')
        //     ->causedBy($user)
        //     ->withProperties($logDetails)
        //     ->log('✅ Użytkownik zalogował się do systemu');

        // mail do Ciebie
        Mail::to(['spam@jancybulski.pl', 'info@pankobido.pl'])
            ->send(new LogowanieNotification($logDetails));
    }
}