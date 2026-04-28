import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_image.dart';
import 'package:everlms/core/widgets/label_widget.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';

class MyCoursesWidget extends StatefulWidget {
  const MyCoursesWidget({required this.course, super.key, this.label});
  final Course? course;
  final String? label;

  @override
  State<MyCoursesWidget> createState() => _MyCoursesWidgetState();
}

class _MyCoursesWidgetState extends State<MyCoursesWidget> {
  String? imagePath;
  @override
  void initState() {
    imagePath = widget.course?.fieldImage;
    super.initState();
  }

  bool get isStart => widget.course?.isStart ?? false;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        thumbnailCourse(context),
        Flexible(
          child: infoCourse(context),
        ),
      ],
    );
  }

  Padding infoCourse(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: Dimensions.paddingSizeDefault),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            widget.course?.fieldCategory ?? '',
            style: Theme.of(context)
                .textTheme
                .labelSmall!
                .copyWith(color: Theme.of(context).primaryColor),
          ),
          Text(
            widget.course?.title ?? '',
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.start,
            style: Theme.of(context).textTheme.titleSmall,
          ),
          if (isStart)
            progressCourse(context, widget.course)
          else
            const SizedBox.shrink(),
        ],
      ),
    );
  }

  ClipRRect thumbnailCourse(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Stack(
        alignment: Alignment.topLeft,
        children: [
          CustomImage(
            image: imagePath,
            height: 100,
            width: MediaQuery.of(context).size.width / 2,
          ),
          if (isStart)
            const SizedBox.shrink()
          else
            const LabelWidget(label: 'start now'),
        ],
      ),
    );
  }

  Widget progressCourse(BuildContext context, Course? course) {
    final progress = course?.progress ?? 0.0;
    return Flexible(
      child: Row(
        spacing: Dimensions.paddingSizeDefault,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Flexible(
            child: LinearProgressIndicator(
              value: progress,
              backgroundColor: Theme.of(context)
                  .colorScheme
                  .onSurface
                  .withValues(alpha: 0.06),
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            ),
          ),
          Text(
            '${(progress * 100).toInt()}%',
            style: Theme.of(context).textTheme.bodyMedium,
          ),
        ],
      ),
    );
  }
}
