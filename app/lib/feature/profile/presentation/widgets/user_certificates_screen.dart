import 'package:everlms/core/config/app_config.dart';
import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/profile/presentation/providers/certificate_provider.dart';
import 'package:everlms/feature/root/presentation/screens/no_data_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

class UserCertificatesScreen extends ConsumerWidget {
  const UserCertificatesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final certificatesAsync = ref.watch(userCertificatesProvider);
    return Scaffold(
      appBar: CustomAppBar(title: tl(context).my_certificates),
      body: certificatesAsync.when(
        loading: () => const LoadingIndicator(),
        error: (_, __) => NoDataScreen(
          text: tl(context).no_certificate_found,
          type: NoDataType.certificate,
          isShowHomePage: false,
        ),
        data: (listCertificate) {
          if (listCertificate.isEmpty) {
            return NoDataScreen(
              text: tl(context).no_certificate_found,
              type: NoDataType.certificate,
              isShowHomePage: false,
            );
          }
          return ListView.builder(
            padding: const EdgeInsets.symmetric(
              vertical: Dimensions.paddingSizeDefault,
            ),
            itemCount: listCertificate.length,
            itemBuilder: (context, index) {
              var pdfUrl = listCertificate[index].link;
              const fileName = 'Certificate';
              if (pdfUrl != null && pdfUrl.isNotEmpty && pdfUrl[0] == '/') {
                pdfUrl = pdfUrl.substring(1, pdfUrl.length);
              }
              return InkWell(
                onTap: () {
                  context.push(
                    AppRoutes.pdfViewerScreen,
                    extra: {
                      'pdfUrl': '${Config.baseUrl}$pdfUrl',
                      'fileName': fileName,
                    },
                  );
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
                        const SizedBox(
                          width: Dimensions.paddingSizeDefault,
                        ),
                        Flexible(
                          child: Text(
                            listCertificate[index].name ?? '',
                            style: Theme.of(context).textTheme.headlineSmall,
                          ),
                        ),
                        const SizedBox(
                          width: Dimensions.paddingSizeDefault,
                        ),
                        const Icon(
                          Icons.arrow_forward_ios,
                          size: Dimensions.iconSizeExtraLarge,
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
