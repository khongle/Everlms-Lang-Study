import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:flutter/material.dart';

class NotCourse extends StatelessWidget {
  const NotCourse({
    required this.context,
    super.key,
  });

  final BuildContext context;

  @override
  Widget build(BuildContext context) {
    return Column(
      spacing: Dimensions.paddingSizeDefault,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Image.asset(
          Images.emptyCourse,
          scale: 3,
        ),
        Text(
          tl(context).not_yet_course,
          style: Theme.of(context).textTheme.titleMedium,
        ),
        CustomButton(
          buttonText: tl(context).explore_course,
          margin: EdgeInsets.symmetric(
            horizontal: MediaQuery.of(context).size.width / 3,
          ),
          onPressed: () {
            Navigator.of(context).pushNamedAndRemoveUntil('/', (_) => false);
          },
        ),
      ],
    );
  }
}
