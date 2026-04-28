import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/helper.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/comment/data/models/comment_model.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class ListCommentWidget extends ConsumerStatefulWidget {
  const ListCommentWidget({
    required this.list,
    super.key,
    this.isScroll = true,
    this.scrollController,
  });
  final List<CommentModel>? list;
  final bool isScroll;
  final ScrollController? scrollController;

  @override
  ConsumerState<ListCommentWidget> createState() => _ListCommentWidgetState();
}

class _ListCommentWidgetState extends ConsumerState<ListCommentWidget> {
  final editCommentController = TextEditingController();

  int? editCommentIndex;

  @override
  Widget build(BuildContext context) {
    if (widget.list!.isEmpty) {
      return Center(
        child: Text(tl(context).no_comments_yet),
      );
    }
    return ListView.builder(
      shrinkWrap: true,
      itemCount: widget.list?.length ?? 0,
      controller: widget.scrollController,
      physics: widget.isScroll
          ? const AlwaysScrollableScrollPhysics()
          : const NeverScrollableScrollPhysics(),
      itemBuilder: (context, index) {
        final item = widget.list?[index];
        final urlImage = item?.commenter?.image;
        return commentItem(item, context, index, urlImage);
      },
    );
  }

  Widget editCommentItem() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        CustomTextField(
          controller: editCommentController,
          isAutoFocus: true,
          maxLines: null,
          fillColor: Colors.grey.shade200,
        ),
        TextButton(
          onPressed: () {
            setState(() {
              editCommentIndex = -1;
            });
          },
          child: Text(tl(context).cancel),
        ),
      ],
    );
  }

  Widget commentItem(
    CommentModel? item,
    BuildContext context,
    int index,
    String? urlImage,
  ) {
    return ListTile(
      minLeadingWidth: 0,
      contentPadding: EdgeInsets.zero,
      horizontalTitleGap: Dimensions.paddingSizeSmall,
      isThreeLine: true,
      leading: urlImage != null && urlImage.isNotEmpty
          ? CircleAvatar(
              backgroundColor: Colors.grey.shade200,
              radius: Dimensions.paddingSizeExtraLarge,
              backgroundImage: NetworkImage(
                urlImage,
                headers: {
                  'Authorization': ref.read(apiClientProvider).auth ?? '',
                },
              ),
            )
          : CircleAvatar(
              backgroundColor: Colors.grey.shade200,
              radius: Dimensions.paddingSizeLarge,
              child: Transform.scale(
                scale: 1.5,
                child: SvgPicture.asset(
                  Images.profile,
                  colorFilter:
                      ColorFilter.mode(Colors.grey.shade900, BlendMode.srcIn),
                ),
              ),
            ),
      title: Row(
        children: [
          Text(
            item?.commenter?.name ?? '',
            style: Theme.of(context).textTheme.labelLarge,
          ),
          const SizedBox(
            width: Dimensions.paddingSizeDefault,
          ),
          Text(
            formatTimeAgoComment(item?.created ?? ''),
            style: Theme.of(context).textTheme.labelSmall,
          ),
        ],
      ),
      subtitle: Html(
        data: item?.body ?? '',
        shrinkWrap: true,
        style: {
          '*': Style(margin: Margins.all(0)), // zero margin to all
          'p': Style(
            fontSize: FontSize(Dimensions.fontSizeDefault),
          ),
          'img': Style(height: Height(120)),
        },
      ),
    );
  }
}
