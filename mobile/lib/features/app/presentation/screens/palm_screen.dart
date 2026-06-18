import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';

class PalmScreen extends StatefulWidget {
  const PalmScreen({super.key, required this.controller, required this.onPick});

  final AppController controller;
  final Future<void> Function(ImageSource source, ReadingProfileInput profile)
  onPick;

  @override
  State<PalmScreen> createState() => _PalmScreenState();
}

class _PalmScreenState extends State<PalmScreen> {
  final _name = TextEditingController();
  final _birth = TextEditingController();
  String _gender = 'female';
  bool _useProfile = true;

  bool get _hasProfile => widget.controller.user?.hasReadingProfile ?? false;

  bool get _valid {
    if (_useProfile && _hasProfile) return true;
    return _name.text.trim().isNotEmpty && _birth.text.isNotEmpty;
  }

  ReadingProfileInput get _profile => ReadingProfileInput(
    useAccountProfile: _useProfile && _hasProfile,
    name: _name.text.trim().isEmpty ? null : _name.text.trim(),
    birthDate: DateTime.tryParse(_birth.text),
    gender: _gender,
  );

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(widget.controller.locale);
    final result = widget.controller.lastResult;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        InfoPanel(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_hasProfile)
                CheckboxListTile(
                  value: _useProfile,
                  onChanged: (value) =>
                      setState(() => _useProfile = value ?? true),
                  title: Text(s.usePersonalInfo),
                  contentPadding: EdgeInsets.zero,
                ),
              if (!_useProfile || !_hasProfile) ...[
                TextField(
                  controller: _name,
                  decoration: InputDecoration(labelText: s.name),
                  onChanged: (_) => setState(() {}),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _birth,
                  readOnly: true,
                  decoration: InputDecoration(labelText: s.birthDate),
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      locale: Locale(widget.controller.locale),
                      firstDate: DateTime(1900),
                      lastDate: DateTime.now(),
                      initialDate: DateTime(1995),
                    );
                    if (picked != null) {
                      setState(
                        () => _birth.text = picked.toIso8601String().substring(
                          0,
                          10,
                        ),
                      );
                    }
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
                  onChanged: (value) =>
                      setState(() => _gender = value ?? 'female'),
                ),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: FilledButton.icon(
                      onPressed: _valid
                          ? () => widget.onPick(ImageSource.camera, _profile)
                          : null,
                      icon: const Icon(Icons.photo_camera_outlined),
                      label: Text(s.camera),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _valid
                          ? () => widget.onPick(ImageSource.gallery, _profile)
                          : null,
                      icon: const Icon(Icons.photo_library_outlined),
                      label: Text(s.gallery),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        if (widget.controller.isBusy)
          const Padding(
            padding: EdgeInsets.all(24),
            child: Center(child: CircularProgressIndicator()),
          ),
        if (widget.controller.error != null) Text(widget.controller.error!),
        if (result != null)
          Padding(
            padding: const EdgeInsets.only(top: 12),
            child: InfoPanel(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    result.title,
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  const SizedBox(height: 8),
                  Text(result.summary),
                  const SizedBox(height: 12),
                  for (final section in result.sections) ...[
                    Text(
                      section['heading']?.toString() ?? '',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    Text(section['body']?.toString() ?? ''),
                    const SizedBox(height: 10),
                  ],
                  Text(result.safetyNotice),
                ],
              ),
            ),
          ),
      ],
    );
  }
}
