class ConfigModel {
  ConfigModel({this.success = false, this.message = '', this.data});

  ConfigModel.fromJson(Map<String, dynamic> json) {
    success = json['success'];
    message = json['message'];
    data = Data.fromJson(json['data']);
  }
  late bool success;
  late String message;
  Data? data;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['success'] = success;
    data['message'] = message;
    data['data'] = this.data?.toJson();
    return data;
  }
}

class Data {
  Data({
    required this.appConfig,
    required this.androidVersion,
    required this.iosVersion,
    required this.pages,
  });

  Data.fromJson(Map<String, dynamic> json) {
    appConfig = AppConfig.fromJson(json['app_config']);
    androidVersion = AndroidVersion.fromJson(json['android_version']);
    iosVersion = IosVersion.fromJson(json['ios_version']);
    pages = Pages.fromJson(json['pages']);
  }
  late AppConfig appConfig;
  late AndroidVersion androidVersion;
  late IosVersion iosVersion;
  late Pages pages;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['app_config'] = appConfig.toJson();
    data['android_version'] = androidVersion.toJson();
    data['ios_version'] = iosVersion.toJson();
    data['pages'] = pages.toJson();
    return data;
  }
}

class AppConfig {
  AppConfig({
    required this.loginMandatory,
    required this.introSkippable,
    required this.privacyPolicyUrl,
    required this.termsConditionUrl,
    required this.supportUrl,
    required this.cancellationPolicy,
    required this.refundPolicy,
    required this.disableOtp,
    required this.disableEmail,
    required this.defaultCountry,
    required this.logo,
    required this.baseUrl,
    required this.assetBaseUrl,
    required this.defaultCurrency,
    required this.paginationLimit,
  });

  AppConfig.fromJson(Map<String, dynamic> json) {
    loginMandatory = json['login_mandatory'];
    introSkippable = json['intro_skippable'];
    privacyPolicyUrl = json['privacy_policy_url'];
    termsConditionUrl = json['terms_condition_url'];
    supportUrl = json['support_url'];
    cancellationPolicy = json['cancellation_policy'];
    refundPolicy = json['refund_policy'];
    disableOtp = json['disable_otp'];
    disableEmail = json['disable_email'];
    defaultCountry = json['default_country'];
    logo = json['logo'];
    baseUrl = json['base_url'];
    assetBaseUrl = json['asset_base_url'];
    defaultCurrency = json['default_currency'];
    paginationLimit = json['pagination_limit'];
  }
  late bool loginMandatory;
  late bool introSkippable;
  late String privacyPolicyUrl;
  late String termsConditionUrl;
  late String supportUrl;
  late String cancellationPolicy;
  late bool disableOtp;
  late String refundPolicy;
  late bool disableEmail;
  late String defaultCountry;
  late String logo;
  late String baseUrl;
  late String assetBaseUrl;
  late String defaultCurrency;
  dynamic paginationLimit;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['login_mandatory'] = loginMandatory;
    data['intro_skippable'] = introSkippable;
    data['privacy_policy_url'] = privacyPolicyUrl;
    data['terms_condition_url'] = termsConditionUrl;
    data['support_url'] = supportUrl;
    data['cancellation_policy'] = cancellationPolicy;
    data['refund_policy'] = refundPolicy;
    data['disable_otp'] = disableOtp;
    data['disable_email'] = disableEmail;
    data['default_country'] = defaultCountry;
    data['logo'] = logo;
    data['base_url'] = baseUrl;
    data['asset_base_url'] = assetBaseUrl;
    data['default_currency'] = defaultCurrency;
    data['pagination_limit'] = paginationLimit;
    return data;
  }
}

class AndroidVersion {
  AndroidVersion({
    required this.latestApkVersion,
    required this.apkCode,
    required this.apkFileUrl,
    required this.whatsNew,
    required this.updateSkippable,
  });

  AndroidVersion.fromJson(Map<String, dynamic> json) {
    latestApkVersion = json['latest_apk_version'];
    apkCode = json['apk_code'];
    apkFileUrl = json['apk_file_url'];
    whatsNew = json['whats_new'];
    updateSkippable = json['update_skippable'];
  }
  late String latestApkVersion;
  late String apkCode;
  late String apkFileUrl;
  late String whatsNew;
  late bool updateSkippable;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['latest_apk_version'] = latestApkVersion;
    data['apk_code'] = apkCode;
    data['apk_file_url'] = apkFileUrl;
    data['whats_new'] = whatsNew;
    data['update_skippable'] = updateSkippable;
    return data;
  }
}

class IosVersion {
  IosVersion({
    required this.latestIpaVersion,
    required this.ipaCode,
    required this.ipaFileUrl,
    required this.whatsNew,
    required this.updateSkippable,
  });

  IosVersion.fromJson(Map<String, dynamic> json) {
    latestIpaVersion = json['latest_ipa_version'];
    ipaCode = json['ipa_code'];
    ipaFileUrl = json['ipa_file_url'];
    whatsNew = json['whats_new'];
    updateSkippable = json['update_skippable'];
  }
  late String latestIpaVersion;
  late String ipaCode;
  late String ipaFileUrl;
  late String whatsNew;
  late bool updateSkippable;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['latest_ipa_version'] = latestIpaVersion;
    data['ipa_code'] = ipaCode;
    data['ipa_file_url'] = ipaFileUrl;
    data['whats_new'] = whatsNew;
    data['update_skippable'] = updateSkippable;
    return data;
  }
}

class Pages {
  Pages({
    required this.privacyPolicy,
    required this.termsOfUse,
    required this.aboutUs,
    required this.helpAndSupport,
  });

  Pages.fromJson(Map<String, dynamic> json) {
    privacyPolicy = json['privacy_policy'] != null
        ? PageItem.fromJson(json['privacy_policy'])
        : null;
    termsOfUse = json['terms_of_use'] != null
        ? PageItem.fromJson(json['terms_of_use'])
        : null;
    aboutUs =
        json['about_us'] != null ? PageItem.fromJson(json['about_us']) : null;
    helpAndSupport = json['help_support'] != null
        ? PageItem.fromJson(json['help_support'])
        : null;
  }
  PageItem? privacyPolicy;
  PageItem? termsOfUse;
  PageItem? aboutUs;
  PageItem? helpAndSupport;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['privacy_policy'] = privacyPolicy?.toJson();
    data['terms_of_use'] = termsOfUse?.toJson();
    data['about_us'] = aboutUs?.toJson();
    data['help_support'] = helpAndSupport?.toJson();
    return data;
  }
}

class PageItem {
  PageItem({this.id = '', this.title = '', this.body = ''});

  PageItem.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    title = json['title'];
    body = json['body'];
  }
  late String id;
  late String title;
  late String body;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['title'] = title;
    data['body'] = body;
    return data;
  }
}
