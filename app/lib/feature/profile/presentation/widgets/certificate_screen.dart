import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/profile/presentation/providers/certificate_provider.dart';
import 'package:everlms/feature/root/presentation/screens/no_data_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:url_launcher/url_launcher.dart';

class CertificateScreen extends ConsumerWidget {
  const CertificateScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncState = ref.watch(certificateProvider);
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).certificates,
        bgColor: Theme.of(context).cardColor,
        titleColor: Theme.of(context).colorScheme.onSurface,
      ),
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(child: Text('$e')),
        data: (state) {
          final certList = state.certificates;
          if (certList.isEmpty) {
            return NoDataScreen(
              text: tl(context).no_certificate_found,
              type: NoDataType.certificate,
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.symmetric(
              vertical: Dimensions.paddingSizeDefault,
            ),
            itemCount: certList.length,
            itemBuilder: (context, index) {
              final cert = certList[index];
              var pdfUrl = cert.link ?? '';
              if (pdfUrl.isNotEmpty && pdfUrl[0] == '/') {
                pdfUrl = pdfUrl.substring(1);
              }
              final fullUrl = '${Config.baseUrl}$pdfUrl';
              return InkWell(
                onTap: () async {
                  try {
                    await launchUrl(
                      Uri.parse(fullUrl),
                      mode: LaunchMode.externalApplication,
                    );
                  } on Exception {
                    if (!context.mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        backgroundColor: Colors.red,
                        content: Text(tl(context).cant_open_link),
                        duration: const Duration(seconds: 3),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  }
                },
                child: Card(
                  color: Theme.of(context).colorScheme.primaryContainer,
                  margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: Padding(
                    padding:
                        const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    child: Row(
                      children: [
                        SvgPicture.asset(
                          Images.certificates,
                          height: 40,
                          width: 40,
                        ),
                        const SizedBox(width: Dimensions.paddingSizeDefault),
                        Flexible(
                          child: Text(
                            cert.name ?? '',
                            style: Theme.of(context).textTheme.headlineSmall,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
