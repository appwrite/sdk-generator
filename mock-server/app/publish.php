<?php

/**
 * Publish one message to the mock broker as the server would, for the device tests:
 *
 *     docker exec mqtt php app/publish.php <topic> <payload>
 *
 * Connects with an e2e session in the "console" project, publishes at QoS 1 and waits for the
 * PUBACK, so the message has reached the broker (and its persistent sessions) when this exits.
 */

require_once __DIR__ . '/../vendor-mqtt/autoload.php';

use Utopia\Mqtt\Client;
use Utopia\Mqtt\Packet;
use Utopia\Mqtt\Packet\Specs\V5;
use Utopia\Mqtt\Properties;
use Utopia\Mqtt\Property;

[, $topic, $payload] = $argv + [null, null, null];
if ($topic === null || $payload === null) {
    \fwrite(STDERR, "usage: php app/publish.php <topic> <payload>\n");
    exit(2);
}

$failure = null;
\Swoole\Coroutine\run(function () use ($topic, $payload, &$failure) {
    $client = new Client('mqtt://127.0.0.1:1883', ['timeout' => 10]);
    $client->connect();
    $properties = (new Properties())
        ->add(new Property(Property::AUTHENTICATION_METHOD, 'appwrite-session'))
        ->add(new Property(Property::AUTHENTICATION_DATA, 'device-publisher'))
        ->add(new Property(Property::USER, ['projectId' => 'console']));
    $client->send(V5::connect('device-publisher-' . \hrtime(true), 60, true, $properties));
    $connack = $client->receive();
    if ($connack === null || Packet::parse($connack)->type !== Packet::CONNACK) {
        $failure = 'no CONNACK';
        return;
    }
    $client->send(V5::publish($topic, $payload, Packet::QOS_1, 1));
    $puback = $client->receive();
    if ($puback === null || Packet::parse($puback)->type !== Packet::PUBACK) {
        $failure = 'no PUBACK';
        return;
    }
    $client->send(\chr(Packet::DISCONNECT << 4) . \chr(0));
    $client->close();
});

if ($failure !== null) {
    \fwrite(STDERR, "publish failed: {$failure}\n");
    exit(1);
}
