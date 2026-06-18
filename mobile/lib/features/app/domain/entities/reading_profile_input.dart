class ReadingProfileInput {
  const ReadingProfileInput({
    required this.useAccountProfile,
    required this.name,
    required this.birthDate,
    required this.gender,
  });

  final bool useAccountProfile;
  final String? name;
  final DateTime? birthDate;
  final String? gender;

  Map<String, String> toApiFields() => {
    'use_profile': useAccountProfile ? '1' : '0',
    if (!useAccountProfile && name != null) 'name': name!,
    if (!useAccountProfile && birthDate != null)
      'birth_date': birthDate!.toIso8601String().substring(0, 10),
    if (!useAccountProfile && gender != null) 'gender': gender!,
  };
}
