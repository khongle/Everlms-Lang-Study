import 'package:everlms/core/localization/tl.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';

class ExpandableHtml extends StatefulWidget {
  const ExpandableHtml({required this.data, super.key, this.maxLines = 5});
  final String data;
  final int maxLines;

  @override
  State<ExpandableHtml> createState() => _ExpandableHtmlState();
}

class _ExpandableHtmlState extends State<ExpandableHtml> {
  bool isExpanded = false;
  Style headingStyle(BuildContext context) => Style(
        fontSize: FontSize(Theme.of(context).textTheme.bodyMedium!.fontSize ?? 14.0),
        fontWeight: FontWeight.w600,
        margin: Margins.zero,
        padding: HtmlPaddings.zero,
      );

  Map<String, Style> styleHtml(BuildContext context) => {
        'p': Style(margin: Margins.zero, padding: HtmlPaddings.zero),
        'ul': Style(margin: Margins.zero, padding: HtmlPaddings.zero),
        'li': Style(margin: Margins.zero, padding: HtmlPaddings.zero),
        'h1': headingStyle(context),
        'h2': headingStyle(context),
        'h3': headingStyle(context),
        'h4': headingStyle(context),
        'h5': headingStyle(context),
        'h6': headingStyle(context),
      };

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final span = TextSpan(
          text: widget.data,
          style: DefaultTextStyle.of(context).style,
        );

        final tp = TextPainter(
          text: span,
          maxLines: widget.maxLines,
          textDirection: TextDirection.ltr,
        );

        tp.layout(maxWidth: constraints.maxWidth);

        if (!tp.didExceedMaxLines) {
          return Html(
            data: widget.data,
            style: styleHtml(context),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Html(
              data: isExpanded
                  ? widget.data
                  : '${widget.data.substring(0, tp.getPositionForOffset(Offset(tp.width, tp.height)).offset)}...',
              style: styleHtml(context),
            ),
            Padding(
              padding: const EdgeInsets.only(left: 8),
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    isExpanded = !isExpanded;
                  });
                },
                child: Text(
                  isExpanded ? tl(context).show_less : tl(context).show_more,
                  style: const TextStyle(color: Colors.blue),
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
