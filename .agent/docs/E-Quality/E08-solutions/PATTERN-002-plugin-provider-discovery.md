---
solution_id: SOL-002
name: Plugin Provider Discovery via DefaultPluginManager + Annotation
applies_to: "any subsystem where implementations must be injectable, metadata-bearing, and discoverable by third-party modules (payment gateways, AI providers, and future equivalents)"
derived_from_issue: «no E02 issue yet — pattern extracted from as-built lms_payment and lms_ai code»
owning_story: lms_payment
lesson: K04-plugin-manager-for-injectable-extensible-providers
status: ACTIVE
---

# E08 — Solution: Plugin Provider Discovery

> One-line purpose: describes how `lms_payment` and `lms_ai` use Drupal's `DefaultPluginManager` + annotation to make payment gateways and AI providers extensible without modifying core module code.

---

## Problem

EverLMS needs two classes of swappable providers:

1. **Payment gateways** — bank transfer (manual), Stripe (real), PayPal (stub), ZaloPay (stub). Each gateway has different credentials, a different API flow, and potentially different webhook handling. New gateways (e.g., VNPay, MoMo) must be addable by dropping in a new module without touching `lms_payment`.

2. **AI providers** — OpenAI (GPT-4o, DALL-E 3), Google Gemini (2.5-flash/pro, Imagen 4), Pollinations (image-only, free). Providers need injected services (`@http_client`, `@logger`, `@config.factory`). A future provider (e.g., Anthropic Claude) must be registerable from any module.

A **static factory** (see [PATTERN-001](./PATTERN-001-static-grading-strategy-factory.md)) is unsuitable here because:
- Providers require constructor dependency injection.
- Third-party modules must add providers without patching core files.
- Plugin metadata (id, label, description) must be introspectable (e.g., for a settings form select list).

---

## Solution

Both `lms_payment` and `lms_ai` implement **Drupal's `DefaultPluginManager` + PHP annotation** pattern.

### lms_payment — PaymentGatewayManager

**Directory layout (as-built):**

```
web/modules/custom/lms_payment/src/
├── Annotation/PaymentGateway.php            # @PaymentGateway annotation class
├── Plugin/PaymentGatewayInterface.php       # Plugin contract (processPayment, handleWebhook, getLabel)
├── Plugin/PaymentGatewayBase.php            # Abstract base — stubs processPayment + handleWebhook
├── Plugin/PaymentGatewayManager.php         # Extends DefaultPluginManager
└── Plugin/PaymentGateway/
    ├── BankTransferGateway.php              # @PaymentGateway(id="bank_transfer") — REAL
    ├── PayPalGateway.php                    # @PaymentGateway(id="paypal") — STUB (no server verification)
    └── ZaloPayGateway.php                   # @PaymentGateway(id="zalopay") — STUB (hardcoded sandbox URL)

web/modules/custom/lms_stripe/src/
└── Plugin/PaymentGateway/
    └── StripeGateway.php                    # @PaymentGateway(id="stripe") — REAL (Stripe SDK)
```

**Service registration (`lms_payment.services.yml`):**

```yaml
plugin.manager.payment_gateway:
  class: Drupal\lms_payment\Plugin\PaymentGatewayManager
  parent: default_plugin_manager
```

**PaymentGatewayManager constructor** scans for classes annotated `@PaymentGateway` under the `Plugin/PaymentGateway` sub-namespace of every installed module. Cache bin: `lms_payment_payment_gateway_plugins`. Alter hook: `lms_payment_payment_gateway_info`.

**Annotation class:**

```php
// src/Annotation/PaymentGateway.php
/**
 * @Annotation
 */
class PaymentGateway extends Plugin {
    public $id;           // string machine name
    public $label;        // TranslatableMarkup
    public $description;  // TranslatableMarkup
}
```

**Plugin interface:**

```php
// src/Plugin/PaymentGatewayInterface.php
interface PaymentGatewayInterface extends PluginInspectionInterface {
    public function processPayment($order, array $payment_details = []): array;
    public function handleWebhook($request): Response;
    public function getLabel(): string;
}
```

**Usage in `CheckoutForm::submitForm()`:**

```php
$gateway = $this->pluginManagerPaymentGateway->createInstance($payment_method);
$result = $gateway->processPayment($order, $payment_details);
```

**CRITICAL DEFECT (as-built):** A second unrelated interface exists at `lms_payment\Service\PaymentGatewayInterface`. Only `lms_paypal\PayPalGateway` implements this service interface — it is **not annotated with `@PaymentGateway`** and is **never discovered by the plugin manager**. The `lms_paypal` module is effectively dead code. See [`C-Design/C05-modules/lms_payment.md`](../../C-Design/C05-modules/lms_payment.md) and [`D-Development/D03-stories/lms_payment.md`](../../D-Development/D03-stories/lms_payment.md) for the fix path.

---

### lms_ai — AiProviderManager

**Directory layout (as-built):**

```
web/modules/custom/lms_ai/src/
├── Annotation/AiProvider.php               # @AiProvider annotation class
├── Plugin/AiProviderInterface.php          # Contract: generateJson(), generateImage()
├── Plugin/AiProviderManager.php            # Extends DefaultPluginManager
└── Plugin/AiProvider/
    ├── OpenAiProvider.php                  # @AiProvider(id="openai") — REAL (GPT-4o + DALL-E 3)
    ├── GeminiProvider.php                  # @AiProvider(id="gemini") — REAL (Gemini 2.5 + Imagen 4)
    └── PollinationsProvider.php            # @AiProvider(id="pollinations") — image only, free
```

**Service registration (`lms_ai.services.yml`):**

```yaml
plugin.manager.lms_ai_provider:
  class: Drupal\lms_ai\Plugin\AiProviderManager
  arguments: ['@container.namespaces', '@cache.discovery', '@module_handler']
```

Cache bin: `lms_ai_provider_plugins`. Alter hook: `lms_ai_provider_info`.

**Annotation class:**

```php
// src/Annotation/AiProvider.php
/**
 * @Annotation
 */
class AiProvider extends Plugin {
    public $id;
    public $label;        // TranslatableMarkup
    public $description;  // TranslatableMarkup
}
```

**Interface contract:**

```php
// src/Plugin/AiProviderInterface.php
interface AiProviderInterface extends PluginInspectionInterface {
    public function generateJson(string $prompt, ?string $schema, array $options = []): ?array;
    public function generateImage(string $prompt, array $options = []): ?string; // base64 string
    // NOTE: generateText() is implemented by all providers but is NOT declared in this interface — a gap.
}
```

**Usage in services:**

```php
// CourseBuilderService, ZoomAiService
$provider = $this->aiProviderManager->createInstance('openai');
$result = $provider->generateJson($prompt, $schema);
```

**KNOWN DEFECT (as-built):** `CourseBuilderService` hardcodes `createInstance('openai')` instead of reading `lms_ai.settings.default_provider`. This bypasses the admin-configurable default and means switching to Gemini requires a code change. See [`C-Design/C05-modules/lms_ai.md`](../../C-Design/C05-modules/lms_ai.md).

---

## Adding a new provider/gateway — steps

### New payment gateway

1. Create a new module (or add to an existing one) — e.g., `lms_vnpay`.
2. Add `src/Plugin/PaymentGateway/VnPayGateway.php` annotated `@PaymentGateway(id="vnpay", ...)`.
3. Implement `PaymentGatewayInterface` (the **Plugin** interface at `lms_payment\Plugin\PaymentGatewayInterface`, NOT the Service interface).
4. Enable the module — plugin manager discovers it automatically via annotation scan.
5. Add gateway credentials to `PaymentSettingsForm` (or a new settings form in the new module).
6. **Add a webhook route** if the gateway supports async webhooks — none currently exist for any gateway. See `D-Development/D03-stories/payment-webhooks.md`.

### New AI provider

1. Create `src/Plugin/AiProvider/MyProvider.php` in any installed module.
2. Annotate: `@AiProvider(id="my_provider", label=@Translation("My Provider"), description=@Translation("..."))`.
3. Implement `AiProviderInterface`. Add `generateText()` even though it is not in the interface (gap — document and implement defensively).
4. Add credentials to `AiSettingsForm` if needed.
5. Enable module — no factory edit required.

---

## When to apply

Use Drupal `DefaultPluginManager` + annotation when ANY of the following holds:

| Condition | Explanation |
|-----------|-------------|
| Providers need **constructor DI** (services, config, HTTP client) | Plugins are instantiated through the container |
| Third-party modules must **register implementations** without patching core | Annotation scan finds plugins in any module |
| **Plugin metadata** (label, capabilities) must be available at admin UI build time (e.g., populate a select list) | `getDefinitions()` returns all metadata without instantiating plugins |
| Implementations are expected to **grow over time** | One-time scaffolding amortizes across N future plugins |

---

## When NOT to apply

| Situation | Use instead |
|-----------|-------------|
| All variants are stateless, DI-free value objects | Static factory — see [PATTERN-001](./PATTERN-001-static-grading-strategy-factory.md) |
| Only 2–3 variants, all owned by the same module, never extended externally | Simple `match` or `if/else` inside the calling service |
| You need a **service** (singleton, shared state) rather than a per-call plugin instance | Drupal tagged services (`event_subscriber`, `access_check`, etc.) |

---

## Trade-offs

- **Cost:** Annotation scanning and plugin cache warm-up add latency on first request after a cache clear. In production this is hidden behind Drupal's `cache.discovery`. Discovery errors (missing annotation, wrong namespace) are silent — plugins simply do not appear.
- **Risk:** The dual-interface defect in `lms_payment` (`Plugin\PaymentGatewayInterface` vs `Service\PaymentGatewayInterface`) is a concrete example of how easy it is to implement the wrong interface. Any new gateway developer must be explicitly directed to the Plugin interface.
- **Operational impact:** Cache must be cleared (`drush cr`) after enabling a new plugin module. Plugin discovery failures do not produce logged errors by default — add a watchdog call if `getDefinitions()` returns fewer plugins than expected.
- **Alternatives considered:** Drupal hooks (e.g., `hook_payment_gateway_info_alter`) and tagged services were both considered. Tagged services were rejected because they cannot carry plugin metadata. Hook alter was rejected because it requires all implementations to live inside a `.module` file rather than a clean class. Plugin Manager won as the standard Drupal pattern for this use case.

---

## References

- D03 Story (payment): [`D-Development/D03-stories/lms_payment.md`](../../D-Development/D03-stories/lms_payment.md)
- D03 Story (AI): [`D-Development/D03-stories/lms_ai.md`](../../D-Development/D03-stories/lms_ai.md)
- C05 Module spec (payment): [`C-Design/C05-modules/lms_payment.md`](../../C-Design/C05-modules/lms_payment.md)
- C05 Module spec (AI): [`C-Design/C05-modules/lms_ai.md`](../../C-Design/C05-modules/lms_ai.md)
- Contrast with static factory: [`PATTERN-001-static-grading-strategy-factory.md`](./PATTERN-001-static-grading-strategy-factory.md)
- Webhook gap story: [`D-Development/D03-stories/payment-webhooks.md`](../../D-Development/D03-stories/payment-webhooks.md)
- Source files:
  - `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayManager.php`
  - `web/modules/custom/lms_payment/src/Annotation/PaymentGateway.php`
  - `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayInterface.php`
  - `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayBase.php`
  - `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`
  - `web/modules/custom/lms_ai/src/Plugin/AiProviderManager.php`
  - `web/modules/custom/lms_ai/src/Annotation/AiProvider.php`
  - `web/modules/custom/lms_ai/src/Plugin/AiProvider/` (3 providers)
