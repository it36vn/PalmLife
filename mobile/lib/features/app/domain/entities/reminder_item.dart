enum ReminderPriority {
  normal,
  important,
  critical;

  String label(String locale) => switch (this) {
    ReminderPriority.normal => locale == 'en' ? 'Normal' : 'Bình thường',
    ReminderPriority.important => locale == 'en' ? 'Important' : 'Quan trọng',
    ReminderPriority.critical =>
      locale == 'en' ? 'Very important' : 'Rất quan trọng',
  };
}

class ReminderItem {
  const ReminderItem({
    required this.id,
    required this.title,
    required this.body,
    required this.scheduledAt,
    required this.priority,
  });

  factory ReminderItem.fromJson(Map<String, dynamic> json) => ReminderItem(
    id: json['id'] as int,
    title: json['title']?.toString() ?? '',
    body: json['body']?.toString() ?? '',
    scheduledAt:
        DateTime.tryParse(json['scheduled_at']?.toString() ?? '') ??
        DateTime.now().add(const Duration(minutes: 5)),
    priority: ReminderPriority.values.firstWhere(
      (item) => item.name == json['priority']?.toString(),
      orElse: () => ReminderPriority.normal,
    ),
  );

  final int id;
  final String title;
  final String body;
  final DateTime scheduledAt;
  final ReminderPriority priority;

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'body': body,
    'scheduled_at': scheduledAt.toIso8601String(),
    'priority': priority.name,
  };

  ReminderItem copyWith({
    String? title,
    String? body,
    DateTime? scheduledAt,
    ReminderPriority? priority,
  }) => ReminderItem(
    id: id,
    title: title ?? this.title,
    body: body ?? this.body,
    scheduledAt: scheduledAt ?? this.scheduledAt,
    priority: priority ?? this.priority,
  );
}
