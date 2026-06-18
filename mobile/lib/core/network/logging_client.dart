import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class LoggingClient extends http.BaseClient {
  LoggingClient(this._inner);

  final http.Client _inner;

  @override
  Future<http.StreamedResponse> send(http.BaseRequest request) async {
    debugPrint('▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼ START ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼');
    debugPrint('================ REQUEST ================');
    debugPrint('${request.method} ${request.url}');
    debugPrint('Headers: ${request.headers}');

    if (request is http.Request) {
      debugPrint('Body: ${request.body}');
      _printCurl(request);
    }

    try {
      final response = await _inner.send(request);

      final bodyString = await response.stream.bytesToString();

      debugPrint('================ RESPONSE ================');
      debugPrint('${request.method} ${request.url}');
      debugPrint('Status: ${response.statusCode}');
      debugPrint('Data: $bodyString');
      debugPrint('▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ END ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲');
      debugPrint('------------------------------------------');

      return http.StreamedResponse(
        Stream.value(utf8.encode(bodyString)),
        response.statusCode,
        headers: response.headers,
        request: response.request,
        reasonPhrase: response.reasonPhrase,
      );
    } catch (e, st) {
      debugPrint('================ ERROR ================');
      debugPrint(e.toString());
      debugPrint(st.toString());
      rethrow;
    }
  }

  void _printCurl(http.Request request) {
    final headers = request.headers.entries
        .map((e) => "-H '${e.key}: ${e.value}'")
        .join(' ');

    final body = request.body.isNotEmpty
        ? "--data '${request.body}'"
        : "";

    debugPrint("CURL:");
    debugPrint("curl -X ${request.method} $headers $body '${request.url}'");
  }
}