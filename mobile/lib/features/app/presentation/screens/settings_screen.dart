import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../../../core/i18n/app_strings.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';
import '../widgets/settings_action_tile.dart';
import 'account_screen.dart';
import 'change_password_screen.dart';
import 'reminder_screens.dart';
import 'subscription_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key, required this.controller});

  final AppController controller;

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen>
    with WidgetsBindingObserver {
  PermissionStatus _notificationStatus = PermissionStatus.denied;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _refresh();
  }

  Future<void> _refresh() async {
    _notificationStatus = await widget.controller.notificationsDevice
        .notificationStatus();
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);
    final user = widget.controller.user;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        InfoPanel(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
          child: Column(
            children: [
              CircleAvatar(
                radius: 38,
                backgroundColor: Theme.of(context).colorScheme.primary,
                foregroundColor: Theme.of(context).colorScheme.onPrimary,
                child: Text(
                  (user?.name ?? 'U').characters.first.toUpperCase(),
                  style: const TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              const SizedBox(height: 10),
              Text(
                user?.name ?? '',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              SettingsActionTile(
                icon: Icons.person_outline,
                title: Text(s.updateProfile),
                trailing: const Icon(Icons.chevron_right),
                onTap: () =>
                    Navigator.of(context).pushNamed(AccountScreen.routeName),
              ),
              SettingsActionTile(
                icon: Icons.lock_reset,
                title: Text(s.changePassword),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(
                  context,
                ).pushNamed(ChangePasswordScreen.routeName),
              ),
              if (!widget.controller.hasLifetimePlan)
                SettingsActionTile(
                  icon: Icons.workspace_premium_outlined,
                  title: Text(s.upgradeLifetime),
                  subtitle: Text(s.upgradeLifetimeHint),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: widget.controller.isBusy
                      ? null
                      : () async {
                          await widget.controller.refreshPlans();
                          if (!context.mounted) return;
                          Navigator.of(
                            context,
                          ).pushNamed(SubscriptionScreen.routeName);
                        },
                ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        InfoPanel(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
          child: Column(
            children: [
              SettingsActionTile(
                icon: Icons.notifications_outlined,
                title: Text(s.notificationSettings),
                trailing: Switch(
                  value: _notificationStatus.isGranted,
                  onChanged: (_) async {
                    await widget.controller.notificationsDevice.openSettings();
                    await _refresh();
                  },
                ),
                onTap: () async {
                  await widget.controller.notificationsDevice.openSettings();
                  await _refresh();
                },
              ),
              SettingsActionTile(
                icon: Icons.language,
                title: Text(s.language),
                trailing: DropdownButton<String>(
                  value: widget.controller.locale,
                  borderRadius: BorderRadius.circular(8),
                  underline: const SizedBox.shrink(),
                  items: [
                    DropdownMenuItem(value: 'vi', child: Text(s.vietnamese)),
                    DropdownMenuItem(value: 'en', child: Text(s.english)),
                  ],
                  onChanged: (value) {
                    if (value != null) widget.controller.setLocale(value);
                  },
                ),
              ),
              SettingsActionTile(
                icon: Icons.privacy_tip_outlined,
                title: Text(s.privacyPolicy),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => showDialog<void>(
                  context: context,
                  builder: (context) => AlertDialog(
                    title: Text(s.privacyPolicy),
                    content: SingleChildScrollView(child: Text(s.privacyText)),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(),
                        child: Text(s.close),
                      ),
                    ],
                  ),
                ),
              ),
              SettingsActionTile(
                icon: Icons.alarm_outlined,
                title: Text(s.reminders),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (_) =>
                        ReminderListScreen(controller: widget.controller),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        InfoPanel(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
          child: SettingsActionTile(
            icon: Icons.logout,
            title: Text(
              s.logout,
              style: TextStyle(color: Theme.of(context).colorScheme.error),
            ),
            trailing: Icon(
              Icons.chevron_right,
              color: Theme.of(context).colorScheme.error,
            ),
            onTap: widget.controller.isBusy
                ? null
                : () => widget.controller.logout(),
          ),
        ),
      ],
    );
  }
}
