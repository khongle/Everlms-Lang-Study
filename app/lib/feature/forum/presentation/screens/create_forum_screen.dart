import 'dart:convert';
import 'dart:io';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/core/widgets/custom_snackbar.dart';
import 'package:everlms/core/widgets/custom_text_field.dart';
import 'package:everlms/feature/forum/presentation/providers/forum_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path/path.dart' as p;

class CreateForumScreen extends ConsumerStatefulWidget {
  const CreateForumScreen({super.key, this.forumId});
  final String? forumId;

  @override
  ConsumerState<CreateForumScreen> createState() => _CreateForumScreenState();
}

class _CreateForumScreenState extends ConsumerState<CreateForumScreen> {
  TextEditingController subjectController = TextEditingController();

  String? selectForum;
  final _selectImage = ValueNotifier<File?>(null);
  String? imgHtml;

  bool showButtonSend = false;
  final TextEditingController bodyController = TextEditingController();
  @override
  void initState() {
    selectForum = widget.forumId;

    super.initState();
  }

  Future<void> convertFileImageToHtml() async {
    if (_selectImage.value != null) {
      final imgExtension = p.extension(_selectImage.value!.path).substring(1);
      final bytes = await _selectImage.value!.readAsBytes();
      final base64String = base64.encode(bytes);
      imgHtml =
          "<p><img src=data:image/$imgExtension;base64,$base64String alt=''></p>";
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () async {},
      child: Scaffold(
        body: Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
            vertical: Dimensions.paddingSizeSmall,
          ),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Align(
                  alignment: Alignment.topRight,
                  child: IconButton.filled(
                    onPressed: () => context.pop(),
                    icon: const Icon(
                      Icons.close,
                      size: Dimensions.iconSizeLarge,
                    ),
                    style: IconButton.styleFrom(
                      backgroundColor:
                          Theme.of(context).hintColor.withValues(alpha: 0.3),
                    ),
                  ),
                ),
                Align(
                  child: Text(
                    'Create new topic',
                    style: Theme.of(context).textTheme.titleLarge,
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(
                  height: Dimensions.paddingSizeDefault,
                ),
                Text(
                  tl(context).subject,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                CustomTextField(
                  hintText: 'Your text here...',
                  controller: subjectController,
                ),
                const SizedBox(height: 20),
                Text(
                  tl(context).forum,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                Builder(
                  builder: (context) {
                    final forumState = ref.watch(forumProvider).valueOrNull;
                    if (forumState == null || forumState.isLoading) {
                      return Container();
                    }
                    final listItem = forumState.listForum
                        .where((e) => e.id == selectForum)
                        .toList();
                    final currentForum =
                        listItem.isNotEmpty ? listItem.first.name : '';
                    return CustomTextField(
                      isEnabled: false,
                      controller: TextEditingController(text: currentForum),
                    );
                  },
                ),
                const SizedBox(height: 20),
                Text(
                  tl(context).content,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                enterText(),
              ],
            ),
          ),
        ),
        bottomNavigationBar: buttonSave(context),
      ),
    );
  }

  Padding buttonSave(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: CustomButton(
        onPressed: () async {
          final bodyText = (imgHtml ?? '') + bodyController.text;
          if (subjectController.text.isEmpty) {
            customSnackBar('Subject is required');
            return;
          } else if (bodyText.isEmpty) {
            customSnackBar('Body is required');
            return;
          } else if (selectForum == null) {
            customSnackBar('Forum is required');
            return;
          }
          await ref.read(forumProvider.notifier).createForumTopic(
                subject: subjectController.text,
                forumId: selectForum ?? '',
                body: bodyText,
              );
          if (!context.mounted) return;
          Navigator.pop(context);
        },
        buttonText: tl(context).save,
      ),
    );
  }

  Widget enterText() {
    return ValueListenableBuilder(
      valueListenable: _selectImage,
      builder: (context, value, child) {
        return Column(
          children: [
            if (_selectImage.value != null)
              Padding(
                padding: const EdgeInsets.only(
                  bottom: Dimensions.paddingSizeDefault,
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  spacing: Dimensions.paddingSizeMint,
                  children: [
                    Image.file(value!, height: 150),
                    InkWell(
                      onTap: () {
                        _selectImage.value = null;
                      },
                      child: Container(
                        padding: const EdgeInsets.all(
                          Dimensions.paddingSizeExtraSmall,
                        ),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Theme.of(context).hintColor,
                          ),
                        ),
                        child: const Icon(Icons.clear),
                      ),
                    ),
                  ],
                ),
              )
            else
              const SizedBox.shrink(),
            textFieldMessenger(),
          ],
        );
      },
    );
  }

  Row textFieldMessenger() {
    return Row(
      spacing: Dimensions.paddingSizeMint,
      children: [
        Expanded(
          child: FocusScope(
            child: CustomTextField(
              hintText: 'Your text here...',
              controller: bodyController,
              suffixIcon: IconButton(
                onPressed: bodyController.clear,
                icon: const Icon(Icons.clear),
              ),
              maxLines: 5,
            ),
          ),
        ),
        IconButton(
          onPressed: () async {
            final image =
                await ImagePicker().pickImage(source: ImageSource.gallery);
            if (image != null) {
              _selectImage.value = File(image.path);
              convertFileImageToHtml();
            }
          },
          icon: const Icon(
            Icons.image_outlined,
            size: Dimensions.iconSizeExtraLarge,
          ),
        ),
      ],
    );
  }
}
