import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/faq.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';

class FrequentlyAskQuestion extends StatefulWidget {
  const FrequentlyAskQuestion({
    required this.faqList,
    super.key,
  });
  final List<Faq> faqList;
  @override
  State<FrequentlyAskQuestion> createState() => _FrequentlyAskQuestion();
}

class _FrequentlyAskQuestion extends State<FrequentlyAskQuestion> {
  List<Faq> _faqList = [];
  @override
  void initState() {
    super.initState();
    _faqList = widget.faqList;
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (_faqList.isEmpty)
            const SizedBox.shrink()
          else
            Text(
              tl(context).frequently_ask_question,
              style: Theme.of(context).textTheme.headlineSmall,
            ),
          const SizedBox(height: Dimensions.paddingSizeLarge),
          Container(
            alignment: Alignment.center,
            child: ListView.builder(
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _faqList.length,
              shrinkWrap: true,
              itemBuilder: (context, index) {
                return faqItem(context, _faqList[index], index);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget faqItem(BuildContext context, Faq faq, int index) {
    return Container(
      decoration: BoxDecoration(
        border: Border(
          top: BorderSide(
            color: Theme.of(context).hintColor,
          ),
        ),
      ),
      child: ExpansionTile(
        title: Text(
          faq.question ?? '',
          style: Theme.of(context).textTheme.bodyLarge,
        ),
        childrenPadding: EdgeInsets.zero,
        tilePadding: EdgeInsets.zero,
        iconColor: Theme.of(context).colorScheme.onSurface,
        collapsedIconColor: Theme.of(context).colorScheme.onSurface,
        children: [
          ListView.builder(
            shrinkWrap: true,
            itemCount: 1,
            itemBuilder: (context, index) {
              return Html(
                data: faq.answer ?? '',
                style: {
                  '*': Style(
                    margin: Margins.only(bottom: Dimensions.paddingSizeDefault),
                    fontSize: FontSize.large,
                  ),
                  'p': Style(
                    fontSize: FontSize.large,
                  ),
                },
              );
            },
          ),
        ],
      ),
    );
  }
}

class MySeparator extends StatelessWidget {
  const MySeparator({super.key, this.height = 1, this.color = Colors.black});
  final double height;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final boxWidth = constraints.constrainWidth();
        const dashWidth = Dimensions.radiusSmall;
        final dashHeight = height;
        final dashCount = (boxWidth / (1.5 * dashWidth)).floor();
        return Flex(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          direction: Axis.horizontal,
          children: List.generate(dashCount, (_) {
            return SizedBox(
              width: dashWidth,
              height: dashHeight,
              child: DecoratedBox(
                decoration: BoxDecoration(color: color),
              ),
            );
          }),
        );
      },
    );
  }
}
