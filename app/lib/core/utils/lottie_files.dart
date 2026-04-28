class LottieFiles {
  static String get successCheck => 'success_check'.json;
  static String get bouncyFail => 'bouncy_fail'.json;
  static String get congratulation => 'congratulation'.json;
}

extension on String {
  String get json => 'assets/lottie/$this.json';
}
