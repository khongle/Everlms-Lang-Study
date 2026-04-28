import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_default_dialog.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_switch/flutter_switch.dart';
import 'package:go_router/go_router.dart';

class MyLearningCoursesWidget extends ConsumerStatefulWidget {
  const MyLearningCoursesWidget({
    required this.myCourse,
    super.key,
  });
  final LMSMyCourseModel myCourse;

  @override
  ConsumerState<MyLearningCoursesWidget> createState() =>
      _MyLearningCoursesWidgetState();
}

class _MyLearningCoursesWidgetState
    extends ConsumerState<MyLearningCoursesWidget> {
  Future<void>? _actionFuture;

  void startDownloadOfflineCourse() {
    setState(() {
      _actionFuture = ref
          .read(myCourseProvider.notifier)
          .downloadAndExtractZip(widget.myCourse);
    });
  }

  void startRemoveOfflineCourse() {
    setState(() {
      _actionFuture = ref
          .read(myCourseProvider.notifier)
          .removeOfflineCourse(widget.myCourse);
    });
  }

  @override
  Widget build(BuildContext context) {
    final myCourseState = ref.watch(myCourseProvider).valueOrNull;

    return InkWell(
      onTap: () {
        context.push(
          AppRoutes.getCourseDetailsScreenRoute(),
          extra: {'course': Course.fromJson(widget.myCourse.toJson())},
        );
      },
      child: courseItem(widget.myCourse, myCourseState),
    );
  }

  Widget courseItem(LMSMyCourseModel myCourse, MyCourseState? myCourseState) {
    final progress = myCourse.progress ?? 0;
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        color: Theme.of(context).colorScheme.primaryContainer,
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Flexible(
            flex: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Flexible(
                  child: Text(
                    myCourse.courseName!,
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                ),
                const SizedBox(
                  height: Dimensions.paddingSizeSmall,
                ),
                Row(
                  spacing: Dimensions.paddingSizeDefault,
                  children: [
                    SizedBox(
                      width: 45,
                      height: 45,
                      child: CircularProgressIndicator(
                        strokeWidth: 5,
                        backgroundColor: Theme.of(context).highlightColor,
                        color: Theme.of(context).primaryColor,
                        value: progress,
                      ),
                    ),
                    Text(
                      '${(progress * 100).toInt()} %',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ],
                ),
                const SizedBox(
                  height: Dimensions.paddingSizeLarge,
                ),
                Row(
                  spacing: Dimensions.paddingSizeSmall,
                  children: [
                    FlutterSwitch(
                      value: (myCourseState?.downloadedCourseIDs ?? {})
                          .contains(myCourse.lmsUserCourseId),
                      height: 25,
                      width: 50,
                      padding: 2,
                      activeColor:
                          Theme.of(context).primaryColor.withValues(alpha: 0.6),
                      inactiveColor: Theme.of(context).highlightColor,
                      toggleColor: Theme.of(context).colorScheme.onSurface,
                      onToggle: handleActionCourse,
                    ),
                    Text(
                      tl(context).available_offline,
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ],
                ),
                FutureBuilder(
                  future: _actionFuture,
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      final progress =
                          ((myCourseState?.progressDownloadCourse ?? 0) * 100)
                              .toInt();
                      return Padding(
                        padding: const EdgeInsets.only(
                          left: Dimensions.paddingSizeSmall,
                          top: Dimensions.paddingSizeDefault,
                        ),
                        child: Row(
                          spacing: Dimensions.paddingSizeDefault,
                          children: [
                            Flexible(
                              child: SizedBox(
                                width: MediaQuery.of(context).size.width / 2,
                                child: LinearProgressIndicator(
                                  borderRadius: BorderRadius.circular(
                                    Dimensions.radiusDefault,
                                  ),
                                  backgroundColor:
                                      Theme.of(context).highlightColor,
                                  value:
                                      myCourseState?.progressDownloadCourse ??
                                          0,
                                ),
                              ),
                            ),
                            Text(
                              progress != 100 ? '$progress%' : 'Done',
                              style: Theme.of(context).textTheme.bodyMedium,
                            ),
                          ],
                        ),
                      );
                    }
                    return const SizedBox.shrink();
                  },
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Theme.of(context).highlightColor,
            ),
            child: const Icon(
              Icons.arrow_forward_ios_rounded,
            ),
          ),
        ],
      ),
    );
  }

  void handleActionCourse(bool toggle) {
    final isDownloading =
        ref.read(myCourseProvider).valueOrNull?.isDownloading ?? false;
    if (toggle) {
      if (isDownloading) {
        customSnackBar(tl(context).wait_a_moment, isError: false);
      } else {
        startDownloadOfflineCourse();
      }
    } else {
      customDialog(
        title: tl(context).remove_offline_course,
        imageSvg: Images.deleteAccount,
        middleTitle: tl(context).process_take_few_minutes,
        textConfirm: tl(context).remove,
        onConfirm: startRemoveOfflineCourse,
        context: context,
      );
    }
  }
}
