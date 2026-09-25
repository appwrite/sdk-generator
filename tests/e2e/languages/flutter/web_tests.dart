import 'dart:async';

import 'package:packageName/packageName.dart';

// Flutter web e2e: exercises the push service over its web transport
// (MQTT-over-WebSocket via MqttBrowserClient) against the mock broker. The native
// (dart:io) path is covered by tests.dart over TCP; this runs under
// `flutter test --platform chrome`, where the conditional import selects the
// browser client and a raw TCP socket is unavailable.
void main() async {
  Client client = Client()
      .setProject('console')
      .addHeader('Origin', 'http://localhost');

  print('\nTest Started');
  final sdkHeaders = client.getHeaders();
  print(
      "x-sdk-name: ${sdkHeaders['x-sdk-name']}; x-sdk-platform: ${sdkHeaders['x-sdk-platform']}; x-sdk-language: ${sdkHeaders['x-sdk-language']}; x-sdk-version: ${sdkHeaders['x-sdk-version']}");

  // Native push (MQTT over WebSocket): subscribe, then the mock broker delivers a
  // message (server-initiated, as in production — the SDK has no publish method).
  client.setJWT(
      'eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VySWQiOiJlMmUtdXNlciJ9.e2e');
  client.setPushEndpoint('ws://mqtt:8083');
  final push = Push(client);
  final pushOpened = Completer<void>();
  push.onOpen(() {
    if (!pushOpened.isCompleted) {
      pushOpened.complete();
    }
  });
  final pushReceived = Completer<PushMessage>();
  final pushSub = await push.subscribe([Topic.path(['e2e-push'])], (m) {
    if (!pushReceived.isCompleted) {
      pushReceived.complete(m);
    }
  });
  print('Push subscribe:passed');
  try {
    await pushOpened.future.timeout(const Duration(seconds: 10));
    print('Push open:passed');
  } catch (_) {
    print('Push open:failed');
  }
  final pushMessage =
      await pushReceived.future.timeout(const Duration(seconds: 10));
  print(pushMessage.string == 'push-payload' && pushMessage.topic == 'e2e-push'
      ? 'Push message:passed'
      : 'Push message:failed');
  // reliableDelivery (default) => QoS 1 end to end.
  print(pushMessage.qos == 1 ? 'Push qos:passed' : 'Push qos:failed');
  pushSub.unsubscribe();
  push.close();

  // Topic-less subscribe: the signed-in user's own `users/<userId>` topic. After each
  // SUBSCRIBE the mock publishes to `users/e2e-user`, `users/e2e-session-user` and
  // `users/other-user`, so a client passes only if it receives its own topic and
  // nothing else (an over-broad `users/+` or `users/#` subscription would also get
  // the others).
  const e2eSession =
      'eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0=';
  Future<List<String>> userTopicsOf(Client userClient) async {
    final userPush = Push(userClient);
    final received = <String>[];
    // The topic-less form defaults to background: true; the test stays in-process.
    final userSub = await userPush.subscribe(null, (m) {
      received.add(m.topic);
    }, background: false);
    await Future.delayed(const Duration(seconds: 3));
    userSub.unsubscribe();
    userPush.close();
    return received;
  }

  bool onlyTopic(List<String> received, String expected) =>
      received.isNotEmpty && received.every((topic) => topic == expected);

  // JWT and session both set: the JWT's user wins.
  client.setSession(e2eSession);
  try {
    final jwtTopics = await userTopicsOf(client);
    print(onlyTopic(jwtTopics, 'users/e2e-user')
        ? 'Push user topic:passed'
        : 'Push user topic:failed');
  } catch (_) {
    print('Push user topic:failed');
  }

  // Session only: the user id comes from the session secret.
  final sessionClient = Client()
      .setProject('console')
      .setPushEndpoint('ws://mqtt:8083')
      .setSession(e2eSession);
  try {
    final sessionTopics = await userTopicsOf(sessionClient);
    print(onlyTopic(sessionTopics, 'users/e2e-session-user')
        ? 'Push user session topic:passed'
        : 'Push user session topic:failed');
  } catch (_) {
    print('Push user session topic:failed');
  }

  // No credential: a topic-less subscribe has no user to resolve and throws.
  final anonymousPush = Push(Client()
      .setProject('console')
      .setPushEndpoint('ws://mqtt:8083'));
  var noCredentialRejected = false;
  try {
    await anonymousPush.subscribe(null, (_) {});
  } on AppwriteException catch (e) {
    // The credential error itself, not any failure (setup, connection, ...).
    noCredentialRejected = e.message?.contains('signed-in user') ?? false;
  } catch (_) {}
  anonymousPush.close();
  print(noCredentialRejected
      ? 'Push user no credential:passed'
      : 'Push user no credential:failed');

  // Broker errors reach onError carrying the broker's MQTT 5 Reason String: a refused
  // CONNECT (the mock refuses a "deny:<reason>" credential with <reason>) and a server-initiated DISCONNECT
  // (the mock disconnects a client subscribing to "e2e-disconnect/<reason>" with <reason>).
  Future<String> firstError(Push errorPush, String topic) async {
    final error = Completer<String>();
    errorPush.onError((e) {
      if (!error.isCompleted) {
        error.complete(
          e is AppwriteException ? (e.message ?? '') : e.toString(),
        );
      }
    });
    // Not awaited: the error, not the subscribe outcome, is under test.
    unawaited(
      errorPush
          .subscribe(topic, (_) {}, background: false)
          .then((_) {}, onError: (Object _) {}),
    );
    try {
      return await error.future.timeout(const Duration(seconds: 5));
    } catch (_) {
      return '';
    }
  }

  final deniedPush = Push(
    Client()
        .setProject('console')
        .setPushEndpoint('ws://mqtt:8083')
        .setJWT('deny:refused-by-test'),
  );
  final deniedError = await firstError(deniedPush, 'e2e-push');
  print(
    deniedError == 'refused-by-test'
        ? 'Push connect error:passed'
        : 'Push connect error:failed',
  );
  deniedPush.close();

  final kickedPush = Push(client);
  final kickedError = await firstError(kickedPush, 'e2e-disconnect/kicked-by-test');
  print(
    kickedError == 'kicked-by-test'
        ? 'Push disconnect error:passed'
        : 'Push disconnect error:failed',
  );
  kickedPush.close();
}
