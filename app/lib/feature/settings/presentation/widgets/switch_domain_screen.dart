import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/utils/lottie_files.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/settings/presentation/providers/switch_domain_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:lottie/lottie.dart';

class SwitchDomainScreen extends ConsumerStatefulWidget {
  const SwitchDomainScreen({this.urlArg, super.key});
  final String? urlArg;

  @override
  ConsumerState<SwitchDomainScreen> createState() => _SwitchDomainScreenState();
}

class _SwitchDomainScreenState extends ConsumerState<SwitchDomainScreen> {
  late Future<SwitchDomainStep> _function;
  late String url;

  Map<SwitchDomainStep, dynamic> messageResult = {
    SwitchDomainStep.verifying: {'title': 'Verifying...', 'message': ''},
    SwitchDomainStep.domainNotExist: {
      'title': 'Failed',
      'message': 'The domain does not exist',
    },
    SwitchDomainStep.failed: {
      'title': 'Failed',
      'message': 'Something went wrong. Please try again',
    },
    SwitchDomainStep.completed: {
      'title': 'Completed',
      'message':
          'Congratulation! You can restart the application to start using the app with your domain.',
    },
  };

  @override
  void initState() {
    url = widget.urlArg ?? '';
    ref.read(switchDomainProvider.notifier).reset();
    _assignTask();
    super.initState();
  }

  void _assignTask() {
    setState(() {
      _function = ref.read(switchDomainProvider.notifier).verifyDomain();
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(switchDomainProvider);
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).switch_domain,
        isBackButtonExist: false,
      ),
      body: Builder(
        builder: (_) {
          final host = Uri.parse(url).host;
          final domain = host.isEmpty ? url : host;
          return SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: Dimensions.paddingSizeExtraSmall,
                vertical: Dimensions.paddingSizeDefault,
              ),
              child: Column(
                children: [
                  Text(
                    domain,
                    textAlign: TextAlign.center,
                    style: Theme.of(context)
                        .textTheme
                        .titleLarge!
                        .copyWith(color: Colors.blue),
                  ),
                  FutureBuilder<SwitchDomainStep>(
                    future: _function,
                    builder: (context, snapshot) {
                      return resultWidget(
                        context,
                        state,
                        result: snapshot.data ?? SwitchDomainStep.verifying,
                      );
                    },
                  ),
                  Divider(
                    thickness: 0.3,
                    color: Theme.of(context).dividerColor,
                  ),
                  listStatusApi(context, state),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget resultWidget(
    BuildContext context,
    SwitchDomainState state, {
    required SwitchDomainStep result,
  }) {
    return Column(
      children: [
        SizedBox(
          height: 200,
          width: 200,
          child: result == SwitchDomainStep.verifying
              ? progressVerifyWidget(state)
              : result == SwitchDomainStep.completed
                  ? Lottie.asset(
                      LottieFiles.successCheck,
                      height: 180,
                      repeat: false,
                    )
                  : Lottie.asset(
                      LottieFiles.bouncyFail,
                      height: 180,
                      repeat: false,
                    ),
        ),
        Text(
          result == SwitchDomainStep.verifying
              ? messageResult[SwitchDomainStep.verifying]['title']
              : messageResult[result]['title'],
          style: Theme.of(context).textTheme.titleLarge!.copyWith(
                color: result == SwitchDomainStep.verifying ||
                        result == SwitchDomainStep.completed
                    ? Theme.of(context).textTheme.titleLarge!.color
                    : Colors.red,
              ),
        ),
        if (!messageResult.containsKey(result))
          const SizedBox.shrink()
        else
          Padding(
            padding: const EdgeInsets.symmetric(
              vertical: Dimensions.paddingSizeDefault,
              horizontal: Dimensions.paddingSizeLarge,
            ),
            child: Text(
              messageResult[result]['message'],
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
          ),
        if (result == SwitchDomainStep.verifying)
          const SizedBox.shrink()
        else
          rowButtonWidget(result),
      ],
    );
  }

  Widget progressVerifyWidget(SwitchDomainState state) {
    return Padding(
      padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
      child: Stack(
        fit: StackFit.expand,
        children: [
          CircularProgressIndicator(
            value: state.progress,
            strokeCap: StrokeCap.round,
            strokeWidth: Dimensions.paddingSizeSmall,
            backgroundColor: Theme.of(context).highlightColor,
          ),
          Align(
            child: Text(
              '${(state.progress * 100).toInt()}%',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleLarge,
            ),
          ),
        ],
      ),
    );
  }

  Widget listStatusApi(BuildContext context, SwitchDomainState state) {
    return ListView.builder(
      itemCount: state.listResultApi.length,
      shrinkWrap: true,
      padding: EdgeInsets.zero,
      physics: const NeverScrollableScrollPhysics(),
      itemBuilder: (context, index) {
        final item = state.listResultApi.elementAt(index);
        return Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
            vertical: Dimensions.paddingSizeSmall,
          ),
          child: Row(
            children: [
              Container(
                width: 55,
                alignment: Alignment.centerRight,
                child: Text(
                  item.method ?? '',
                  style: Theme.of(context).textTheme.titleMedium!.copyWith(
                        color: item.method == 'GET'
                            ? Colors.green
                            : item.method == 'POST'
                                ? Colors.yellow.shade900
                                : item.method == 'PATCH'
                                    ? Colors.deepPurpleAccent
                                    : item.method == 'DELETE'
                                        ? Colors.redAccent
                                        : Colors.white,
                        fontSize: Dimensions.fontSizeDefault,
                      ),
                ),
              ),
              const SizedBox(
                width: Dimensions.paddingSizeSmall,
              ),
              Expanded(
                child: Text(
                  'API ${index + 1}',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
                margin: const EdgeInsets.only(
                  right: Dimensions.paddingSizeDefault,
                ),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: (item.isError ?? false)
                      ? Colors.red
                      : Colors.green.shade500,
                ),
                child: SvgPicture.asset(
                  (item.isError ?? false) ? Images.cancel : Images.successful,
                  height: 10,
                  colorFilter:
                      const ColorFilter.mode(Colors.white, BlendMode.srcIn),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget rowButtonWidget(SwitchDomainStep status) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeExtraMoreLarge,
      ),
      child: Row(
        spacing: Dimensions.paddingSizeExtraLarge,
        children: [
          Flexible(
            child: CustomButton(
              margin: EdgeInsets.zero,
              buttonText: 'Cancel',
              onPressed: () => context.pop(),
              bgColor: Theme.of(context).colorScheme.error,
            ),
          ),
          if (status == SwitchDomainStep.completed)
            Flexible(
              child: CustomButton(
                margin: EdgeInsets.zero,
                onPressed: () =>
                    ref.read(switchDomainProvider.notifier).restartApp(url),
                buttonText: 'Restart',
              ),
            )
          else
            Flexible(
              child: CustomButton(
                margin: EdgeInsets.zero,
                onPressed: _assignTask,
                buttonText: 'Try again',
                bgColor: Theme.of(context)
                    .colorScheme
                    .onSurface
                    .withValues(alpha: 0.8),
              ),
            ),
        ],
      ),
    );
  }
}
