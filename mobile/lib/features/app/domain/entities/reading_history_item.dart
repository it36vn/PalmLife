class ReadingHistoryItem {
  const ReadingHistoryItem({
    required this.id,
    required this.type,
    required this.locale,
    required this.createdAt,
    required this.result,
  });

  factory ReadingHistoryItem.fromJson(Map<String, dynamic> json) =>
      ReadingHistoryItem(
        id: json['id'] as int,
        type: json['type']?.toString() ?? '',
        locale: json['locale']?.toString() ?? 'vi',
        createdAt:
            DateTime.tryParse(json['created_at']?.toString() ?? '') ??
            DateTime.now(),
        result: Map<String, dynamic>.from(json['result'] as Map? ?? {}),
      );

  final int id;
  final String type;
  final String locale;
  final DateTime createdAt;
  final Map<String, dynamic> result;
}
