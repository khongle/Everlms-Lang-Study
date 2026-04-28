import 'dart:convert';
import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/comment/presentation/providers/comment_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path/path.dart' as p;
import 'package:permission_handler/permission_handler.dart';

class CreateCommentWidget extends ConsumerStatefulWidget {
  const CreateCommentWidget({
    required this.entityId,
    required this.type,
    super.key,
  });
  final String? entityId;
  final CommentType type;

  @override
  ConsumerState<CreateCommentWidget> createState() =>
      _CreateCommentWidgetState();
}

class _CreateCommentWidgetState extends ConsumerState<CreateCommentWidget> {
  String? imgHtml;
  final TextEditingController _commentController = TextEditingController();
  late FocusNode? _focusNode;
  bool isOnline = false;
  File? _selectImage;
  String? _commentData;

  @override
  void initState() {
    super.initState();
    _focusNode = FocusNode();
    _commentController.clear();
    checkNetwork();
  }

  Future<void> checkNetwork() async {
    final connectivityResult = await Connectivity().checkConnectivity();

    if (connectivityResult.contains(ConnectivityResult.mobile) ||
        connectivityResult.contains(ConnectivityResult.wifi)) {
      setState(() {
        isOnline = true;
      });
    }
  }

  @override
  void dispose() {
    _focusNode?.dispose();
    _commentController.dispose();
    super.dispose();
  }

  Future<String?> _pickImage(ImageSource source) async {
    if (source == ImageSource.camera) {
      await Permission.camera.status;
      await Permission.camera.request();
      if (!(await Permission.camera.isGranted)) {
        openAppSettings();
      }
    }
    final image = await ImagePicker().pickImage(source: source);
    if (image != null) {
      setState(() {
        _selectImage = File(image.path);
      });
      final imageHtml = await _convertFileImageToHtml(_selectImage);
      return imageHtml;
    }
    return null;
  }

  void _removePickImage() {
    setState(() {
      _selectImage = null;
    });
  }

  String? _handleDataComment({String? image, String comment = ''}) {
    if (comment.isNotEmpty && image != null) {
      return '<p>$comment</p>$image';
    } else if (comment.isNotEmpty) {
      return comment;
    } else if (image != null) {
      return image;
    }
    return null;
  }

  Future<String> _convertFileImageToHtml(File? file) async {
    if (file != null) {
      final imgExtension = p.extension(file.path).substring(1);
      final bytes = await file.readAsBytes();
      final base64String = base64.encode(bytes);
      return "<p><img src=data:image/$imgExtension;base64,$base64String alt=''></p>";
    }
    return '';
  }

  @override
  Widget build(BuildContext context) {
    return isOnline ? mainUI() : const SizedBox.shrink();
  }

  Widget mainUI() {
    return TapRegion(
      onTapOutside: (event) => _focusNode?.unfocus(),
      child: Container(
        color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.03),
        padding: EdgeInsets.only(
          left: Dimensions.paddingSizeDefault,
          right: Dimensions.paddingSizeDefault,
          top: Dimensions.paddingSizeSmall,
          bottom: MediaQuery.of(context).viewInsets.bottom > 0
              ? MediaQuery.of(context).viewInsets.bottom +
                  Dimensions.paddingSizeSmall
              : Dimensions.paddingSizeExtraMoreLarge, // handle keyboard
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (_selectImage != null)
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(
                      bottom: Dimensions.paddingSizeSmall,
                      right: Dimensions.paddingSizeMint,
                    ),
                    child: Image.file(
                      _selectImage!,
                      height: 100,
                    ),
                  ),
                  InkWell(
                    onTap: _removePickImage,
                    child: Container(
                      padding: const EdgeInsets.all(5),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                          width: 1.5,
                          color: Theme.of(context)
                              .colorScheme
                              .onSurface
                              .withValues(alpha: 0.6),
                        ),
                      ),
                      child: Icon(
                        Icons.clear,
                        size: Dimensions.iconSizeLarge,
                        color: Theme.of(context)
                            .colorScheme
                            .onSurface
                            .withValues(alpha: 0.6),
                      ),
                    ),
                  ),
                ],
              )
            else
              const SizedBox.shrink(),
            textFieldComment(),
          ],
        ),
      ),
    );
  }

  Widget textFieldComment() {
    return Row(
      spacing: Dimensions.paddingSizeMint,
      children: [
        Expanded(
          child: CustomTextField(
            controller: _commentController,
            focusNode: _focusNode,
            onChanged: (text) {
              setState(() {
                _commentData = text;
              });
            },
            suffixIcon: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                IconButton(
                  onPressed: () async {
                    _focusNode?.unfocus();
                    imgHtml = await _pickImage(ImageSource.gallery);
                    _focusNode?.requestFocus();
                  },
                  icon: const Icon(
                    Icons.image_outlined,
                    size: Dimensions.iconSizeExtraLarge,
                  ),
                ),
                IconButton(
                  onPressed: () async {
                    _focusNode?.unfocus();
                    imgHtml = await _pickImage(ImageSource.camera);
                    _focusNode?.requestFocus();
                  },
                  icon: const Icon(
                    Icons.camera_alt_outlined,
                    size: Dimensions.iconSizeExtraLarge,
                  ),
                ),
              ],
            ),
            maxLines: null,
            borderRadius: Dimensions.radiusExtraMoreLarge,
            isFucusBorder: true,
            hintText: '${tl(context).write_a_comment}...',
          ),
        ),
        if (_selectImage != null ||
            (_commentData != '' && _commentData != null))
          IconButton(
            highlightColor: Colors.transparent,
            onPressed: () async {
              final commentBody = _handleDataComment(
                image: imgHtml,
                comment: _commentController.text,
              );
              final type = Comment.create(widget.type);

              if (commentBody != null) {
                await ref.read(commentProvider.notifier).createComment(
                      commentBody: commentBody,
                      entityId: widget.entityId ?? '',
                      entityType: type.entityType ?? '',
                      commentType: type.commentType ?? '',
                    );
                if (type.commentType == 'lesson_default') {
                  ref
                      .read(commentProvider.notifier)
                      .getCommentLesson(widget.entityId ?? '');
                } else {
                  ref.read(commentProvider.notifier).getComment(
                        entityId: widget.entityId ?? '',
                        entityType: type.entityType ?? '',
                        commentType: type.commentType ?? '',
                      );
                }
                setState(() {
                  _commentData = null;
                  _selectImage = null;
                });
                _commentController.clear();
                _focusNode?.unfocus();
                imgHtml = null;
              }
            },
            icon: SvgPicture.asset(
              Images.sendMessage,
              height: 25,
            ),
            style: IconButton.styleFrom(
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
          )
        else
          const SizedBox(
            width: Dimensions.paddingSizeDefault,
          ),
      ],
    );
  }
}

enum CommentType {
  lesson,
  forum,
  group,
  course,
}

class Comment {
  Comment({required this.entityType, required this.commentType});
  factory Comment.create(CommentType type) {
    switch (type) {
      case CommentType.lesson:
        {
          return Comment(
            entityType: 'lms_lesson',
            commentType: 'lesson_default',
          );
        }
      case CommentType.forum:
        {
          return Comment(entityType: 'node', commentType: 'comment_forum');
        }
      case CommentType.group:
        {
          return Comment(
            entityType: 'lms_lesson',
            commentType: 'lesson_default',
          );
        }
      case CommentType.course:
        {
          return Comment(
            entityType: 'lms_lesson',
            commentType: 'lesson_default',
          );
        }
    }
  }
  String? entityType;
  String? commentType;
}
