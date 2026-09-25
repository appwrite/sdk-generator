// React Native push e2e, run under Node as Platform.OS "android".
//
// The RN Push service is raw-TCP only (mqtt.js over react-native-tcp-socket), which the
// browser harness (browser.js) cannot exercise. This entry runs the *generated* Push client
// under Node against the mock broker's TCP listener (mqtt:1883). rollup.push.config.mjs
// aliases `react-native-tcp-socket` to a thin Node net/tls adapter (shims/), so the real
// src/services/push.ts + src/lib/tcp-stream.ts run unmodified; only the leaf native socket
// is swapped for Node's net (an identical API subset). There is no native Android module here
// (as in Expo Go), so background delivery is unavailable and the topic-less subscribe's
// default falls back to the foreground.
import { Client } from './src/client';
import { Push } from './src/services/push';
import { Topic } from './src/topic';

const ENDPOINT = 'mqtt://mqtt:1883';
const timeout = (ms, value) => new Promise((resolve) => setTimeout(() => resolve(value), ms));

async function main() {
    const client = new Client();

    console.log('Test Started');
    const sdkHeaders = client.getHeaders();
    console.log(
        `x-sdk-name: ${sdkHeaders['x-sdk-name']}; x-sdk-platform: ${sdkHeaders['x-sdk-platform']}; x-sdk-language: ${sdkHeaders['x-sdk-language']}; x-sdk-version: ${sdkHeaders['x-sdk-version']}`,
    );

    // A decodable JWT whose userId is "e2e-user", so the topic-less subscribe below resolves to
    // users/e2e-user.
    client.setProject('console');
    client.setJWT('eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VySWQiOiJlMmUtdXNlciJ9.e2e');
    client.setPushEndpoint(ENDPOINT);

    const push = new Push(client);
    let openResolve;
    const opened = new Promise((resolve) => (openResolve = resolve));
    push.onOpen(() => openResolve());
    let messageResolve;
    const received = new Promise((resolve) => (messageResolve = resolve));

    // Server-initiated: the broker delivers on SUBSCRIBE (no client publish).
    const sub = await push.subscribe([Topic.path(['e2e-push'])], (message) => {
        messageResolve({ text: message.payload.toString(), topic: message.topic, qos: message.qos });
    });
    console.log('Push subscribe:passed');
    console.log((await Promise.race([opened.then(() => true), timeout(10000, false)])) ? 'Push open:passed' : 'Push open:failed');
    const message = await Promise.race([received, timeout(10000, { text: 'timeout', topic: '', qos: -1 })]);
    console.log(message.text === 'push-payload' && message.topic === 'e2e-push' ? 'Push message:passed' : 'Push message:failed');
    // retry (default) => QoS 1 end to end.
    console.log(message.qos === 1 ? 'Push qos:passed' : 'Push qos:failed');
    sub.unsubscribe();
    push.close();

    // Topic-less subscribe: the signed-in user's own topic, users/<userId>. After each SUBSCRIBE
    // the mock publishes to users/e2e-user, users/e2e-session-user and users/other-user, so a
    // client passes only if it receives its own topic and nothing else. Its background default
    // falls back to the foreground here (no native module), so the messages still arrive.
    const e2eSession = 'eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0=';
    const userTopicsOf = async (userClient) => {
        const userPush = new Push(userClient);
        const topics = [];
        const userSub = await userPush.subscribe((m) => topics.push(m.topic));
        await timeout(3000);
        userSub.unsubscribe();
        userPush.close();
        return topics;
    };
    const onlyTopic = (topics, expected) => topics.length > 0 && topics.every((topic) => topic === expected);

    // JWT and session both set: the JWT's user wins.
    client.setSession(e2eSession);
    console.log(onlyTopic(await userTopicsOf(client), 'users/e2e-user') ? 'Push user topic:passed' : 'Push user topic:failed');

    // Session only: the user id comes from the session secret.
    const sessionClient = new Client().setProject('console').setPushEndpoint(ENDPOINT).setSession(e2eSession);
    console.log(
        onlyTopic(await userTopicsOf(sessionClient), 'users/e2e-session-user')
            ? 'Push user session topic:passed'
            : 'Push user session topic:failed',
    );

    // No credential: a topic-less subscribe has no user to resolve and rejects.
    const anonymousPush = new Push(new Client().setProject('console').setPushEndpoint(ENDPOINT));
    let noCredentialRejected = false;
    try {
        await anonymousPush.subscribe(() => {});
    } catch (e) {
        // The credential error itself, not any failure (setup, connection, ...).
        noCredentialRejected = e instanceof Error && e.message.includes('signed-in user');
    }
    anonymousPush.close();
    console.log(noCredentialRejected ? 'Push user no credential:passed' : 'Push user no credential:failed');

    // Broker errors reach onError carrying the broker's MQTT 5 Reason String: a refused CONNECT
    // (the mock refuses a "deny:<reason>" credential with <reason>) and a server-initiated
    // DISCONNECT (the mock disconnects a client subscribing to "e2e-disconnect/<reason>").
    const firstError = (errorPush) =>
        new Promise((resolve) => {
            errorPush.onError((error) => resolve(error.message));
            setTimeout(() => resolve(''), 5000);
        });
    const deniedPush = new Push(new Client().setProject('console').setPushEndpoint(ENDPOINT).setJWT('deny:refused-by-test'));
    const deniedError = firstError(deniedPush);
    try {
        await deniedPush.subscribe(['e2e-push'], () => {});
    } catch (e) {}
    console.log((await deniedError) === 'refused-by-test' ? 'Push connect error:passed' : 'Push connect error:failed');
    deniedPush.close();

    const kickedPush = new Push(client);
    const kickedError = firstError(kickedPush);
    try {
        await kickedPush.subscribe(['e2e-disconnect/kicked-by-test'], () => {});
    } catch (e) {}
    console.log((await kickedError) === 'kicked-by-test' ? 'Push disconnect error:passed' : 'Push disconnect error:failed');
    kickedPush.close();

    // mqtt.js keepalive timers would otherwise hold the event loop open.
    process.exit(0);
}

main().catch((error) => {
    console.log('PUSH ERROR: ' + (error && error.message ? error.message : String(error)));
    process.exit(1);
});
