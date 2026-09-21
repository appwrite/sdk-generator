<?php

/**
 * A minimal but real MQTT broker (utopia-php/mqtt) used by the e2e suite so the native
 * push SDKs can be exercised end to end. The library owns framing, decoding, per-version
 * encoding, packet ids, the QoS handshake, keep-alive reaping and subscription matching
 * (+ / # wildcards); this file only supplies test policy through a Handler:
 *
 *  - CONNECT is accepted unless the credential is the literal "deny" (so a test can assert
 *    a rejected connection); the projectId user property becomes the connection prefix.
 *  - SUBSCRIBE grants every filter at the requested QoS (capped at QoS 1) and then, mirroring
 *    real server-initiated push, delivers a message on the subscribed topic so the e2e can
 *    assert receipt — the SDKs only subscribe, they have no publish method.
 *
 * It listens on both transports the library ships: plain TCP (1883) and WebSocket (8083),
 * so the TCP SDKs and the browser/WebSocket SDK can both reach it.
 *
 * The broker needs utopia-php/mqtt, which requires utopia-php/telemetry ^0.4 — a constraint
 * the mock HTTP server's utopia-php/framework (older telemetry line, carries param(model:))
 * does not allow. So it installs into a separate ./vendor-mqtt, kept apart from the HTTP
 * mock's ./vendor; the manifest is inlined in the Dockerfile (no composer.mqtt.json). This
 * file loads that vendor.
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
    public function onConnect(Connect $connect, Connection $connection): Connack|Auth
    {
        $connection->prefix = $connect->userProperties()['projectId'] ?? '';

        if (($connect->authData ?? '') === 'deny') {
            return Connack::refuse(Connack::NOT_AUTHORIZED);
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
            $grantedQos = \min($filter->qos, Packet::QOS_1);
            $suback->grant($grantedQos);

            // Real push is server-initiated (server -> client); the SDKs only subscribe and
            // have no publish method. So, mirroring the real broker, deliver a message on the
            // subscribed topic here so the e2e can assert receipt. Deferred one tick so it
            // lands after the SUBACK the library sends when this handler returns.
            $topic = $filter->topic;
            \Swoole\Timer::after(100, static function () use ($connection, $topic, $grantedQos) {
                $connection->publish($topic, 'push-payload', qos: $grantedQos);
            });
        }

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

$server = new Server($adapter, new MockHandler());
$server->onStart(fn () => print("mqtt broker started\n"));
$server->error(fn (\Throwable $error, string $action) => \fwrite(STDERR, "mqtt {$action} error: " . $error->getMessage() . "\n"));
$server->start();
