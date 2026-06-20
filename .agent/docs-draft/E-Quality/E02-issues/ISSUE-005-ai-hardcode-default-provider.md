# ISSUE-005 — AI Hardcode Default Provider (`openai`)

| Field | Value |
|-------|-------|
| ID | ISSUE-005 |
| Severity | **Medium** |
| Status | OPEN |
| Source | TASK-012 audit — code review `CourseBuilderService` |
| Related Unit | U-047 — AI provider plugin architecture |
| Related Flag | FF_FEAT_AI_PROVIDER |
| Found | 2026-04-15 |
| Owner | Unassigned |

## Mô tả

`CourseBuilderService::generateCourseStructure()` gọi hardcode:

```php
$ai = $this->aiProviderManager->createInstance('openai');
```

Mặc dù hệ thống đã có plugin architecture (`AiProviderManager`) hỗ trợ nhiều provider (OpenAI, Gemini, Pollinations), provider được dùng không lấy từ config hay user preference — luôn luôn là `openai`. Tương tự trong `ScormBuilderService` và `GoogleSlideBuilderService`.

## Cách tái hiện (Reproduction)

1. Vào `AiSettingsForm` → cấu hình default provider là `gemini`
2. Trigger course builder
3. Request vẫn đi đến OpenAI API, bỏ qua cấu hình

## Root Cause Hypothesis

`CourseBuilderService` không inject config factory hoặc `lms_ai.settings` config. Plugin manager đã sẵn sàng nhưng service gọi thẳng `createInstance('openai')` thay vì đọc giá trị cấu hình từ `\Drupal::config('lms_ai.settings')->get('default_provider')`.

## Tác động

- **Architecture** (G4): Vi phạm plugin abstraction — mục đích của AiProviderManager không được tận dụng
- **Flexibility**: Admin không thể switch provider qua UI
- **Cost**: Luôn dùng OpenAI kể cả khi Pollinations (free) phù hợp hơn

## Phương án khắc phục đề xuất

```php
// Trong constructor inject: ConfigFactoryInterface $configFactory
$providerId = $this->configFactory->get('lms_ai.settings')->get('default_provider') ?? 'openai';
$ai = $this->aiProviderManager->createInstance($providerId);
```

Áp dụng cho tất cả 3 builder services. Validate provider ID tồn tại trước khi `createInstance`.

## Traceability

- Unit spec: U-047 (AI provider plugin architecture)
- Related issues: ISSUE-003 (AI rollback)
- Test cần: verify provider switching qua config
