import 'package:cached_network_image/cached_network_image.dart';
import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CustomImage extends ConsumerWidget {
  const CustomImage({
    required this.image,
    super.key,
    this.height,
    this.width,
    this.fit = BoxFit.cover,
    this.placeholder = Images.placeholder,
  });
  final String? image;
  final double? height;
  final double? width;
  final BoxFit? fit;
  final String placeholder;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return image != null && image != ''
        ? CachedNetworkImage(
            imageUrl: image!,
            httpHeaders: {
              'Authorization': ref.read(apiClientProvider).auth ?? '',
            },
            height: height,
            width: width,
            fit: fit,
            placeholder: (context, url) => Image.asset(
              placeholder,
              height: height,
              width: width,
              fit: fit,
              cacheHeight: height?.toInt(),
              cacheWidth: width?.toInt(),
            ),
            errorWidget: (context, url, error) => Image.asset(
              placeholder,
              height: height,
              width: width,
              fit: fit,
              cacheHeight: height?.toInt(),
              cacheWidth: width?.toInt(),
            ),
          )
        : Image.asset(
            placeholder,
            height: height,
            width: width,
            fit: fit,
            cacheHeight: height?.toInt(),
            cacheWidth: width?.toInt(),
          );
  }
}
