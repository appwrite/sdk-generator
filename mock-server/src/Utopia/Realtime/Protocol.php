<?php

namespace Utopia\MockServer\Utopia\Realtime;

use Utopia\MockServer\Utopia\Exception;
use Utopia\WebSocket\Server;

/**
 * Realtime WebSocket protocol handler for the mock server.
 *
 * Mirrors the message contract the Appwrite realtime SDKs expect:
 *
 *   client -> server:  { type: 'authentication' | 'subscribe' | 'unsubscribe' | 'presence' | 'ping', data: ... }
 *   server -> client:  { type: 'connected' | 'event' | 'response' | 'pong' | 'error',                data: ... }
 *
 * The mock does NOT enforce real authorization or query filtering; it
 * acknowledges client requests with shaped responses so SDK code paths
 * (subscribe/unsubscribe/presence) can be exercised end-to-end.
 */
class Protocol
{
    /** Connection state by Swoole fd. */
    private array $connections = [];

    public function open(Server $server, int $fd, string $project): void
    {
        $this->connections[$fd] = new Connection($fd, $project);

        $this->send($server, $fd, 'connected', [
            'channels' => [],
            'user'     => null,
        ]);
    }

    public function close(int $fd): void
    {
        unset($this->connections[$fd]);
    }

    public function message(Server $server, int $fd, string $raw): void
    {
        $connection = $this->connections[$fd] ?? null;
        if ($connection === null) {
            return;
        }

        $message = json_decode($raw, true);
        if (!is_array($message) || !isset($message['type'])) {
            $this->error($server, $fd, 'Invalid message', 400);
            return;
        }

        $type = (string) $message['type'];
        $data = $message['data'] ?? null;

        try {
            match ($type) {
                'authentication' => $this->handleAuth($connection, $data),
                'subscribe'      => $this->handleSubscribe($server, $connection, $data),
                'unsubscribe'    => $this->handleUnsubscribe($connection, $data),
                'presence'       => $this->handlePresence($server, $connection, $data),
                'ping'           => $this->send($server, $fd, 'pong'),
                default          => $this->error($server, $fd, "Unknown message type: {$type}", 400),
            };
        } catch (Exception $e) {
            $this->error($server, $fd, $e->getMessage(), $e->getCode() ?: 400);
        } catch (\Throwable $e) {
            $this->error($server, $fd, $e->getMessage(), 500);
        }
    }

    private function handleAuth(Connection $connection, mixed $data): void
    {
        // Mock accepts any session and synthesises a user object.
        if (is_array($data) && !empty($data['session'])) {
            $connection->user = [
                '$id'  => 'user_' . substr(md5((string) $data['session']), 0, 8),
                'name' => 'Mock User',
            ];
        }
    }

    private function handleSubscribe(Server $server, Connection $connection, mixed $data): void
    {
        if (!is_array($data)) {
            throw new Exception(Exception::GENERAL_ARGUMENT_INVALID, 'subscribe payload must be an array', 400);
        }

        // SDKs send a list of subscription rows: [{ subscriptionId, channels, queries }]
        $rows = $this->isList($data) ? $data : [$data];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $subscriptionId = isset($row['subscriptionId']) ? (string) $row['subscriptionId'] : '';
            $channels       = isset($row['channels']) && is_array($row['channels']) ? $row['channels'] : [];
            $queries        = isset($row['queries'])  && is_array($row['queries'])  ? $row['queries']  : [];

            if ($subscriptionId === '' || empty($channels)) {
                continue;
            }
            $connection->subscribe($subscriptionId, $channels, $queries);

            $eventPayload = ['response' => 'WS:/v1/realtime:passed'];
            if (!$this->subscriptionMatchesPayload($queries, $eventPayload)) {
                continue;
            }

            $this->send($server, $connection->fd, 'event', [
                'channels'      => array_values($channels),
                'events'        => ['test.event'],
                'timestamp'     => gmdate('Y-m-d\TH:i:s.000\+00:00'),
                'payload'       => $eventPayload,
                'subscriptions' => [$subscriptionId],
            ]);
        }
    }

    /**
     * @param string[] $queries
     * @param array<string, mixed> $payload
     */
    private function subscriptionMatchesPayload(array $queries, array $payload): bool
    {
        foreach ($queries as $query) {
            if (!$this->queryMatchesPayload((string) $query, $payload)) {
                return false;
            }
        }
        return true;
    }

    private function queryMatchesPayload(string $query, array $payload): bool
    {
        $parsed = json_decode($query, true);
        if (!is_array($parsed)) {
            // Unparseable queries are treated as "no filter" so the mock
            // never rejects events for an unknown query shape.
            return true;
        }

        $method    = (string) ($parsed['method'] ?? '');
        $attribute = (string) ($parsed['attribute'] ?? '');
        $values    = is_array($parsed['values'] ?? null) ? $parsed['values'] : [];

        if ($attribute === '' || !array_key_exists($attribute, $payload)) {
            return false;
        }

        $actual = $payload[$attribute];

        return match ($method) {
            'equal'    => in_array($actual, $values, true),
            'notEqual' => !in_array($actual, $values, true),
            default    => true, // unknown matcher: pass through (don't filter)
        };
    }

    private function handleUnsubscribe(Connection $connection, mixed $data): void
    {
        if (!is_array($data)) {
            return;
        }
        $rows = $this->isList($data) ? $data : [$data];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['subscriptionId'])) {
                $connection->unsubscribe((string) $row['subscriptionId']);
            }
        }
    }

    private function handlePresence(Server $server, Connection $connection, mixed $data): void
    {
        if (!is_array($data) || !isset($data['status'])) {
            throw new Exception(Exception::GENERAL_ARGUMENT_INVALID, 'presence payload requires status', 400);
        }

        $now = gmdate('Y-m-d\TH:i:s.000\+00:00');
        $presence = [
            '$id'           => $data['presenceId'] ?? ('presence_' . bin2hex(random_bytes(6))),
            '$sequence'     => '1',
            '$createdAt'    => $now,
            '$updatedAt'    => $now,
            '$permissions'  => $data['permissions'] ?? [],
            'userInternalId' => '1',
            'userId'        => $connection->user['$id'] ?? '674af8f3e12a5f9ac0be',
            'status'        => (string) $data['status'],
            'source'        => 'WS',
        ];
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $presence['metadata'] = $data['metadata'];
        }
        if (isset($data['expiresAt'])) {
            $presence['expiry'] = (string) $data['expiresAt'];
        }

        $this->send($server, $connection->fd, 'response', [
            'to'       => 'presence',
            'presence' => $presence,
        ]);
    }

    private function send(Server $server, int $fd, string $type, mixed $data = null): void
    {
        $payload = ['type' => $type];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        $server->send([$fd], json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function error(Server $server, int $fd, string $message, int $code = 400): void
    {
        $this->send($server, $fd, 'error', [
            'message' => $message,
            'code'    => $code,
        ]);
    }

    private function isList(array $value): bool
    {
        if ($value === []) {
            return false;
        }
        return array_keys($value) === range(0, count($value) - 1);
    }
}
