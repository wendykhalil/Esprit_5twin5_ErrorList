<?php

namespace App\Providers;

use App\Broadcasting\ReverbBroadcaster;
use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;
use GuzzleHttp\Client as GuzzleClient;

class ReverbBroadcasterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the custom Reverb broadcaster after the BroadcastManager is resolved
        $this->app->afterResolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('reverb', function ($app, $config) {
                $guzzleClient = new GuzzleClient([
                    'connect_timeout' => 10,
                    'timeout' => 30,
                ]);

                return new ReverbBroadcaster($guzzleClient, $config);
            });
        });
    }
}
