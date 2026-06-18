import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'core/i18n/app_strings.dart';
import 'features/app/data/app_repository.dart';
import 'features/app/data/device_notification_service.dart';
import 'features/app/presentation/app_controller.dart';
import 'features/app/presentation/screens/account_screen.dart';
import 'features/app/presentation/screens/auth_screen.dart';
import 'features/app/presentation/screens/change_password_screen.dart';
import 'features/app/presentation/screens/home_screen.dart';
import 'features/app/presentation/screens/subscription_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  final repository = AppRepository(prefs: prefs);
  await repository.initializeAuth();
  final notificationService = DeviceNotificationService(prefs: prefs);
  await notificationService.initialize();
  await notificationService.requestNotifications();
  SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp]);
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
      statusBarBrightness: Brightness.light,
    ),
  );
  final controller = AppController(
    repository: repository,
    notificationService: notificationService,
  );
  await controller.restore();
  runApp(PalmLifeApp(controller: controller));
}

class PalmLifeApp extends StatelessWidget {
  const PalmLifeApp({super.key, required this.controller});

  final AppController controller;

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (context, _) {
        final strings = AppStrings.of(controller.locale);

        return MaterialApp(
          debugShowCheckedModeBanner: false,
          title: strings.appName,
          locale: Locale(controller.locale),
          supportedLocales: const [Locale('vi'), Locale('en')],
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          theme: ThemeData(
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF0D7C66),
              brightness: Brightness.light,
            ),
            scaffoldBackgroundColor: const Color(0xFFF5F7F6),
            useMaterial3: true,
            appBarTheme: const AppBarTheme(
              centerTitle: false,
              elevation: 0,
              scrolledUnderElevation: 0,
              backgroundColor: Colors.transparent,
              titleTextStyle: TextStyle(
                color: Color(0xFF16201D),
                fontSize: 22,
                fontWeight: FontWeight.w800,
              ),
            ),
            inputDecorationTheme: const InputDecorationTheme(
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.all(Radius.circular(8)),
                borderSide: BorderSide(color: Color(0xFFD9DED7)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.all(Radius.circular(8)),
                borderSide: BorderSide(color: Color(0xFFD9DED7)),
              ),
            ),
            filledButtonTheme: FilledButtonThemeData(
              style: FilledButton.styleFrom(
                minimumSize: const Size(0, 52),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
            outlinedButtonTheme: OutlinedButtonThemeData(
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(0, 52),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
            cardTheme: const CardThemeData(
              elevation: 0,
              color: Colors.white,
              margin: EdgeInsets.zero,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(8)),
              ),
            ),
          ),
          home: controller.isAuthenticated
              ? HomeScreen(controller: controller)
              : AuthScreen(controller: controller),
          routes: {
            SubscriptionScreen.routeName: (_) =>
                SubscriptionScreen(controller: controller),
            AccountScreen.routeName: (_) =>
                AccountScreen(controller: controller),
            ChangePasswordScreen.routeName: (_) =>
                ChangePasswordScreen(controller: controller),
          },
          onGenerateRoute: (settings) {
            final routeName = settings.name ?? '';
            if (routeName.startsWith(ChangePasswordScreen.routeName)) {
              return MaterialPageRoute<void>(
                builder: (_) => ChangePasswordScreen(controller: controller),
                settings: settings,
              );
            }
            return null;
          },
        );
      },
    );
  }
}
