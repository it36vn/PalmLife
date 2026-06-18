import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key, required this.controller});

  final AppController controller;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(controller.locale);
    return RefreshIndicator(
      onRefresh: controller.refreshNotifications,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          if (controller.notifications.isEmpty) Text(s.emptyNotifications),
          if (controller.notifications.isNotEmpty &&
              controller.unreadNotificationsCount > 0)
            Align(
              alignment: Alignment.centerRight,
              child: TextButton.icon(
                onPressed: controller.isBusy
                    ? null
                    : controller.markAllNotificationsRead,
                icon: const Icon(Icons.done_all),
                label: Text(s.markAllRead),
              ),
            ),
          for (final item in controller.notifications)
            Padding(
              padding: const EdgeInsets.only(bottom: 12.0),
              child: InfoPanel(
                // padding: EdgeInsets.zero,
                color: item.isRead
                        ? Theme.of(context).colorScheme.surfaceContainer
                        : Theme.of(context).colorScheme.primaryContainer,
                child: InkWell(
                  
                  borderRadius: BorderRadius.circular(8),
                  onTap: item.isRead || controller.isBusy
                      ? null
                      : () => controller.markNotificationRead(item.id),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Stack(
                        clipBehavior: Clip.none,
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: Image.asset(
                              'android/app/src/main/res/mipmap-xxxhdpi/ic_launcher.png',
                              width: 48,
                              height: 48,
                              fit: BoxFit.cover,
                            ),
                          ),
                          if (!item.isRead)
                            Positioned(
                              right: -2,
                              top: -2,
                              child: Container(
                                width: 12,
                                height: 12,
                                decoration: BoxDecoration(
                                  color: Colors.greenAccent.shade700,
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: Theme.of(context).colorScheme.surface,
                                    width: 2,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              controller.locale == 'en'
                                  ? item.titleEn
                                  : item.titleVi,
                              style: Theme.of(context).textTheme.titleMedium
                                  ?.copyWith(fontWeight: FontWeight.w800),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              controller.locale == 'en'
                                  ? item.bodyEn
                                  : item.bodyVi,
                            ),
                            const SizedBox(height: 6),
                            Text(
                              _relativeTime(item, s),
                              style: Theme.of(context).textTheme.bodySmall
                                  ?.copyWith(
                                    color: Theme.of(
                                      context,
                                    ).colorScheme.onSurfaceVariant,
                                  ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          if (controller.notificationsHasMore)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Center(
                child: OutlinedButton(
                  onPressed: controller.isBusy
                      ? null
                      : controller.loadMoreNotifications,
                  child: Text(s.loadMore),
                ),
              ),
            ),
        ],
      ),
    );
  }

  String _relativeTime(AppNotification item, AppStrings s) {
    final createdAt = item.createdAt.toLocal();
    final now = DateTime.now();
    final diff = now.difference(createdAt);

    if (diff.inMinutes < 1) return s.nowText;
    if (diff.inHours < 1) return s.minutesAgo(diff.inMinutes);
    if (diff.inHours < 24) return s.hoursAgo(diff.inHours);
    if (diff.inDays < 2) return s.yesterday;
    if (diff.inDays < 30) return s.daysAgo(diff.inDays);
    if (diff.inDays < 365) {
      final months = (diff.inDays / 30).floor().clamp(1, 11);
      return s.monthsAgo(months);
    }

    final day = createdAt.day.toString().padLeft(2, '0');
    final month = createdAt.month.toString().padLeft(2, '0');
    return '$day/$month/${createdAt.year}';
  }
}
