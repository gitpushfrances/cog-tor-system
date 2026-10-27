<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Sign-in history for the activity log.
        Event::listen(Login::class, function (Login $event) {
            activity('auth')->causedBy($event->user)->event('login')
                ->withProperties(['ip' => request()->ip()])->log('Signed in');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                activity('auth')->causedBy($event->user)->event('logout')->log('Signed out');
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            activity('auth')->event('failed_login')
                ->withProperties(['username' => $event->credentials['username'] ?? null, 'ip' => request()->ip()])
                ->log('Failed sign-in');
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
