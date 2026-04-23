# G01-ADR-006: AI Provider Drupal Plugin Architecture

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

**Resolves:** DEC-002 (AI provider abstraction: plugin vs service)

---

## Context

Module `lms_ai` cần tích hợp với nhiều AI provider (OpenAI, Google Gemini, Pollinations) để:
- Generate quiz questions từ course content
- Generate course description, SCORM content
- Generate images cho bài học

Yêu cầu:
- Có thể switch provider mà không cần sửa code caller
- Admin có thể configure provider qua Drupal admin UI
- Support thêm provider mới trong tương lai (ví dụ: Anthropic Claude, Cohere)
- Provider-specific credentials (API key, model name) cần lưu trong Drupal config

DEC-002 đặt câu hỏi: Drupal Plugin system hay Service+Config pattern?

---

## Decision

Sử dụng **Drupal Plugin Manager Pattern** (`DefaultPluginManager`) cho AI providers:

**Interface** (`lms_ai/src/Plugin/AiProviderInterface.php`):
```php
interface AiProviderInterface extends PluginInspectionInterface {
  public function generateJson(string $prompt, ?string $schema = NULL, array $options = []): ?array;
  public function generateImage(string $prompt, array $options = []): ?string;
}
```

**Manager** (`lms_ai/src/Plugin/AiProviderManager.php`):
```php
class AiProviderManager extends DefaultPluginManager {
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/AiProvider',
      $namespaces,
      $module_handler,
      AiProviderInterface::class,
      AiProvider::class
    );
    $this->alterInfo('lms_ai_provider_info');
    $this->setCacheBackend($cache_backend, 'lms_ai_provider_plugins');
  }
}
```

**Implementations** (3 providers đã có):
- `Plugin/AiProvider/OpenAiProvider.php`
- `Plugin/AiProvider/GeminiProvider.php`
- `Plugin/AiProvider/PollinationsProvider.php`

**Discovery:** `Plugin/AiProvider/` directory, annotation `@AiProvider`

---

## Rationale

**Tại sao Drupal Plugin thay vì Service+Config:**

1. **Discovery tự động:** Plugin manager tự scan `Plugin/AiProvider/` directories kể cả trong contrib/custom modules. Service+Config yêu cầu đăng ký thủ công trong `services.yml`.

2. **Third-party extensibility:** Với `alterInfo('lms_ai_provider_info')`, các module khác có thể modify plugin definitions mà không cần sửa `lms_ai` code.

3. **Cache plugin definitions:** `AiProviderManager` cache plugin discovery tại `lms_ai_provider_plugins` — không scan filesystem mỗi request.

4. **Drupal-native pattern:** Cùng pattern với `PaymentGatewayManager`, `BlockManager`, `FieldTypeManager` — developer quen thuộc ngay.

5. **Annotation-driven config:** Provider metadata (label, default model, capabilities) trong `@AiProvider` annotation, không cần YAML file riêng.

---

### Options Considered

#### Option 1: Drupal Plugin Manager (CHOSEN)
- **Pros:** Auto-discovery, third-party extensible, cache built-in, Drupal-native, consistent với payment plugin pattern
- **Cons:** Annotation boilerplate cho mỗi provider, DefaultPluginManager constructor verbose
- **Fit:** Rất phù hợp — pattern đã proven trong `lms_payment`

#### Option 2: Service + Config Entity (REJECTED)
- **Pros:** Simpler setup, không cần annotation, dễ test với mock service
- **Cons:** Phải đăng ký từng provider trong `services.yml` thủ công, không extensible bởi contrib modules, không có plugin discovery mechanism
- **Reason rejected:** DEC-002 resolved — extensibility quan trọng hơn simplicity cho AI layer

#### Option 3: Hard-coded switch trong service (REJECTED)
- **Pros:** Zero overhead
- **Cons:** Không extensible, vi phạm Open/Closed Principle, phải sửa core code để thêm provider
- **Reason rejected:** Hoàn toàn không phù hợp với yêu cầu extensibility

---

## Consequences

### Positive
- Thêm provider mới: tạo class với `@AiProvider` annotation trong `Plugin/AiProvider/` — không sửa existing code
- Admin có thể select provider từ dropdown (populated từ plugin definitions)
- Caller code không biết concrete provider — chỉ gọi qua `AiProviderInterface`
- Plugin cache giảm filesystem scan overhead
- Consistent với `PaymentGatewayManager` pattern (ADR-007)

### Negative
- Annotation-based discovery coupling — nếu annotation thay đổi cần update tất cả providers
- `DefaultPluginManager` constructor cần 3 arguments — boilerplate trong `services.yml`
- Drupal Plugin system learning curve cho developer chưa quen

### Risks
- **RISK-006** (G02): AI provider API breaking changes — mitigate bằng plugin abstraction (provider-specific logic nằm trong từng plugin) và feature flag
- Provider config (API key) cần lưu trong Drupal State hoặc config entity — tránh lưu trong code hoặc `settings.php` hardcode
- TASK-012 audit ghi nhận hardcoded AI credentials là known issue cần fix

---

## Related

- **Design docs:** C01-architecture-decision.md (Design Patterns: Plugin/AI providers)
- **Open decision:** DEC-002 (resolved by this ADR)
- **Risk:** RISK-006 (G02-risk-register.md)
- **Implementation:** `web/modules/custom/lms_ai/src/Plugin/`
- **Other ADRs:** ADR-007 (Payment gateway plugin, same pattern), ADR-001 (Drupal 11)
- **Module:** `lms_ai`
