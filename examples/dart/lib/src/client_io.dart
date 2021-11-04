import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:http/io_client.dart';
import 'client_mixin.dart';
import 'client_base.dart';
import 'enums.dart';
import 'exception.dart';
import 'response.dart';

ClientBase createClient({
  required String endPoint,
  required bool selfSigned,
}) =>
    ClientIO(
      endPoint: endPoint,
      selfSigned: selfSigned,
    );

class ClientIO extends ClientBase with ClientMixin {
  String _endPoint;
  Map<String, String>? _headers;
  late Map<String, String> config;
  bool _initialized = false;
  late http.Client _httpClient;
  late HttpClient _nativeClient;

  ClientIO({
    String endPoint = 'https://appwrite.io/v1',
    bool selfSigned = false,
  }) : _endPoint = endPoint {
    _nativeClient = new HttpClient()
      ..badCertificateCallback =
          ((X509Certificate cert, String host, int port) => selfSigned);
    _httpClient = new IOClient(_nativeClient);
    this._endPoint = endPoint;
    this._headers = {
      'content-type': 'application/json',
      'x-sdk-version': 'appwrite:dart:0.0.1',
      'X-Appwrite-Response-Format' : '0.7.0',
    };

    this.config = {};

    assert(_endPoint.startsWith(RegExp("http://|https://")),
        "endPoint $_endPoint must start with 'http'");
  }

  String get endPoint => _endPoint;

     /// Your project ID
    ClientIO setProject(value) {
        config['project'] = value;
        addHeader('X-Appwrite-Project', value);
        return this;
    }
     /// Your secret API key
    ClientIO setKey(value) {
        config['key'] = value;
        addHeader('X-Appwrite-Key', value);
        return this;
    }
     /// Your secret JSON Web Token
    ClientIO setJWT(value) {
        config['jWT'] = value;
        addHeader('X-Appwrite-JWT', value);
        return this;
    }
    ClientIO setLocale(value) {
        config['locale'] = value;
        addHeader('X-Appwrite-Locale', value);
        return this;
    }

  ClientIO setSelfSigned({bool status = true}) {
    _nativeClient.badCertificateCallback =
        ((X509Certificate cert, String host, int port) => status);
    return this;
  }

  ClientIO setEndpoint(String endPoint) {
    this._endPoint = endPoint;
    return this;
  }

  ClientIO addHeader(String key, String value) {
    _headers![key] = value;
    return this;
  }

  Future<Response> call(
    HttpMethod method, {
    String path = '',
    Map<String, String> headers = const {},
    Map<String, dynamic> params = const {},
    ResponseType? responseType,
  }) async {
    late http.Response res;
    http.BaseRequest request = this.prepareRequest(
      method,
      uri: Uri.parse(_endPoint + path),
      headers: {...this._headers!, ...headers},
      params: params,
    );

    try {
      final streamedResponse = await _httpClient.send(request);
      res = await toResponse(streamedResponse);
      return this.prepareResponse(
        res,
        responseType: responseType,
      );
    } catch (e) {
      if (e is AppwriteException) {
        throw e;
      }
      throw AppwriteException(e.toString());
    }
  }
}
