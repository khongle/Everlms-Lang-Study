import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/images.dart';
import 'package:flutter/material.dart';

List<Map<String, String>> getOnBoardPagerData(BuildContext context) => [
      {
        'text': tl(context).intro_screen_text_1,
        'subTitle': tl(context).intro_screen_subtitle_1,
        'image': Images.introFirst,
      },
      {
        'text': tl(context).intro_screen_text_2,
        'subTitle': tl(context).intro_screen_subtitle_2,
        'image': Images.introSecond,
      },
      {
        'text': tl(context).intro_screen_text_3,
        'subTitle': tl(context).intro_screen_subtitle_3,
        'image': Images.introThird,
      }
    ];
