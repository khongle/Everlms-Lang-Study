import 'package:everlms/feature/root/data/models/common/section.dart';

class LMSMyCourseModel {
  LMSMyCourseModel({
    this.courseName,
    this.fieldImage,
    this.id,
    this.fieldCategory,
    this.fieldResult,
    this.fieldTotalQuizPass,
    this.fieldUserCertificate,
    this.created,
    this.lmsUserCourseId,
    this.fieldLearnerNumber,
    this.fieldTotalLessons,
    this.fieldTotalHours,
    this.fieldAge,
    this.fieldLevel,
    this.fieldLanguage,
    this.fieldShortDescription,
    this.fieldDescription,
    this.fieldBody,
    this.fieldRequirements,
    this.fieldWhatYouLearn,
    this.url,
    this.progress,
    this.fieldLessons,
    this.listChapters,
    this.fieldFaq,
  });

  LMSMyCourseModel.fromJsonOffline(Map<String, dynamic> json) {
    id = json['product_id'];
    courseName = json['course_name'];
    fieldImage = json['field_image'];
    fieldCategory = json['field_category'];
    fieldResult = json['field_result'];
    fieldTotalQuizPass = json['field_total_quiz_pass'];
    fieldUserCertificate = json['field_user_certificate'];
    created = json['created'];
    lmsUserCourseId = json['uuid'];
    fieldLearnerNumber = json['field_learner_number'];
    fieldTotalLessons = json['field_total_lessons'];
    fieldTotalHours = json['field_total_hours'];
    fieldAge = json['field_age'];
    fieldLevel = json['field_level'];
    fieldLanguage = json['field_language'];
    fieldShortDescription = json['field_short_description'] as String?;
    fieldRequirements = json['field_requirements'];
    fieldWhatYouLearn = json['field_what_you_learn'];
    fieldDescription = json['field_body'];
    // url = json['url'];
    // fieldLessons = json['field_lessons'].cast<String>();
    // fieldFaq = (json['field_faq'] as String).split('-');
    progress = json['progress'];
  }

  // work with new api
  LMSMyCourseModel.fromJson(Map<String, dynamic> json) {
    id = (json['product_id'] != null && json['product_id'].isNotEmpty)
        ? json['product_id'][0]['value']
        : null;
    courseName = (json['title'] != null && json['title'].isNotEmpty)
        ? json['title'][0]['value']
        : null;
    fieldImage = (json['field_image'] != null && json['field_image'].isNotEmpty)
        ? json['field_image'][0]['url']
        : null;
    fieldCategory = json['field_category'];
    // fieldResult = json['field_result'];
    // fieldTotalQuizPass = json['field_total_quiz_pass'];
    // fieldUserCertificate = json['field_user_certificate'];
    // created = json['created'];
    lmsUserCourseId =
        (json['uuid'] != null && (json['uuid'] as List).isNotEmpty)
            ? json['uuid'][0]['value']
            : null;
    // fieldLearnerNumber = json['field_learner_number'];
    // fieldTotalLessons = json['field_total_lessons'];
    // fieldTotalHours = json['field_total_hours'];
    // fieldAge = json['field_age'];
    // fieldLevel = json['field_level'];
    // fieldLanguage = json['field_language'];
    fieldFaq = (json['field_faq'] != null && json['field_faq'].isNotEmpty)
        ? List.from((json['field_faq'] as List).map((e) => e['target_id']))
        : null;
    progress = double.parse(json['progress'].toString()) / 100;
    fieldShortDescription = (json['field_short_description'] != null &&
            json['field_short_description'].isNotEmpty)
        ? json['field_short_description'][0]['value']
        : null;
    fieldRequirements = (json['field_requirements'] != null &&
            json['field_requirements'].isNotEmpty)
        ? json['field_requirements'][0]['value']
        : null;
    fieldWhatYouLearn = (json['field_what_you_learn'] != null &&
            json['field_what_you_learn'].isNotEmpty)
        ? json['field_what_you_learn'][0]['value']
        : null;
    fieldBody = (json['body'] != null && json['body'].isNotEmpty)
        ? json['body'][0]['value']
        : null;
  }

  LMSMyCourseModel.fromJsonTeacherCourse(Map<String, dynamic> json) {
    id = (json['product_id'] != null && json['product_id'].isNotEmpty)
        ? json['product_id'][0]['value']
        : null;
    courseName = (json['title'] != null && json['title'].isNotEmpty)
        ? json['title'][0]['value']
        : null;
    fieldImage = (json['field_image'] != null && json['field_image'].isNotEmpty)
        ? json['field_image'][0]['url']
        : null;
    fieldCategory =
        (json['field_category'] != null && json['field_category'].isNotEmpty)
            ? json['field_category'][0]['target_id']
            : null;
    fieldResult =
        (json['field_result'] != null && json['field_result'].isNotEmpty)
            ? json['field_result'][0]['value']
            : null;
    fieldTotalQuizPass = (json['field_total_quiz_pass'] != null &&
            json['field_total_quiz_pass'].isNotEmpty)
        ? json['field_total_quiz_pass'][0]['value']
        : null;
    fieldUserCertificate = (json['field_user_certificate'] != null &&
            json['field_user_certificate'].isNotEmpty)
        ? json['field_user_certificate'][0]['value']
        : null;
    created = (json['created'] != null && json['created'].isNotEmpty)
        ? json['created'][0]['value']
        : null;
    lmsUserCourseId = (json['uuid'] != null && json['uuid'].isNotEmpty)
        ? json['uuid'][0]['value']
        : null;
    fieldLearnerNumber = (json['field_learner_number'] != null &&
            json['field_learner_number'].isNotEmpty)
        ? json['field_learner_number'][0]['value']
        : null;
    fieldTotalLessons = (json['field_total_lessons'] != null &&
            json['field_total_lessons'].isNotEmpty)
        ? json['field_total_lessons'][0]['value']
        : null;
    fieldTotalHours = (json['field_total_hours'] != null &&
            json['field_total_hours'].isNotEmpty)
        ? json['field_total_hours'][0]['value']
        : null;
    fieldAge = (json['field_age'] != null && json['field_age'].isNotEmpty)
        ? json['field_age'][0]['value']
        : null;
    fieldLevel = (json['field_level'] != null && json['field_level'].isNotEmpty)
        ? json['field_level'][0]['target_id']
        : null;
    fieldLanguage =
        (json['field_language'] != null && json['field_language'].isNotEmpty)
            ? json['field_language'][0]['target_id']
            : null;
    fieldShortDescription = (json['field_short_description'] != null &&
            json['field_short_description'].isNotEmpty)
        ? json['field_short_description'][0]['value']
        : null;
    fieldRequirements = (json['field_requirements'] != null &&
            json['field_requirements'].isNotEmpty)
        ? json['field_requirements'][0]['value']
        : null;
    fieldWhatYouLearn = (json['field_what_you_learn'] != null &&
            json['field_what_you_learn'].isNotEmpty)
        ? json['field_what_you_learn'][0]['value']
        : null;
    url = (json['path'] != null && json['path'].isNotEmpty)
        ? json['path'][0]['alias']
        : null;
    fieldLessons = (json['field_lessons'] as List<dynamic>?)
        ?.map((item) => item['target_id'] as String)
        .toList();
  }
  String? id;
  String? courseName;
  String? fieldImage;
  String? fieldCategory;
  String? fieldResult;
  String? fieldTotalQuizPass;
  String? fieldUserCertificate;
  String? created;
  String? lmsUserCourseId;
  String? fieldLearnerNumber;
  String? fieldTotalLessons;
  String? fieldTotalHours;
  String? fieldAge;
  String? fieldLevel;
  String? fieldLanguage;
  String? fieldShortDescription;
  String? fieldDescription;
  String? fieldBody;
  String? fieldRequirements;
  String? fieldWhatYouLearn;
  String? url;
  double? progress;
  List<String>? fieldLessons;
  List<Chapter>? listChapters;
  List<dynamic>? fieldFaq;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['title'] = courseName;
    data['product_id'] = id;
    data['course_name'] = courseName;
    data['field_image'] = fieldImage;
    data['field_category'] = fieldCategory;
    data['field_result'] = fieldResult;
    data['field_total_quiz_pass'] = fieldTotalQuizPass;
    data['field_user_certificate'] = fieldUserCertificate;
    data['created'] = created;
    data['uuid'] = lmsUserCourseId;
    data['field_learner_number'] = fieldLearnerNumber;
    data['field_total_lessons'] = fieldTotalLessons;
    data['field_total_hours'] = fieldTotalHours;
    data['field_age'] = fieldAge;
    data['field_level'] = fieldLevel;
    data['field_language'] = fieldLanguage;
    data['field_short_description'] = fieldShortDescription;
    data['field_requirements'] = fieldRequirements;
    data['field_what_you_learn'] = fieldWhatYouLearn;
    data['field_lessons'] = fieldLessons;
    data['field_faq'] = fieldFaq;
    data['progress'] = progress;
    data['field_body'] = fieldBody;
    return data;
  }
}
