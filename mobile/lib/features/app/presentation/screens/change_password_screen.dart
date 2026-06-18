import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';
import '../widgets/password_requirements.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key, required this.controller});

  static const routeName = '/change-password';
  final AppController controller;

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _current = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _currentTouched = false;
  bool _passwordTouched = false;
  bool _confirmTouched = false;

  @override
  void initState() {
    super.initState();
    _current.addListener(() => setState(() {}));
    _password.addListener(() => setState(() {}));
    _confirm.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _current.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);
    if (!widget.controller.isAuthenticated) {
      return AppScaffold(
        title: s.changePassword,
        children: [
          InfoPanel(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  s.loginWithTemporaryPassword,
                  style: Theme.of(
                    context,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 8),
                Text(s.loginWithTemporaryPasswordHint),
              ],
            ),
          ),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: () =>
                Navigator.of(context).popUntil((route) => route.isFirst),
            icon: const Icon(Icons.login),
            label: Text(s.login),
          ),
        ],
      );
    }

    final passwordRules = PasswordRules(_password.text);
    final canSubmit =
        !widget.controller.isBusy &&
        _current.text.isNotEmpty &&
        passwordRules.isValid &&
        _confirm.text == _password.text;

    return AppScaffold(
      title: s.changePassword,
      children: [
        InfoPanel(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [Text(s.changePasswordHint)],
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _current,
          decoration: InputDecoration(
            labelText: s.currentPassword,
            errorText: _currentError(s),
          ),
          obscureText: true,
          onTap: () => setState(() => _currentTouched = true),
          onChanged: (_) => setState(() => _currentTouched = true),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _password,
          decoration: InputDecoration(labelText: s.newPassword),
          obscureText: true,
          onTap: () => setState(() => _passwordTouched = true),
          onChanged: (_) => setState(() => _passwordTouched = true),
        ),
        PasswordRequirementList(
          password: _password.text,
          locale: widget.controller.locale,
          visible: _passwordTouched,
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _confirm,
          decoration: InputDecoration(
            labelText: s.confirmPassword,
            errorText: _confirmError(s),
          ),
          obscureText: true,
          onTap: () => setState(() => _confirmTouched = true),
          onChanged: (_) => setState(() => _confirmTouched = true),
        ),
        if (widget.controller.error != null) ...[
          const SizedBox(height: 12),
          Text(
            widget.controller.error!,
            style: TextStyle(color: Theme.of(context).colorScheme.error),
          ),
        ],
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: canSubmit
              ? () async {
                  final ok = await widget.controller.changePassword(
                    _current.text,
                    _password.text,
                  );
                  if (!context.mounted || !ok) return;
                  ScaffoldMessenger.of(
                    context,
                  ).showSnackBar(SnackBar(content: Text(s.passwordChanged)));
                  Navigator.of(context).pop();
                }
              : null,
          icon: const Icon(Icons.lock_reset),
          label: Text(s.save),
        ),
      ],
    );
  }

  String? _currentError(AppStrings s) {
    if (!_currentTouched) return null;
    return _current.text.isEmpty ? s.requiredField : null;
  }

  String? _confirmError(AppStrings s) {
    if (!_confirmTouched) return null;
    if (_confirm.text.isEmpty) return s.requiredField;
    return _confirm.text == _password.text ? null : s.passwordMismatch;
  }
}
