import 'package:everlms/feature/splash/data/models/config_model.dart';
import 'package:everlms/feature/splash/presentation/providers/splash_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final pagesContentProvider = FutureProvider<Pages?>((ref) async {
  final repo = ref.read(splashRepositoryProvider);
  final response = await repo.getConfigData();
  if (response.statusCode == 200) {
    return Pages.fromJson(response.body['data']);
  }
  return null;
});
