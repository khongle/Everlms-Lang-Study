import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() async {
  TestWidgetsFlutterBinding.ensureInitialized();

  // test('category api test ', () async {
  //   // Build our app and trigger a frame.
  //   await CategoryController().getCategoryList();
  //   expect(find.text('0'), findsNothing);
  //   expect(find.text('1'), findsOneWidget);
  // });
  setUp(() {
    // Clear and set mock values before each test
    SharedPreferences.setMockInitialValues({});
  });
}
