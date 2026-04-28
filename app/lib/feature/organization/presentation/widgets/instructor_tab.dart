import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/organization/presentation/providers/organization_provider.dart';
import 'package:everlms/feature/root/data/models/common/instructor_old.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class InstructorTab extends ConsumerWidget {
  const InstructorTab({required this.orgId, super.key});
  final int orgId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(organizationProvider(orgId)).valueOrNull;
    if (state == null) return const SizedBox.shrink();

    return NotificationListener<ScrollNotification>(
      onNotification: (scrollInfo) {
        if (scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
          ref.read(organizationProvider(orgId).notifier).loadMoreInstructors();
        }
        return false;
      },
      child: ListView.builder(
        itemCount: state.instructorList.length,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemBuilder: (context, index) {
          if (index == state.instructorList.length - 1 &&
              state.isInstructorLoading) {
            return const LoadingIndicator();
          }
          return _instructorItem(state.instructorList[index], context);
        },
      ),
    );
  }

  Widget _instructorItem(InstructorOld instructor, BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(8),
      child: InkWell(
        onTap: () => context.push(
          AppRoutes.instructorDetails,
          extra: instructor.id,
        ),
        child: SizedBox(
          height: 76,
          child: Row(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.all(
                  Radius.circular(Dimensions.radiusExtraMoreLarge),
                ),
                child: CustomImage(
                  image: instructor.image ?? '',
                  height: 50,
                  width: 50,
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeDefault),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    instructor.name ?? '',
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                    ),
                  ),
                  Text(
                    instructor.instructor ?? '',
                    style: robotoMedium.copyWith(
                      color: Theme.of(context).primaryColor,
                      fontSize: Dimensions.fontSizeExtraSmall,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
