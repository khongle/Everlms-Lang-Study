# EverLMS — Test Plan

## 1. Testing Strategy

| Layer | Tool | Scope |
|-------|------|-------|
| Unit Test | PHPUnit | Services, handlers, utilities |
| Kernel Test | PHPUnit (KernelTestBase) | Entity CRUD, database, services with DI |
| Functional Test | PHPUnit (BrowserTestBase) | Full page rendering, forms |
| E2E Test | Playwright | User flows, cross-browser |
| Code Quality | PHPCS + PHPStan | Standards, static analysis |

---

## 2. Unit Tests

### 2.1 Question Handlers
| # | Test | Mô tả |
|---|------|-------|
| UT-01 | `MultipleChoiceHandlerTest` | Score calculation cho multi-select |
| UT-02 | `SingleChoiceHandlerTest` | Score calculation cho single-select |
| UT-03 | `TrueFalseHandlerTest` | Boolean scoring |
| UT-04 | `FillBlankHandlerTest` | Gap matching logic |
| UT-05 | `ShortAnswerHandlerTest` | exact/contains/keywords matching |
| UT-06 | `MatchingHandlerTest` | Pair matching score |
| UT-07 | `OrderingHandlerTest` | Sequence scoring |
| UT-08 | `EssayHandlerTest` | Word count validation |

### 2.2 Services
| # | Test | Mô tả |
|---|------|-------|
| UT-10 | `ProgressCalculatorTest` | Progress % calculation |
| UT-11 | `CertificateGeneratorTest` | Token replacement |
| UT-12 | `CouponValidatorTest` | Coupon validation rules |
| UT-13 | `PaymentGatewayManagerTest` | Gateway resolution |

---

## 3. Kernel Tests

| # | Test | Mô tả |
|---|------|-------|
| KT-01 | `CourseEntityTest` | Course CRUD + field validation |
| KT-02 | `ChapterEntityTest` | Chapter CRUD + ordering |
| KT-03 | `LessonEntityTest` | Lesson CRUD + types |
| KT-04 | `QuizEntityTest` | Quiz CRUD + question assignment |
| KT-05 | `QuestionEntityTest` | Question bundles CRUD |
| KT-06 | `QuizResultEntityTest` | Result creation + score storage |
| KT-07 | `OrderEntityTest` | Order CRUD + status transitions |
| KT-08 | `MembershipEntityTest` | Membership creation + access |
| KT-09 | `CertificateEntityTest` | Certificate + user certificate |
| KT-10 | `ProgressTrackingTest` | User progress entities |

---

## 4. E2E Tests (Playwright)

### 4.1 Student Flows
| # | Test | Steps |
|---|------|-------|
| E2E-01 | Registration | Register → Verify email → Login |
| E2E-02 | Course Browse | Browse → Filter → Search → View detail |
| E2E-03 | Free Enrollment | Find free course → Enroll → Start learning |
| E2E-04 | Paid Enrollment | Add to cart → Checkout → Stripe payment → Enroll |
| E2E-05 | Lesson Completion | Open lesson → Read/Watch → Mark complete |
| E2E-06 | Quiz Taking - One Page | Start quiz → Answer all → Submit → View result |
| E2E-07 | Quiz Taking - Steps | Start quiz → Navigate → Submit → View result |
| E2E-08 | Certificate Download | Complete course → View cert → Download PDF |
| E2E-09 | Progress Tracking | Complete lessons → Verify progress bar updates |
| E2E-10 | Course Rating | Complete course → Rate → Write review |

### 4.2 Instructor Flows
| # | Test | Steps |
|---|------|-------|
| E2E-20 | Course Creation | Login → Create course → Add chapters → Add lessons |
| E2E-21 | Quiz Creation | Create quiz → Add questions (each type) → Publish |
| E2E-22 | Essay Grading | View grading queue → Grade essay → Submit score |
| E2E-23 | Student Progress | View enrolled students → Check progress |

### 4.3 Admin Flows
| # | Test | Steps |
|---|------|-------|
| E2E-30 | User Management | Create user → Assign role → Verify access |
| E2E-31 | Order Management | View orders → Process refund |
| E2E-32 | Reports | View dashboard → Export report |
| E2E-33 | Certificate Template | Create template → Preview → Activate |
| E2E-34 | Settings | Configure payment → Configure email |

---

## 5. Performance Tests

| # | Test | Target |
|---|------|--------|
| PT-01 | Homepage load time | < 2s (cold), < 1s (cached) |
| PT-02 | Course listing (100+ courses) | < 2s |
| PT-03 | Lesson page load | < 1.5s |
| PT-04 | Quiz submission (50 questions) | < 3s |
| PT-05 | Search results | < 1s |
| PT-06 | API response time | < 500ms |

---

## 6. Test Commands

```bash
# Run all PHPUnit tests
ddev exec vendor/bin/phpunit -c web/core web/modules/custom/

# Run specific module tests
ddev exec vendor/bin/phpunit web/modules/custom/lms_quiz/tests/

# Run Playwright tests
ddev exec npx playwright test

# Code quality
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/
ddev exec vendor/bin/phpstan analyze web/modules/custom/
```
