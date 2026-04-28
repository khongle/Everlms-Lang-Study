import 'dart:async';
import 'dart:io';

import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/lottie_files.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/core/widgets/loading_dialog.dart';
import 'package:everlms/feature/cart/data/models/calculations.dart';
import 'package:everlms/feature/cart/presentation/providers/cart_provider.dart';
import 'package:everlms/feature/cart/presentation/widgets/cart_item.dart';
import 'package:everlms/feature/cart/presentation/widgets/cart_purchase_buttons.dart';
import 'package:everlms/feature/cart/presentation/widgets/complete_dialog.dart';
import 'package:everlms/feature/cart/services/consumable_store.dart';
import 'package:everlms/feature/cheek_out/presentation/providers/payment_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_stripe/flutter_stripe.dart' as stripe;
import 'package:go_router/go_router.dart';
import 'package:in_app_purchase/in_app_purchase.dart';
import 'package:in_app_purchase_storekit/in_app_purchase_storekit.dart';
import 'package:in_app_purchase_storekit/store_kit_wrappers.dart';
import 'package:paypal_payment/paypal_payment.dart';

// Auto-consume must be true on iOS.
// To try without auto-consume on another platform, change `true` to `false` here.

const String _kConsumableId = 'consumable';

class CartScreen extends ConsumerStatefulWidget {
  const CartScreen({this.courseArg, super.key});
  final Course? courseArg;

  @override
  ConsumerState<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends ConsumerState<CartScreen> {
  final InAppPurchase _inAppPurchase = InAppPurchase.instance;
  late StreamSubscription<List<PurchaseDetails>> _subscription;
  List<ProductDetails> _products = <ProductDetails>[];
  List<PurchaseDetails> _purchases = <PurchaseDetails>[];
  String? _queryProductError;
  final List<String> _kProductIds = [];

  Course? course;

  @override
  void initState() {
    super.initState();
    course = widget.courseArg;
    // FaqController.getFaqCourse was only used in commented-out code
    if (course != null) {
      _kProductIds.add(course!.orderId.toString());
    }
    final purchaseUpdated = _inAppPurchase.purchaseStream;
    _subscription = purchaseUpdated.listen(
      _listenToPurchaseUpdated,
      onDone: () {
        _subscription.cancel();
      },
      onError: (Object error) {
        // handle error here.
      },
    );
    initStoreInfo();
  }

  String convertCourseIdToProductId(String orderId) {
    return 'course_0';
  }

  Future<void> initStoreInfo() async {
    final isAvailable = await _inAppPurchase.isAvailable();
    if (!isAvailable) {
      setState(() {
        _products = <ProductDetails>[];
        _purchases = <PurchaseDetails>[];
      });
      return;
    }

    if (Platform.isIOS) {
      final iosPlatformAddition = _inAppPurchase
          .getPlatformAddition<InAppPurchaseStoreKitPlatformAddition>();
      await iosPlatformAddition.setDelegate(ExamplePaymentQueueDelegate());
    }
    final productId = _kProductIds.map(convertCourseIdToProductId).toList();
    final productDetailResponse =
        await _inAppPurchase.queryProductDetails(productId.toSet());

    if (productDetailResponse.error != null) {
      setState(() {
        _queryProductError = productDetailResponse.error!.message;
        _products = productDetailResponse.productDetails;
        _purchases = <PurchaseDetails>[];
      });
      return;
    }

    if (productDetailResponse.productDetails.isEmpty) {
      setState(() {
        _queryProductError = null;
        _products = productDetailResponse.productDetails;
        _purchases = <PurchaseDetails>[];
      });
      return;
    }

    setState(() {
      _products = productDetailResponse.productDetails;
    });
    if (kDebugMode) {
      debugPrint('consumables');
      debugPrint('$_products');
    }
  }

  Future<void> inAppPurchasePayment() async {
    for (final product in _products) {
      final purchaseParam = PurchaseParam(productDetails: product);
      final canMakePurchase = await InAppPurchase.instance.isAvailable();
      if (canMakePurchase) {
        _inAppPurchase.buyNonConsumable(purchaseParam: purchaseParam);
      } else {
        customSnackBar('In-app purchases are not available');
      }
    }
  }

  Future<void> deliverProduct(PurchaseDetails purchaseDetails) async {
    if (purchaseDetails.productID == _kConsumableId) {
      await ConsumableStore.save(purchaseDetails.purchaseID!);
    } else {
      _purchases.add(purchaseDetails);
      await updateCompletePurchase(context, 'APPLE');
    }
  }

  void handleError(IAPError error) {
    // Error handling for IAP - no UI state to update
  }

  Future<bool> _verifyPurchase(PurchaseDetails purchaseDetails) {
    // IMPORTANT!! Always verify a purchase before delivering the product.
    // For the purpose of an example, we directly return true.
    return Future<bool>.value(true);
  }

  void _handleInvalidPurchase(PurchaseDetails purchaseDetails) {
    // handle invalid purchase here if  _verifyPurchase` failed.
    return customSnackBar('You have not purchase this course before');
  }

  Future<void> _listenToPurchaseUpdated(
    List<PurchaseDetails> purchaseDetailsList,
  ) async {
    for (final purchaseDetails in purchaseDetailsList) {
      if (purchaseDetails.status == PurchaseStatus.pending) {
        // Purchase pending — IAP will update status when resolved
      } else {
        if (purchaseDetails.status == PurchaseStatus.error) {
          handleError(purchaseDetails.error!);
        } else if (purchaseDetails.status == PurchaseStatus.purchased ||
            purchaseDetails.status == PurchaseStatus.restored) {
          final valid = await _verifyPurchase(purchaseDetails);
          if (valid) {
            unawaited(deliverProduct(purchaseDetails));
          } else {
            _handleInvalidPurchase(purchaseDetails);
            return;
          }
        }
        if (purchaseDetails.pendingCompletePurchase) {
          await _inAppPurchase.completePurchase(purchaseDetails);
        }
      }
    }
    return;
  }

  @override
  void dispose() {
    super.dispose();
    if (Platform.isIOS) {
      final iosPlatformAddition = _inAppPurchase
          .getPlatformAddition<InAppPurchaseStoreKitPlatformAddition>();
      iosPlatformAddition.setDelegate(null);
    }
    _subscription.cancel();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: 'Enroll',
        onBackPressed: () {
          context.pop();
          ref.read(cartProvider.notifier).emptyCart();
        },
      ),
      body: _queryProductError != null
          ? Center(child: Text(_queryProductError!))
          : listProduct(),
      bottomNavigationBar: CartPurchaseButtons(
        onPayPal: (calculations, courses) =>
            payPalPayment(context, calculations, courses),
        onStripe: (calculations, courses) =>
            initPaymentSheet(context, calculations, courses),
        onInAppPurchase: inAppPurchasePayment,
        onRestorePurchase: _inAppPurchase.restorePurchases,
      ),
    );
  }

  Widget listProduct() {
    // custom
    return Consumer(
      builder: (context, ref, child) {
        final cartState =
            ref.watch(cartProvider).valueOrNull ?? const CartState();
        return SingleChildScrollView(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Column(
            children: [
              ListView.builder(
                shrinkWrap: true,
                physics: const ScrollPhysics(),
                itemCount: cartState.cartList.length,
                padding: const EdgeInsets.symmetric(
                  vertical: Dimensions.paddingSizeDefault,
                ),
                itemBuilder: (context, index) {
                  return CartItem(
                    cart: cartState.cartList[index],
                    variation: null, // variations not used in UI
                    index: index,
                    course: course!,
                  );
                },
              ),
            ],
          ),
        );
      },
    );
  }

  void payPalPayment(
    BuildContext context,
    Calculations? calculations,
    List<Course> listCourse,
  ) {
    final totalPayable =
        double.tryParse(calculations?.totalPayable ?? '0') ?? 0.0;
    final formattedTotalPayable = totalPayable.toStringAsFixed(2);
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => PaypalOrderPayment(
          amount: formattedTotalPayable,
          currencyCode: calculations?.currencyCode ?? 'USD',
          clientId: "${dotenv.env['PAYPAL_CLIENT_ID']}",
          secretKey: "${dotenv.env['PAYPAL_SECRET_KEY']}",
          returnURL: '${Config.baseUrl}payment/success',
          cancelURL: '${Config.baseUrl}payment/cancel',
          note: 'Contact us for any questions on your order.',
          onSuccess: (Map params) async {
            await updateCompletePurchase(context, 'APPLE');
          },
          onError: (error) {
            if (kDebugMode) {
              debugPrint('onError: $error');
            }
          },
          onCancel: (params) {
            if (kDebugMode) {
              debugPrint('cancelled: $params');
            }
          },
        ),
      ),
    );
  }

  Future<void> initPaymentSheet(
    BuildContext context,
    Calculations? calculations,
    List<Course> listCourse,
  ) async {
    final totalPayable =
        (double.tryParse(calculations?.totalPayable ?? '0') ?? 0.0) * 100;
    final intent =
        await ref.read(paymentProvider.notifier).createStripePaymentIntent(
              amount: totalPayable.toInt(),
              currency: 'usd',
              secretKey: dotenv.env['STRIPE_SECRET_KEY'] ?? '',
            );

    await stripe.Stripe.instance.initPaymentSheet(
      paymentSheetParameters: stripe.SetupPaymentSheetParameters(
        merchantDisplayName: 'Flutter Stripe Store Demo',
        paymentIntentClientSecret: intent.clientSecret,
        customerEphemeralKeySecret: intent.ephemeralKey,
        customerId: intent.customer,
        style: ThemeMode.dark,
      ),
    );
    await stripe.Stripe.instance.presentPaymentSheet();
    if (!context.mounted) return;
    await updateCompletePurchase(context, 'APPLE');
  }

  Future<void> updateCompletePurchase(BuildContext context, String type) async {
    if (!context.mounted) return;
    LoadingDialog.show(context);
    // change to membership
    final checkBoughtMembership = await ref
        .read(cartProvider.notifier)
        .updateCompleteCart(typePayment: 'APPLE');
    if (!context.mounted) return;
    if (checkBoughtMembership) {
      context.go(AppRoutes.getMainRoute(AppRoutes.splash));
      customSnackBar('Change to membership', isError: false);
    }
    if (!context.mounted) return;
    LoadingDialog.hide(context);
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => CompleteDialog(
        lottie: LottieFiles.successCheck,
        title: 'Enrollment Completed',
        description:
            'You now have full access to this course.\nHappy learning!',
      ),
    ).then((_) {
      if (context.mounted) context.pop();
    });
  }
}

class ExamplePaymentQueueDelegate implements SKPaymentQueueDelegateWrapper {
  @override
  bool shouldContinueTransaction(
    SKPaymentTransactionWrapper transaction,
    SKStorefrontWrapper storefront,
  ) {
    return true;
  }

  @override
  bool shouldShowPriceConsent() {
    return false;
  }
}
