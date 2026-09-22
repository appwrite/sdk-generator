// React Native push e2e, run under Node.
//
// The RN Push service is raw-TCP only (mqtt.js over react-native-tcp-socket), which the
// browser harness (browser.js) cannot exercise. This entry runs the *generated* Push client
// under Node against the mock broker's TCP listener (mqtt:1883). rollup.push.config.mjs
// aliases `react-native-tcp-socket` to a thin Node net/tls adapter (shims/), so the real
// src/services/push.ts + src/lib/tcp-stream.ts run unmodified; only the leaf native socket
// is swapped for Node's net (an identical API subset). The literal native module and
// background delivery still need a device and stay out of CI.
import { Client } from './src/client';
import { Push } from './src/services/push';

async function main() {
    const client = new Client();

    console.log('Test Started');
    const sdkHeaders = client.getHeaders();
    console.log(
        `x-sdk-name: ${sdkHeaders['x-sdk-name']}; x-sdk-platform: ${sdkHeaders['x-sdk-platform']}; x-sdk-language: ${sdkHeaders['x-sdk-language']}; x-sdk-version: ${sdkHeaders['x-sdk-version']}`,
    );

    client.setProject('console');
    client.setJWT('e2e-jwt');
    // TCP transport: the mock broker's raw-TCP listener (browsers/Flutter-web use ws:8083).
    client.setPushEndpoint('mqtt://mqtt:1883');

    const push = new Push(client);

    let openResolve;
    const opened = new Promise((resolve) => (openResolve = resolve));
    push.onOpen(() => openResolve());

    let messageResolve;
    const received = new Promise((resolve) => (messageResolve = resolve));

    // Server-initiated: the broker delivers on SUBSCRIBE (no client publish).
    const sub = await push.subscribe('e2e/push', (message) => {
        messageResolve({
            text: message.payload.toString(),
            topic: message.topic,
            qos: message.qos,
        });
    });
    console.log('Push subscribe:passed');

    const openOk = await Promise.race([
        opened.then(() => true),
        new Promise((resolve) => setTimeout(() => resolve(false), 10000)),
    ]);
    console.log(openOk ? 'Push open:passed' : 'Push open:failed');

    const message = await Promise.race([
        received,
        new Promise((resolve) =>
            setTimeout(() => resolve({ text: 'timeout', topic: '', qos: -1 }), 10000),
        ),
    ]);
    console.log(
        message.text === 'push-payload' && message.topic === 'e2e/push'
            ? 'Push message:passed'
            : 'Push message:failed',
    );
    // reliableDelivery default => QoS 1 end to end.
    console.log(message.qos === 1 ? 'Push qos:passed' : 'Push qos:failed');

    sub.unsubscribe();
    push.close();
    // mqtt.js keepalive timers would otherwise hold the event loop open.
    process.exit(0);
}

main().catch((error) => {
    console.log('PUSH ERROR: ' + (error && error.message ? error.message : String(error)));
    process.exit(1);
});
