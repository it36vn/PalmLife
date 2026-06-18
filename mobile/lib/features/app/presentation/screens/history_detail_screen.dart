import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../widgets/app_scaffold.dart';

class HistoryDetailScreen extends StatelessWidget {
  const HistoryDetailScreen({
    super.key,
    required this.item,
    required this.locale,
  });

  final ReadingHistoryItem item;
  final String locale;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(locale);
    final sections = (item.result['sections'] as List<dynamic>? ?? [])
        .whereType<Map>()
        .map((section) => Map<String, dynamic>.from(section))
        .toList();
    final title = item.result['title']?.toString().trim();
    final summary = item.result['summary']?.toString().trim();
    final safetyNotice = item.result['safety_notice']?.toString().trim();

    return AppScaffold(
      title: s.historyDetail,
      children: [
        InfoPanel(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title?.isNotEmpty == true ? title! : item.type,
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 6),
              Text(
                _formatDateTime(item.createdAt.toLocal()),
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
              if (summary?.isNotEmpty == true) ...[
                const SizedBox(height: 14),
                Text(summary!),
              ],
            ],
          ),
        ),
        const SizedBox(height: 12),
        for (final section in sections)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: InfoPanel(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    section['heading']?.toString() ?? '',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(section['body']?.toString() ?? ''),
                ],
              ),
            ),
          ),
        if (safetyNotice?.isNotEmpty == true)
          InfoPanel(
            child: Text(
              safetyNotice!,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
          ),
      ],
    );
  }

  String _formatDateTime(DateTime value) {
    final day = value.day.toString().padLeft(2, '0');
    final month = value.month.toString().padLeft(2, '0');
    final hour = value.hour.toString().padLeft(2, '0');
    final minute = value.minute.toString().padLeft(2, '0');
    return '$day/$month/${value.year} $hour:$minute';
  }
}
