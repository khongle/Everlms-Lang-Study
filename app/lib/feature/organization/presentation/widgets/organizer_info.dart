import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/organization/data/models/organization_profile.dart';
import 'package:flutter/material.dart';

class OrganizerInfo extends StatelessWidget {
  const OrganizerInfo({required this.data, super.key});
  final OrganizationProfileData data;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _stat('${data.totalCourse ?? 0}', 'Courses'),
          _stat('${data.totalInstructor ?? 0}', 'Instructors'),
          _stat('${data.totalStudents ?? 0}', 'Students'),
        ],
      ),
    );
  }

  Widget _stat(String value, String label) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
        ),
        Text(
          label,
          style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
        ),
      ],
    );
  }
}
