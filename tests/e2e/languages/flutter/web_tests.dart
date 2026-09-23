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
  client.setJWT('e2e-jwt');
  client.setPushEndpoint('ws://mqtt:8083');
  final push = Push(client);
  final pushOpened = Completer<void>();
  push.onOpen(() {
    if (!pushOpened.isCompleted) {
      pushOpened.complete();
    }
  });
  final pushReceived = Completer<PushMessage>();
  final pushSub = await push.subscribe('e2e-push', (m) {
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
}
