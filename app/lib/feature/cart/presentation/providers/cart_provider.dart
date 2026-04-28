import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/cart/data/models/calculations.dart';
import 'package:everlms/feature/cart/data/models/cart.dart';
import 'package:everlms/feature/cart/data/models/cart_list.dart';
import 'package:everlms/feature/cart/data/repositories/cart_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final cartRepositoryProvider = Provider<CartRepository>((ref) {
  return CartRepository(
    ref.watch(apiClientProvider),
    ref.watch(sharedPreferencesProvider),
  );
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class CartState {
  const CartState({
    this.cartList = const [],
    this.listCourse = const [],
    this.calculations,
    this.isLoading = false,
    this.isApplyCouponLoading = false,
    this.isDeletingFromCart = false,
    this.deletingCartIndex = -1,
    this.selectedCartItemIndex = -1,
  });

  final List<Cart> cartList;
  final List<Course> listCourse;
  final Calculations? calculations;
  final bool isLoading;
  final bool isApplyCouponLoading;
  final bool isDeletingFromCart;
  final int deletingCartIndex;
  final int selectedCartItemIndex;

  CartState copyWith({
    List<Cart>? cartList,
    List<Course>? listCourse,
    Calculations? calculations,
    bool? isLoading,
    bool? isApplyCouponLoading,
    bool? isDeletingFromCart,
    int? deletingCartIndex,
    int? selectedCartItemIndex,
    bool clearCalculations = false,
  }) {
    return CartState(
      cartList: cartList ?? this.cartList,
      listCourse: listCourse ?? this.listCourse,
      calculations:
          clearCalculations ? null : (calculations ?? this.calculations),
      isLoading: isLoading ?? this.isLoading,
      isApplyCouponLoading: isApplyCouponLoading ?? this.isApplyCouponLoading,
      isDeletingFromCart: isDeletingFromCart ?? this.isDeletingFromCart,
      deletingCartIndex: deletingCartIndex ?? this.deletingCartIndex,
      selectedCartItemIndex:
          selectedCartItemIndex ?? this.selectedCartItemIndex,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class CartNotifier extends AsyncNotifier<CartState> {
  @override
  Future<CartState> build() async {
    return const CartState();
  }

  CartRepository get _repo => ref.read(cartRepositoryProvider);

  Future<void> getCartList() async {
    final current = state.valueOrNull ?? const CartState();
    state = AsyncData(current.copyWith(isLoading: true));

    final response = await _repo.getCartList();
    if (response != null && response.statusCode == 200) {
      final cartListModel =
          CartList.fromJson(response.body as Map<String, dynamic>);
      state = AsyncData(
        current.copyWith(
          cartList: cartListModel.data?.carts ?? [],
          listCourse: cartListModel.data?.listCourse ?? [],
          calculations: cartListModel.data?.calculations,
          isLoading: false,
        ),
      );
    } else {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }

  Future<void> addToCart(int id) async {
    await _repo.addToCart(id);
    await getCartList();
  }

  Future<void> removeFromCart({
    required int orderItemId,
    int index = -1,
  }) async {
    final current = state.valueOrNull ?? const CartState();
    state = AsyncData(
      current.copyWith(
        isDeletingFromCart: true,
        deletingCartIndex: index,
      ),
    );

    await _repo.removeFromCart(orderItemId);
    await getCartList();
  }

  Future<void> emptyCart() async {
    await _repo.emptyCart();
    final current = state.valueOrNull ?? const CartState();
    state = AsyncData(
      current.copyWith(
        cartList: [],
        listCourse: [],
        clearCalculations: true,
      ),
    );
  }

  Future<bool> updateCompleteCart({required String typePayment}) async {
    final response = await _repo.updateCompleteCart(typePayment);
    if (response != null && response.statusCode == 200) {
      await getCartList();
      return true;
    }
    return false;
  }

  Future<void> applyCoupon({
    required String code,
    required String type,
    required String id,
  }) async {
    final current = state.valueOrNull ?? const CartState();
    state = AsyncData(current.copyWith(isApplyCouponLoading: true));

    await _repo.applyCoupon(code: code, type: type, id: id);
    await getCartList();
  }

  bool isCartListContainCourseId(int id) {
    final carts = state.valueOrNull?.cartList ?? [];
    return carts.any((cart) => cart.courseId == id);
  }

  void selectCartItem(int index) {
    final current = state.valueOrNull ?? const CartState();
    state = AsyncData(current.copyWith(selectedCartItemIndex: index));
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final cartProvider = AsyncNotifierProvider<CartNotifier, CartState>(
  CartNotifier.new,
);
