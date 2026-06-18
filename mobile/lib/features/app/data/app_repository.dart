import 'dart:io';
import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:mobile/core/network/logging_client.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/network/api_client.dart';
import '../domain/app_models.dart';

class AppRepository {
  AppRepository({
    required SharedPreferences prefs,
    FlutterSecureStorage? secureStorage,
    ApiClient? api,
  }) : _prefs = prefs,
       _secureStorage = secureStorage ?? const FlutterSecureStorage(),
       _api = api ?? ApiClient(httpClient: LoggingClient(http.Client()),);

  static const _tokenKey = 'auth_token';
  static const _remindersKey = 'reminders';
  final SharedPreferences _prefs;
  final FlutterSecureStorage _secureStorage;
  final ApiClient _api;
  String? _token;

  String? get token => _token;

  Future<void> initializeAuth() async {
    _token = await _secureStorage.read(key: _tokenKey);
    if (_token != null) return;

    final legacyToken = _prefs.getString(_tokenKey);
    if (legacyToken == null || legacyToken.isEmpty) return;
    await saveToken(legacyToken);
    await _prefs.remove(_tokenKey);
  }

  Future<void> saveToken(String value) async {
    _token = value;
    await _secureStorage.write(key: _tokenKey, value: value);
    await _prefs.remove(_tokenKey);
  }

  Future<void> clearToken() async {
    _token = null;
    await _secureStorage.delete(key: _tokenKey);
    await _prefs.remove(_tokenKey);
  }

  Future<AuthPayload> register({
    required String name,
    required String email,
    required String password,
    required String locale,
  }) async {
    final json = await _api.post('/register', {
      'name': name,
      'email': email,
      'password': password,
      'locale': locale,
      'accepted_terms': true,
      'accepted_privacy': true,
    });
    await saveToken(json['token'].toString());
    return AuthPayload.fromJson(json);
  }

  Future<AuthPayload> login({
    required String email,
    required String password,
  }) async {
    final json = await _api.post('/login', {
      'email': email,
      'password': password,
    });
    await saveToken(json['token'].toString());
    return AuthPayload.fromJson(json);
  }

  Future<String> forgotPassword({
    required String email,
    required String locale,
  }) async {
    final json = await _api.post('/password/forgot', {
      'email': email,
      'locale': locale,
    });
    return json['message']?.toString() ?? '';
  }

  Future<void> changePassword({
    required String currentPassword,
    required String password,
  }) async {
    await _api.post('/password/change', {
      'current_password': currentPassword,
      'password': password,
    }, token: token);
  }

  Future<AuthPayload?> me() async {
    final current = token;
    if (current == null) return null;
    final json = await _api.get('/me', token: current);
    return AuthPayload.fromJson(json);
  }

  Future<List<SubscriptionPlan>> plans() async {
    final json = await _api.get('/subscriptions', token: token);
    return (json['plans'] as List<dynamic>)
        .map(
          (item) =>
              SubscriptionPlan.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList();
  }

  Future<QuotaStatus> verifyStorePurchase({
    required String platform,
    required String productId,
    required String purchaseToken,
    required String? transactionId,
  }) async {
    final json = await _api.post('/subscriptions/store/verify', {
      'platform': platform,
      'product_id': productId,
      'purchase_token': purchaseToken,
      'transaction_id': transactionId,
    }, token: token);
    return QuotaStatus.fromJson(json['quota'] as Map<String, dynamic>?);
  }

  Future<AnalysisResult> analyze(
    File image,
    String locale,
    String type,
    ReadingProfileInput profile,
  ) async {
    final json = await _api.uploadAnalysis(
      path: '/palm-readings',
      image: image,
      type: type,
      locale: locale,
      token: token!,
      fields: profile.toApiFields(),
    );
    return AnalysisResult.fromJson(json);
  }

  Future<PaginatedResult<ReadingHistoryItem>> history({int page = 1}) async {
    final json = await _api.get(
      '/palm-readings',
      token: token,
      queryParameters: {'page': page.toString()},
    );
    return PaginatedResult.fromJson(
      json,
      listKey: 'analyses',
      itemBuilder: (item) =>
          ReadingHistoryItem.fromJson(Map<String, dynamic>.from(item as Map)),
    );
  }

  Future<void> deleteHistoryItem(int id) async {
    await _api.delete('/palm-readings/$id', {}, token: token);
  }

  Future<PaginatedResult<AppNotification>> notifications({int page = 1}) async {
    final json = await _api.get(
      '/notifications',
      token: token,
      queryParameters: {'page': page.toString()},
    );
    return PaginatedResult.fromJson(
      json,
      listKey: 'notifications',
      itemBuilder: (item) =>
          AppNotification.fromJson(Map<String, dynamic>.from(item as Map)),
    );
  }

  Future<void> markNotificationRead(int id) async {
    await _api.post('/notifications/$id/read', {}, token: token);
  }

  Future<void> markAllNotificationsRead() async {
    await _api.post('/notifications/read-all', {}, token: token);
  }

  Future<List<ReminderItem>> reminders() async {
    final raw = _prefs.getString(_remindersKey);
    if (raw == null || raw.isEmpty) return const [];
    return (jsonDecode(raw) as List<dynamic>)
        .map(
          (item) =>
              ReminderItem.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList()
      ..sort((a, b) => a.scheduledAt.compareTo(b.scheduledAt));
  }

  Future<void> saveReminders(List<ReminderItem> reminders) => _prefs.setString(
    _remindersKey,
    jsonEncode(reminders.map((item) => item.toJson()).toList()),
  );

  Future<UserProfile> updateAccount({
    required String name,
    required String email,
    required DateTime? birthDate,
    required String? gender,
  }) async {
    final json = await _api.put('/account', {
      'name': name,
      'email': email,
      'birth_date': birthDate?.toIso8601String().substring(0, 10),
      'gender': gender,
    }, token: token);
    return UserProfile.fromJson(Map<String, dynamic>.from(json['user'] as Map));
  }

  Future<void> deleteAccount({
    required String password,
    required String locale,
  }) async {
    await _api.delete('/account', {
      'password': password,
      'confirm_delete': true,
      'locale': locale,
    }, token: token);
    await clearToken();
  }

  Future<void> logout() async {
    final current = token;
    if (current != null) {
      await _api.post('/logout', {}, token: current);
    }
    await clearToken();
  }
}

class AuthPayload {
  const AuthPayload({
    required this.user,
    required this.quota,
    required this.notifications,
  });

  factory AuthPayload.fromJson(Map<String, dynamic> json) => AuthPayload(
    user: UserProfile.fromJson(Map<String, dynamic>.from(json['user'] as Map)),
    quota: QuotaStatus.fromJson(json['quota'] as Map<String, dynamic>?),
    notifications: (json['notifications'] as List<dynamic>? ?? [])
        .map(
          (item) =>
              AppNotification.fromJson(Map<String, dynamic>.from(item as Map)),
        )
        .toList(),
  );

  final UserProfile user;
  final QuotaStatus quota;
  final List<AppNotification> notifications;
}

class PaginatedResult<T> {
  const PaginatedResult({
    required this.items,
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.lastPage,
    required this.hasMorePages,
  });

  factory PaginatedResult.fromJson(
    Map<String, dynamic> json, {
    required String listKey,
    required T Function(Object? item) itemBuilder,
  }) {
    final rawItems = json[listKey] as List<dynamic>? ?? const [];
    final pagination = json['pagination'] as Map<String, dynamic>? ?? {};
    final currentPage = _intValue(pagination['current_page'], fallback: 1);
    final lastPage = _intValue(pagination['last_page'], fallback: currentPage);

    return PaginatedResult(
      items: rawItems.map(itemBuilder).toList(),
      currentPage: currentPage,
      perPage: _intValue(pagination['per_page'], fallback: rawItems.length),
      total: _intValue(pagination['total'], fallback: rawItems.length),
      lastPage: lastPage,
      hasMorePages:
          pagination['has_more_pages'] == true || currentPage < lastPage,
    );
  }

  final List<T> items;
  final int currentPage;
  final int perPage;
  final int total;
  final int lastPage;
  final bool hasMorePages;

  static int _intValue(Object? value, {required int fallback}) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? fallback;
  }
}
