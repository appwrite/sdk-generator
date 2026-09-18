<?php

/**
 * A minimal but real MQTT broker (utopia-php/mqtt) used by the e2e suite so the native
 * push SDKs can be exercised end to end. The library owns framing, decoding, per-version
 * encoding, packet ids, the QoS handshake, keep-alive reaping and subscription matching
 * (+ / # wildcards); this file only supplies test policy through a Handler:
 *
 *  - CONNECT is accepted unless the credential is the literal "deny" (so a test can assert
 *    a rejected connection); the projectId user property becomes the connection prefix.
 *  - SUBSCRIBE grants every filter at the requested QoS (capped at QoS 1).
 *  - a client PUBLISH is fanned out to the matching subscribers the broker hands us.
 *
 * It listens on both transports the library ships: plain TCP (1883) and WebSocket (8083),
 * so the TCP SDKs and the browser/WebSocket SDK can both reach it.
 *
 * The broker needs utopia-php/mqtt (dev), which requires utopia-php/telemetry ^0.4 — a
 * constraint no utopia-php/framework release (used by the mock HTTP server) allows. So its
 * dependencies live in a separate manifest (composer.mqtt.json) installed to ./vendor-mqtt,
 * kept apart from the HTTP mock's ./vendor; this file loads that one.
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

        return Connack::accept();
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

/**
 * The library's Swoole adapter relies on Swoole's default WebSocket handshake, which does
 * not echo the `Sec-WebSocket-Protocol: mqtt` subprotocol. Browser MQTT clients (MQTT.js)
 * send that header and reject the connection unless the server echoes it back — a real
 * deployment gets this from the proxy in front of the broker. Since the e2e browser talks
 * to the broker directly, add a compliant handshake here that echoes it. TCP is untouched.
 */
class WebSocketProtocolAdapter extends Adapter\Swoole
{
    public function start(): void
    {
        // $server is protected on the parent and already created in its constructor; add the
        // handshake handler before the parent registers its own handlers and starts.
        if ($this->server instanceof \Swoole\WebSocket\Server) {
            $this->server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool {
                $key = $request->header['sec-websocket-key'] ?? '';
                $accept = \base64_encode(\sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

                $response->header('Upgrade', 'websocket');
                $response->header('Connection', 'Upgrade');
                $response->header('Sec-WebSocket-Accept', $accept);
                $response->header('Sec-WebSocket-Version', '13');
                if (isset($request->header['sec-websocket-protocol'])) {
                    // MQTT-over-WebSocket uses the "mqtt" subprotocol; echo it so the client accepts.
                    $response->header('Sec-WebSocket-Protocol', 'mqtt');
                }

                $response->status(101);
                $response->end();

                return true;
            });
        }

        parent::start();
    }
}

$maxPacketSize = 64000;
$adapter = new WebSocketProtocolAdapter([
    new Adapter\Swoole\Tcp('0.0.0.0', 1883, $maxPacketSize),
    new Adapter\Swoole\WebSocket('0.0.0.0', 8083, $maxPacketSize),
], workers: 1);

$server = new Server($adapter, new MockHandler());
$server->onStart(fn () => print("mqtt broker started\n"));
$server->error(fn (\Throwable $error, string $action) => \fwrite(STDERR, "mqtt {$action} error: " . $error->getMessage() . "\n"));
$server->start();
