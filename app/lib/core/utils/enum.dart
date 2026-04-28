enum AppState {
  loading,
  loaded,
  error,
}

enum UserState {
  found,
  notFound,
  block,
}

enum HighlightCourse {
  learnIn2h('Learn a New Skill in 2 hours'),
  free('Start learning with Free Courses'),
  featured('Featured courses'),
  bestseller('Top courses');

  const HighlightCourse(this.name);
  final String name;
}

enum SwitchDomainState { verifying, domainNotExist, failed, completed }

enum HtmlType { termsOfUse, aboutUs, privacyPolicy, helpAndSupport }
