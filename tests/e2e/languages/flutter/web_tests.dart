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

  // Native push (MQTT over WebSocket) round-trip against the mock broker.
  client.setJWT('e2e-jwt');
  client.setPushEndpoint('ws://mqtt:8083/mqtt');
  final push = Push(client);
  final pushReceived = Completer<PushMessage>();
  final pushUnsub = await push.subscribe('e2e/push', (m) {
    if (!pushReceived.isCompleted) {
      pushReceived.complete(m);
    }
  });
  print('Push subscribe:passed');
  await push.publish('e2e/push', 'push-payload');
  final pushMessage =
      await pushReceived.future.timeout(const Duration(seconds: 10));
  print(pushMessage.string == 'push-payload'
      ? 'Push message:passed'
      : 'Push message:failed');
  pushUnsub();
  push.close();
}
