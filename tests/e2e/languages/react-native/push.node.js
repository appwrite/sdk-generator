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
import { NativeModules, PermissionsAndroid, Platform } from 'react-native';
import { __test as expoNotifications } from 'expo-notifications';
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

    const cookieEndpoint = 'https://cloud.example.test/v1';
    let cookieValue = encodeURIComponent(e2eSession);
    NativeModules.AppwriteCookies = {
        session: async (url, project) => (url === cookieEndpoint && project === 'console' ? cookieValue : null),
    };
    const cookieClient = new Client().setEndpoint(cookieEndpoint).setProject('console').setPushEndpoint(ENDPOINT);
    console.log(
        onlyTopic(await userTopicsOf(cookieClient), 'users/e2e-session-user')
            ? 'Push user cookie topic:passed'
            : 'Push user cookie topic:failed',
    );

    const switchPush = new Push(cookieClient);
    await switchPush.subscribe(['e2e-switch'], () => {});
    cookieValue = 'deny:switched-user';
    let switchedError = '';
    try {
        await switchPush.subscribe(['e2e-switch'], () => {});
    } catch (e) {
        switchedError = e instanceof Error ? e.message : String(e);
    }
    switchPush.close();
    console.log(switchedError === 'switched-user' ? 'Push user cookie switch:passed' : 'Push user cookie switch:failed');

    cookieValue = encodeURIComponent(e2eSession);
    const pendingPush = new Push(cookieClient);
    let pendingOpened = false;
    pendingPush.onOpen(() => (pendingOpened = true));
    const outcome = (subscribing) => subscribing.then(() => '', (e) => (e instanceof Error ? e.message : String(e)));
    const firstPending = outcome(pendingPush.subscribe(['e2e-switch'], () => {}));
    await new Promise((resolve) => setImmediate(resolve));
    const switchedWhileConnecting = !pendingOpened;
    cookieValue = 'deny:switched-pending';
    const secondPending = await outcome(pendingPush.subscribe(['e2e-switch'], () => {}));
    const firstPendingOutcome = await Promise.race([firstPending, timeout(5000, 'timeout')]);
    pendingPush.close();
    console.log(
        secondPending === 'switched-pending' &&
            (firstPendingOutcome === 'switched-pending' || (!switchedWhileConnecting && firstPendingOutcome === ''))
            ? 'Push user cookie pending switch:passed'
            : `Push user cookie pending switch:failed (first: ${firstPendingOutcome}, second: ${secondPending}, connecting: ${switchedWhileConnecting})`,
    );

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
        host: async () => true,
        ack: () => {},
        release: async () => {},
        stop: async () => {},
        setForeground: async () => {},
        hasSaved: async () => false,
        resumed: [],
        resume: async (...args) => {
            NativeModules.AppwritePush.resumed.push(args);
        },
        backgroundStatus: async () =>
            JSON.stringify({ exactAlarms: false, ignoringBatteryOptimizations: false, foregroundService: false, bestEffort: true }),
        requestExactAlarms: async () => true,
        requestIgnoreBatteryOptimizations: async () => false,
        setErrorCallback: async () => null,
        defaultClientId: async () => 'e2e-install',
        getInitialNotification: async () =>
            JSON.stringify({ topic: 'news', payload: JSON.stringify({ data: { saleId: '42' } }) }),
        listening: [],
        listenOpened: async (listening) => {
            NativeModules.AppwritePush.listening.push(listening);
        },
        addListener: () => {},
        removeListeners: () => {},
    };
    const firstErrors = [];
    const secondErrors = [];
    const firstUser = new Push(sessionClient).onError((error) => firstErrors.push(error));
    await firstUser.subscribe(['news'], () => {}, { background: true });
    const firstClosed = [];
    firstUser.onClose(() => firstClosed.push(true));
    const secondOpened = [];
    const secondClosed = [];
    const secondUser = new Push(client)
        .onError((error) => secondErrors.push(error))
        .onOpen(() => secondOpened.push(true))
        .onClose(() => secondClosed.push(true));
    await secondUser.subscribe(['news'], () => {}, { background: true });
    await timeout(200);
    // A Push joining the connected native host hears onOpen once hosting completes, without a
    // second onOpen for the one already there; a close reaches the hosted ones only.
    const thirdOpened = [];
    const thirdClosed = [];
    const thirdUser = new Push(client)
        .onOpen(() => thirdOpened.push(true))
        .onClose(() => thirdClosed.push(true));
    await thirdUser.subscribe(['sports'], () => {}, { background: true });
    const firstClosedBefore = firstClosed.length;
    NativeModules.AppwritePush.emitter.emit('AppwritePushConnection', { connected: false });
    NativeModules.AppwritePush.emitter.emit('AppwritePushConnection', { connected: false });
    const nativeConnection =
        secondOpened.length === 1 && thirdOpened.length === 1 && secondClosed.length === 1 && thirdClosed.length === 1 &&
        firstClosed.length === firstClosedBefore
            ? 'Push native connection JS:passed'
            : `Push native connection JS:failed (opened: ${secondOpened.length}/${thirdOpened.length}, closed: ${secondClosed.length}/${thirdClosed.length}, displaced: ${firstClosed.length - firstClosedBefore})`;
    thirdUser.close();
    console.log(firstErrors.some((e) => e.message.includes('Background delivery stopped')) && secondErrors.length === 0 ? 'Push native displaced:passed' : 'Push native displaced:failed');
    console.log(PermissionsAndroid.requested.length === 1 && PermissionsAndroid.requested[0] === 'android.permission.POST_NOTIFICATIONS' ? 'Push notification permission:passed' : 'Push notification permission:failed');
    // Taps on background notifications: the launching one is parsed to its data, and later ones
    // reach onNotificationOpened until it is stopped.
    const launched = await firstUser.getInitialNotification();
    const tapped = [];
    const stopOpened = firstUser.onNotificationOpened((opened) => tapped.push(opened));
    NativeModules.AppwritePush.emitter.emit('AppwritePushOpened', { topic: 'news', payload: JSON.stringify({ data: { saleId: '7' } }) });
    stopOpened();
    stopOpened();
    NativeModules.AppwritePush.emitter.emit('AppwritePushOpened', { topic: 'news', payload: 'not json' });
    // Background status and the requests reach the native module, and the saved delivery resumed
    // with the session set on the client, which is never taken for a sign-out.
    const status = await firstUser.backgroundStatus();
    const askedExact = await firstUser.requestExactAlarms();
    const askedBattery = await firstUser.requestIgnoreBatteryOptimizations();
    const resumed = NativeModules.AppwritePush.resumed.find((args) => args[0] === 'appwrite-session');
    console.log(nativeConnection);
    console.log(status?.bestEffort === true && askedExact === true && askedBattery === false && resumed?.[1] === e2eSession && resumed?.[2] === false ? 'Push background status JS:passed' : 'Push background status JS:failed');
    console.log(launched?.topic === 'news' && launched.data.saleId === '42' && tapped.length === 1 && tapped[0].data.saleId === '7' && NativeModules.AppwritePush.listening.join() === 'true,false' ? 'Push notification opened JS:passed' : 'Push notification opened JS:failed');

    // The same taps outside Android, where expo-notifications posted the notification: the launching
    // response is read and cleared, notifications the SDK did not post are ignored, and later taps
    // reach onNotificationOpened until it is stopped.
    const expoResponse = (data) => ({ notification: { request: { content: { data } } } });
    const expoTap = (saleId) => expoResponse({ topic: 'news', payload: JSON.stringify({ data: { saleId } }) });
    Platform.OS = 'ios';
    const iosPush = new Push(sessionClient);
    expoNotifications.lastResponse = expoTap('1');
    const iosLaunched = await iosPush.getInitialNotification();
    const iosCleared = expoNotifications.lastResponse === null;
    expoNotifications.lastResponse = expoResponse({ screen: 'other-library' });
    const iosForeign = await iosPush.getInitialNotification();
    const iosTapped = [];
    const stopIos = iosPush.onNotificationOpened((opened) => iosTapped.push(opened));
    expoNotifications.respond(expoResponse({ screen: 'other-library' }));
    expoNotifications.respond(expoTap('2'));
    stopIos();
    expoNotifications.respond(expoTap('3'));
    iosPush.close();
    Platform.OS = 'android';
    console.log(iosLaunched?.data.saleId === '1' && iosCleared && iosForeign === null && iosTapped.length === 1 && iosTapped[0].data.saleId === '2' ? 'Push notification opened expo:passed' : 'Push notification opened expo:failed');
    firstUser.close();
    secondUser.close();
    delete NativeModules.AppwritePush;

    const closingPush = new Push(
        new Client().setProject('console').setPushEndpoint(ENDPOINT).setJWT('slow:closing'),
    );
    const closingOutcome = closingPush.subscribe(['e2e-switch'], () => {}).then(
        () => '',
        (e) => (e instanceof Error ? e.message : String(e)),
    );
    await timeout(100);
    closingPush.close();
    const closedOutcome = await Promise.race([closingOutcome, timeout(5000, 'timeout')]);
    console.log(
        closedOutcome === 'Push was closed before the subscription was established'
            ? 'Push close while connecting:passed'
            : `Push close while connecting:failed (${closedOutcome})`,
    );

    const quickPush = new Push(new Client().setProject('console').setPushEndpoint(ENDPOINT).setSession(e2eSession));
    let quickOpened = false;
    quickPush.onOpen(() => (quickOpened = true));
    const quickOutcome = quickPush.subscribe(['e2e-switch'], () => {}).then(
        () => '',
        (e) => (e instanceof Error ? e.message : String(e)),
    );
    quickPush.close();
    const quickClosed = await Promise.race([quickOutcome, timeout(5000, 'timeout')]);
    await timeout(200);
    console.log(
        quickClosed === 'Push was closed before the subscription was established' && !quickOpened
            ? 'Push close right after subscribe:passed'
            : `Push close right after subscribe:failed (${quickClosed}, opened: ${quickOpened})`,
    );
    quickPush.close();

    // mqtt.js keepalive timers would otherwise hold the event loop open.
    process.exit(0);
}

main().catch((error) => {
    console.log('PUSH ERROR: ' + (error && error.message ? error.message : String(error)));
    process.exit(1);
});
