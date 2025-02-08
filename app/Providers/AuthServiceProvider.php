<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Define policies here (e.g., 'App\Model' => 'App\Policies\ModelPolicy')
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
