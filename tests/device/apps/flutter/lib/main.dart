import 'dart:convert';

import 'package:appwrite/appwrite.dart';
import 'package:flutter/material.dart';

// The device tests' user on the mock broker: base64 of {"id": "e2e-device-user", "secret": "device"}.
const session = 'eyJpZCI6ImUyZS1kZXZpY2UtdXNlciIsInNlY3JldCI6ImRldmljZSJ9';

final client = Client()
    .setEndpoint('http://10.0.2.2/v1')
    .setProject('console')
    .setPushEndpoint('mqtt://10.0.2.2:1883')
    .setSession(session);
final push = Push(client);

void main() => runApp(const MaterialApp(home: PushScreen()));

class PushScreen extends StatefulWidget {
  const PushScreen({super.key});

  @override
  State<PushScreen> createState() => _PushScreenState();
}

class _PushScreenState extends State<PushScreen> {
  final lines = <String>[];
  PushBackgroundStatus? status;

  // One event per line, read by the device test driver.
  void log(String line) {
    debugPrint('push-e2e $line');
    if (mounted) setState(() => lines.insert(0, line));
  }

  @override
  void initState() {
    super.initState();
    start().catchError((Object e) => log('error: $e'));
  }

  Future<void> start() async {
    push
        .onOpen(() => log('connected'))
        .onClose(() => log('disconnected'))
        .onError((e) => log('error: $e'));
    await push.subscribe(null, (message) {
      final body = jsonDecode(message.data) as Map<String, dynamic>;
      log('message: ${body['notification']?['title']}');
    });
    log('subscribed');
    await checkStatus();
    final opened = await push.getInitialNotification();
    if (opened != null) {
      log('launched: ${opened.data['saleId']}');
    }
    push.onNotificationOpened(
      (opened) => log('opened: ${opened.data['saleId']}'),
    );
  }

  Future<void> checkStatus() async {
    final current = await push.backgroundStatus();
    if (current != null) {
      log(
        'status: exact=${current.exactAlarms} battery=${current.ignoringBatteryOptimizations} '
        'bestEffort=${current.bestEffort}',
      );
    }
    if (mounted) setState(() => status = current);
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: Column(
        children: [
          if (status != null && !status!.exactAlarms)
            TextButton(
              onPressed: () async =>
                  log('asked exact: ${await push.requestExactAlarms()}'),
              child: const Text('Allow exact alarms'),
            ),
          if (status != null && !status!.ignoringBatteryOptimizations)
            TextButton(
              onPressed: () async => log(
                'asked battery: ${await push.requestIgnoreBatteryOptimizations()}',
              ),
              child: const Text('Ignore battery optimisation'),
            ),
          TextButton(
            onPressed: checkStatus,
            child: const Text('Check background status'),
          ),
          Expanded(
            child: ListView(children: [for (final line in lines) Text(line)]),
          ),
        ],
      ),
    ),
  );
}
