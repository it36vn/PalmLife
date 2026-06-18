import 'dart:io';
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../app_controller.dart';
import 'history_screen.dart';
import 'notification_screen.dart';
import 'palm_screen.dart';
import 'reminder_screens.dart';
import 'settings_screen.dart';
import 'subscription_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key, required this.controller});

  final AppController controller;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with WidgetsBindingObserver {
  int _tab = 1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    widget.controller.notificationsDevice.selectedReminderId.addListener(
      _openSelectedReminder,
    );
    WidgetsBinding.instance.addPostFrameCallback((_) {
      unawaited(
        widget.controller.notificationsDevice.consumeMediaSettingsReturn(),
      );
      widget.controller.refreshHistory();
      widget.controller.refreshNotifications();
      widget.controller.loadReminders();
      widget.controller.startRealtimeSync();
      _openSelectedReminder();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    widget.controller.notificationsDevice.selectedReminderId.removeListener(
      _openSelectedReminder,
    );
    widget.controller.stopRealtimeSync();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      unawaited(_handleResumed());
    }
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.detached) {
      widget.controller.stopRealtimeSync();
    }
  }

  Future<void> _handleResumed() async {
    final returningFromSettings = await widget.controller.notificationsDevice
        .consumeMediaSettingsReturn();
    if (returningFromSettings) {
      if (mounted) setState(() {});
      return;
    }

    widget.controller.startRealtimeSync();
    await widget.controller.syncRealtimeNow(showLocalAlerts: false);
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);
    final pages = [
      HistoryScreen(controller: widget.controller),
      PalmScreen(controller: widget.controller, onPick: _pick),
      NotificationsScreen(controller: widget.controller),
      SettingsScreen(controller: widget.controller),
    ];
    final titles = [s.history, s.palmReading, s.notifications, s.settings];

    return Scaffold(
      appBar: AppBar(title: Text(titles[_tab])),
      body: SafeArea(child: pages[_tab]),
      bottomNavigationBar: Theme(
        data: Theme.of(context).copyWith(
          splashColor: Colors.transparent,
          highlightColor: Colors.transparent,
          splashFactory: NoSplash.splashFactory,
          navigationBarTheme: const NavigationBarThemeData(
            indicatorColor: Colors.transparent,
          ),
        ),
        child: NavigationBar(
          animationDuration: Duration.zero,
          selectedIndex: _tab,
          onDestinationSelected: (value) => setState(() => _tab = value),
          destinations: [
            NavigationDestination(
              icon: const Icon(Icons.history),
              label: s.history,
            ),
            NavigationDestination(
              icon: const Icon(Icons.back_hand_outlined),
              label: s.palmReading,
            ),
            NavigationDestination(
              icon: widget.controller.unreadNotificationsCount == 0
                  ? const Icon(Icons.notifications_outlined)
                  : Badge.count(
                      count: widget.controller.unreadNotificationsCount,
                      child: const Icon(Icons.notifications_outlined),
                    ),
              label: s.notifications,
            ),
            NavigationDestination(
              icon: const Icon(Icons.settings_outlined),
              label: s.settings,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _pick(ImageSource source, ReadingProfileInput profile) async {
    await widget.controller.refreshQuotaIfResetElapsed();
    if (!mounted) return;

    if (_quotaExhausted) {
      await _showQuotaExhaustedDialog();
      return;
    }

    if (await _shouldOpenPermissionSettings(source)) {
      if (!mounted) return;
      await _showPermissionSettingsDialog(source);
      return;
    }

    await _markPickTried(source);

    XFile? picked;
    try {
      picked = await ImagePicker().pickImage(
        source: source,
        imageQuality: 82,
        maxWidth: 1600,
      );
    } on PlatformException {
      return;
    }

    if (picked == null) return;
    await _analyzePickedImage(picked, profile);
  }

  Future<bool> _shouldOpenPermissionSettings(ImageSource source) async {
    final status = await _permissionStatus(source);
    if (_permissionAllowed(status)) return false;
    if (status.isPermanentlyDenied || status.isRestricted) return true;

    return source == ImageSource.camera
        ? widget.controller.notificationsDevice.hasTriedCameraPick
        : widget.controller.notificationsDevice.hasTriedGalleryPick;
  }

  Future<PermissionStatus> _permissionStatus(ImageSource source) async {
    return source == ImageSource.camera
        ? widget.controller.notificationsDevice.cameraStatus()
        : await widget.controller.notificationsDevice.galleryStatus();
  }

  bool _permissionAllowed(PermissionStatus status) =>
      status.isGranted || status.isLimited;

  bool get _quotaExhausted {
    final quota = widget.controller.quota;
    return quota?.isExhausted ?? false;
  }

  Future<void> _markPickTried(ImageSource source) {
    return source == ImageSource.camera
        ? widget.controller.notificationsDevice.markCameraPickTried()
        : widget.controller.notificationsDevice.markGalleryPickTried();
  }

  Future<void> _showPermissionSettingsDialog(ImageSource source) async {
    final s = AppStrings.of(widget.controller.locale);
    final open = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(s.mediaPermissionTitle(source == ImageSource.camera)),
        content: Text(s.mediaPermissionMessage(source == ImageSource.camera)),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(s.close),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: Text(s.openSettings),
          ),
        ],
      ),
    );
    if (open == true) {
      await widget.controller.notificationsDevice.openMediaPermissionSettings();
    }
  }

  Future<void> _analyzePickedImage(
    XFile picked,
    ReadingProfileInput profile,
  ) async {
    final quotaError = await widget.controller.analyze(
      File(picked.path),
      'palm',
      profile,
    );
    if (!mounted || !quotaError) return;
    await _showQuotaExhaustedDialog();
  }

  Future<void> _showQuotaExhaustedDialog() async {
    if (!mounted) return;
    final s = AppStrings.of(widget.controller.locale);
    final upgrade = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(s.subscription),
        content: Text(s.quotaExhausted),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text(s.maybeLater),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: Text(s.upgradeNow),
          ),
        ],
      ),
    );
    if (upgrade == true && mounted) {
      Navigator.of(context).pushNamed(SubscriptionScreen.routeName);
    }
  }

  void _openSelectedReminder() {
    final id = widget.controller.notificationsDevice.selectedReminderId.value;
    if (id == null || !mounted) return;
    ReminderItem? reminder;
    for (final item in widget.controller.reminders) {
      if (item.id == id) {
        reminder = item;
        break;
      }
    }
    final selected = reminder;
    if (selected == null) return;
    widget.controller.notificationsDevice.selectedReminderId.value = null;
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => ReminderDetailScreen(
          controller: widget.controller,
          reminder: selected,
        ),
      ),
    );
  }
}
