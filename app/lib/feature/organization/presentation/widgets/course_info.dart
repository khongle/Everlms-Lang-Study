import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/organization/data/models/organization_profile.dart';
import 'package:flutter/material.dart';

class CourseInformation extends StatelessWidget {
  const CourseInformation({required this.profileData, super.key});
  final OrganizationProfileData profileData;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return SizedBox(
      height: 44,
      child: ListView(
        scrollDirection: Axis.horizontal,
        children: [
          _cardWidget(
            context,
            isDark,
            profileData.totalCourse?.toString() ?? '0',
            tl(context).total_course,
          ),
          _cardWidget(
            context,
            isDark,
            profileData.totalInstructor?.toString() ?? '0',
            tl(context).total_instructor,
          ),
          _cardWidget(
            context,
            isDark,
            profileData.totalStudents?.toString() ?? '0',
            tl(context).total_students,
          ),
          _cardWidget(
            context,
            isDark,
            profileData.totalEnrollments?.toString() ?? '0',
            tl(context).total_enrollments,
          ),
        ],
      ),
    );
  }

  Widget _cardWidget(
    BuildContext context,
    bool isDark,
    String total,
    String title,
  ) {
    return Padding(
      padding: const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
      child: Container(
        width: 145,
        decoration: BoxDecoration(
          border: Border.all(
            color: isDark
                ? Theme.of(context)
                    .textTheme
                    .bodyLarge!
                    .color!
                    .withValues(alpha: 0.6)
                : Theme.of(context).primaryColorLight,
          ),
          borderRadius: const BorderRadius.all(
            Radius.circular(Dimensions.paddingSizeExtraSmall),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(4),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                title,
                style: robotoRegular.copyWith(
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.6),
                  fontSize: Dimensions.fontSizeSmall,
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Text(
                total,
                style: robotoSemiBold.copyWith(
                  color: Theme.of(context).primaryColor,
                  fontSize: Dimensions.fontSizeSmall,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
