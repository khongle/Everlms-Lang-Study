import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/expandable_html.dart';
import 'package:everlms/feature/root/data/models/common/lesson.dart';
import 'package:flutter/material.dart';

class LessonInformation extends StatefulWidget {
  const LessonInformation({super.key, this.lesson});
  final Lesson? lesson;

  @override
  State<LessonInformation> createState() => _LessonInformationState();
}

class _LessonInformationState extends State<LessonInformation>
    with TickerProviderStateMixin {
  late TabController _tabController;
  int tabIndex = 0;
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  Widget build(BuildContext context) {
    return mainUI();
  }

  Widget mainUI() {
    return Column(
      children: [
        SizedBox(
          height: 40,
          child: TabBar(
            controller: _tabController,
            indicatorSize: TabBarIndicatorSize.tab,
            labelStyle: Theme.of(context).textTheme.titleSmall,
            dividerHeight: 0.5,
            dividerColor: Theme.of(context).hintColor,
            onTap: (value) {
              setState(() {
                tabIndex = value;
              });
            },
            tabs: const [
              Tab(
                text: 'Overview',
              ),
              Tab(
                text: 'Detail',
              ),
            ],
          ),
        ),
        const SizedBox(
          height: Dimensions.paddingSizeSmall,
        ),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: Dimensions.paddingSizeDefault,
          ),
          child: IndexedStack(
            index: tabIndex,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  titleSection(tl(context).what_you_will_learn),
                  ExpandableHtml(data: widget.lesson?.whatWillYouLearn ?? ''),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  titleSection(tl(context).description),
                  ExpandableHtml(data: widget.lesson?.description ?? ''),
                ],
              ),
              ExpandableHtml(data: widget.lesson?.detail ?? ''),
            ],
          ),
        ),
        const SizedBox(
          height: Dimensions.paddingSizeDefault,
        ),
      ],
    );
  }

  Widget titleSection(String title) {
    return Text(
      title,
      style: robotoSemiBold.copyWith(fontSize: Dimensions.fontSizeLarge),
    );
  }
}
