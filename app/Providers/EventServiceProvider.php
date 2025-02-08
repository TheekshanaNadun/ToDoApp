<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        
        // Add Todo-related events
        \App\Events\TodoCreated::class => [
            \App\Listeners\LogTodoCreated::class,
            \App\Listeners\SendTodoNotification::class,
        ],
        \App\Events\TodoUpdated::class => [
            \App\Listeners\LogTodoUpdate::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Optional: Register additional event subscribers
        // Event::subscribe(TodoEventSubscriber::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
