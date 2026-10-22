<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }

        // Artisan loads the developer environment before PHPUnit. Pin the
        // values the suite asserts so a local Reverb port cannot leak in.
        foreach ([
            'BROADCAST_DRIVER' => 'reverb',
            'BROADCAST_CONNECTION' => 'reverb',
            'REVERB_APP_ID' => 'testing',
            'REVERB_APP_KEY' => 'testing',
            'REVERB_APP_SECRET' => 'testing',
            'REVERB_HOST' => 'localhost',
            'VITE_REVERB_HOST' => 'localhost',
            'REVERB_PORT' => '8081',
            'VITE_REVERB_PORT' => '8081',
            'REVERB_SERVER_PORT' => '8081',
            'REVERB_SCHEME' => 'http',
        ] as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'testing',
            'broadcasting.connections.reverb.secret' => 'testing',
            'broadcasting.connections.reverb.app_id' => 'testing',
            'broadcasting.connections.reverb.options.host' => 'localhost',
            'broadcasting.connections.reverb.options.port' => 8081,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'reverb.apps.apps.0.key' => 'testing',
            'reverb.apps.apps.0.secret' => 'testing',
            'reverb.apps.apps.0.app_id' => 'testing',
            'reverb.apps.apps.0.options.host' => 'localhost',
            'reverb.apps.apps.0.options.port' => 8081,
        ]);
    }
}
