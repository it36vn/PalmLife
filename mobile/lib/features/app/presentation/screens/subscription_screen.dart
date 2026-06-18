import 'package:flutter/material.dart';

import '../../../../core/i18n/app_strings.dart';
import '../../domain/app_models.dart';
import '../app_controller.dart';
import '../widgets/app_scaffold.dart';

class SubscriptionScreen extends StatelessWidget {
  const SubscriptionScreen({super.key, required this.controller});

  static const routeName = '/subscriptions';
  final AppController controller;

  @override
  Widget build(BuildContext context) {
    final s = AppStrings.of(controller.locale);
    final currency = controller.locale == 'en' ? 'VND' : 'đ';
    final upgradePlans = controller.plans
        .where((plan) => !plan.isFree)
        .toList();

    return AppScaffold(
      title: s.subscription,
      children: [
        Text(s.legalShort),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: controller.isBusy ? null : controller.restorePurchases,
          icon: const Icon(Icons.restore),
          label: Text(
            controller.locale == 'en'
                ? 'Restore purchases'
                : 'Khôi phục mua hàng',
          ),
        ),
        const SizedBox(height: 16),
        for (final plan in upgradePlans) ...[
          Card(
            child: ListTile(
              title: Text(
                controller.locale == 'en' ? plan.nameEn : plan.nameVi,
              ),
              subtitle: Text(_subtitleFor(plan)),
              trailing: Text(
                _storePriceFor(plan) ??
                    (plan.priceVnd == 0
                        ? '0 $currency'
                        : '${_format(plan.priceVnd)} $currency'),
              ),
              onTap: controller.isBusy ? null : () => controller.buyPlan(plan),
            ),
          ),
          const SizedBox(height: 8),
        ],
        if (controller.message != null)
          Text(
            controller.message!,
            style: TextStyle(color: Theme.of(context).colorScheme.primary),
          ),
        if (controller.error != null)
          Text(
            controller.error!,
            style: TextStyle(color: Theme.of(context).colorScheme.error),
          ),
      ],
    );
  }

  String _subtitleFor(SubscriptionPlan plan) {
    final base = controller.locale == 'en'
        ? plan.descriptionEn
        : plan.descriptionVi;
    if (plan.isFree || _storePriceFor(plan) != null) return base;

    final note = controller.locale == 'en'
        ? 'Create this product in App Store Connect and Google Play Console.'
        : 'Cần tạo product này trong App Store Connect và Google Play Console.';
    return '$base\n$note';
  }

  String? _storePriceFor(SubscriptionPlan plan) {
    final productId = plan.appleProductId ?? plan.googleProductId;
    if (productId == null) return null;
    return controller.storeProducts[productId]?.price;
  }

  String _format(int value) {
    final text = value.toString();
    final buffer = StringBuffer();
    for (var i = 0; i < text.length; i++) {
      if (i > 0 && (text.length - i) % 3 == 0) buffer.write('.');
      buffer.write(text[i]);
    }
    return buffer.toString();
  }
}
