import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';

class NetworkService {
  factory NetworkService() => _instance;

  NetworkService._internal();
  static final NetworkService _instance = NetworkService._internal();

  final Connectivity _connectivity = Connectivity();
  final StreamController<bool> _networkStatusController =
      StreamController<bool>.broadcast();

  Stream<bool> get networkStatusStream => _networkStatusController.stream;

  /// Optional callback invoked when connectivity is restored.
  /// Set from the app layer (e.g. a Riverpod provider) to handle
  /// offline-sync (forum topics, comments, quiz submissions).
  void Function()? onConnectivityRestored;

  void initialize() {
    _connectivity.onConnectivityChanged.listen((result) {
      final isConnected = result.any((item) => item != ConnectivityResult.none);
      _networkStatusController.add(isConnected);
      if (isConnected) {
        onConnectivityRestored?.call();
      }
    });
  }

  Future<bool> checkInternetConnection() async {
    final connectivityResult = await _connectivity.checkConnectivity();
    return connectivityResult.any((item) => item != ConnectivityResult.none);
  }

  void dispose() {
    _networkStatusController.close();
  }
}
