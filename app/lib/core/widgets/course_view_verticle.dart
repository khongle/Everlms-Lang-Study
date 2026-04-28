import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/responsive_helper.dart';
import 'package:everlms/core/widgets/featured_courses_widget.dart';
import 'package:everlms/feature/root/presentation/screens/no_data_screen.dart';
import 'package:flutter/material.dart';

class ServiceViewVertical extends StatelessWidget {
  const ServiceViewVertical({
    required this.service,
    super.key,
    this.isScrollable = false,
    this.shimmerLength = 20,
    this.padding = const EdgeInsets.all(Dimensions.paddingSizeSmall),
    this.noDataText,
    this.type,
    this.onVegFilterTap,
    this.noDataType,
  });
  final List<dynamic>? service;
  final EdgeInsetsGeometry? padding;
  final bool? isScrollable;
  final int? shimmerLength;
  final String? noDataText;
  final String? type;
  final NoDataType? noDataType;
  final Function(String type)? onVegFilterTap;

  @override
  Widget build(BuildContext context) {
    var isNull = true;

    isNull = service == null;
    if (!isNull) {}

    return GridView.builder(
      key: UniqueKey(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisSpacing: Dimensions.paddingSizeDefault,
        mainAxisSpacing: Dimensions.paddingSizeDefault,
        childAspectRatio: ResponsiveHelper.isTab(context) ? .9 : .8,
        // mainAxisExtent: ResponsiveHelper.isMobile(context) ? 200 : 260,
        crossAxisCount: ResponsiveHelper.isMobile(context)
            ? 2
            : ResponsiveHelper.isTab(context)
                ? 3
                : 5,
      ),
      physics: isScrollable!
          ? const ScrollPhysics()
          : const NeverScrollableScrollPhysics(),
      // shrinkWrap: isScrollable! ? false : true,
      // physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: service?.length,
      padding: padding,
      itemBuilder: (context, index) {
        return FeaturedCoursesWidget(course: service?.elementAt(index));
      },
    );
  }
}
