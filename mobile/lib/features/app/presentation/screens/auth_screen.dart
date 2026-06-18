import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../../../core/platform/support_launcher.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';
import '../widgets/password_requirements.dart';

class AuthScreen extends StatefulWidget {
  const AuthScreen({super.key, required this.controller});

  final AppController controller;

  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _register = false;
  bool _accepted = true;
  bool _nameTouched = false;
  bool _emailTouched = false;
  bool _passwordTouched = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _password.addListener(() => setState(() {}));
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);

    return AppScaffold(
      title: s.appName,
      actions: [
        DropdownButtonHideUnderline(
          child: DropdownButton<String>(
            value: widget.controller.locale,
            borderRadius: BorderRadius.circular(8),
            items: [
              DropdownMenuItem(value: 'vi', child: Text(s.vietnamese)),
              DropdownMenuItem(value: 'en', child: Text(s.english)),
            ],
            onChanged: (value) {
              if (value != null) widget.controller.setLocale(value);
            },
          ),
        ),
      ],
      children: [
        Text(s.legalShort, style: Theme.of(context).textTheme.bodyMedium),
        const SizedBox(height: 16),
        SegmentedButton<bool>(
          segments: [
            ButtonSegment(
              value: false,
              label: Text(s.login),
              icon: const Icon(Icons.login),
            ),
            ButtonSegment(
              value: true,
              label: Text(s.register),
              icon: const Icon(Icons.person_add_alt_1),
            ),
          ],
          selected: {_register},
          onSelectionChanged: (value) =>
              setState(() => _register = value.first),
        ),
        const SizedBox(height: 16),
        if (_register)
          TextField(
            controller: _name,
            decoration: InputDecoration(
              labelText: s.name,
              errorText: _nameError(s),
            ),
            onChanged: (_) => setState(() => _nameTouched = true),
          ),
        if (_register) const SizedBox(height: 12),
        TextField(
          controller: _email,
          decoration: InputDecoration(
            labelText: s.email,
            errorText: _emailError(s),
          ),
          keyboardType: TextInputType.emailAddress,
          onChanged: (_) => setState(() => _emailTouched = true),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _password,
          decoration: InputDecoration(labelText: s.password),
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
        if (_register)
          CheckboxListTile(
            value: _accepted,
            onChanged: (value) => setState(() => _accepted = value ?? false),
            title: Text(s.consent),
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
            titleAlignment: ListTileTitleAlignment.top,
          ),
        if (widget.controller.error != null)
          Text(
            widget.controller.error!,
            style: TextStyle(color: Theme.of(context).colorScheme.error),
          ),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: widget.controller.isBusy || !_canSubmit ? null : _submit,
          icon: widget.controller.isBusy
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : const Icon(Icons.arrow_forward),
          label: Text(_register ? s.register : s.login),
        ),
        if (!_register) ...[
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: widget.controller.isBusy ? null : _forgotPassword,
            icon: const Icon(Icons.lock_reset),
            label: Text(s.forgotPassword),
          ),
        ],
      ],
    );
  }

  Future<void> _submit() async {
    setState(() {
      _nameTouched = true;
      _emailTouched = true;
      _passwordTouched = true;
    });
    if (!_canSubmit) return;

    if (_register) {
      await widget.controller.register(
        _name.text.trim(),
        _email.text.trim(),
        _password.text,
      );
    } else {
      final loggedIn = await widget.controller.login(
        _email.text.trim(),
        _password.text,
      );
      if (!loggedIn && mounted && widget.controller.lockedAccount != null) {
        await _showLockedAccountDialog(widget.controller.lockedAccount!);
      }
    }
  }

  bool get _canSubmit {
    if (!PasswordRules(_password.text).isValid) {
      return false;
    }
    if (_register && !_accepted) return false;
    if (_register && _name.text.trim().isEmpty) return false;
    return _isEmail(_email.text.trim()) && _password.text.isNotEmpty;
  }

  String? _nameError(AppStrings s) {
    if (!_register || !_nameTouched) return null;
    return _name.text.trim().isEmpty ? s.requiredField : null;
  }

  String? _emailError(AppStrings s) {
    if (!_emailTouched) return null;
    final email = _email.text.trim();
    if (email.isEmpty) return s.requiredField;
    return _isEmail(email) ? null : s.invalidEmail;
  }

  bool _isEmail(String value) =>
      RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value);

  Future<void> _forgotPassword() async {
    final s = AppStrings.of(widget.controller.locale);
    final email = _email.text.trim();
    if (email.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(s.enterEmailFirst)));
      return;
    }

    final sent = await widget.controller.forgotPassword(email);
    if (!mounted || !sent) return;

    await showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(s.forgotPassword),
        content: Text(widget.controller.message ?? s.forgotPasswordSent),
        actions: [
          FilledButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text(s.continueText),
          ),
        ],
      ),
    );
  }

  Future<void> _showLockedAccountDialog(LockedAccount locked) async {
    final s = AppStrings.of(widget.controller.locale);
    const launcher = SupportLauncher();

    await showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(s.lockedAccountTitle),
        content: Text(
          locked.message.isEmpty ? s.lockedAccountMessage : locked.message,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text(s.close),
          ),
          OutlinedButton.icon(
            onPressed: locked.supportEmail.isEmpty
                ? null
                : () => launcher.email(locked.supportEmail),
            icon: const Icon(Icons.mail_outline),
            label: Text(s.sendEmail),
          ),
          FilledButton.icon(
            onPressed: locked.supportPhone.isEmpty
                ? null
                : () => launcher.call(locked.supportPhone),
            icon: const Icon(Icons.call_outlined),
            label: Text(s.callSupport),
          ),
        ],
      ),
    );
  }
}
