import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';
import 'package:everlms/feature/root/data/models/common/my_course_offline.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MyCourseWidgetItem extends ConsumerStatefulWidget {
  const MyCourseWidgetItem({
    required this.course,
    required this.offlineCourse,
    required this.url,
    super.key,
  });
  final LMSMyCourseModel course;
  final List<MyCourseOffline> offlineCourse;
  final String url;

  @override
  ConsumerState<MyCourseWidgetItem> createState() => _MyCourseWidgetItemState();
}

class _MyCourseWidgetItemState extends ConsumerState<MyCourseWidgetItem> {
  String status = '';
  bool isDownloaded = false;
  @override
  void initState() {
    isDownloaded = widget.offlineCourse
        .where((e) => e.title == widget.course.courseName)
        .isNotEmpty;
    super.initState();
  }

  void setStatus(String s) {
    setState(() {
      status = s;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
      child: InkWell(
        // onTap: () => Navigator.of(context).pushNamed(AppRoutes.learningScreen, arguments: course.id),
        onTap: () {
          context.push(
            AppRoutes.getCourseDetailsScreenRoute(),
            extra: Course.fromJson(
              widget.course.toJson(),
            ),
          );
        },
        radius: 6,
        child: Container(
          width: MediaQuery.of(context).size.width / 1.3,
          decoration: BoxDecoration(
            border: Border.all(
              color: Theme.of(context)
                  .textTheme
                  .bodyLarge!
                  .color!
                  .withValues(alpha: 0.06),
            ),
            borderRadius: const BorderRadius.all(Radius.circular(6)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Column(
              children: [
                Row(
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.all(Radius.circular(6)),
                      child: CustomImage(
                        width: 60,
                        height: 60,
                        image:
                            Config.baseUrl + (widget.course.fieldImage ?? ''),
                      ),
                    ),
                    const SizedBox(
                      width: Dimensions.paddingSizeSmall,
                    ),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.course.courseName ?? '',
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeDefault,
                            ),
                          ),
                          const SizedBox(
                            height: Dimensions.paddingSizeExtraSmall,
                          ),
                          Row(
                            children: [
                              Expanded(
                                flex: 3,
                                child: Text(
                                  '${widget.course.fieldTotalLessons} Lesson',
                                  style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeExtraSmall,
                                    color: Theme.of(context)
                                        .textTheme
                                        .bodyLarge!
                                        .color!
                                        .withValues(alpha: .5),
                                  ),
                                ),
                              ),
                              Expanded(
                                flex: 2,
                                child: Visibility(
                                  visible: widget.url != '',
                                  child: Text(
                                    status,
                                    style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeExtraSmall,
                                      color: Theme.of(context)
                                          .textTheme
                                          .bodyLarge!
                                          .color!
                                          .withValues(alpha: .5),
                                    ),
                                  ),
                                ),
                              ),
                              Builder(
                                builder: (context) {
                                  if (isDownloaded) {
                                    return Container();
                                  }
                                  return Expanded(
                                    child: IconButton(
                                      onPressed: () {
                                        setStatus('Downloading');
                                        ref
                                            .read(
                                              myCourseProvider.notifier,
                                            )
                                            .downloadAndExtractZip(
                                              widget.course,
                                            );
                                      },
                                      icon: const Icon(
                                        Icons.file_download_outlined,
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                // ClipRRect(
                //   borderRadius: const BorderRadius.all(Radius.circular(10)),
                //   child: LinearProgressIndicator(
                //     value: calculatePercentage(
                //         widget.course.completedPercentage ?? "0%"),
                //     valueColor: AlwaysStoppedAnimation<Color>(context.isDarkMode
                //         ? Colors.green.withValues(alpha: .5)
                //         : Theme.of(context).primaryColor.withValues(alpha: .5)),
                //     backgroundColor: context.isDarkMode
                //         ? Colors.green.withValues(alpha: .1)
                //         : Theme.of(context).primaryColor.withValues(alpha: .1),
                //   ),
                // )
              ],
            ),
          ),
        ),
      ),
    );
  }

  double calculatePercentage(String percentage) {
    return double.parse(percentage.replaceAll('%', '')) / 100;
  }
}
