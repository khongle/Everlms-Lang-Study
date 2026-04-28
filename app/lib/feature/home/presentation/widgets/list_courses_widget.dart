import 'package:everlms/core/router/app_router.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/course_widget.dart';
import 'package:everlms/core/widgets/title_view.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class ListCoursesWidget extends StatefulWidget {
  const ListCoursesWidget({
    required this.list,
    required this.title,
    super.key,
    this.axis = Axis.vertical,
  });
  final List<Course> list;
  final String? title;
  final Axis axis;

  @override
  State<ListCoursesWidget> createState() => _ListCoursesWidgetState();
}

class _ListCoursesWidgetState extends State<ListCoursesWidget> {
  List<Course> listCourses = [];
  final ScrollController _scrollController = ScrollController();
  final isShowViewAll = ValueNotifier<bool>(true);
  @override
  void initState() {
    listCourses = widget.list;
    _scrollController.addListener(() {
      if (_scrollController.position.pixels ==
          _scrollController.position.maxScrollExtent) {
        isShowViewAll.value = false;
      } else {
        isShowViewAll.value = true;
      }
    });
    super.initState();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (listCourses.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          height: 50,
          child: ValueListenableBuilder<bool>(
            valueListenable: isShowViewAll,
            builder: (context, isShowViewAll, _) {
              return TitleView(
                title: widget.title ?? '',
                isViewAllEnabled: listCourses.length > 5 && isShowViewAll,
                onViewAllClicked: () {
                  context.push(
                    AppRoutes.viewAllCoursesScreen,
                    extra: {
                      'title': widget.title ?? '',
                      'courseList': listCourses,
                    },
                  );
                },
              );
            },
          ),
        ),
        if (widget.axis == Axis.vertical)
          listVertical(context, listCourses)
        else
          listHorizontal(context, listCourses),
      ],
    );
  }

  Widget listVertical(BuildContext context, List<Course> listCourses) {
    return ListView.builder(
      scrollDirection: widget.axis,
      itemCount: listCourses.length >= 4 ? 4 : listCourses.length,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: EdgeInsets.zero,
      itemBuilder: (context, index) {
        return CourseWidget(
          course: listCourses.elementAt(index),
        );
      },
    );
  }

  Widget listHorizontal(BuildContext context, List<Course> listCourses) {
    return SizedBox(
      height: 210,
      child: ListView.separated(
        separatorBuilder: (context, index) => const SizedBox(
          width: Dimensions.paddingSizeLarge,
        ),
        scrollDirection: Axis.horizontal,
        controller: _scrollController,
        itemCount: listCourses.length,
        shrinkWrap: true,
        itemBuilder: (context, index) {
          return CourseWidget(
            course: listCourses.elementAt(index),
            axis: Axis.vertical,
          );
        },
      ),
    );
  }
}
