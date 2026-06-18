import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_timezone/flutter_timezone.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest.dart' as tzdata;
import 'package:timezone/timezone.dart' as tz;
import 'package:url_launcher/url_launcher.dart';

import '../domain/app_models.dart';

class DeviceNotificationService {
  DeviceNotificationService({SharedPreferences? prefs}) : _prefs = prefs;

  final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();
  static const _cameraPickTriedKey = 'camera_pick_permission_tried';
  static const _galleryPickTriedKey = 'gallery_pick_permission_tried';
  static const _mediaSettingsOpenKey = 'media_permission_settings_open';

  final ValueNotifier<int?> selectedReminderId = ValueNotifier<int?>(null);
  final SharedPreferences? _prefs;
  bool _initialized = false;

  Future<void> initialize() async {
    if (_initialized) return;
    tzdata.initializeTimeZones();
    final timeZone = await FlutterTimezone.getLocalTimezone();
    tz.setLocalLocation(tz.getLocation(timeZone.identifier));

    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings();
    final details = await _plugin.getNotificationAppLaunchDetails();

    await _plugin.initialize(
      settings: const InitializationSettings(android: android, iOS: ios),
      onDidReceiveNotificationResponse: (response) {
        final id = int.tryParse(response.payload ?? '');
        if (id != null) selectedReminderId.value = id;
      },
    );

    final launchPayload =
        details?.notificationResponse?.payload ??
        details?.notificationResponse?.input;
    final launchId = int.tryParse(launchPayload ?? '');
    if (launchId != null) selectedReminderId.value = launchId;

    _initialized = true;
  }

  Future<PermissionStatus> notificationStatus() =>
      Permission.notification.status;

  Future<PermissionStatus> requestNotifications() =>
      Permission.notification.request();

  Future<PermissionStatus> cameraStatus() => Permission.camera.status;

  Future<PermissionStatus> requestCamera() => Permission.camera.request();

  Future<PermissionStatus> galleryStatus() async {

    if (!isMobile) {
    return Future.value(PermissionStatus.granted);
  }

  if (Platform.isIOS) {
    final result = await Permission.photos.request();
    return result;
  }

  if (Platform.isAndroid) {
    final result = await Permission.photos.request();
    return result;
  }

  return Future.value(PermissionStatus.granted);
  }

  bool get hasTriedCameraPick => _prefs?.getBool(_cameraPickTriedKey) ?? false;

  bool get hasTriedGalleryPick =>
      _prefs?.getBool(_galleryPickTriedKey) ?? false;

  Future<void> markCameraPickTried() async {
    await _prefs?.setBool(_cameraPickTriedKey, true);
  }

  Future<void> markGalleryPickTried() async {
    await _prefs?.setBool(_galleryPickTriedKey, true);
  }

  Future<void> openSettings() async {
    await openAppSettings();
  }

  Future<void> openMediaPermissionSettings() async {
    await _prefs?.setBool(_mediaSettingsOpenKey, true);
    await openAppSettings();
  }

  Future<bool> consumeMediaSettingsReturn() async {
    final wasOpen = _prefs?.getBool(_mediaSettingsOpenKey) ?? false;
    if (wasOpen) await _prefs?.remove(_mediaSettingsOpenKey);
    return wasOpen;
  }

  Future<void> scheduleReminder(ReminderItem reminder) async {
    await initialize();
    final when = tz.TZDateTime.from(reminder.scheduledAt, tz.local);
    final androidDetails = AndroidNotificationDetails(
      'xem_chi_tay_reminders',
      'Nhắc nhở',
      channelDescription: 'Nhắc nhở cá nhân trong ứng dụng Xem Chỉ Tay',
      importance: reminder.priority == ReminderPriority.critical
          ? Importance.max
          : Importance.defaultImportance,
      priority: reminder.priority == ReminderPriority.critical
          ? Priority.max
          : Priority.defaultPriority,
    );
    await _plugin.zonedSchedule(
      id: reminder.id,
      title: reminder.title,
      body: reminder.body.isEmpty
          ? reminder.priority.label('vi')
          : reminder.body,
      scheduledDate: when,
      notificationDetails: NotificationDetails(
        android: androidDetails,
        iOS: const DarwinNotificationDetails(),
      ),
      androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
      payload: reminder.id.toString(),
    );
  }

  Future<void> showAppNotification(
    AppNotification notification,
    String locale,
  ) async {
    await initialize();
    final title = locale == 'en' ? notification.titleEn : notification.titleVi;
    final body = locale == 'en' ? notification.bodyEn : notification.bodyVi;
    const androidDetails = AndroidNotificationDetails(
      'xem_chi_tay_updates',
      'Thông báo',
      channelDescription: 'Thông báo mới từ ứng dụng Xem Chỉ Tay',
      importance: Importance.high,
      priority: Priority.high,
    );

    await _plugin.show(
      id: 100000 + notification.id,
      title: title,
      body: body,
      notificationDetails: const NotificationDetails(
        android: androidDetails,
        iOS: DarwinNotificationDetails(),
      ),
      payload: 'notification:${notification.id}',
    );
  }

  Future<void> cancelReminder(int id) => _plugin.cancel(id: id);

  Future<void> email(String email) async {
    final uri = Uri(scheme: 'mailto', path: email);
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> call(String phone) async {
    final uri = Uri(scheme: 'tel', path: phone);
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  bool get isMobile => !kIsWeb && (Platform.isAndroid || Platform.isIOS);
}
