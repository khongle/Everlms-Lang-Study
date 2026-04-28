import 'dart:convert';

import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/quiz/data/models/data.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ItemQuiz extends StatelessWidget {
  const ItemQuiz({this.quiz, this.permission = true, this.color, super.key});
  final DataQuiz? quiz;
  final bool permission;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    return ExpansionTile(
      shape: RoundedRectangleBorder(
        side: BorderSide(
          color: Theme.of(context).primaryColor,
        ),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      collapsedShape: RoundedRectangleBorder(
        side: BorderSide(
          color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
        ),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      collapsedBackgroundColor: color,
      title: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              quiz?.quiz?.name ?? '',
              style: Theme.of(context).textTheme.titleSmall,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          const SizedBox(width: 10),
          if (permission)
            Text(
              quiz?.quiz?.fieldStatusQuiz ?? '',
              style: Theme.of(context)
                  .textTheme
                  .labelLarge!
                  .copyWith(color: Theme.of(context).primaryColor),
            )
          else
            const SizedBox.shrink(),
        ],
      ),
      childrenPadding: EdgeInsets.zero,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Html(
                data: quiz?.quiz?.fieldBody ?? '',
              ),
              if (permission)
                CustomButton(
                  buttonText: tl(context).view_details,
                  margin: const EdgeInsets.only(
                    bottom: Dimensions.paddingSizeDefault,
                  ),
                  onPressed: () async {
                    final sharedPreferences =
                        await SharedPreferences.getInstance();
                    sharedPreferences.setString(
                      'dataQuiz',
                      json.encode(quiz?.toJson()),
                    );
                    if (!context.mounted) return;
                    context.push(AppRoutes.quizDetailsScreen, extra: quiz);
                  },
                )
              else
                const SizedBox.shrink(),
            ],
          ),
        ),
      ],
    );
  }
}
