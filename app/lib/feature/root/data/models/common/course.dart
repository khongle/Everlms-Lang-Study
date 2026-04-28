import 'package:everlms/feature/course_details/data/models/course_detail/course_preview.dart';
import 'package:everlms/feature/root/data/models/common/instructor.dart';
import 'package:everlms/feature/root/data/models/common/section.dart';

class CourseModel {
  CourseModel({this.success, this.message, this.content});

  CourseModel.fromJson(Map<String, dynamic> json) {
    success = json['success'];
    message = json['message'];
    content =
        json['data'] != null ? ServiceContent.fromJson(json['data']) : null;
  }
  bool? success;
  String? message;
  ServiceContent? content;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['success'] = success;
    data['message'] = message;
    if (content != null) {
      data['data'] = content!.toJson();
    }
    return data;
  }
}

class ServiceContent {
  ServiceContent({this.courses});

  ServiceContent.fromJson(Map<String, dynamic> json) {
    if (json['courses'] != null) {
      courses = <Course>[];
      json['courses'].forEach((v) {
        courses!.add(Course.fromJson(v));
      });
    }
  }
  List<Course>? courses;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    if (courses != null) {
      data['courses'] = courses!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Course {
  Course({
    this.id,
    this.thumbnail,
    this.title,
    this.totalLessons,
    this.totalEnrolls,
    this.isFree,
    this.isFeature,
    this.isBestseller,
    this.isStart,
    this.totalRating,
    this.price,
    this.isDiscounted,
    this.discountType,
    this.discountedPrice,
    this.fieldResult,
    this.fieldLanguage,
    this.fieldLevel,
    this.fieldBody,
    this.fieldImage,
    this.fieldCoursePreview,
    this.fieldCategory,
    this.totalQuizzes,
    this.fieldUserCertificate,
    this.created,
    this.changed,
    this.lmsUserCourseId,
    this.fieldLearnerNumber,
    this.fieldTotalHours,
    this.fieldAge,
    this.fieldShortDescription,
    this.fieldRequirements,
    this.fieldWhatYouLearn,
    this.fieldFaq,
    this.url,
    this.progress,
    this.instructor,
    this.teacherId,
    this.languages,
    this.fieldLessons,
    this.listChapters,
  });

  Course.fromJson(Map<String, dynamic> json) {
    if (json['id'] is String) {
      id = int.tryParse(json['id']);
    } else {
      id = json['id'];
    }
    thumbnail = json['thumbnail'];
    title = json['title'];
    totalLessons = json['field_total_lessons'];
    totalEnrolls = json['total_enrolls'];
    isFree = json['is_free'];
    totalRating = json['total_rating'];
    price = json['price'];
    isDiscounted = json['is_discounted'];
    discountType = json['discount_type'];
    discountedPrice = json['discounted_price'];
    fieldResult = json['field_result'];
    fieldLanguage = json['field_language'];
    fieldLevel = json['field_level'];
    fieldImage = json['field_image'];
    fieldCategory = json['field_category'];
    fieldResult = json['field_result'];
    totalQuizzes = json['field_total_quizzes'];
    fieldUserCertificate = json['field_user_certificate'];
    created = json['created'];
    // lmsUserCourseId = json['lms_user_course_id'];
    lmsUserCourseId = json['uuid'];
    fieldLearnerNumber = json['field_learner_number'];
    fieldTotalHours = json['field_total_hours'];
    fieldAge = json['field_age'];
    fieldLevel = json['field_level'];
    fieldLanguage = json['field_language'];
    fieldShortDescription = json['field_short_description'];
    fieldRequirements = json['field_requirements'];
    fieldWhatYouLearn = json['field_what_you_learn'];
    fieldLessons = json['field_lessons'];
    listChapters = json['list_sections'];
    url = json['url'];
    fieldFaq = json['field_faq'];
    fieldBody = json['field_body'];
    final rawProgress = json['progress'];
    if (rawProgress is num) {
      progress = rawProgress.toDouble() / 100;
    } else {
      progress = 0.0;
    }
  }

  factory Course.fromJsonCourseDetail(Map<String, dynamic> json) {
    int? parseId(dynamic value) {
      if (value is int) {
        return value;
      } else if (value is String) {
        return int.tryParse(value);
      }
      return null;
    }

    String? parseRating(dynamic value) {
      final str = (value != null && (value as List).isNotEmpty)
          ? value[0]['rating'].toString()
          : null;
      final rating =
          double.parse((double.parse(str ?? '0') / 20).toStringAsFixed(1))
              .toString();
      return rating;
    }

    double? parseProgress(dynamic value) {
      if (value is num) {
        return value.toDouble() / 100;
      }
      return null;
    }

    return Course(
      id: (json['product_id'] != null &&
              (json['product_id'] as List).isNotEmpty)
          ? parseId(json['product_id'][0]['value'])
          : null,
      title: (json['title'] != null && (json['title'] as List).isNotEmpty)
          ? json['title'][0]['value']
          : null,
      fieldCoursePreview: (json['field_course_preview'] != null &&
              (json['field_course_preview'] as List).isNotEmpty)
          ? CoursePreview.fromJson(json['field_course_preview'][0])
          : null,
      isFree: (json['field_free'] != null &&
              (json['field_free'] as List).isNotEmpty)
          ? ((json['field_free'][0]['value']) == '1')
          : null,
      isFeature: (json['field_featured_course'] != null &&
              (json['field_featured_course'] as List).isNotEmpty)
          ? ((json['field_featured_course'][0]['value']) == '1')
          : null,
      isBestseller: (json['field_bestseller'] != null &&
              (json['field_bestseller'] as List).isNotEmpty)
          ? ((json['field_bestseller'][0]['value']) == '1')
          : null,
      isStart: json['start_course'] ?? false,
      fieldImage: (json['field_image'] != null &&
              (json['field_image'] as List).isNotEmpty)
          ? json['field_image'][0]['url']
          : null,
      fieldLanguage: json['field_language'].toString(),
      fieldLearnerNumber: (json['field_learner_number'] != null &&
              (json['field_learner_number'] as List).isNotEmpty)
          ? json['field_learner_number'][0]['value'].toString()
          : null,
      fieldLessons: (json['field_lessons'] != null &&
              (json['field_lessons'] as List).isNotEmpty)
          ? (json['field_lessons'] as List)
              .map((lesson) => lesson['target_id'].toString())
              .toList()
          : null,
      fieldRequirements: (json['field_requirements'] != null &&
              (json['field_requirements'] as List).isNotEmpty)
          ? json['field_requirements'][0]['value']
          : null,
      fieldLevel: json['field_level'].toString(),
      fieldShortDescription: (json['field_short_description'] != null &&
              (json['field_short_description'] as List).isNotEmpty)
          ? json['field_short_description'][0]['value']
          : null,
      totalRating: parseRating(json['field_rating']),
      fieldTotalHours: (json['field_total_hours'] != null &&
              (json['field_total_hours'] as List).isNotEmpty)
          ? json['field_total_hours'][0]['value'].toString()
          : null,
      totalLessons: (json['field_total_lessons'] != null &&
              (json['field_total_lessons'] as List).isNotEmpty)
          ? parseId(json['field_total_lessons'][0]['value'])
          : null,
      totalQuizzes: (json['field_total_quizzes'] != null &&
              (json['field_total_quizzes'] as List).isNotEmpty)
          ? json['field_total_quizzes'][0]['value'].toString()
          : null,
      fieldWhatYouLearn: (json['field_what_you_learn'] != null &&
              (json['field_what_you_learn'] as List).isNotEmpty)
          ? json['field_what_you_learn'][0]['value']
          : null,
      fieldFaq:
          (json['field_faq'] != null && (json['field_faq'] as List).isNotEmpty)
              ? (json['field_faq'] as List).map((e) => e['target_id']).toList()
              : [],
      url: (json['path'] != null && (json['path'] as List).isNotEmpty)
          ? json['path'][0]['alias']
          : null,
      fieldBody: (json['body'] != null && (json['body'] as List).isNotEmpty)
          ? json['body'][0]['value']
          : null,
      fieldCategory:
          json['field_category'] is String ? json['field_category'] : '',
      teacherId: (json['field_teacher'] != null &&
              (json['field_teacher'] as List).isNotEmpty)
          ? json['field_teacher'][0]['target_id'].toString()
          : '',
      created: json['created'][0]['value'],
      changed: json['changed'][0]['value'],
      progress: parseProgress(json['progress'] ?? 0),
    );
  }
  int? id;
  int? orderId;
  String? thumbnail;
  String? title;
  int? totalLessons;
  int? totalEnrolls;
  bool? isFree;
  bool? isFeature;
  bool? isBestseller;
  bool? isStart;
  String? totalRating;
  String? price;
  bool? isDiscounted;
  String? discountType;
  String? discountedPrice;
  String? fieldResult;
  String? fieldLanguage;
  String? fieldLevel;
  CoursePreview? fieldCoursePreview;
  String? fieldImage;
  String? fieldCategory;
  String? totalQuizzes;
  String? fieldUserCertificate;
  String? created;
  String? changed;
  String? lmsUserCourseId;
  String? fieldLearnerNumber;
  String? fieldTotalHours;
  String? fieldAge;
  String? fieldBody;
  String? fieldShortDescription;
  String? fieldRequirements;
  String? fieldWhatYouLearn;
  List<dynamic>? fieldFaq;
  String? url;
  double? progress;
  Instructor? instructor;
  String? teacherId;
  List<String>? languages;
  List<String>? fieldLessons;
  List<Chapter>? listChapters;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['thumbnail'] = thumbnail;
    data['title'] = title;
    data['field_total_lessons'] = totalLessons;
    data['total_enrolls'] = totalEnrolls;
    data['is_free'] = isFree;
    data['total_rating'] = totalRating;
    data['price'] = price;
    data['is_discounted'] = isDiscounted;
    data['discount_type'] = discountType;
    data['discounted_price'] = discountedPrice;
    data['field_result'] = fieldResult;
    data['field_language'] = fieldLanguage;
    data['field_level'] = fieldLevel;
    data['field_image'] = fieldImage;
    data['field_category'] = fieldCategory;
    data['field_result'] = fieldResult;
    data['field_total_quizzes'] = totalQuizzes;
    data['field_user_certificate'] = fieldUserCertificate;
    data['created'] = created;
    data['lms_user_course_id'] = lmsUserCourseId;
    data['field_learner_number'] = fieldLearnerNumber;
    data['field_total_hours'] = fieldTotalHours;
    data['field_age'] = fieldAge;
    data['field_level'] = fieldLevel;
    data['field_language'] = fieldLanguage;
    data['field_short_description'] = fieldShortDescription;
    data['field_requirements'] = fieldRequirements;
    data['field_what_you_learn'] = fieldWhatYouLearn;
    data['field_lessons'] = fieldLessons;
    data['list_sections'] = listChapters;
    data['url'] = url;
    data['field_faq'] = fieldFaq;
    data['progress'] = progress;
    return data;
  }
}
