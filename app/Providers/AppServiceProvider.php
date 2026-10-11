<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Reclamation;
use App\Policies\ReclamationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();
    }

    /**
     * Register model policies.
     */
    protected function registerPolicies(): void
    {
        \Illuminate\Support\Facades\Gate::policy(Reclamation::class, ReclamationPolicy::class);
    }
}
