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
    final hasAvailablePlan = upgradePlans.any(_hasStoreProduct);

    return AppScaffold(
      title: s.subscription,
      children: [
        Text(s.legalShort),
        if (hasAvailablePlan) ...[
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
        ],
        const SizedBox(height: 16),
        if (!hasAvailablePlan)
          SoftPanel(
            child: Text(
              s.noAvailablePlans,
              style: Theme.of(context).textTheme.bodyLarge,
              textAlign: TextAlign.center,
            ),
          ),
        if (hasAvailablePlan)
          for (final plan in upgradePlans) ...[
            Builder(
              builder: (context) {
                final hasStoreProduct = _hasStoreProduct(plan);
                final canTap = hasStoreProduct && !controller.isBusy;
                final textColor = hasStoreProduct
                    ? null
                    : Theme.of(context).disabledColor;

                return AbsorbPointer(
                  absorbing: !canTap,
                  child: Card(
                    child: ListTile(
                      enabled: canTap,
                      title: Text(
                        controller.locale == 'en' ? plan.nameEn : plan.nameVi,
                        style: TextStyle(color: textColor),
                      ),
                      subtitle: Text(
                        _subtitleFor(plan),
                        style: TextStyle(color: textColor),
                      ),
                      trailing: Text(
                        _storePriceFor(plan) ??
                            (plan.priceVnd == 0
                                ? '0 $currency'
                                : '${_format(plan.priceVnd)} $currency'),
                        style: TextStyle(color: textColor),
                      ),
                      onTap: canTap ? () => controller.buyPlan(plan) : null,
                    ),
                  ),
                );
              },
            ),
            const SizedBox(height: 8),
          ],
        if (controller.message != null)
          Text(
            controller.message!,
            style: TextStyle(color: Theme.of(context).colorScheme.primary),
          ),
        if (hasAvailablePlan && controller.error != null)
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
    if (plan.isFree || _hasStoreProduct(plan)) return base;

    final note = controller.locale == 'en'
        ? 'This plan is currently unavailable. Please try again later.'
        : 'Gói này hiện chưa thể mua. Vui lòng thử lại sau.';
    return '$base\n$note';
  }

  bool _hasStoreProduct(SubscriptionPlan plan) => _storePriceFor(plan) != null;

  String? _storePriceFor(SubscriptionPlan plan) {
    final productId = controller.storeProductIdFor(plan);
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
