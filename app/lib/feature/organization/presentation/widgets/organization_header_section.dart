import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/organization/data/models/organization_profile.dart';
import 'package:flutter/material.dart';

class OrganizationHeaderSection extends StatelessWidget {
  const OrganizationHeaderSection({required this.data, super.key});
  final OrganizationProfileData data;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Row(
        children: [
          if (data.logo != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.network(
                data.logo!,
                width: 60,
                height: 60,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) =>
                    const SizedBox(width: 60, height: 60),
              ),
            ),
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(
            child: Text(
              data.name ?? '',
              style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge),
            ),
          ),
        ],
      ),
    );
  }
}
