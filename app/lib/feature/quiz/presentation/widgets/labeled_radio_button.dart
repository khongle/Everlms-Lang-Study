import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/quiz/data/models/questions.dart';
import 'package:flutter/material.dart';

class LabeledRadioButton extends StatefulWidget {
  const LabeledRadioButton({
    required this.question,
    required this.indexQuestion,
    required this.isCorrectAnsSelected,
    super.key,
    this.selectedIndex,
    this.isClickable = true,
    this.resultIndex,
    this.indexRight,
    this.heightQuestion,
  });
  final Questions question;
  final int indexQuestion;
  final ValueChanged<bool> isCorrectAnsSelected;
  final ValueChanged<List<int>>? selectedIndex;
  final bool? isClickable;
  final List<int>? resultIndex;
  final List<int>? indexRight;
  final double? heightQuestion;

  @override
  State<LabeledRadioButton> createState() => _LabeledRadioButtonState();
}

class _LabeledRadioButtonState extends State<LabeledRadioButton> {
  int groupValue = -1;
  List<bool> groupCheckbox = [];

  @override
  void initState() {
    groupCheckbox = List<bool>.generate(
      widget.question.option?.length ?? 0,
      (index) => widget.resultIndex?.contains(index) ?? false,
    ).toList();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return question(context);
  }

  Widget question(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        Text(
          '${widget.indexQuestion + 1} - ${widget.question.question}',
          style: robotoSemiBold.copyWith(
            color: Theme.of(context).colorScheme.onSurface,
            fontSize: Dimensions.fontSizeLarge,
          ),
        ),
        const SizedBox(height: Dimensions.paddingSizeDefault),
        Column(
          children: questionList(),
        ),
      ],
    );
  }

  Padding questionWidget(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 10),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(6),
          border: Border.all(
            color:
                Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.06),
          ),
          color: Colors.white,
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(
                widget.question.question,
                style: robotoRegular.copyWith(
                  color: Theme.of(context).colorScheme.onSurface,
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
              const SizedBox(height: 10),
              Column(
                children: questionList(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> questionList() =>
      List.generate(widget.question.option?.length ?? 0, (index) {
        if (widget.indexRight != null &&
            (widget.indexRight?.contains(index) ?? false)) {
          return optionRightItem(
            widget.question.option?.elementAt(index).answer ?? '',
            index,
          );
        }
        return optionItem(
          widget.question.option?.elementAt(index).answer ?? '',
          index,
        );
      });

  Widget optionItem(String option, int value) {
    return InkWell(
      onTap: () =>
          widget.isClickable! ? _handleCheckboxValueChange(value) : null,
      child: Container(
        padding: const EdgeInsets.symmetric(
          vertical: Dimensions.paddingSizeExtraSmall,
          horizontal: Dimensions.paddingSizeExtraLarge,
        ),
        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
        decoration: BoxDecoration(
          color: widget.indexRight != null
              ? (groupCheckbox[value]
                  ? Theme.of(context).colorScheme.error.withValues(alpha: 0.09)
                  : null)
              : null,
          border: Border.all(
            color: widget.indexRight != null
                ? (widget.resultIndex!.contains(value)
                    ? Theme.of(context).colorScheme.error
                    : Theme.of(context)
                        .colorScheme
                        .onSurface
                        .withValues(alpha: 0.6))
                : groupCheckbox[value]
                    ? Theme.of(context).primaryColor
                    : Theme.of(context)
                        .colorScheme
                        .onSurface
                        .withValues(alpha: 0.6),
          ),
          borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Flexible(
              child: Text(
                option,
                style: robotoBold.copyWith(
                  color: Theme.of(context)
                      .colorScheme
                      .onSurface
                      .withValues(alpha: 0.9),
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
            ),
            const SizedBox(width: Dimensions.paddingSizeDefault),
            SizedBox(
              height: 30,
              width: 30,
              child: Checkbox(
                shape: const CircleBorder(),
                value: groupCheckbox[value],
                materialTapTargetSize: MaterialTapTargetSize.padded,
                side: !(widget.indexRight?.contains(value) ?? true)
                    ? const BorderSide(color: Colors.transparent)
                    : null,
                fillColor: WidgetStateProperty.resolveWith<Color>((states) {
                  if (states.contains(WidgetState.selected)) {
                    if (widget.indexRight != null) {
                      return Colors.red;
                    }
                    return Theme.of(context).primaryColor;
                  }
                  return Theme.of(context).colorScheme.surface;
                }),
                onChanged: (newValue) {
                  if (widget.isClickable!) _handleCheckboxValueChange(value);
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color colorQuestion(bool isSelected, bool isWrong) {
    if (isSelected) {
      // handle user select while take quiz
      return Theme.of(context).primaryColor;
    } else if (isWrong) {
      // result include answer
      return Colors.red;

      // result not include answer
    }
    return Theme.of(context).colorScheme.surface;
  }

  // multi choice
  Widget optionRightItem(String option, int index) {
    return Container(
      padding: const EdgeInsets.symmetric(
        vertical: Dimensions.paddingSizeSmall,
        horizontal: Dimensions.paddingSizeExtraLarge,
      ),
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: widget.resultIndex!.contains(-1)
            ? Theme.of(context).colorScheme.error.withValues(alpha: 0.09)
            : Theme.of(context).primaryColor.withValues(alpha: 0.09),
        border: Border.all(
          color: widget.resultIndex!.contains(-1)
              ? Theme.of(context).colorScheme.error
              : Theme.of(context).primaryColor,
        ),
        borderRadius: BorderRadius.circular(Dimensions.radiusLarge),
      ),
      child: Row(
        children: [
          Expanded(
            child: Text(
              option,
              style: robotoBold.copyWith(
                color: Theme.of(context)
                    .colorScheme
                    .onSurface
                    .withValues(alpha: 0.9),
                fontSize: Dimensions.fontSizeDefault,
              ),
            ),
          ),
          SizedBox(
            height: 30,
            width: 30,
            child: Checkbox(
              shape: const CircleBorder(),
              value: widget.resultIndex!.contains(index),
              materialTapTargetSize: MaterialTapTargetSize.padded,
              side: widget.resultIndex!.contains(-1)
                  ? const BorderSide(color: Colors.transparent)
                  : !widget.resultIndex!.contains(index)
                      ? const BorderSide(color: Colors.transparent)
                      : null,
              fillColor: WidgetStateProperty.resolveWith<Color>((states) {
                if (!widget.resultIndex!.contains(index)) {
                  return Colors.transparent;
                }
                if (states.contains(WidgetState.selected) &&
                    widget.resultIndex!.contains(index)) {
                  return Theme.of(context).primaryColor;
                }
                if (states.contains(WidgetState.selected)) {
                  return Colors.red;
                }
                return Theme.of(context).colorScheme.onSurface;
              }),
              onChanged: (newValue) {
                if (widget.isClickable!) _handleCheckboxValueChange(index);
              },
            ),
          ),
        ],
      ),
    );
  }

  void _handleCheckboxValueChange(int? value) {
    setState(() {
      if (value != null) {
        groupCheckbox[value] = !groupCheckbox[value];
        final correctAnsIndex = widget.question.correctAnswerIndex?[0] ?? -1;
        final givenAnsIndex = <int>[];
        if (groupCheckbox[value]) {
          givenAnsIndex.add(value);
        }
        if (givenAnsIndex.contains(correctAnsIndex)) {
          widget.isCorrectAnsSelected(true);
        } else {
          widget.isCorrectAnsSelected(false);
        }
        if (widget.selectedIndex != null) {
          final selects = <int>[];
          for (var i = 0; i < groupCheckbox.length; i++) {
            if (groupCheckbox[i]) {
              selects.add(i);
            }
          }
          widget.selectedIndex!(selects);
        }
      }
    });
  }
}
