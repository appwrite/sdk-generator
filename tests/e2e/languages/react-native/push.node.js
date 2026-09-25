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
import { EventEmitter } from 'events';
import { NativeModules } from 'react-native';
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
        messageResolve({ text: message.data, topic: message.topic, qos: message.qos });
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

    // A message published while the user is signed out reaches their next sign-in, though that
    // sign-in has a new session secret (subscribing to "e2e-replay-publish" makes the mock publish
    // on "e2e-replay", queued for clients that subscribed before and are offline).
    const firstSignIn = new Push(new Client().setProject('console').setPushEndpoint(ENDPOINT).setSession(e2eSession));
    await firstSignIn.subscribe(['e2e-replay'], () => {});
    firstSignIn.close();
    await timeout(500);
    const publisher = new Push(client);
    await publisher.subscribe(['e2e-replay-publish'], () => {});
    publisher.close();
    const nextSession = Buffer.from(JSON.stringify({ id: 'e2e-session-user', secret: 'another-sign-in' })).toString('base64');
    const nextSignIn = new Push(new Client().setProject('console').setPushEndpoint(ENDPOINT).setSession(nextSession));
    const replayed = await Promise.race([
        new Promise((resolve) => {
            nextSignIn.subscribe(['e2e-replay'], (m) => resolve(m.data));
        }),
        timeout(5000, ''),
    ]);
    nextSignIn.close();
    console.log(replayed === 'push-replayed' ? 'Push session replay:passed' : 'Push session replay:failed');

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

    // Two credentials with background delivery on Android: the device hosts one, so the Push that
    // started it first hears on onError that its background delivery stopped, and the other does
    // not. A stand-in answers for the SDK's native module, which needs an Android device.
    NativeModules.AppwritePush = {
        emitter: new EventEmitter(),
        host: async () => {},
        ack: () => {},
        release: async () => {},
        stop: async () => {},
        setForeground: async () => {},
        hasSaved: async () => false,
        resume: async () => {},
        setErrorCallback: async () => null,
        defaultClientId: async () => 'e2e-install',
        addListener: () => {},
        removeListeners: () => {},
    };
    const firstErrors = [];
    const secondErrors = [];
    const firstUser = new Push(sessionClient).onError((error) => firstErrors.push(error));
    await firstUser.subscribe(['news'], () => {}, { background: true });
    const secondUser = new Push(client).onError((error) => secondErrors.push(error));
    await secondUser.subscribe(['news'], () => {}, { background: true });
    await timeout(200);
    console.log(firstErrors.some((e) => e.message.includes('Background delivery stopped')) && secondErrors.length === 0 ? 'Push native displaced:passed' : 'Push native displaced:failed');
    firstUser.close();
    secondUser.close();
    delete NativeModules.AppwritePush;

    // mqtt.js keepalive timers would otherwise hold the event loop open.
    process.exit(0);
}

main().catch((error) => {
    console.log('PUSH ERROR: ' + (error && error.message ? error.message : String(error)));
    process.exit(1);
});
