<?php

namespace Utopia\MockServer\Utopia\Realtime;

use Utopia\MockServer\Utopia\Exception;
use Utopia\Query\Exception as QueryException;
use Utopia\Query\Method;
use Utopia\Query\Query;
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
        if (empty($queries)) {
            return true;
        }

        try {
            $parsed = Query::parseQueries(array_values(array_map('strval', $queries)));
        } catch (QueryException) {
            return false;
        }

        foreach ($parsed as $query) {
            if (!$this->evaluateQuery($query, $payload)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Evaluate a parsed Query against the event payload
     *
     * @param array<string, mixed> $payload
     */
    private function evaluateQuery(Query $query, array $payload): bool
    {
        $method = $query->getMethod();
        $values = $query->getValues();

        if ($method === Method::Select) {
            // select('*') is the realtime convention for "listen to all".
            return count($values) === 1 && $values[0] === '*';
        }

        if ($method === Method::And) {
            foreach ($values as $sub) {
                if (!$sub instanceof Query || !$this->evaluateQuery($sub, $payload)) {
                    return false;
                }
            }
            return true;
        }

        if ($method === Method::Or) {
            foreach ($values as $sub) {
                if ($sub instanceof Query && $this->evaluateQuery($sub, $payload)) {
                    return true;
                }
            }
            return false;
        }

        $attribute = $query->getAttribute();
        if ($attribute === '' || !array_key_exists($attribute, $payload)) {
            return false;
        }

        $actual = $payload[$attribute];

        return match ($method) {
            Method::Equal            => in_array($actual, $values, true),
            Method::NotEqual         => !in_array($actual, $values, true),
            Method::LessThan         => isset($values[0]) && $actual < $values[0],
            Method::LessThanEqual    => isset($values[0]) && $actual <= $values[0],
            Method::GreaterThan      => isset($values[0]) && $actual > $values[0],
            Method::GreaterThanEqual => isset($values[0]) && $actual >= $values[0],
            Method::IsNull           => $actual === null,
            Method::IsNotNull        => $actual !== null,
            default                  => false, // unimplemented matcher → fail closed
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
        $presenceId = $data['presenceId'] ?? ('presence_' . bin2hex(random_bytes(6)));
        $presence = [
            '$id'           => $presenceId,
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

        // ACK the sender so a single-client "fire and verify" smoke test
        // still works (clients ignore the 'response' type).
        $this->send($server, $connection->fd, 'response', [
            'to'       => 'presence',
            'presence' => $presence,
        ]);

        // Fan out an 'event' frame to every connection (including the sender,
        // if it has its own subscription) that subscribes to a channel
        // overlapping with this presence document. This is what lets a
        // subscribe(...) listener observe an upsertPresence(...) end-to-end.
        $channels = [
            'presences',
            "presences.{$presenceId}",
            "presences.{$presenceId}.upsert",
        ];
        $eventName = "presences.{$presenceId}.upsert";
        foreach ($this->connections as $fd => $conn) {
            $matches = $conn->matchingSubscriptions($channels);
            if (empty($matches)) {
                continue;
            }
            $this->send($server, $fd, 'event', [
                'channels'      => $channels,
                'events'        => [$eventName],
                'timestamp'     => $now,
                'payload'       => $presence,
                'subscriptions' => $matches,
            ]);
        }
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
