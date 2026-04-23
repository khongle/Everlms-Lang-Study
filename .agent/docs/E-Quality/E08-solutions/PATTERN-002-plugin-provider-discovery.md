# PATTERN-002 — Plugin Provider Discovery

| Field | Value |
|-------|-------|
| ID | PATTERN-002 |
| Source Modules | `lms_ai` (AiProviderManager), `lms_payment` (PaymentGatewayManager) |
| Source Files | `lms_ai/src/Plugin/AiProviderManager.php`, `lms_ai/src/Annotation/AiProvider.php`, `lms_payment/src/Plugin/PaymentGatewayManager.php` |
| Pattern Type | Drupal Plugin Manager (PSR-compliant discovery) |
| Problem Solved | Thêm provider/gateway mới mà không sửa core service |

## Bối cảnh

EverLMS cần hỗ trợ nhiều AI providers (OpenAI, Gemini, Pollinations) và nhiều payment gateways (Stripe, PayPal, ZaloPay, BankTransfer) — và phải dễ mở rộng thêm trong tương lai.

Static factory (PATTERN-001) không phù hợp vì:
- Provider cần inject dependencies (HTTP client, logger, config)
- Muốn third-party module có thể đăng ký provider riêng
- Cần metadata (tên provider, supported capabilities) kèm theo

## Giải pháp Thực tế

### AiProviderManager (lms_ai)

```
lms_ai/src/
├── Annotation/AiProvider.php          # @AiProvider annotation class
├── Plugin/AiProviderInterface.php     # Contract: generateText(), generateJson()
├── Plugin/AiProviderManager.php       # Extends DefaultPluginManager
└── Plugin/AiProvider/
    ├── OpenAiProvider.php             # @AiProvider(id="openai", label="OpenAI")
    ├── GeminiProvider.php             # @AiProvider(id="gemini", label="Google Gemini")
    └── PollinationsProvider.php       # @AiProvider(id="pollinations", label="Pollinations")
```

`AiProviderManager` extends Drupal `DefaultPluginManager` — discovery tự động qua annotation + namespace scan.

```php
// Sử dụng
$ai = $this->aiProviderManager->createInstance('gemini');
$ai->generateText($prompt);
```

### Cách thêm provider mới

1. Tạo class implement `AiProviderInterface`
2. Đặt trong `Plugin/AiProvider/` của bất kỳ module nào
3. Thêm `@AiProvider(id="my_provider", label="My Provider")` annotation
4. Drupal tự discover — không cần sửa core

## Khi nào áp dụng

- Cần extensibility bởi third-party module
- Strategy cần dependency injection (services, config, HTTP client)
- Muốn metadata (labels, capabilities) đi kèm plugin
- Số lượng implementations có thể tăng theo thời gian

## So sánh với PATTERN-001

| Aspect | PATTERN-001 (Static Factory) | PATTERN-002 (Plugin Manager) |
|--------|------------------------------|------------------------------|
| DI support | Không | Đầy đủ |
| Third-party extensible | Không | Có |
| Discovery | Hardcoded `match()` | Annotation scan |
| Overhead | Minimal | Drupal plugin cache |
| Best for | Stateless value objects | Stateful, injectable services |

## Issue liên quan

- ISSUE-005: `CourseBuilderService` hardcode `createInstance('openai')` thay vì đọc config → vi phạm mục đích của pattern này
- Fix: inject `ConfigFactoryInterface`, đọc `lms_ai.settings.default_provider`

## Bài học

Plugin Manager pattern là chuẩn Drupal cho extensible subsystems. Luôn kết hợp với config form (AiSettingsForm) để admin có thể chọn active provider qua UI thay vì hardcode.
