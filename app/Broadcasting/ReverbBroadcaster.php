<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Arr;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Custom Reverb broadcaster that directly communicates with Reverb's WebSocket server
 * using Pusher-compatible HTTP API authentication.
 */
class ReverbBroadcaster extends Broadcaster
{
    /**
     * Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The broadcaster configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new broadcaster instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param array $config
     */
    public function __construct(GuzzleClient $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function auth($request)
    {
        $channelName = $this->normalizeChannelName($request->channel_name);

        if (empty($request->channel_name) ||
            ($this->isGuardedChannel($request->channel_name) &&
                !$this->retrieveUser($request, $channelName))) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request,
            $channelName
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        // For Reverb, we'll use a simple HMAC-based authentication similar to Pusher
        if (str_starts_with($request->channel_name, 'private')) {
            return $this->generateChannelAuth($request->channel_name, $request->socket_id);
        }

        $channelName = $this->normalizeChannelName($request->channel_name);
        $user = $this->retrieveUser($request, $channelName);

        $broadcastIdentifier = method_exists($user, 'getAuthIdentifierForBroadcasting')
            ? $user->getAuthIdentifierForBroadcasting()
            : $user->getAuthIdentifier();

        return $this->generatePresenceChannelAuth(
            $request->channel_name,
            $request->socket_id,
            $broadcastIdentifier,
            $result
        );
    }

    /**
     * Generate authentication for a private channel.
     *
     * @param string $channelName
     * @param string $socketId
     * @return array
     */
    protected function generateChannelAuth($channelName, $socketId)
    {
        $stringToSign = "{$socketId}:{$channelName}";
        $auth = $this->config['key'] . ':' . hash_hmac(
            'sha256',
            $stringToSign,
            $this->config['secret']
        );

        return ['auth' => $auth];
    }

    /**
     * Generate authentication for a presence channel.
     *
     * @param string $channelName
     * @param string $socketId
     * @param mixed $user
     * @param array $userData
     * @return array
     */
    protected function generatePresenceChannelAuth($channelName, $socketId, $user, $userData = [])
    {
        $presenceData = [
            'user_id' => $user,
            'user_info' => $userData,
        ];

        $stringToSign = "{$socketId}:{$channelName}:" . json_encode($presenceData);
        $auth = $this->config['key'] . ':' . hash_hmac(
            'sha256',
            $stringToSign,
            $this->config['secret']
        );

        return [
            'auth' => $auth,
            'channel_data' => json_encode($presenceData),
        ];
    }

    /**
     * Broadcast the given event.
     *
     * @param array $channels
     * @param string $event
     * @param array $payload
     * @return void
     * @throws \Illuminate\Broadcasting\BroadcastException
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        // PHPUnit must not require a running Reverb process. Production calls still reach the server.
        if (app()->runningUnitTests()) {
            return;
        }

        $socket = Arr::pull($payload, 'socket');

        // Construct the Reverb broadcast endpoint URL
        $host = $this->config['options']['host'] ?? 'localhost';
        $port = $this->config['options']['port'] ?? 8081;
        $scheme = $this->config['options']['scheme'] ?? 'http';
        
        $path = "/apps/{$this->config['app_id']}/events";
        $url = "{$scheme}://{$host}:{$port}{$path}";

        // Extract channel names from Channel objects
        $channel_names = array_map(function ($channel) {
            if (is_string($channel)) {
                return $channel;
            }
            // If it's a Channel object, get its name property
            return $channel->name ?? (string)$channel;
        }, $channels);
        
        $timestamp = time();
        
        // Build query parameters for signature
        $query_params = [
            'auth_key' => $this->config['key'],
            'auth_timestamp' => $timestamp,
            'auth_version' => '1.0',
        ];

        // Build body
        $body_data = [
            'name' => $event,
            'channels' => $channel_names,
            'data' => json_encode($payload),
        ];

        if ($socket) {
            $body_data['socket_id'] = $socket;
        }

        // Calculate body MD5
        $body = json_encode($body_data);
        $body_md5 = md5($body);
        $query_params['body_md5'] = $body_md5;

        ksort($query_params);

        // Build the string to sign: METHOD\nPATH\nQUERY_STRING
        $queryString = http_build_query($query_params);
        $stringToSign = implode("\n", [
            'POST',
            $path,
            $queryString,
        ]);

        $authSignature = hash_hmac('sha256', $stringToSign, $this->config['secret']);

        try {
            // Build the full URL with query parameters including signature
            $query_params['auth_signature'] = $authSignature;
            $fullUrl = $url . '?' . http_build_query($query_params);

            $this->client->post($fullUrl, [
                'json' => $body_data,
                'timeout' => 30,
            ]);
        } catch (\Exception $e) {
            throw new BroadcastException(
                sprintf('Reverb broadcast error: %s.', $e->getMessage())
            );
        }
    }

    /**
     * Get the normalized channel name.
     *
     * @param string $channel
     * @return string
     */
    protected function normalizeChannelName($channel)
    {
        return str_replace(['private-', 'presence-'], '', $channel);
    }
}
