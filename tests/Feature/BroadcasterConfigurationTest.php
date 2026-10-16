<?php

namespace Tests\Feature;

use App\Broadcasting\ReverbBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcasterConfigurationTest extends TestCase
{
    /**
     * Verify that the custom ReverbBroadcaster is registered and accessible
     */
    public function test_reverb_broadcaster_is_registered()
    {
        $manager = app(BroadcastManager::class);
        
        // Get the reverb driver
        $broadcaster = $manager->driver('reverb');
        
        // Verify it's our custom ReverbBroadcaster
        $this->assertInstanceOf(ReverbBroadcaster::class, $broadcaster);
    }

    /**
     * Verify broadcasting configuration is correct
     */
    public function test_broadcasting_configuration_is_correct()
    {
        $config = config('broadcasting');
        
        // Check default driver is reverb
        $this->assertEquals('reverb', $config['default']);
        
        // Check reverb connection exists
        $this->assertArrayHasKey('reverb', $config['connections']);
        
        // Check reverb connection configuration
        $reverbConfig = $config['connections']['reverb'];
        $this->assertEquals('reverb', $reverbConfig['driver']);
        $this->assertNotEmpty($reverbConfig['key']);
        $this->assertNotEmpty($reverbConfig['secret']);
        $this->assertNotEmpty($reverbConfig['app_id']);
        
        // Check options are properly configured
        $this->assertArrayHasKey('options', $reverbConfig);
        $this->assertEquals('localhost', $reverbConfig['options']['host']);
        $this->assertEquals(8081, $reverbConfig['options']['port']);
        $this->assertEquals('http', $reverbConfig['options']['scheme']);
    }

    /**
     * Verify Reverb server configuration has correct nested structure
     */
    public function test_reverb_apps_configuration_is_correct()
    {
        $config = config('reverb');
        
        // Check apps.provider is set to 'config'
        $this->assertEquals('config', $config['apps']['provider']);
        
        // Check apps.apps array exists
        $this->assertArrayHasKey('apps', $config['apps']);
        $this->assertIsArray($config['apps']['apps']);
        $this->assertGreaterThan(0, count($config['apps']['apps']));
        
        // Check first app configuration
        $app = $config['apps']['apps'][0];
        $this->assertNotEmpty($app['key']);
        $this->assertNotEmpty($app['secret']);
        $this->assertNotEmpty($app['app_id']);
        $this->assertArrayHasKey('ping_interval', $app);
        $this->assertArrayHasKey('activity_timeout', $app);
        $this->assertArrayHasKey('max_message_size', $app);
    }

    /**
     * Verify that all critical environment variables are set
     */
    public function test_required_environment_variables_are_set()
    {
        $this->assertNotEmpty(env('BROADCAST_DRIVER'));
        $this->assertNotEmpty(env('BROADCAST_CONNECTION'));
        $this->assertNotEmpty(env('REVERB_APP_ID'));
        $this->assertNotEmpty(env('REVERB_APP_KEY'));
        $this->assertNotEmpty(env('REVERB_APP_SECRET'));
        $this->assertNotEmpty(env('REVERB_HOST'));
        $this->assertNotEmpty(env('REVERB_PORT'));
    }

    /**
     * Verify that BROADCAST_DRIVER is set to 'reverb', not 'null' or 'log'
     */
    public function test_broadcast_driver_is_reverb_not_disabled()
    {
        $driver = env('BROADCAST_DRIVER');
        $this->assertEquals('reverb', $driver, 'BROADCAST_DRIVER should be "reverb", not "null" or "log"');
    }
}
