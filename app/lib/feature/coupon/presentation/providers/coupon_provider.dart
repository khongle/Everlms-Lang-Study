import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/coupon/data/models/coupon.dart';
import 'package:everlms/feature/coupon/data/repositories/coupon_repository.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final couponRepositoryProvider = Provider<CouponRepository>((ref) {
  return CouponRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CouponState {
  const CouponState({this.coupons = const []});

  final List<Coupon> coupons;

  CouponState copyWith({List<Coupon>? coupons}) {
    return CouponState(coupons: coupons ?? this.coupons);
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class CouponNotifier extends AsyncNotifier<CouponState> {
  @override
  Future<CouponState> build() async {
    final repo = ref.read(couponRepositoryProvider);
    final result = await repo.getCouponList();
    return CouponState(coupons: result?.data?.coupons ?? []);
  }

  Future<void> copyCodeToClipBoard(String code) async {
    await Clipboard.setData(ClipboardData(text: code));
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final couponProvider =
    AsyncNotifierProvider<CouponNotifier, CouponState>(CouponNotifier.new);
