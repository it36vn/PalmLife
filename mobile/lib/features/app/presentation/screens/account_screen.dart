import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';
import '../widgets/password_requirements.dart';

class AccountScreen extends StatefulWidget {
  const AccountScreen({super.key, required this.controller});

  static const routeName = '/account';
  final AppController controller;

  @override
  State<AccountScreen> createState() => _AccountScreenState();
}

class _AccountScreenState extends State<AccountScreen> {
  late final TextEditingController _name;
  late final TextEditingController _email;
  DateTime? _birthDate;
  String? _gender;

  @override
  void initState() {
    super.initState();
    _name = TextEditingController(text: widget.controller.user?.name);
    _email = TextEditingController(text: widget.controller.user?.email);
    _birthDate = widget.controller.user?.birthDate;
    _gender = widget.controller.user?.gender ?? 'female';
  }

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);

    return AppScaffold(
      title: s.account,
      children: [
        TextField(
          controller: _name,
          decoration: InputDecoration(labelText: s.name),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _email,
          decoration: InputDecoration(labelText: s.email),
        ),
        const SizedBox(height: 12),
        ListTile(
          contentPadding: EdgeInsets.zero,
          title: Text(s.birthDate),
          subtitle: Text(
            _birthDate == null
                ? s.requiredField
                : _birthDate!.toIso8601String().substring(0, 10),
          ),
          trailing: const Icon(Icons.event),
          onTap: () async {
            final picked = await showDatePicker(
              context: context,
              locale: Locale(widget.controller.locale),
              firstDate: DateTime(1900),
              lastDate: DateTime.now().subtract(const Duration(days: 1)),
              initialDate: _birthDate ?? DateTime(1995),
            );
            if (picked != null) setState(() => _birthDate = picked);
          },
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          initialValue: _gender,
          decoration: InputDecoration(labelText: s.gender),
          items: [
            DropdownMenuItem(value: 'female', child: Text(s.female)),
            DropdownMenuItem(value: 'male', child: Text(s.male)),
            DropdownMenuItem(value: 'other', child: Text(s.other)),
          ],
          onChanged: (value) => setState(() => _gender = value),
        ),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: widget.controller.isBusy
              ? null
              : () async {
                  final messenger = ScaffoldMessenger.of(context);
                  await widget.controller.updateAccount(
                    _name.text.trim(),
                    _email.text.trim(),
                    _birthDate,
                    _gender,
                  );
                  if (!mounted) return;
                  if (widget.controller.error == null) {
                    setState(() {
                      _name.text = widget.controller.user?.name ?? _name.text;
                      _email.text =
                          widget.controller.user?.email ?? _email.text;
                    });
                    messenger.showSnackBar(SnackBar(content: Text(s.save)));
                  }
                },
          icon: const Icon(Icons.save_outlined),
          label: Text(s.save),
        ),
        const SizedBox(height: 8),
        const SizedBox(height: 20),
        FilledButton.tonalIcon(
          onPressed: widget.controller.isBusy ? null : _confirmDeleteAccount,
          icon: const Icon(Icons.delete_outline),
          label: Text(s.deleteAccount),
        ),
        if (widget.controller.error != null) ...[
          const SizedBox(height: 12),
          Text(
            widget.controller.error!,
            style: TextStyle(color: Theme.of(context).colorScheme.error),
          ),
        ],
      ],
    );
  }

  Future<void> _confirmDeleteAccount() async {
    final s = AppStrings.of(widget.controller.locale);
    final password = TextEditingController();
    String? fieldError;
    var isDeleting = false;
    var passwordTouched = false;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          final passwordRules = PasswordRules(password.text);
          final canDelete = !isDeleting && passwordRules.isValid;

          return AlertDialog(
            insetPadding: const EdgeInsets.all(16),
            title: Text(s.deleteAccountTitle),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(s.deleteAccountMessage),
                const SizedBox(height: 14),
                TextField(
                  controller: password,
                  obscureText: true,
                  enabled: !isDeleting,
                  decoration: InputDecoration(
                    labelText: s.password,
                    errorText: fieldError,
                  ),
                  onTap: () => setDialogState(() => passwordTouched = true),
                  onChanged: (_) => setDialogState(() {
                    passwordTouched = true;
                    fieldError = null;
                  }),
                ),
                PasswordRequirementList(
                  password: password.text,
                  locale: widget.controller.locale,
                  visible: passwordTouched,
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: isDeleting
                    ? null
                    : () => Navigator.of(context).pop(false),
                child: Text(s.cancel),
              ),
              FilledButton.tonalIcon(
                onPressed: canDelete
                    ? () async {
                        setDialogState(() {
                          isDeleting = true;
                          fieldError = null;
                        });
                        final ok = await widget.controller.deleteAccount(
                          password.text,
                        );
                        if (!context.mounted) return;
                        if (ok) {
                          Navigator.of(context).pop(true);
                          return;
                        }
                        setDialogState(() {
                          isDeleting = false;
                          fieldError = widget.controller.error;
                        });
                      }
                    : null,
                icon: isDeleting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.delete_outline),
                label: Text(s.confirmDelete),
              ),
            ],
          );
        },
      ),
    );
    password.dispose();
    if (confirmed != true) return;
    if (!mounted) return;
    Navigator.of(context).popUntil((route) => route.isFirst);
  }
}
