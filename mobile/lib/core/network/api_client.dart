import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({http.Client? httpClient}) : _http = httpClient ?? http.Client();

  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://xemchitay.it36vn.com/api',
  );

  final http.Client _http;

  Future<Map<String, dynamic>> get(
    String path, {
    String? token,
    Map<String, String?> queryParameters = const {},
  }) async {
    final response = await _http.get(
      _uri(path, queryParameters: queryParameters),
      headers: _headers(token),
    );
    return _decode(response);
  }

  Future<Map<String, dynamic>> post(
    String path,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await _http.post(
      _uri(path),
      headers: _headers(token),
      body: jsonEncode(body),
    );
    return _decode(response);
  }

  Future<Map<String, dynamic>> put(
    String path,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await _http.put(
      _uri(path),
      headers: _headers(token),
      body: jsonEncode(body),
    );
    return _decode(response);
  }

  Future<Map<String, dynamic>> delete(
    String path,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final request = http.Request('DELETE', _uri(path))
      ..headers.addAll(_headers(token))
      ..body = jsonEncode(body);
    final response = await http.Response.fromStream(await _http.send(request));
    return _decode(response);
  }

  Future<Map<String, dynamic>> uploadAnalysis({
    String path = '/analysis',
    required File image,
    required String type,
    required String locale,
    required String token,
    Map<String, String> fields = const {},
  }) async {
    final request = http.MultipartRequest('POST', _uri(path))
      ..headers.addAll({..._platformHeaders, ..._authHeaders(token)})
      ..fields.addAll({
        'type': type,
        'locale': locale,
        'disclaimer_acknowledged': '1',
        ...fields,
      })
      ..files.add(await http.MultipartFile.fromPath('image', image.path));

    final response = await http.Response.fromStream(await _http.send(request));
    return _decode(response);
  }

  Uri _uri(String path, {Map<String, String?> queryParameters = const {}}) {
    final uri = Uri.parse('$baseUrl$path');
    final filtered = Map<String, String>.fromEntries(
      queryParameters.entries
          .where((entry) => entry.value != null)
          .map((entry) => MapEntry(entry.key, entry.value!)),
    );
    if (filtered.isEmpty) return uri;
    return uri.replace(queryParameters: {...uri.queryParameters, ...filtered});
  }

  Map<String, String> _headers(String? token) => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Flatform': Platform.isIOS
        ? 'ios'
        : 'android',
    ..._platformHeaders,
    if (token != null) ..._authHeaders(token),
  };

  Map<String, String> get _platformHeaders => {
    'X-App-Platform': Platform.isIOS
        ? 'ios'
        : Platform.isAndroid
        ? 'android'
        : Platform.operatingSystem,
    'X-Store-Platform': Platform.isIOS
        ? 'Apple Store'
        : Platform.isAndroid
        ? 'Google Store'
        : 'Unknown Store',
  };

  Map<String, String> _authHeaders(String token) => {
    'Authorization': 'Bearer $token',
  };

  Map<String, dynamic> _decode(http.Response response) {
    final body = response.body.isEmpty
        ? <String, dynamic>{}
        : jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode >= 400) {
      throw ApiException(
        response.statusCode,
        body['message']?.toString() ?? 'Request failed',
        body,
      );
    }
    return body;
  }
}

class ApiException implements Exception {
  ApiException(this.statusCode, this.message, this.body);

  final int statusCode;
  final String message;
  final Map<String, dynamic> body;

  @override
  String toString() => message;
}
