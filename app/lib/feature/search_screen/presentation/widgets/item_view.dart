import 'package:everlms/core/widgets/course_view_verticle.dart';
import 'package:everlms/feature/root/presentation/screens/no_data_screen.dart';
import 'package:everlms/feature/search_screen/presentation/providers/search_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ItemView extends ConsumerWidget {
  const ItemView({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final searchState = ref.watch(searchProvider).valueOrNull;
    if (searchState == null) return const SizedBox.shrink();

    final results = searchState.searchResults;
    if (results != null && results.isNotEmpty) {
      return Center(
        child: SizedBox(
          width: double.infinity,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ServiceViewVertical(
                service: results,
                noDataType: NoDataType.search,
              ),
            ],
          ),
        ),
      );
    }
    return const SizedBox.shrink();
  }
}
