import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/notification/data/models/notification_model.dart';
import 'package:everlms/feature/notification/presentation/providers/notification_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/svg.dart';

class NotificationScreen extends ConsumerWidget {
  const NotificationScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncState = ref.watch(notificationProvider);

    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).notification,
        centerTitle: false,
        bgColor: Theme.of(context).cardColor,
        titleColor: Theme.of(context).colorScheme.onSurface,
      ),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (err, _) => Center(child: Text(err.toString())),
        data: (state) => state.notificationList.isEmpty
            ? _noDataWidget(context)
            : _notificationList(context, state.notificationList),
      ),
    );
  }

  Widget _notificationList(
    BuildContext context,
    List<CustomNotification> list,
  ) {
    return Padding(
      padding: const EdgeInsets.all(10),
      child: ListView.builder(
        shrinkWrap: true,
        itemCount: list.length,
        physics: const ClampingScrollPhysics(),
        itemBuilder: (context, index) => _item(context, list[index]),
      ),
    );
  }

  Widget _item(
    BuildContext context,
    CustomNotification notif,
  ) {
    return ListTile(
      leading: SvgPicture.asset(Images.notificationWithBg),
      title: Text(notif.name ?? ''),
      titleTextStyle: robotoRegular,
      trailing: Text(
        notif.created ?? '',
        style: robotoRegular.copyWith(
          fontSize: Dimensions.fontSizeSmall,
          color: Theme.of(context)
              .textTheme
              .titleSmall!
              .color!
              .withValues(alpha: 0.6),
        ),
      ),
    );
  }

  Widget _noDataWidget(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Image.asset(Images.emptyNotification, scale: 3),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Text(
            tl(context).no_notifications_yet,
            style: robotoSemiBold.copyWith(
              fontSize: Dimensions.fontSizeLarge,
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Text(
            tl(context).you_have_no_notifications,
            style: robotoRegular.copyWith(
              fontSize: Dimensions.fontSizeSmall,
              color: Theme.of(context)
                  .textTheme
                  .bodySmall!
                  .color!
                  .withValues(alpha: .7),
            ),
          ),
        ],
      ),
    );
  }
}
