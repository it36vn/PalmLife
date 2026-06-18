class SubscriptionPlan {
  const SubscriptionPlan({
    required this.code,
    required this.nameVi,
    required this.nameEn,
    required this.priceVnd,
    required this.descriptionVi,
    required this.descriptionEn,
    required this.appleProductId,
    required this.googleProductId,
    required this.storeProductType,
  });

  factory SubscriptionPlan.fromJson(Map<String, dynamic> json) =>
      SubscriptionPlan(
        code: json['code'].toString(),
        nameVi: json['name_vi'].toString(),
        nameEn: json['name_en'].toString(),
        priceVnd: json['price_vnd'] as int,
        descriptionVi: json['description_vi'].toString(),
        descriptionEn: json['description_en'].toString(),
        appleProductId: _optionalString(json['apple_product_id']),
        googleProductId: _optionalString(json['google_product_id']),
        storeProductType:
            json['store_product_type']?.toString() ?? 'subscription',
      );

  final String code;
  final String nameVi;
  final String nameEn;
  final int priceVnd;
  final String descriptionVi;
  final String descriptionEn;
  final String? appleProductId;
  final String? googleProductId;
  final String storeProductType;

  bool get isFree => code == 'free' || priceVnd == 0;
  bool get isConsumableStoreProduct => storeProductType == 'consumable';
}

String? _optionalString(Object? value) {
  final text = value?.toString().trim();
  return text == null || text.isEmpty ? null : text;
}
