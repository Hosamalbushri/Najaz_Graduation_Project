<?php

namespace Najaz\Notification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Service Request Events
        Event::listen('service.request.save.after', 'Najaz\Notification\Listeners\ServiceRequest@createServiceRequest');
        Event::listen('service.request.update.after', 'Najaz\Notification\Listeners\ServiceRequest@updateServiceRequest');
        Event::listen('service.request.update-status.after', 'Najaz\Notification\Listeners\ServiceRequest@updateServiceRequest');

        // Identity Verification Events
        Event::listen('identity.verification.created', 'Najaz\Notification\Listeners\IdentityVerification@createIdentityVerification');
        Event::listen('identity.verification.update-status.after', 'Najaz\Notification\Listeners\IdentityVerification@updateIdentityVerification');
    }
}

