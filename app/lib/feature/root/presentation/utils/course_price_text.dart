import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';

String coursePriceText(Course course) {
  if (course.isFree == true) {
    return tlCtx.free;
  }

  if (course.isDiscounted == true) {
    return course.discountedPrice?.toString() ?? '';
  }

  return course.price?.toString() ?? '';
}
