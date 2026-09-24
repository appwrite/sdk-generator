<?php

/**
 * A minimal but real MQTT broker (utopia-php/mqtt) used by the e2e suite so the native
 * push SDKs can be exercised end to end. The library owns framing, decoding, per-version
 * encoding, packet ids, the QoS handshake, keep-alive reaping and subscription matching
 * (+ / # wildcards); this file only supplies test policy through a Handler:
 *
 *  - CONNECT is accepted unless the credential is "deny" or "deny:<reason>", which is refused
 *    (with <reason> as the Reason String) so a test can assert a rejected connection and that
 *    its reason reaches the SDK; the projectId user property becomes the connection prefix.
 *  - SUBSCRIBE to "e2e-disconnect/<reason>" makes the broker DISCONNECT the client with <reason>
 *    as the Reason String.
 *  - SUBSCRIBE grants every filter at the requested QoS (capped at QoS 1) and then, mirroring
 *    real server-initiated push, publishes the test message to the fixed topic "e2e-push"
 *    through the broker's subscription index/fan-out — so a client receives it only if its
 *    subscription matches — and one message to each of "users/e2e-user", "users/e2e-session-user"
 *    and "users/other-user", the per-user topics a topic-less subscribe() can resolve to. The SDKs only subscribe; they have no
 *    publish method.
 *
 * It listens on both transports the library ships: plain TCP (1883) and WebSocket (8083),
 * so the TCP SDKs and the browser/WebSocket SDK can both reach it.
 *
 * The broker needs utopia-php/mqtt, which requires utopia-php/telemetry ^0.4 — a constraint
 * the mock HTTP server's utopia-php/framework (older telemetry line, carries param(model:))
 * does not allow. So its dependencies live in a separate manifest (composer.mqtt.json, with a
 * committed composer.mqtt.lock) installed to ./vendor-mqtt, kept apart from the HTTP mock's
 * ./vendor; this file loads that one.
 */

require_once __DIR__ . '/../vendor-mqtt/autoload.php';

use Utopia\Mqtt\Adapter;
use Utopia\Mqtt\Connection;
use Utopia\Mqtt\Handler;
use Utopia\Mqtt\Packet;
use Utopia\Mqtt\Packet\Auth;
use Utopia\Mqtt\Packet\Connack;
use Utopia\Mqtt\Packet\Connect;
use Utopia\Mqtt\Packet\Disconnect;
use Utopia\Mqtt\Packet\Publish;
use Utopia\Mqtt\Packet\Puback;
use Utopia\Mqtt\Packet\Suback;
use Utopia\Mqtt\Packet\Subscribe;
use Utopia\Mqtt\Packet\Unsuback;
use Utopia\Mqtt\Packet\Unsubscribe;
use Utopia\Mqtt\Properties;
use Utopia\Mqtt\Property;
use Utopia\Mqtt\Server;

class MockHandler implements Handler
{
    /** Set after construction so onSubscribe can route through the broker's fan-out. */
    public ?Server $server = null;

    public function onConnect(Connect $connect, Connection $connection): Connack|Auth
    {
        $connection->prefix = $connect->userProperties()['projectId'] ?? '';

        // A rejected credential, explained with an MQTT 5 Reason String like the real broker's
        // refuseConnect(). The test picks the reason: "deny:<reason>" is refused with <reason>.
        $credential = $connect->authData ?? '';
        if ($credential === 'deny' || \str_starts_with($credential, 'deny:')) {
            $reason = \substr($credential, \strlen('deny:'));
            $properties = $reason === '' || $reason === false
                ? null
                : (new Properties())->add(new Property(Property::REASON_STRING, $reason));

            return Connack::refuse(Connack::NOT_AUTHORIZED, $properties);
        }

        // Mirror the real broker: derive a stable per-connection id when the client sends
        // none, so an empty client id (the SDKs' default in reliable mode) is accepted.
        $clientId = $connect->clientId;
        if ($clientId === '') {
            $clientId = 'custom_' . $connection->prefix . '_' . \spl_object_id($connection);
        }
        $connection->setClientId($clientId);

        // Echo the authentication method in the CONNACK. MQTT 5 enhanced-auth clients that use
        // a challenge/response mechanism (HiveMQ on Android) require the accepting CONNACK to
        // carry the same Authentication Method they sent, or they abort the connection.
        $properties = null;
        if (!empty($connect->authMethod)) {
            $properties = (new Properties())
                ->add(new Property(Property::AUTHENTICATION_METHOD, $connect->authMethod));
        }

        return Connack::accept(false, $properties);
    }

    public function onAuthenticate(Auth $auth, Connection $connection): Connack|Auth|Disconnect
    {
        return Auth::success($auth->method);
    }

    public function onSubscribe(Subscribe $subscribe, Connection $connection): Suback
    {
        $suback = new Suback();
        foreach ($subscribe->filters() as $filter) {
            $suback->grant(\min($filter->qos, Packet::QOS_1));
        }

        // Real push is server-initiated (server -> client); the SDKs only subscribe and have no
        // publish method. After the SUBACK, publish the test message to the fixed topic
        // "e2e-push" through the broker's subscription index/fan-out (Server::subscribers), not
        // a direct echo on this socket — so a client only receives it if its subscription
        // actually matches "e2e-push", exercising real topic routing. Deferred one tick so it
        // lands after the SUBACK the library sends when this handler returns.
        // Subscribing to "e2e-disconnect/<reason>" makes the broker drop the connection with a
        // reason code and <reason> as the Reason String (a server-initiated DISCONNECT).
        foreach ($subscribe->filters() as $filter) {
            if (\str_starts_with($filter->topic, 'e2e-disconnect/')) {
                $reason = \substr($filter->topic, \strlen('e2e-disconnect/'));
                \Swoole\Timer::after(100, fn () => $connection->disconnect(Disconnect::NOT_AUTHORIZED, $reason));
            }
        }

        $prefix = $connection->prefix;
        \Swoole\Timer::after(100, function () use ($prefix) {
            foreach ($this->server?->subscribers($prefix, 'e2e-push') ?? [] as [$subscriber, $grantedQos]) {
                $subscriber->publish('e2e-push', 'push-payload', qos: \min($grantedQos, Packet::QOS_1));
            }
            // Per-user topics a topic-less subscribe() resolves to: e2e-user (the e2e JWT),
            // e2e-session-user (the e2e session secret) and other-user, which no test client
            // owns, so a client that over-subscribes (users/+, users/#) receives it and fails.
            foreach (['e2e-user', 'e2e-session-user', 'other-user'] as $user) {
                $topic = 'users/' . $user;
                foreach ($this->server?->subscribers($prefix, $topic) ?? [] as [$subscriber, $grantedQos]) {
                    $subscriber->publish($topic, 'push-user-payload', qos: \min($grantedQos, Packet::QOS_1));
                }
            }
        });

        return $suback;
    }

    public function onUnsubscribe(Unsubscribe $unsubscribe, Connection $connection): Unsuback
    {
        $unsuback = new Unsuback();
        // One success code per filter (the broker has already dropped them from its index).
        $count = \count($unsubscribe->filters());
        for ($i = 0; $i < $count; $i++) {
            $unsuback->success();
        }

        return $unsuback;
    }

    /**
     * Fan a client PUBLISH out to the matching subscribers the broker resolved, each at the
     * QoS granted to that subscription (clamped by the message QoS).
     *
     * @param iterable<array{0: Connection, 1: int}> $subscribers
     */
    public function onPublish(Publish $publish, Connection $connection, iterable $subscribers): void
    {
        foreach ($subscribers as [$subscriber, $grantedQos]) {
            $subscriber->publish($publish->topic, $publish->payload, qos: \min($publish->qos, $grantedQos));
        }

        // A client PUBLISH at QoS 1 expects a PUBACK. The library leaves this to the handler
        // (real deployments are server -> client only, so clients never publish), so the mock
        // must ack it or the publisher's publish() call hangs.
        if ($publish->qos > 0) {
            $connection->puback($publish->packetId);
        }
    }

    public function onPuback(Puback $puback, Connection $connection): void
    {
        $connection->acknowledge($puback->packetId);
    }

    public function onDisconnect(?Disconnect $disconnect, Connection $connection): void
    {
    }
}

$maxPacketSize = 64000;
$adapter = new Adapter\Swoole([
    new Adapter\Swoole\Tcp('0.0.0.0', 1883, $maxPacketSize),
    new Adapter\Swoole\WebSocket('0.0.0.0', 8083, $maxPacketSize),
], workers: 1);

$handler = new MockHandler();
$server = new Server($adapter, $handler);
$handler->server = $server;
$server->onStart(fn () => print("mqtt broker started\n"));
$server->error(fn (\Throwable $error, string $action) => \fwrite(STDERR, "mqtt {$action} error: " . $error->getMessage() . "\n"));
$server->start();
