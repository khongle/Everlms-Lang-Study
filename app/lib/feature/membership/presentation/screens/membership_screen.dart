import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/membership/presentation/providers/membership_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MembershipScreen extends ConsumerWidget {
  const MembershipScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncState = ref.watch(membershipProvider);

    return Scaffold(
      appBar: CustomAppBar(title: tl(context).membership),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (err, _) => Center(child: Text(err.toString())),
        data: (state) => _membershipList(context, state, ref),
      ),
    );
  }

  Widget _membershipList(
    BuildContext context,
    MembershipState state,
    WidgetRef ref,
  ) {
    return ListView.builder(
      shrinkWrap: true,
      itemCount: state.memberships.length,
      itemBuilder: (context, index) {
        final item = state.memberships[index];
        return Container(
          margin: const EdgeInsets.only(
            left: 16,
            right: 16,
            top: 8,
            bottom: 8,
          ),
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(10),
            boxShadow: [
              BoxShadow(
                color: Theme.of(context).shadowColor,
                blurRadius: 10,
                offset: const Offset(0, 5),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item.title ?? '',
                style: robotoBold.copyWith(
                  color: Theme.of(context).textTheme.headlineSmall!.color,
                  fontSize: 26,
                ),
              ),
              Text(
                item.fieldSubtitle ?? '',
                style: robotoBold.copyWith(
                  color: Theme.of(context).textTheme.displaySmall!.color,
                  fontWeight: FontWeight.normal,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                item.priceNumber ?? '',
                style: robotoBold.copyWith(
                  color: Theme.of(context).primaryColor,
                  fontSize: 30,
                ),
              ),
              Text(
                item.listPriceNumber ?? '',
                style: robotoBold.copyWith(
                  color: Theme.of(context).primaryColor,
                  fontSize: 20,
                  decoration: TextDecoration.lineThrough,
                ),
              ),
              CustomButton(
                buttonText: 'Buy Now',
                onPressed: () async {
                  LoadingDialog.show(context);
                  // Bridge: CartController migrated to cartProvider
                  await ref.read(cartProvider.notifier).addToCart(
                        int.tryParse(item.productId ?? '') ?? 0,
                      );
                  if (!context.mounted) return;
                  await context.push(AppRoutes.cart);
                  if (!context.mounted) return;
                  LoadingDialog.hide(context);
                },
              ),
              Divider(
                indent: 10,
                endIndent: 10,
                color: Theme.of(context).dividerColor,
              ),
              Html(data: item.body),
            ],
          ),
        );
      },
    );
  }
}
