part of appwrite;

class RTSub {
  final Stream stream;
  final Function close;

  RTSub({required this.stream, required this.close});
}

class Realtime {
  static Realtime? _instance;
  late WebSocketChannel websok;

  String? endPoint;
  String? project;
  String? lastUrl;
  late Map<String, List<StreamController>> channels;
  Map<String, dynamic>? lastMessage;

  Realtime._internal() {
    channels = Map<String, List<StreamController>>.from({});
  }

  static Realtime get instance {
    if (_instance == null) {
      _instance = Realtime._internal();
    }
    return _instance!;
  }

  _closeConnection() {
    websok.sink.close.call();
  }

  createSocket() {
    //close existing connection
    if (endPoint == null) return;
    if (channels.keys.length < 1) {
      _closeConnection();
      return;
    }
    var uri = Uri.parse(endPoint!);
    uri = Uri(
        host: uri.host,
        scheme: uri.scheme,
        queryParameters: {
          "project": project,
          "channels[]": channels.keys,
        },
        path: uri.path + "/realtime");
    if (lastUrl == uri.toString() && websok.closeCode == null) {
      return;
    }
    lastUrl = uri.toString();
    print('subscription: $lastUrl');
    websok = WebSocketChannel.connect(uri);
    websok.stream.listen((event) {
      final data = jsonDecode(event);
      lastMessage = data;
      if (data['channels'] != null) {
        List<String> received = List<String>.from(data['channels']);
        received.forEach((channel) {
          if (channels[channel] != null) {
            channels[channel]!.forEach((stream) {
              stream.sink.add(event);
            });
          }
        });
      }
    });
  }
}
