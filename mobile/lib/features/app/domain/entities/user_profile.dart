class UserProfile {
  const UserProfile({
    required this.name,
    required this.email,
    required this.birthDate,
    required this.gender,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) => UserProfile(
    name: json['name']?.toString() ?? '',
    email: json['email']?.toString() ?? '',
    birthDate: DateTime.tryParse(json['birth_date']?.toString() ?? ''),
    gender: json['gender']?.toString(),
  );

  final String name;
  final String email;
  final DateTime? birthDate;
  final String? gender;

  bool get hasReadingProfile =>
      name.isNotEmpty && birthDate != null && (gender ?? '').isNotEmpty;
}
