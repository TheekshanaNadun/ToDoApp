<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            \App\Events\YourEvent::class,
            \App\Listeners\YourListener::class
        );
    }
}
