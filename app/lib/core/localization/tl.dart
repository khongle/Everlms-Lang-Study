import 'package:everlms/l10n/app_localizations.dart';
import 'package:everlms/main.dart' show navigatorKey;
import 'package:flutter/widgets.dart';

/// Global AppLocalizations accessor.
///
/// • In **widgets** (build method): prefer `context.tr.keyName`.
/// • In **controllers / non-widget code**: use the top-level getter `tlCtx`
///   which resolves through the app's global navigator context.
///
/// The getter is only safe to call **after** the first widget build.
/// Calling it during static field initialisation or very early in `main()`
/// will throw – use the [tlMaybe] getter in those edge-cases.

/// Convenience function for widget build methods.
AppLocalizations tl(BuildContext context) => AppLocalizations.of(context);

/// Extension for even shorter syntax: `context.tr.keyName`
extension AppLocalizationsX on BuildContext {
  AppLocalizations get tr => AppLocalizations.of(this);
}

/// Convenience getter for controllers / helpers (uses global navigator context).
AppLocalizations get tlCtx => AppLocalizations.of(navigatorKey.currentContext!);

/// Nullable variant – returns null if no context is available yet.
AppLocalizations? get tlCtxMaybe {
  final ctx = navigatorKey.currentContext;
  return ctx == null ? null : AppLocalizations.of(ctx);
}
