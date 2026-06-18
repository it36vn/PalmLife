import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/i18n/app_strings.dart';

void main() {
  test('Vietnamese is the default product language', () {
    final strings = AppStrings.of('vi');

    expect(strings.appName, 'Xem Chỉ Tay');
    expect(strings.login, 'Đăng nhập');
  });
}
