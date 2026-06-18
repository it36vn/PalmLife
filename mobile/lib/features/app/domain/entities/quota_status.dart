class QuotaStatus {
  const QuotaStatus({
    required this.used,
    required this.limit,
    required this.remaining,
    required this.period,
    required this.resetsAt,
  });

  factory QuotaStatus.fromJson(Map<String, dynamic>? json) => QuotaStatus(
    used: json?['used'] as int? ?? 0,
    limit: json?['limit'] as int?,
    remaining: json?['remaining'] as int?,
    period: json?['period']?.toString() ?? 'lifetime',
    resetsAt: DateTime.tryParse(json?['resets_at']?.toString() ?? ''),
  );

  final int used;
  final int? limit;
  final int? remaining;
  final String period;
  final DateTime? resetsAt;

  bool get isUnlimited => period == 'unlimited';

  bool get isExhausted => !isUnlimited && remaining != null && remaining! <= 0;

  bool shouldRefreshBeforeBlocking(DateTime now) {
    final resetTime = resetsAt;
    if (!isExhausted || resetTime == null) return false;
    return !now.isBefore(resetTime);
  }
}
