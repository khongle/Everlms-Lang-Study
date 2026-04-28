import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/my_courses/presentation/providers/my_course_provider.dart';
import 'package:everlms/feature/my_learning_course/presentation/widgets/my_learning_course_widget.dart';
import 'package:everlms/feature/my_learning_course/presentation/widgets/not_courses.dart';
import 'package:everlms/feature/root/data/models/common/lms_my_course.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MyLearningCourseScreen extends ConsumerStatefulWidget {
  const MyLearningCourseScreen({super.key});

  @override
  ConsumerState<MyLearningCourseScreen> createState() =>
      _MyLearningCourseScreenState();
}

class _MyLearningCourseScreenState extends ConsumerState<MyLearningCourseScreen>
    with AutomaticKeepAliveClientMixin {
  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      ref.read(myCourseProvider.notifier).getDownloadedCourseIDs();
    });
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final myCourseState = ref.watch(myCourseProvider).valueOrNull;
    final isLoading = myCourseState?.isLoading ?? false;
    final listCourses = List<LMSMyCourseModel>.from(
      myCourseState?.courses ?? [],
    );

    return SafeArea(
      child: Scaffold(
        body: Builder(
          builder: (context) {
            if (isLoading) {
              return const LoadingIndicator();
            } else if (listCourses.isNotEmpty) {
              listCourses
                  .sort((a, b) => (a.progress ?? 0).compareTo(b.progress ?? 0));
              return SingleChildScrollView(
                padding: const EdgeInsets.all(Dimensions.paddingSizeExtraLarge),
                child: Column(
                  key: UniqueKey(),
                  mainAxisAlignment: MainAxisAlignment.center,
                  spacing: Dimensions.paddingSizeDefault,
                  children: [
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: listCourses.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: const EdgeInsets.symmetric(
                            vertical: Dimensions.paddingSizeSmall,
                          ),
                          child: MyLearningCoursesWidget(
                            myCourse: listCourses[index],
                          ),
                        );
                      },
                    ),
                  ],
                ),
              );
            }
            return Center(child: NotCourse(context: context));
          },
        ),
      ),
    );
  }
}
