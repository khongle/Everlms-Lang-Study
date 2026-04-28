import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/rating_bar.dart';
import 'package:everlms/feature/root/data/models/common/instructor.dart';
import 'package:flutter/material.dart';

class InstructorDetailScreen extends StatefulWidget {
  const InstructorDetailScreen({this.instructorArg, super.key});
  final dynamic instructorArg;

  @override
  State<InstructorDetailScreen> createState() => _InstructorDetailScreenState();
}

class _InstructorDetailScreenState extends State<InstructorDetailScreen> {
  Instructor? instructor;
  @override
  void initState() {
    instructor = widget.instructorArg;
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    final rating = instructor?.rating?.toString() ?? '0';
    return Scaffold(
      appBar: CustomAppBar(
        title: instructor?.name ?? '',
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  vertical: Dimensions.paddingSizeExtraMoreLarge,
                ),
                child: ClipOval(
                  child: Image.network(
                    instructor?.image ?? '',
                    fit: BoxFit.cover,
                    height: 150,
                    width: 150,
                    errorBuilder: (context, error, stackTrace) => Image.asset(
                      Images.placeholder,
                      fit: BoxFit.cover,
                      height: 150,
                      width: 150,
                    ),
                  ),
                ),
              ),
            ),
            Column(
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                  ),
                  child: Text(
                    instructor?.name ?? '',
                    style: Theme.of(context).textTheme.headlineMedium,
                  ),
                ),
                dividerSection(context),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                  ),
                  child: Text(
                    instructor?.expertise ?? '',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                ),
                dividerSection(context),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                  ),
                  child: Text(
                    instructor?.biography ?? '',
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                ),
                dividerSection(context),
                Align(
                  alignment: Alignment.centerLeft,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeDefault,
                    ),
                    child: CustomRatingBar(
                      rating: double.parse(rating),
                      size: 20,
                      ratingCount: int.parse(rating),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Padding dividerSection(BuildContext context) {
    return Padding(
      padding:
          const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeLarge),
      child: Divider(
        color: Theme.of(context).hintColor,
      ),
    );
  }
}
