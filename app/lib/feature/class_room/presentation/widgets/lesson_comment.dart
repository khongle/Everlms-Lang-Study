import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/create_comment_widget.dart';
import 'package:everlms/core/widgets/list_comment_widget.dart';
import 'package:everlms/feature/comment/presentation/providers/comment_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CommentLesson extends ConsumerStatefulWidget {
  const CommentLesson({required this.lessonId, super.key});
  final String? lessonId;

  @override
  ConsumerState<CommentLesson> createState() => _CommentLessonState();
}

class _CommentLessonState extends ConsumerState<CommentLesson> {
  final _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _focusNode.requestFocus();
      ref
          .read(commentProvider.notifier)
          .getCommentLesson(widget.lessonId ?? '');
    });
  }

  @override
  Widget build(BuildContext context) {
    return bottomSheetComment();
  }

  Widget bottomSheetComment() {
    return DraggableScrollableSheet(
      initialChildSize: 0.8,
      maxChildSize: 0.8,
      minChildSize: 0.65,
      builder: (context, scrollController) => Container(
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(30),
            topRight: Radius.circular(30),
          ),
        ),
        child: Column(
          children: [
            Container(
              width: 40,
              height: 3,
              decoration: BoxDecoration(
                color: Theme.of(context).dividerColor,
                borderRadius:
                    BorderRadius.circular(Dimensions.radiusExtraMoreLarge),
              ),
              margin: const EdgeInsets.only(top: Dimensions.paddingSizeSmall),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeSmall,
            ),
            Expanded(
              child: Builder(
                builder: (context) {
                  final commentState = ref.watch(commentProvider).valueOrNull;
                  return Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeSmall,
                    ),
                    child: ListCommentWidget(
                      list: commentState?.lessonComments ?? [],
                      scrollController: scrollController,
                    ),
                  );
                },
              ),
            ),
            Divider(
              height: 0,
              thickness: 0.5,
              color: Theme.of(context).dividerColor.withValues(alpha: 0.6),
            ),
            CreateCommentWidget(
              entityId: widget.lessonId,
              type: CommentType.lesson,
            ),
          ],
        ),
      ),
    );
  }
}
