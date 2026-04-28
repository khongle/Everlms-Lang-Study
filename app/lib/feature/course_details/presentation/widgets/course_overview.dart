import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/expandable_html.dart';
import 'package:flutter/material.dart';

class CourseOverview extends StatelessWidget {
  const CourseOverview({
    super.key,
    this.whatWillYouLearn,
    this.requirements,
    this.description,
  });
  final String? whatWillYouLearn;
  final String? requirements;
  final String? description;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeLarge,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          section(tl(context).what_you_will_learn, whatWillYouLearn),
          section(tl(context).requirements, requirements),
          section(tl(context).description, description, isLast: true),
        ],
      ),
    );
  }

  Widget section(String title, String? data, {bool isLast = false}) {
    if (data == null) {
      return const SizedBox.shrink();
    }
    return Builder(
      builder: (context) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.headlineSmall),
            ExpandableHtml(data: data),
            if (!isLast)
              const SizedBox(
                height: Dimensions.paddingSizeDefault,
              ),
          ],
        );
      },
    );
  }
}
