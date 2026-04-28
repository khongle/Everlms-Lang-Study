import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/feature/class_room/presentation/providers/classroom_provider.dart';
import 'package:everlms/feature/class_room/presentation/widgets/lesson_comment.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';

PopupMenuItem fullScreenButton(BuildContext context, WidgetRef ref) {
  final isFullScreen =
      ref.read(classroomProvider).valueOrNull?.isFullScreen ?? false;
  return platformButton(
    context,
    isFullScreen ? 'Exit full screen' : 'Full screen',
    isFullScreen ? Images.minimize : Images.maximize,
    ref.read(classroomProvider.notifier).toggleFullScreen,
  );
}

PopupMenuItem openExternal(BuildContext context, WidgetRef ref, String url) {
  return platformButton(context, 'Open with external', Images.externalLink,
      () async {
    await ref.read(classroomProvider.notifier).openDocUrl(url);
  });
}

PopupMenuItem downloadFileButton(BuildContext context, String url) {
  final uri = url.split('/').last;
  final fileName = Uri.decodeFull(uri);
  return platformButton(context, tl(context).download, Images.download, () {
    downloadAndSaveFile(url, fileName);
  });
}

Widget lessonAppBarAction(
  BuildContext context,
  WidgetRef ref,
  Lesson lesson, {
  required bool isOnline,
}) {
  final listButton = <PopupMenuItem>[];
  if (lesson.type == LessonType.scorm.value) {
    listButton.add(fullScreenButton(context, ref));
  } else if (lesson.type == LessonType.microsoftDocs.value) {
    listButton.add(
      openExternal(context, ref, lesson.urlMicrosoftDocs),
    );
  } else if (lesson.type == LessonType.pdf.value) {
    listButton.addAll([
      fullScreenButton(context, ref),
      if (isOnline)
        downloadFileButton(
          context,
          lesson.urlPdf,
        ),
      openExternal(context, ref, lesson.urlPdf),
    ]);
  } else {
    listButton.add(fullScreenButton(context, ref));
  }

  listButton.add(commentButton(context, lesson.id.toString()));

  return Padding(
    padding: const EdgeInsets.only(right: Dimensions.paddingSizeRadius),
    child: PopupMenuButton(
      itemBuilder: (context) => listButton,
      position: PopupMenuPosition.under,
      constraints: const BoxConstraints(maxWidth: 200),
      offset: const Offset(0, 10),
      color: Theme.of(context).brightness == Brightness.dark
          ? Colors.grey.shade900
          : Colors.grey.shade100,
      icon: const Icon(Icons.menu),
    ),
  );
}

PopupMenuItem<dynamic> commentButton(BuildContext context, String lessonID) {
  return platformButton(
    context,
    tl(context).comments,
    Images.messageCircleMore,
    () {
      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        backgroundColor: Colors.transparent,
        builder: (context) {
          return CommentLesson(
            lessonId: lessonID,
          );
        },
      );
    },
  );
}

PopupMenuItem platformButton(
  BuildContext context,
  String title,
  String icon,
  VoidCallback onTap,
) {
  return PopupMenuItem(
    onTap: onTap,
    child: Row(
      spacing: Dimensions.paddingSizeSmall,
      children: [
        SvgPicture.asset(
          icon,
          height: Dimensions.iconSizeLarge,
          colorFilter: ColorFilter.mode(
            Theme.of(context).primaryColor,
            BlendMode.srcIn,
          ),
        ),
        Text(title),
      ],
    ),
  );
}

Widget noAvailableWidget(String name) {
  return Builder(
    builder: (context) => Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        const Icon(
          Icons.cloud_off,
          size: 50,
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
        Text(
          '$name is only available in online mode',
          style: Theme.of(context).textTheme.titleSmall,
        ),
      ],
    ),
  );
}
