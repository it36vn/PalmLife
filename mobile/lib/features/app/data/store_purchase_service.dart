import 'dart:io';

import 'package:in_app_purchase/in_app_purchase.dart';

import '../domain/app_models.dart';

class StorePurchaseService {
  StorePurchaseService({InAppPurchase? inAppPurchase})
    : _iap = inAppPurchase ?? InAppPurchase.instance;

  final InAppPurchase _iap;
  Map<String, ProductDetails> _products = {};

  Stream<List<PurchaseDetails>> get purchaseStream => _iap.purchaseStream;

  String get platform => Platform.isIOS ? 'ios' : 'android';

  String? productIdFor(SubscriptionPlan plan) =>
      Platform.isIOS ? plan.appleProductId : plan.googleProductId;

  Future<Map<String, StoreProduct>> loadProducts(
    List<SubscriptionPlan> plans,
  ) async {
    final available = await _iap.isAvailable();
    if (!available) return {};

    final ids = plans
        .where((plan) => !plan.isFree)
        .map(productIdFor)
        .whereType<String>()
        .toSet();
    if (ids.isEmpty) return {};

    final response = await _iap.queryProductDetails(ids);
    _products = {
      for (final product in response.productDetails) product.id: product,
    };

    return {
      for (final product in response.productDetails)
        product.id: StoreProduct(
          id: product.id,
          title: product.title,
          description: product.description,
          price: product.price,
        ),
    };
  }

  Future<void> buy(SubscriptionPlan plan) async {
    final productId = productIdFor(plan);
    if (productId == null) {
      throw StateError('Missing store product id.');
    }
    final product = _products[productId];
    if (product == null) {
      throw StateError('Store product is not loaded.');
    }

    await _iap.buyNonConsumable(
      purchaseParam: PurchaseParam(productDetails: product),
    );
  }

  Future<void> restore() => _iap.restorePurchases();

  Future<void> complete(PurchaseDetails purchase) =>
      _iap.completePurchase(purchase);
}

class StoreProduct {
  const StoreProduct({
    required this.id,
    required this.title,
    required this.description,
    required this.price,
  });

  final String id;
  final String title;
  final String description;
  final String price;
}
