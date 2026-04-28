class CourseNotPurchasedModel {
  CourseNotPurchasedModel({
    this.productId,
    this.title,
    this.fieldAge,
    this.fieldCategory,
    this.fieldImage,
    this.fieldLanguage,
    this.fieldLearnerNumber,
    this.fieldLessons,
    this.fieldLevel,
    this.fieldRequirements,
    this.fieldShortDescription,
    this.title1,
    this.fieldTotalHours,
    this.fieldTotalLessons,
    this.fieldWhatYouLearn,
  });

  CourseNotPurchasedModel.fromJson(Map<String, dynamic> json) {
    productId = json['product_id'];
    title = json['title'];
    fieldAge = json['field_age'];
    fieldCategory = json['field_category'];
    fieldImage = json['field_image'];
    fieldLanguage = json['field_language'];
    fieldLearnerNumber = json['field_learner_number'];
    fieldLessons = json['field_lessons'];
    fieldLevel = json['field_level'];
    fieldRequirements = json['field_requirements'];
    fieldShortDescription = json['field_short_description'];
    title1 = json['title_1'];
    fieldTotalHours = json['field_total_hours'];
    fieldTotalLessons = json['field_total_lessons'];
    fieldWhatYouLearn = json['field_what_you_learn'];
  }
  String? productId;
  String? title;
  String? fieldAge;
  String? fieldCategory;
  String? fieldImage;
  String? fieldLanguage;
  String? fieldLearnerNumber;
  String? fieldLessons;
  String? fieldLevel;
  String? fieldRequirements;
  String? fieldShortDescription;
  String? title1;
  String? fieldTotalHours;
  String? fieldTotalLessons;
  String? fieldWhatYouLearn;

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['product_id'] = productId;
    data['title'] = title;
    data['field_age'] = fieldAge;
    data['field_category'] = fieldCategory;
    data['field_image'] = fieldImage;
    data['field_language'] = fieldLanguage;
    data['field_learner_number'] = fieldLearnerNumber;
    data['field_lessons'] = fieldLessons;
    data['field_level'] = fieldLevel;
    data['field_requirements'] = fieldRequirements;
    data['field_short_description'] = fieldShortDescription;
    data['title_1'] = title1;
    data['field_total_hours'] = fieldTotalHours;
    data['field_total_lessons'] = fieldTotalLessons;
    data['field_what_you_learn'] = fieldWhatYouLearn;
    return data;
  }
}
