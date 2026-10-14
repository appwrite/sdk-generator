import 'react-native-url-polyfill/auto';
import { useEffect, useState } from 'react';
import { Button, ScrollView, Text, View } from 'react-native';
import { Client, Push } from 'react-native-appwrite';

// The device tests' user on the mock broker: base64 of {"id": "e2e-device-user", "secret": "device"}.
const SESSION = 'eyJpZCI6ImUyZS1kZXZpY2UtdXNlciIsInNlY3JldCI6ImRldmljZSJ9';

const client = new Client()
  .setEndpoint('http://10.0.2.2/v1')
  .setProject('console')
  .setPushEndpoint('mqtt://10.0.2.2:1883')
  .setSession(SESSION);
const push = new Push(client);

export default function App() {
  const [lines, setLines] = useState([]);
  const [status, setStatus] = useState(null);

  // One event per line, read by the device test driver.
  const log = (line) => {
    console.log(`push-e2e ${line}`);
    setLines((previous) => [line, ...previous]);
  };

  const checkStatus = async () => {
    const current = await push.backgroundStatus();
    log(
      `status: exact=${current.exactAlarms} battery=${current.ignoringBatteryOptimizations} bestEffort=${current.bestEffort}`,
    );
    setStatus(current);
  };

  useEffect(() => {
    let stopOpened;
    (async () => {
      push
        .onOpen(() => log('connected'))
        .onClose(() => log('disconnected'))
        .onError((error) => log(`error: ${error.message}`));
      await push.subscribe((message) => {
        log(`message: ${JSON.parse(message.data).notification?.title}`);
      });
      log('subscribed');
      await checkStatus();
      const opened = await push.getInitialNotification();
      if (opened) log(`launched: ${opened.data.saleId}`);
      stopOpened = push.onNotificationOpened(({ data }) => log(`opened: ${data.saleId}`));
    })().catch((error) => log(`error: ${error.message}`));
    return () => stopOpened?.();
  }, []);

  return (
    <View style={{ flex: 1, padding: 16, paddingTop: 48, gap: 8 }}>
      {status && !status.exactAlarms && (
        <Button
          title="Allow exact alarms"
          onPress={async () => log(`asked exact: ${await push.requestExactAlarms()}`)}
        />
      )}
      {status && !status.ignoringBatteryOptimizations && (
        <Button
          title="Ignore battery optimisation"
          onPress={async () => log(`asked battery: ${await push.requestIgnoreBatteryOptimizations()}`)}
        />
      )}
      <Button title="Check background status" onPress={checkStatus} />
      <ScrollView>
        {lines.map((line, i) => (
          <Text key={i}>{line}</Text>
        ))}
      </ScrollView>
    </View>
  );
}
