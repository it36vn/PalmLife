class QuotaStatus {
  const QuotaStatus({
    required this.used,
    required this.limit,
    required this.remaining,
    required this.period,
  });

  factory QuotaStatus.fromJson(Map<String, dynamic>? json) => QuotaStatus(
    used: json?['used'] as int? ?? 0,
    limit: json?['limit'] as int?,
    remaining: json?['remaining'] as int?,
    period: json?['period']?.toString() ?? 'lifetime',
  );

  final int used;
  final int? limit;
  final int? remaining;
  final String period;
}
