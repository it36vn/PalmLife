import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
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

    final ProductDetailsResponse response;
    try {
      response = await _iap.queryProductDetails(ids);
    } on PlatformException catch (exception) {
      _throwStorePurchaseException(
        'Store product query failed on $platform. '
        'Requested: ${ids.join(', ')}. '
        'Error: ${exception.code} ${exception.message ?? ''}',
      );
    }

    _products = {
      for (final product in response.productDetails) product.id: product,
    };

    if (response.notFoundIDs.isNotEmpty) {
      _debugStorePurchase(
        'Some store products were not found on $platform. '
        'Requested: ${ids.join(', ')}. '
        'Loaded: ${_products.keys.join(', ')}. '
        'Not found: ${response.notFoundIDs.join(', ')}.',
      );
    }

    if (response.error != null && response.productDetails.isNotEmpty) {
      _debugStorePurchase(
        'Store product query returned partial results on $platform. '
        'Requested: ${ids.join(', ')}. '
        'Loaded: ${_products.keys.join(', ')}. '
        'Error: ${response.error?.code ?? ''} ${response.error?.message ?? ''}',
      );
    }

    if (response.productDetails.isEmpty &&
        (response.error != null || response.notFoundIDs.isNotEmpty)) {
      _throwStorePurchaseException(
        'Store product query failed on $platform. '
        'Requested: ${ids.join(', ')}. '
        'Loaded: ${_products.keys.join(', ')}. '
        'Not found: ${response.notFoundIDs.join(', ')}. '
        'Error: ${response.error?.code ?? ''} ${response.error?.message ?? ''}',
      );
    }

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
      _throwStorePurchaseException(
        'Missing store product id for ${plan.code}.',
      );
    }
    var product = _products[productId];
    if (product == null) {
      await loadProducts([plan]);
      product = _products[productId];
    }
    if (product == null) {
      _throwStorePurchaseException('Store product is not loaded: $productId');
    }

    final purchaseParam = PurchaseParam(productDetails: product);
    if (plan.isConsumableStoreProduct) {
      await _iap.buyConsumable(purchaseParam: purchaseParam);
    } else {
      await _iap.buyNonConsumable(purchaseParam: purchaseParam);
    }
  }

  Future<void> restore() => _iap.restorePurchases();

  Future<void> complete(PurchaseDetails purchase) =>
      _iap.completePurchase(purchase);
}

void _debugStorePurchase(String debugMessage) {
  assert(() {
    debugPrint('[StorePurchaseService] $debugMessage');
    return true;
  }());
}

Never _throwStorePurchaseException(String debugMessage) {
  _debugStorePurchase(debugMessage);
  throw const StorePurchaseException();
}

class StorePurchaseException implements Exception {
  const StorePurchaseException();
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
