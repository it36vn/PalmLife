import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/app/domain/entities/quota_status.dart';

void main() {
  test('only unlimited quota avoids exhaustion', () {
    final lifetimeTrial = QuotaStatus.fromJson({
      'period': 'lifetime',
      'remaining': 0,
      'limit': 5,
      'used': 5,
    });
    final unlimited = QuotaStatus.fromJson({
      'period': 'unlimited',
      'remaining': null,
      'limit': null,
      'used': 0,
    });

    expect(lifetimeTrial.isExhausted, isTrue);
    expect(unlimited.isExhausted, isFalse);
  });

  test('exhausted weekly or monthly quota refreshes after reset time', () {
    final quota = QuotaStatus.fromJson({
      'period': 'week',
      'remaining': 0,
      'limit': 5,
      'used': 5,
      'resets_at': '2026-06-22T00:00:00+00:00',
    });

    expect(
      quota.shouldRefreshBeforeBlocking(
        DateTime.parse('2026-06-21T23:59:59+00:00'),
      ),
      isFalse,
    );
    expect(
      quota.shouldRefreshBeforeBlocking(
        DateTime.parse('2026-06-22T00:00:00+00:00'),
      ),
      isTrue,
    );
  });
}
