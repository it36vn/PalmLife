import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';
import 'history_detail_screen.dart';

class HistoryScreen extends StatelessWidget {
  const HistoryScreen({super.key, required this.controller});

  final AppController controller;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(controller.locale);
    return RefreshIndicator(
      onRefresh: controller.refreshHistory,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (controller.history.isEmpty) Text(s.emptyHistory),
          for (final item in controller.history)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: InkWell(
                borderRadius: BorderRadius.circular(8),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (_) => HistoryDetailScreen(
                      item: item,
                      locale: controller.locale,
                    ),
                  ),
                ),
                child: InfoPanel(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              item.result['title']?.toString() ?? item.type,
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                          ),
                          IconButton(
                            tooltip: s.deleteHistory,
                            visualDensity: VisualDensity.compact,
                            onPressed: controller.isBusy
                                ? null
                                : () => _confirmDelete(context, item.id, s),
                            icon: const Icon(Icons.delete_outline),
                          ),
                        ],
                      ),
                      Text(item.createdAt.toLocal().toString().substring(0, 16)),
                      const SizedBox(height: 8),
                      Text(item.result['summary']?.toString() ?? ''),
                    ],
                  ),
                ),
              ),
            ),
          if (controller.historyHasMore)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Center(
                child: OutlinedButton(
                  onPressed: controller.isBusy
                      ? null
                      : controller.loadMoreHistory,
                  child: Text(s.loadMore),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Future<void> _confirmDelete(
    BuildContext context,
    int id,
    AppStrings s,
  ) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(s.deleteHistoryTitle),
        content: Text(s.deleteHistoryMessage),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(false),
            child: Text(s.cancel),
          ),
          FilledButton(
            onPressed: () => Navigator.of(dialogContext).pop(true),
            child: Text(s.confirmDelete),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      await controller.deleteHistoryItem(id);
    }
  }
}
