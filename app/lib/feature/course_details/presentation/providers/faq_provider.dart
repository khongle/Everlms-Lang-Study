import 'package:everlms/core/providers/core_providers.dart';
import 'package:everlms/feature/course_details/data/models/course_detail/faq.dart';
import 'package:everlms/feature/course_details/data/repositories/faq_repository.dart';
import 'package:everlms/feature/root/data/models/common/course.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// ---------------------------------------------------------------------------
// Repository provider
// ---------------------------------------------------------------------------
final faqRepositoryProvider = Provider<FaqRepository>((ref) {
  return FaqRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// All FAQs provider (loaded once, cached)
// ---------------------------------------------------------------------------
final faqListProvider = FutureProvider<List<Faq>>((ref) async {
  final repo = ref.read(faqRepositoryProvider);
  return repo.getFaqList();
});

// ---------------------------------------------------------------------------
// FAQs filtered by course
// ---------------------------------------------------------------------------
final Provider<List<Faq>> Function(Course) faqByCourseProvider =
    Provider.family<List<Faq>, Course>((ref, course) {
  final allFaqs = ref.watch(faqListProvider).valueOrNull ?? [];
  final faqIDs = course.fieldFaq ?? [];
  if (allFaqs.isEmpty || faqIDs.isEmpty) return [];
  return allFaqs.where((faq) => faqIDs.contains(faq.id?.toString())).toList();
});
