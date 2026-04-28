import 'package:everlms/core/theme/text_styles.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/loading_indicator.dart';
import 'package:everlms/feature/organization/presentation/providers/organization_provider.dart';
import 'package:everlms/feature/organization/presentation/widgets/course_info.dart';
import 'package:everlms/feature/organization/presentation/widgets/course_tab.dart';
import 'package:everlms/feature/organization/presentation/widgets/instructor_tab.dart';
import 'package:everlms/feature/organization/presentation/widgets/organization_header_section.dart';
import 'package:everlms/feature/organization/presentation/widgets/organizer_info.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class OrganizationScreen extends ConsumerStatefulWidget {
  const OrganizationScreen({required this.orgId, super.key});
  final int orgId;

  @override
  ConsumerState<OrganizationScreen> createState() => _OrganizationScreenState();
}

class _OrganizationScreenState extends ConsumerState<OrganizationScreen>
    with TickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final asyncState = ref.watch(organizationProvider(widget.orgId));

    return Scaffold(
      body: asyncState.when(
        loading: () => const LoadingIndicator(),
        error: (err, _) => Center(child: Text(err.toString())),
        data: (state) => _mainUI(context, state),
      ),
    );
  }

  Widget _mainUI(BuildContext context, OrganizationState state) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final profile = state.profile;
    if (profile == null) return const SizedBox.shrink();

    return DefaultTabController(
      length: 2,
      child: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) {
          return [
            SliverAppBar(
              expandedHeight: 240,
              elevation: 0,
              pinned: true,
              backgroundColor: Theme.of(context).primaryColor,
              leading: IconButton(
                icon: Icon(
                  Icons.arrow_back,
                  color: isDark ? Colors.white : Theme.of(context).cardColor,
                ),
                onPressed: () => context.pop(),
              ),
              title: _SABT(
                child: Text(
                  profile.name ?? '',
                  textAlign: TextAlign.start,
                  style: robotoSemiBold.copyWith(
                    color: isDark
                        ? Theme.of(context).colorScheme.onSurface
                        : Theme.of(context).cardColor,
                    fontSize: Dimensions.fontSizeDefault,
                  ),
                ),
              ),
              flexibleSpace: FlexibleSpaceBar(
                expandedTitleScale: 1,
                background: ColoredBox(
                  color: Theme.of(context).cardColor,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      OrganizationHeaderSection(data: profile),
                    ],
                  ),
                ),
              ),
            ),
            SliverPersistentHeader(
              delegate: _Delegate(
                height: 87,
                widget: ColoredBox(
                  color: Theme.of(context).cardColor,
                  child: OrganizerInfo(data: profile),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: CourseInformation(profileData: profile),
            ),
            SliverPersistentHeader(
              pinned: true,
              delegate: _Delegate(
                height: 40,
                widget: ColoredBox(
                  color: Theme.of(context).cardColor,
                  child: TabBar(
                    padding: const EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeDefault,
                    ),
                    indicatorWeight: 1,
                    controller: _tabController,
                    indicatorSize: TabBarIndicatorSize.tab,
                    indicatorColor: Theme.of(context).primaryColor,
                    labelColor: Theme.of(context).primaryColor,
                    unselectedLabelColor: isDark
                        ? Theme.of(context)
                            .colorScheme
                            .onSurface
                            .withValues(alpha: 0.6)
                        : Theme.of(context).colorScheme.onSurface,
                    unselectedLabelStyle: robotoMedium.copyWith(
                      color: Theme.of(context).primaryColor,
                      fontSize: Dimensions.fontSizeSmall,
                    ),
                    labelStyle: robotoBold.copyWith(
                      color: Theme.of(context).colorScheme.onSurface,
                      fontSize: Dimensions.fontSizeSmall,
                    ),
                    tabs: const [
                      Tab(text: 'COURSE'),
                      Tab(text: 'INSTRUCTOR'),
                    ],
                  ),
                ),
              ),
            ),
          ];
        },
        body: TabBarView(
          controller: _tabController,
          children: [
            CourseTab(orgId: widget.orgId),
            InstructorTab(orgId: widget.orgId),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Helper widgets (kept local to this screen)
// ---------------------------------------------------------------------------
class _Delegate extends SliverPersistentHeaderDelegate {
  _Delegate({required this.height, required this.widget});
  final double height;
  final Widget widget;

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) {
    return widget;
  }

  @override
  double get maxExtent => height;

  @override
  double get minExtent => height;

  @override
  bool shouldRebuild(SliverPersistentHeaderDelegate oldDelegate) => true;
}

class _SABT extends StatefulWidget {
  const _SABT({required this.child});
  final Widget child;

  @override
  _SABTState createState() => _SABTState();
}

class _SABTState extends State<_SABT> {
  ScrollPosition? _position;
  bool? _visible;

  @override
  void dispose() {
    _removeListener();
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _removeListener();
    _addListener();
  }

  void _addListener() {
    _position = Scrollable.of(context).position;
    _position?.addListener(_positionListener);
    _positionListener();
  }

  void _removeListener() {
    _position?.removeListener(_positionListener);
  }

  void _positionListener() {
    final settings =
        context.dependOnInheritedWidgetOfExactType<FlexibleSpaceBarSettings>();
    final visible =
        settings == null || settings.currentExtent <= settings.minExtent;
    if (_visible != visible) {
      setState(() => _visible = visible);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Visibility(
      visible: _visible ?? false,
      child: widget.child,
    );
  }
}
