<?php

namespace Utopia\MockServer\Utopia\Realtime;

/**
 * Per-connection state for a single WebSocket client.
 *
 * One instance lives per Swoole `fd` for as long as the socket is open.
 */
class Connection
{
    public int $fd;
    public string $project = '';
    public ?array $user = null;

    /**
     * Active subscriptions keyed by client-supplied subscriptionId.
     * Each value is `['channels' => string[], 'queries' => string[]]`.
     *
     * @var array<string, array{channels: string[], queries: string[]}>
     */
    public array $subscriptions = [];

    public function __construct(int $fd, string $project = '')
    {
        $this->fd = $fd;
        $this->project = $project;
    }

    public function subscribe(string $subscriptionId, array $channels, array $queries): void
    {
        $this->subscriptions[$subscriptionId] = [
            'channels' => array_values($channels),
            'queries'  => array_values($queries),
        ];
    }

    public function unsubscribe(string $subscriptionId): void
    {
        unset($this->subscriptions[$subscriptionId]);
    }

    /**
     * Return subscription IDs whose channel set intersects the given channels.
     *
     * @param string[] $channels
     * @return string[]
     */
    public function matchingSubscriptions(array $channels): array
    {
        $matches = [];
        foreach ($this->subscriptions as $id => $sub) {
            if (!empty(array_intersect($channels, $sub['channels']))) {
                $matches[] = $id;
            }
        }
        return $matches;
    }
}
