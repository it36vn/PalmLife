import 'package:flutter/material.dart';

class SettingsActionTile extends StatelessWidget {
  const SettingsActionTile({
    super.key,
    this.icon,
    required this.title,
    this.subtitle,
    this.trailing,
    this.onTap,
  });

  final IconData? icon;
  final Widget title;
  final Widget? subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16),
      minLeadingWidth: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      leading: icon != null
          ? Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: colorScheme.primary.withValues(alpha: .10),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: colorScheme.primary, size: 20),
            )
          : null,
      title: DefaultTextStyle.merge(
        style: const TextStyle(fontWeight: FontWeight.w700),
        child: title,
      ),
      subtitle: subtitle,
      trailing: trailing,
      onTap: onTap,
    );
  }
}
