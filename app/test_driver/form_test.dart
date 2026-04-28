import 'package:flutter_driver/driver_extension.dart';
import 'package:flutter_driver/flutter_driver.dart';
import 'package:test/test.dart';

void main() {
  group('form', () {
    final button = find.byValueKey('route_form');

    enableFlutterDriverExtension();
    // const app.MyApp(languages: languages, bookingID: bookingID)();
    FlutterDriver? driver;
    // connect flutter driver to app before the executing run
    setUpAll(() async {
      driver = await FlutterDriver.connect();
    });
    // disconnect flutter driver to app before the executing run
    tearDownAll(() async {
      if (driver != null) {
        driver!.close();
      }
    });
    // start test
    test('Routing to form', () async {
      await driver!.tap(button);
    });
  });
}
