import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:in_app_purchase/in_app_purchase.dart';

import '../../../core/network/api_client.dart';
import '../data/app_repository.dart';
import '../data/device_notification_service.dart';
import '../data/store_purchase_service.dart';
import '../domain/app_models.dart';

class AppController extends ChangeNotifier {
  AppController({
    required AppRepository repository,
    DeviceNotificationService? notificationService,
    StorePurchaseService? storePurchaseService,
  }) : _repository = repository,
       notificationsDevice = notificationService ?? DeviceNotificationService(),
       _store = storePurchaseService ?? StorePurchaseService() {
    _purchaseSubscription = _store.purchaseStream.listen(_handlePurchases);
  }

  final AppRepository _repository;
  final DeviceNotificationService notificationsDevice;
  final StorePurchaseService _store;
  late final StreamSubscription<List<PurchaseDetails>> _purchaseSubscription;
  Timer? _realtimeTimer;
  bool _syncingRealtime = false;
  final Set<int> _knownNotificationIds = <int>{};

  String locale = 'vi';
  UserProfile? user;
  QuotaStatus? quota;
  List<SubscriptionPlan> plans = const [];
  Map<String, StoreProduct> storeProducts = const {};
  List<AppNotification> notifications = const [];
  AnalysisResult? lastResult;
  List<ReadingHistoryItem> history = const [];
  List<ReminderItem> reminders = const [];
  int historyPage = 1;
  bool historyHasMore = false;
  int notificationsPage = 1;
  bool notificationsHasMore = false;
  String? error;
  String? message;
  LockedAccount? lockedAccount;
  bool isBusy = false;

  bool get isAuthenticated => user != null && _repository.token != null;
  bool get hasLifetimePlan => quota?.period == 'unlimited';
  int get unreadNotificationsCount =>
      notifications.where((item) => !item.isRead).length;

  Future<void> restore() async {
    await _guard(() async {
      final payload = await _repository.me();
      user = payload?.user;
      quota = payload?.quota;
      notifications = payload?.notifications ?? const [];
      if (payload != null) {
        final notificationPage = await _repository.notifications();
        notifications = notificationPage.items;
        notificationsPage = notificationPage.currentPage;
        notificationsHasMore = notificationPage.hasMorePages;
        _rememberNotifications(notifications);
      }
      reminders = await _repository.reminders();
      plans = await _repository.plans();
      await _syncStoreProducts();
    }, swallowAuth: true);
  }

  void toggleLocale() {
    locale = locale == 'vi' ? 'en' : 'vi';
    notifyListeners();
  }

  void setLocale(String value) {
    locale = value == 'en' ? 'en' : 'vi';
    notifyListeners();
  }

  Future<void> register(String name, String email, String password) async {
    await _guard(() async {
      final payload = await _repository.register(
        name: name,
        email: email,
        password: password,
        locale: locale,
      );
      user = payload.user;
      quota = payload.quota;
      final historyPageResult = await _repository.history();
      history = historyPageResult.items;
      historyPage = historyPageResult.currentPage;
      historyHasMore = historyPageResult.hasMorePages;
      final notificationPage = await _repository.notifications();
      notifications = notificationPage.items;
      notificationsPage = notificationPage.currentPage;
      notificationsHasMore = notificationPage.hasMorePages;
      _rememberNotifications(notifications);
      reminders = await _repository.reminders();
      plans = await _repository.plans();
      await _syncStoreProducts();
    });
  }

  Future<bool> login(String email, String password) async {
    var ok = false;
    await _guard(
      () async {
        final payload = await _repository.login(
          email: email,
          password: password,
        );
        user = payload.user;
        quota = payload.quota;
        final historyPageResult = await _repository.history();
        history = historyPageResult.items;
        historyPage = historyPageResult.currentPage;
        historyHasMore = historyPageResult.hasMorePages;
        final notificationPage = await _repository.notifications();
        notifications = notificationPage.items;
        notificationsPage = notificationPage.currentPage;
        notificationsHasMore = notificationPage.hasMorePages;
        _rememberNotifications(notifications);
        reminders = await _repository.reminders();
        plans = await _repository.plans();
        await _syncStoreProducts();
        ok = true;
      },
      onApiException: (exception) {
        if (exception.body['code'] == 'account_locked') {
          lockedAccount = LockedAccount.fromJson(exception.body);
        }
      },
    );
    return ok;
  }

  Future<bool> forgotPassword(String email) async {
    var ok = false;
    await _guard(() async {
      message = await _repository.forgotPassword(email: email, locale: locale);
      ok = true;
    });
    return ok;
  }

  Future<bool> changePassword(String currentPassword, String password) async {
    var ok = false;
    await _guard(() async {
      await _repository.changePassword(
        currentPassword: currentPassword,
        password: password,
      );
      ok = true;
    });
    return ok;
  }

  Future<void> refreshPlans() async {
    await _guard(() async {
      plans = await _repository.plans();
      await _syncStoreProducts();
    });
  }

  Future<void> refreshQuota() async {
    await _guard(() async {
      final payload = await _repository.me();
      user = payload?.user ?? user;
      quota = payload?.quota ?? quota;
      notifications = payload?.notifications ?? notifications;
      _rememberNotifications(notifications);
    });
  }

  Future<void> refreshQuotaIfResetElapsed() async {
    final currentQuota = quota;
    if (currentQuota == null ||
        !currentQuota.shouldRefreshBeforeBlocking(DateTime.now())) {
      return;
    }

    await refreshQuota();
  }

  Future<void> buyPlan(SubscriptionPlan plan) async {
    if (plan.isFree) return;
    await _guard(() async {
      await _store.buy(plan);
    });
  }

  Future<void> restorePurchases() async {
    await _guard(_store.restore);
  }

  Future<bool> analyze(
    File image,
    String type,
    ReadingProfileInput profile,
  ) async {
    var quotaError = false;
    await _guard(
      () async {
        lastResult = await _repository.analyze(image, locale, type, profile);
        final historyPageResult = await _repository.history();
        history = historyPageResult.items;
        historyPage = historyPageResult.currentPage;
        historyHasMore = historyPageResult.hasMorePages;
        final payload = await _repository.me();
        quota = payload?.quota ?? quota;
        notifications = payload?.notifications ?? notifications;
        _rememberNotifications(notifications);
      },
      onApiException: (exception) {
        quotaError =
            exception.statusCode == 402 ||
            exception.body['code'] == 'quota_exhausted';
      },
    );
    return quotaError;
  }

  Future<void> updateAccount(
    String name,
    String email,
    DateTime? birthDate,
    String? gender,
  ) async {
    await _guard(
      () async => user = await _repository.updateAccount(
        name: name,
        email: email,
        birthDate: birthDate,
        gender: gender,
      ),
    );
  }

  Future<bool> deleteAccount(String password) async {
    var ok = false;
    await _guard(() async {
      await _repository.deleteAccount(password: password, locale: locale);
      user = null;
      quota = null;
      lastResult = null;
      notifications = const [];
      notificationsPage = 1;
      notificationsHasMore = false;
      _knownNotificationIds.clear();
      stopRealtimeSync();
      storeProducts = const {};
      ok = true;
    });
    return ok;
  }

  Future<void> logout() async {
    await _guard(() async {
      await _repository.logout();
      user = null;
      quota = null;
      lastResult = null;
      notifications = const [];
      history = const [];
      reminders = const [];
      historyPage = 1;
      historyHasMore = false;
      notificationsPage = 1;
      notificationsHasMore = false;
      _knownNotificationIds.clear();
      stopRealtimeSync();
      storeProducts = const {};
    });
  }

  Future<void> refreshHistory() async {
    await _guard(() async {
      final page = await _repository.history();
      history = page.items;
      historyPage = page.currentPage;
      historyHasMore = page.hasMorePages;
    });
  }

  Future<void> loadMoreHistory() async {
    if (!historyHasMore || isBusy) return;
    await _guard(() async {
      final page = await _repository.history(page: historyPage + 1);
      history = [...history, ...page.items];
      historyPage = page.currentPage;
      historyHasMore = page.hasMorePages;
    });
  }

  Future<void> deleteHistoryItem(int id) async {
    await _guard(() async {
      await _repository.deleteHistoryItem(id);
      history = history.where((item) => item.id != id).toList();
      if (history.isEmpty && historyHasMore) {
        final page = await _repository.history();
        history = page.items;
        historyPage = page.currentPage;
        historyHasMore = page.hasMorePages;
      }
    });
  }

  Future<void> refreshNotifications() async {
    await _guard(() async {
      final page = await _repository.notifications();
      notifications = page.items;
      notificationsPage = page.currentPage;
      notificationsHasMore = page.hasMorePages;
      _rememberNotifications(notifications);
    });
  }

  Future<void> loadMoreNotifications() async {
    if (!notificationsHasMore || isBusy) return;
    await _guard(() async {
      final page = await _repository.notifications(page: notificationsPage + 1);
      notifications = [...notifications, ...page.items];
      notificationsPage = page.currentPage;
      notificationsHasMore = page.hasMorePages;
      _rememberNotifications(notifications);
    });
  }

  Future<void> markNotificationRead(int id) async {
    await _guard(() async {
      await _repository.markNotificationRead(id);
      final page = await _repository.notifications();
      notifications = page.items;
      notificationsPage = page.currentPage;
      notificationsHasMore = page.hasMorePages;
      _rememberNotifications(notifications);
    });
  }

  Future<void> markAllNotificationsRead() async {
    await _guard(() async {
      await _repository.markAllNotificationsRead();
      final page = await _repository.notifications();
      notifications = page.items;
      notificationsPage = page.currentPage;
      notificationsHasMore = page.hasMorePages;
      _rememberNotifications(notifications);
    });
  }

  void startRealtimeSync() {
    if (_realtimeTimer?.isActive ?? false) return;
    _rememberNotifications(notifications);
    unawaited(syncRealtimeNow(showLocalAlerts: false));
    _realtimeTimer = Timer.periodic(
      const Duration(seconds: 12),
      (_) => unawaited(syncRealtimeNow()),
    );
  }

  void stopRealtimeSync() {
    _realtimeTimer?.cancel();
    _realtimeTimer = null;
  }

  Future<void> syncRealtimeNow({bool showLocalAlerts = true}) async {
    if (!isAuthenticated || isBusy || _syncingRealtime) return;
    _syncingRealtime = true;

    try {
      final historyPageResult = await _repository.history();
      final notificationPage = await _repository.notifications();
      final previousNotificationIds = Set<int>.from(_knownNotificationIds);
      final newNotifications = notificationPage.items
          .where((item) => !previousNotificationIds.contains(item.id))
          .toList()
          .reversed;

      final historyChanged =
          historyPage != historyPageResult.currentPage ||
          historyHasMore != historyPageResult.hasMorePages ||
          !_sameIds(history, historyPageResult.items);
      final notificationsChanged =
          notificationsPage != notificationPage.currentPage ||
          notificationsHasMore != notificationPage.hasMorePages ||
          !_sameIds(notifications, notificationPage.items) ||
          notifications.any((item) {
            AppNotification? updated;
            for (final next in notificationPage.items) {
              if (next.id == item.id) {
                updated = next;
                break;
              }
            }
            return updated != null && updated.readAt != item.readAt;
          });

      if (historyChanged) {
        history = historyPageResult.items;
        historyPage = historyPageResult.currentPage;
        historyHasMore = historyPageResult.hasMorePages;
      }

      if (notificationsChanged) {
        notifications = notificationPage.items;
        notificationsPage = notificationPage.currentPage;
        notificationsHasMore = notificationPage.hasMorePages;
      }

      _rememberNotifications(notificationPage.items);

      if (historyChanged || notificationsChanged) {
        notifyListeners();
      }

      if (showLocalAlerts) {
        for (final notification in newNotifications) {
          if (!notification.isRead) {
            await notificationsDevice.showAppNotification(notification, locale);
          }
        }
      }
    } catch (_) {
      // Realtime sync should stay quiet; manual refresh still reports errors.
    } finally {
      _syncingRealtime = false;
    }
  }

  Future<void> loadReminders() async {
    reminders = await _repository.reminders();
    notifyListeners();
  }

  Future<void> saveReminder(ReminderItem reminder) async {
    final next = [
      ...reminders.where((item) => item.id != reminder.id),
      reminder,
    ]..sort((a, b) => a.scheduledAt.compareTo(b.scheduledAt));
    await _repository.saveReminders(next);
    await notificationsDevice.scheduleReminder(reminder);
    reminders = next;
    notifyListeners();
  }

  Future<void> deleteReminder(int id) async {
    final next = reminders.where((item) => item.id != id).toList();
    await _repository.saveReminders(next);
    await notificationsDevice.cancelReminder(id);
    reminders = next;
    notifyListeners();
  }

  Future<void> _syncStoreProducts() async {
    storeProducts = await _store.loadProducts(plans);
  }

  Future<void> _handlePurchases(List<PurchaseDetails> purchases) async {
    for (final purchase in purchases) {
      if (purchase.status == PurchaseStatus.error) {
        error = purchase.error?.message;
        notifyListeners();
        continue;
      }

      if (purchase.status != PurchaseStatus.purchased &&
          purchase.status != PurchaseStatus.restored) {
        continue;
      }

      SubscriptionPlan? plan;
      for (final item in plans) {
        final productId = _store.productIdFor(item);
        if (productId == purchase.productID) {
          plan = item;
          break;
        }
      }

      if (plan == null) {
        error = 'Unknown store product: ${purchase.productID}';
        notifyListeners();
        continue;
      }

      await _guard(() async {
        quota = await _repository.verifyStorePurchase(
          platform: _store.platform,
          productId: purchase.productID,
          purchaseToken: purchase.verificationData.serverVerificationData,
          transactionId: purchase.purchaseID,
        );
        final payload = await _repository.me();
        notifications = payload?.notifications ?? notifications;
        _rememberNotifications(notifications);
        if (purchase.pendingCompletePurchase) {
          await _store.complete(purchase);
        }
      });
    }
  }

  @override
  void dispose() {
    stopRealtimeSync();
    _purchaseSubscription.cancel();
    super.dispose();
  }

  void _rememberNotifications(List<AppNotification> items) {
    _knownNotificationIds.addAll(items.map((item) => item.id));
  }

  bool _sameIds<T>(List<T> current, List<T> next) {
    if (current.length != next.length) return false;
    for (var index = 0; index < current.length; index += 1) {
      final currentId = switch (current[index]) {
        AppNotification item => item.id,
        ReadingHistoryItem item => item.id,
        _ => null,
      };
      final nextId = switch (next[index]) {
        AppNotification item => item.id,
        ReadingHistoryItem item => item.id,
        _ => null,
      };
      if (currentId != nextId) return false;
    }
    return true;
  }

  Future<void> _guard(
    Future<void> Function() action, {
    bool swallowAuth = false,
    void Function(ApiException exception)? onApiException,
  }) async {
    isBusy = true;
    error = null;
    message = null;
    lockedAccount = null;
    notifyListeners();

    try {
      await action();
    } on ApiException catch (exception) {
      onApiException?.call(exception);
      if (!swallowAuth) error = exception.message;
      if (swallowAuth && exception.statusCode == 401) {
        await _repository.clearToken();
      }
    } catch (exception) {
      if (!swallowAuth) error = exception.toString();
    } finally {
      isBusy = false;
      notifyListeners();
    }
  }
}

class LockedAccount {
  const LockedAccount({
    required this.message,
    required this.supportEmail,
    required this.supportPhone,
  });

  factory LockedAccount.fromJson(Map<String, dynamic> json) => LockedAccount(
    message: json['message']?.toString() ?? '',
    supportEmail: json['support_email']?.toString() ?? '',
    supportPhone: json['support_phone']?.toString() ?? '',
  );

  final String message;
  final String supportEmail;
  final String supportPhone;
}
