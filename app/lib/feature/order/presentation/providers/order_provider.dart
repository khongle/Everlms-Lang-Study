import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/order/data/models/order_history.dart';
import 'package:everlms/feature/order/data/models/order_history_list.dart';
import 'package:everlms/feature/order/data/repositories/order_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final orderRepositoryProvider = Provider<OrderRepository>((ref) {
  return OrderRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
class OrderState {
  const OrderState({
    this.orderHistoryList = const [],
    this.currentPage = 1,
    this.isLoading = false,
  });

  final List<OrderHistory> orderHistoryList;
  final int currentPage;
  final bool isLoading;

  OrderState copyWith({
    List<OrderHistory>? orderHistoryList,
    int? currentPage,
    bool? isLoading,
  }) {
    return OrderState(
      orderHistoryList: orderHistoryList ?? this.orderHistoryList,
      currentPage: currentPage ?? this.currentPage,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------
class OrderNotifier extends AsyncNotifier<OrderState> {
  @override
  Future<OrderState> build() async {
    return const OrderState();
  }

  OrderRepository get _repo => ref.read(orderRepositoryProvider);

  Future<void> getOrderHistory(String type) async {
    final current = state.valueOrNull ?? const OrderState();
    state = AsyncData(current.copyWith(isLoading: true));

    final response = await _repo.getOrderHistory(current.currentPage, type);
    if (response != null && response.statusCode == 200) {
      final model =
          OrderHistoryList.fromJson(response.body as Map<String, dynamic>);
      state = AsyncData(
        current.copyWith(
          orderHistoryList: model.data?.orderHistories ?? [],
          isLoading: false,
        ),
      );
    } else {
      state = AsyncData(current.copyWith(isLoading: false));
    }
  }
}

// ---------------------------------------------------------------------------
// Provider
// ---------------------------------------------------------------------------
final orderProvider = AsyncNotifierProvider<OrderNotifier, OrderState>(
  OrderNotifier.new,
);
