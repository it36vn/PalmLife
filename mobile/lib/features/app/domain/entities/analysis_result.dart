class AnalysisResult {
  const AnalysisResult({
    required this.title,
    required this.summary,
    required this.sections,
    required this.safetyNotice,
  });

  factory AnalysisResult.fromJson(Map<String, dynamic> json) {
    final result =
        json['analysis']?['result'] as Map<String, dynamic>? ??
        json['result'] as Map<String, dynamic>? ??
        {};
    return AnalysisResult(
      title: result['title']?.toString() ?? '',
      summary: result['summary']?.toString() ?? '',
      sections: (result['sections'] as List<dynamic>? ?? [])
          .map((item) => Map<String, dynamic>.from(item as Map))
          .toList(),
      safetyNotice: result['safety_notice']?.toString() ?? '',
    );
  }

  final String title;
  final String summary;
  final List<Map<String, dynamic>> sections;
  final String safetyNotice;
}
