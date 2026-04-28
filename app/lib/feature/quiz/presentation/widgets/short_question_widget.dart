import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:flutter/material.dart';

class ShortQuestionWidget extends StatefulWidget {
  const ShortQuestionWidget({
    required this.question,
    super.key,
    this.isDisabled = false,
    this.onTextChanged,
  });
  final Questions question;
  final bool? isDisabled;
  final ValueChanged<String>? onTextChanged;

  @override
  State<ShortQuestionWidget> createState() => _ShortQuestionWidgetState();
}

class _ShortQuestionWidgetState extends State<ShortQuestionWidget> {
  final controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 10),
      child: Container(
        width: 290,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(6),
          border: Border.all(
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: 0.06),
          ),
          color: Colors.white,
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                widget.question.question,
                style: robotoRegular.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: controller,
                onChanged: (value) => widget.onTextChanged?.call(value),
                readOnly: widget.isDisabled ?? false,
                textInputAction: TextInputAction.newline,
                maxLines: null,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context)
                          .textTheme
                          .bodyMedium
                          ?.color
                          ?.withValues(alpha: 0.6),
                    ),
                decoration: InputDecoration(
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide.none,
                  ),
                  isDense: true,
                  filled: true,
                  fillColor: Theme.of(context).cardColor,
                  hintText: tl(context).write_your_ans,
                  hintStyle: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeDefault,
                    color: Theme.of(context).hintColor.withValues(
                          alpha: Theme.of(context).brightness == Brightness.dark
                              ? .5
                              : 1,
                        ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
