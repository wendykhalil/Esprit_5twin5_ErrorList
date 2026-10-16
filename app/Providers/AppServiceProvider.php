<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Reclamation;
use App\Models\Inspection;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Observers\InspectionObserver;
use App\Observers\ReleveEnergieObserver;
use App\Observers\ReservationObserver;
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
        Inspection::observe(InspectionObserver::class);
        ReleveEnergie::observe(ReleveEnergieObserver::class);
        Reservation::observe(ReservationObserver::class);
    }

    /**
     * Register model policies.
     */
    protected function registerPolicies(): void
    {
        \Illuminate\Support\Facades\Gate::policy(Reclamation::class, ReclamationPolicy::class);
    }
}
