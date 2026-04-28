import 'dart:io';

import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:everlms/feature/root/data/models/common/my_course_offline.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class MyCourseOfflineWidgetItem extends StatelessWidget {
  const MyCourseOfflineWidgetItem({
    required this.course,
    super.key,
  });
  final MyCourseOffline course;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
      child: InkWell(
        onTap: () {
          // Future.delayed(const Duration(seconds: 20), () {
          //   _startLocalServer();
          // });
          // Navigator.of(context).push(MaterialPageRoute(
          //     builder: (_) => ScormViewer(
          //         scormContentPath: 'http://127.0.0.1:8080/${course.url}')));
          context.push(
            AppRoutes.getCourseDetailsOfflineRoute(),
            extra: Course.fromJson(course.course?.toJson() ?? {}),
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
                  .withValues(alpha: 0.6),
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
                      child: SizedBox(
                        height: 60,
                        width: 100,
                        child: Image.file(
                          File(course.thumbnail ?? ''),
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return const Icon(Icons.error);
                          },
                        ),
                      ),
                    ),
                    const SizedBox(
                      width: Dimensions.paddingSizeSmall,
                    ),
                    Flexible(
                      child: Text(
                        course.title ?? '',
                        style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeLarge,
                        ),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(
                      height: Dimensions.paddingSizeExtraSmall,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
