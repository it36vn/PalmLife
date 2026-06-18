import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';

class PasswordRules {
  const PasswordRules(this.value);

  final String value;

  bool get hasMinLength => value.length >= 12;
  bool get hasUppercase => RegExp(r'[A-Z]').hasMatch(value);
  bool get hasLowercase => RegExp(r'[a-z]').hasMatch(value);
  bool get hasDigit => RegExp(r'\d').hasMatch(value);
  bool get hasSymbol =>
      RegExp(r'[!@#$%^&*(),.?":{}|<>\[\]\\;_\-+=/~`]').hasMatch(value);
  bool get hasNoSpace => !RegExp(r'\s').hasMatch(value);

  bool get isValid =>
      hasMinLength &&
      hasUppercase &&
      hasLowercase &&
      hasDigit &&
      hasSymbol &&
      hasNoSpace;

  List<String> unmet(AppStrings s) => [
    if (!hasMinLength) s.passwordMinLength,
    if (!hasUppercase) s.passwordUppercase,
    if (!hasLowercase) s.passwordLowercase,
    if (!hasDigit) s.passwordDigit,
    if (!hasSymbol) s.passwordSymbol,
    if (!hasNoSpace) s.passwordNoSpace,
  ];

  String? firstError(AppStrings s) {
    if (value.isEmpty) return s.requiredField;
    final missing = unmet(s);
    return missing.isEmpty ? null : missing.first;
  }
}

class PasswordRequirementList extends StatelessWidget {
  const PasswordRequirementList({
    super.key,
    required this.password,
    required this.locale,
    this.visible = true,
  });

  final String password;
  final String locale;
  final bool visible;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(locale);
    final missing = PasswordRules(password).unmet(s);
    if (!visible || missing.isEmpty) {
      return const SizedBox.shrink();
    }

    return AnimatedSize(
      duration: const Duration(milliseconds: 180),
      curve: Curves.easeOut,
      child: Container(
        width: double.infinity,
        margin: const EdgeInsets.only(top: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Theme.of(
            context,
          ).colorScheme.errorContainer.withValues(alpha: .42),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: Theme.of(context).colorScheme.error.withValues(alpha: .22),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            for (final item in missing)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.error_outline,
                      size: 17,
                      color: Theme.of(context).colorScheme.error,
                    ),
                    const SizedBox(width: 8),
                    Expanded(child: Text(item)),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}
