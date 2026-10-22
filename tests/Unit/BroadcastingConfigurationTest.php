<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * BroadcastingConfigurationTest
 *
 * Unit tests for broadcasting configuration.
 * Tests the two critical fixes:
 * 1. BROADCAST_DRIVER=reverb (was 'log')
 * 2. VITE_REVERB_PORT=8081 (was 8080)
 */
class BroadcastingConfigurationTest extends TestCase
{
    /**
     * Test: Verify BROADCAST_DRIVER is set to 'reverb' (not 'log')
     * This is critical because 'log' driver prevents real-time broadcasting.
     */
    public function test_broadcast_driver_is_set_to_reverb(): void
    {
        $driver = env('BROADCAST_DRIVER', 'log');
        $this->assertEquals(
            'reverb',
            $driver,
            'BROADCAST_DRIVER must be "reverb" to enable real-time broadcasting. Currently set to: ' . $driver
        );
    }

    /**
     * Test: Verify VITE_REVERB_PORT is 8081 (not 8080)
     * Frontend uses this port to connect to WebSocket server.
     * If port is wrong, frontend cannot establish WebSocket connection.
     */
    public function test_vite_reverb_port_is_8081(): void
    {
        $port = env('VITE_REVERB_PORT', 8080);
        $this->assertEquals(
            8081,
            $port,
            'VITE_REVERB_PORT must be 8081 to match Reverb server port. Currently set to: ' . $port
        );
    }

    /**
     * Test: Verify REVERB_PORT backend configuration is 8081
     */
    public function test_reverb_server_port_is_8081(): void
    {
        $port = env('REVERB_PORT', 8080);
        $this->assertEquals(
            8081,
            $port,
            'REVERB_PORT backend configuration must be 8081. Currently set to: ' . $port
        );
    }

    /**
     * Test: Verify REVERB_SERVER_PORT is 8081
     * The actual Reverb server listens on this port.
     */
    public function test_reverb_server_listening_port_is_8081(): void
    {
        $port = env('REVERB_SERVER_PORT', 8080);
        $this->assertEquals(
            8081,
            $port,
            'REVERB_SERVER_PORT must be 8081 (the port Reverb server actually listens on). Currently: ' . $port
        );
    }

    /**
     * Test: Verify frontend and backend ports match
     * If they don't match, frontend cannot connect to WebSocket.
     */
    public function test_frontend_and_backend_reverb_ports_match(): void
    {
        $backendPort = env('REVERB_PORT', 8080);
        $frontendPort = env('VITE_REVERB_PORT', 8080);

        $this->assertEquals(
            $backendPort,
            $frontendPort,
            'Frontend VITE_REVERB_PORT and backend REVERB_PORT must match. ' .
            'Frontend: ' . $frontendPort . ', Backend: ' . $backendPort
        );
    }

    /**
     * Test: Verify Reverb host is localhost (or configured correctly)
     */
    public function test_reverb_host_is_configured(): void
    {
        $host = env('REVERB_HOST', 'localhost');
        $this->assertNotEmpty($host, 'REVERB_HOST must be configured');
    }

    /**
     * Test: Verify VITE_REVERB_HOST matches REVERB_HOST
     */
    public function test_frontend_and_backend_reverb_hosts_match(): void
    {
        $backendHost = env('REVERB_HOST', 'localhost');
        $frontendHost = env('VITE_REVERB_HOST', 'localhost');

        $this->assertEquals(
            $backendHost,
            $frontendHost,
            'Frontend VITE_REVERB_HOST and backend REVERB_HOST must match. ' .
            'Frontend: ' . $frontendHost . ', Backend: ' . $backendHost
        );
    }
}
