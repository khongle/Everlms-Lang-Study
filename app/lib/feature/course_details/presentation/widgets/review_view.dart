import 'package:cached_network_image/cached_network_image.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/course_details/data/models/user_review/review.dart';
import 'package:everlms/feature/course_details/presentation/providers/course_detail_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ReviewView extends ConsumerStatefulWidget {
  const ReviewView({required this.id, super.key});
  final int id;

  @override
  ConsumerState<ReviewView> createState() => _ReviewViewState();
}

class _ReviewViewState extends ConsumerState<ReviewView> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(courseDetailProvider(widget.id).notifier).loadReviews(widget.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(courseDetailProvider(widget.id)).valueOrNull;
    final reviews = state?.reviews ?? [];
    return Column(
      children: [
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: reviews.length,
          itemBuilder: (context, index) {
            return reviewItem(context, reviews[index]);
          },
        ),
        if (state?.isLoadingReviews ?? false) const LoadingIndicator(),
        if (!(state?.isLoadingReviews ?? false) &&
            (state?.hasMoreReviews ?? false))
          moreButton(context),
      ],
    );
  }

  Container moreButton(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context)
            .textTheme
            .bodyLarge!
            .color!
            .withValues(alpha: 0.06),
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(Dimensions.radiusSmall),
          bottomLeft: Radius.circular(Dimensions.radiusSmall),
        ),
      ),
      child: InkWell(
        onTap: () {
          // TODO: Add paginateReviews to courseDetailProvider
        },
        child: Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeSmall,
            vertical: Dimensions.paddingSizeRadius,
          ),
          child: Text(
            tl(context).show_more,
            style: robotoRegular.copyWith(
              color: Theme.of(context)
                  .textTheme
                  .bodyLarge!
                  .color!
                  .withValues(alpha: 0.6),
              fontSize: Dimensions.fontSizeSmall,
            ),
          ),
        ),
      ),
    );
  }

  Widget reviewItem(BuildContext context, Review review) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      child: Container(
        alignment: Alignment.center,
        decoration: BoxDecoration(
          border: Border.all(
            color: Theme.of(context)
                .textTheme
                .bodyLarge!
                .color!
                .withValues(alpha: 0.06),
          ),
          borderRadius:
              const BorderRadius.all(Radius.circular(Dimensions.radiusSmall)),
        ),
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(
                      right: Dimensions.paddingSizeSmall,
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(25),
                      child: CachedNetworkImage(
                        imageUrl: review.user?.image ?? '',
                        fit: BoxFit.cover,
                        width: 50,
                        height: 50,
                        placeholder: (context, url) =>
                            Image.asset(Images.placeholder),
                        errorWidget: (context, url, error) =>
                            const Icon(Icons.error),
                      ),
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              review.user?.name ?? '',
                              style: robotoMedium.copyWith(
                                color: Theme.of(context)
                                    .textTheme
                                    .bodyLarge!
                                    .color,
                                fontSize: Dimensions.fontSizeDefault,
                              ),
                            ),
                            Container(
                              alignment: Alignment.center,
                              height: 12,
                              width: 77,
                              child: RatingBar.builder(
                                initialRating:
                                    double.parse(review.rating ?? '0.0'),
                                minRating: 1,
                                allowHalfRating: true,
                                ignoreGestures: true,
                                itemSize: 12,
                                itemBuilder: (context, _) =>
                                    const Icon(Icons.star, color: Colors.amber),
                                onRatingUpdate: (rating) {
                                  //print(rating);
                                },
                              ),
                            ),
                          ],
                        ),
                        Text(
                          review.date ?? '',
                          style: robotoRegular.copyWith(
                            color: Theme.of(context)
                                .textTheme
                                .bodyLarge!
                                .color!
                                .withValues(alpha: 0.6),
                            fontSize: Dimensions.fontSizeExtraSmall,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(
                height: 13,
              ),
              Text(
                review.comment ?? '',
                maxLines: 2,
                textAlign: TextAlign.start,
                overflow: TextOverflow.ellipsis,
                style: robotoMedium.copyWith(
                  color: Theme.of(context)
                      .textTheme
                      .bodyLarge!
                      .color!
                      .withValues(alpha: 0.6),
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
