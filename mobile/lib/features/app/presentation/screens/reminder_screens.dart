import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';

class ReminderListScreen extends StatelessWidget {
  const ReminderListScreen({super.key, required this.controller});

  final AppController controller;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(controller.locale);
    return AppScaffold(
      title: s.reminders,
      actions: [
        IconButton(
          onPressed: () => Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => ReminderEditScreen(controller: controller),
            ),
          ),
          icon: const Icon(Icons.add),
          tooltip: s.addReminder,
        ),
      ],
      children: [
        if (controller.reminders.isEmpty) Text(s.emptyReminders),
        for (final item in controller.reminders)
          Card(
            child: ListTile(
              title: Text(item.title),
              subtitle: Text(
                '${item.scheduledAt.toLocal().toString().substring(0, 16)} • ${item.priority.label(controller.locale)}',
              ),
              leading: Icon(Icons.circle, color: _priorityColor(item.priority)),
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => ReminderDetailScreen(
                    controller: controller,
                    reminder: item,
                  ),
                ),
              ),
              trailing: IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () => controller.deleteReminder(item.id),
              ),
            ),
          ),
      ],
    );
  }
}

class ReminderDetailScreen extends StatelessWidget {
  const ReminderDetailScreen({
    super.key,
    required this.controller,
    required this.reminder,
  });

  final AppController controller;
  final ReminderItem reminder;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(controller.locale);
    return AppScaffold(
      title: s.reminderDetail,
      actions: [
        IconButton(
          onPressed: () => Navigator.of(context).pushReplacement(
            MaterialPageRoute<void>(
              builder: (_) => ReminderEditScreen(
                controller: controller,
                reminder: reminder,
              ),
            ),
          ),
          icon: const Icon(Icons.edit_outlined),
        ),
        IconButton(
          onPressed: () async {
            await controller.deleteReminder(reminder.id);
            if (context.mounted) Navigator.of(context).pop();
          },
          icon: const Icon(Icons.delete_outline),
        ),
      ],
      children: [
        InfoPanel(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.circle, color: _priorityColor(reminder.priority)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      reminder.title,
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(reminder.scheduledAt.toLocal().toString().substring(0, 16)),
              const SizedBox(height: 10),
              Text(reminder.body.isEmpty ? s.noContent : reminder.body),
            ],
          ),
        ),
      ],
    );
  }
}

class ReminderEditScreen extends StatefulWidget {
  const ReminderEditScreen({
    super.key,
    required this.controller,
    this.reminder,
  });

  final AppController controller;
  final ReminderItem? reminder;

  @override
  State<ReminderEditScreen> createState() => _ReminderEditScreenState();
}

class _ReminderEditScreenState extends State<ReminderEditScreen> {
  late final TextEditingController _title;
  late final TextEditingController _body;
  late DateTime _dateTime;
  late ReminderPriority _priority;
  bool _touched = false;

  @override
  void initState() {
    super.initState();
    _title = TextEditingController(text: widget.reminder?.title);
    _body = TextEditingController(text: widget.reminder?.body);
    _dateTime =
        widget.reminder?.scheduledAt ??
        DateTime.now().add(const Duration(minutes: 5));
    _priority = widget.reminder?.priority ?? ReminderPriority.normal;
  }

  @override
  void dispose() {
    _title.dispose();
    _body.dispose();
    super.dispose();
  }

  bool get _valid =>
      _title.text.trim().isNotEmpty && _dateTime.isAfter(DateTime.now());

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);
    return AppScaffold(
      title: widget.reminder == null ? s.addReminder : s.editReminder,
      children: [
        TextField(
          controller: _title,
          decoration: InputDecoration(
            labelText: s.title,
            errorText: _touched && _title.text.trim().isEmpty
                ? s.requiredField
                : null,
          ),
          onChanged: (_) => setState(() => _touched = true),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _body,
          decoration: InputDecoration(labelText: s.content),
          minLines: 3,
          maxLines: 5,
        ),
        const SizedBox(height: 12),
        ListTile(
          title: Text(s.dateTime),
          subtitle: Text(_dateTime.toLocal().toString().substring(0, 16)),
          trailing: const Icon(Icons.event),
          onTap: _pickDateTime,
        ),
        if (!_dateTime.isAfter(DateTime.now()))
          Text(
            s.futureDateError,
            style: TextStyle(color: Theme.of(context).colorScheme.error),
          ),
        const SizedBox(height: 12),
        DropdownButtonFormField<ReminderPriority>(
          initialValue: _priority,
          decoration: InputDecoration(labelText: s.priority),
          items: [
            for (final item in ReminderPriority.values)
              DropdownMenuItem(
                value: item,
                child: Row(
                  children: [
                    Icon(Icons.circle, color: _priorityColor(item), size: 14),
                    const SizedBox(width: 8),
                    Text(item.label(widget.controller.locale)),
                  ],
                ),
              ),
          ],
          onChanged: (value) => setState(() => _priority = value ?? _priority),
        ),
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: _valid ? _save : null,
          icon: const Icon(Icons.save_outlined),
          label: Text(s.save),
        ),
      ],
    );
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      locale: Locale(widget.controller.locale),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365 * 5)),
      initialDate: _dateTime.isAfter(DateTime.now())
          ? _dateTime
          : DateTime.now(),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_dateTime),
    );
    if (time == null) return;
    setState(() {
      _dateTime = DateTime(
        date.year,
        date.month,
        date.day,
        time.hour,
        time.minute,
      );
      _touched = true;
    });
  }

  Future<void> _save() async {
    setState(() => _touched = true);
    if (!_valid) return;
    final reminder = ReminderItem(
      id: widget.reminder?.id ?? DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: _title.text.trim(),
      body: _body.text.trim(),
      scheduledAt: _dateTime,
      priority: _priority,
    );
    await widget.controller.saveReminder(reminder);
    if (mounted) Navigator.of(context).pop();
  }
}

Color _priorityColor(ReminderPriority priority) => switch (priority) {
  ReminderPriority.normal => Colors.teal,
  ReminderPriority.important => Colors.orange,
  ReminderPriority.critical => Colors.red,
};
