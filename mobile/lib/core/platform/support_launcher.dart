import 'package:flutter/services.dart';

class SupportLauncher {
  const SupportLauncher();

  static const _channel = MethodChannel('com.it36vn.xemchitay/support');

  Future<void> email(String email) =>
      _channel.invokeMethod<void>('email', {'email': email});

  Future<void> call(String phone) =>
      _channel.invokeMethod<void>('call', {'phone': phone});
}
