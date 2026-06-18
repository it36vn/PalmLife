class AppNotification {
  const AppNotification({
    required this.id,
    required this.titleVi,
    required this.titleEn,
    required this.bodyVi,
    required this.bodyEn,
    required this.createdAt,
    required this.readAt,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) =>
      AppNotification(
        id: json['id'] as int,
        titleVi: json['title_vi']?.toString() ?? '',
        titleEn: json['title_en']?.toString() ?? '',
        bodyVi: json['body_vi']?.toString() ?? '',
        bodyEn: json['body_en']?.toString() ?? '',
        createdAt:
            DateTime.tryParse(json['created_at']?.toString() ?? '') ??
            DateTime.now(),
        readAt: DateTime.tryParse(json['read_at']?.toString() ?? ''),
      );

  final int id;
  final String titleVi;
  final String titleEn;
  final String bodyVi;
  final String bodyEn;
  final DateTime createdAt;
  final DateTime? readAt;

  bool get isRead => readAt != null;
}
