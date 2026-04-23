---
description: Scan PHP/JS files for IDE lint errors, Drupal API violations, and best practice issues — single file or directory
---

# Code Refactor — Drupal Quality Check

// turbo-all

## Steps

1. **Find targets**: If path is a directory, find all `.php` and `.js` files. If a single file, use directly.

2. **Scan & fix** — check ALL categories below:

   ---

   ### A: IDE Lint Errors

   #### Entity `load()` / `loadMultiple()` — add `@var`
   ```php
   /** @var \Drupal\commerce_product\Entity\ProductVariationInterface|null $variation */
   $variation = $storage->load($id);

   /** @var \Drupal\commerce_order\Entity\OrderInterface[] $orders */
   $orders = $storage->loadMultiple($ids);
   ```

   **Entity type → Interface lookup:**
   | Entity Type | Interface |
   |---|---|
   | `commerce_product` | `\Drupal\commerce_product\Entity\ProductInterface` |
   | `commerce_product_variation` | `\Drupal\commerce_product\Entity\ProductVariationInterface` |
   | `commerce_order` | `\Drupal\commerce_order\Entity\OrderInterface` |
   | `commerce_order_item` | `\Drupal\commerce_order\Entity\OrderItemInterface` |
   | `commerce_store` | `\Drupal\commerce_store\Entity\StoreInterface` |
   | `user` | `\Drupal\user\UserInterface` |
   | `node` | `\Drupal\node\NodeInterface` |
   | `taxonomy_term` | `\Drupal\taxonomy\TermInterface` |
   | `file` | `\Drupal\file\FileInterface` |
   | `media` | `\Drupal\media\MediaInterface` |

   #### `entityTypeManager` property vs method
   - `ControllerBase` subclass → `$this->entityTypeManager()` (method)
   - `FormBase` subclass → `$this->entityTypeManager` (property, inject via `create()`)

   #### Missing items & Namespaces
   - `use` statements cho tất cả class/interface trong `@var` và toàn bộ các class reference.
   - **Bắt buộc dùng `use` statement ở đầu file, KHÔNG dùng fully qualified namespace inline** (vd: cấm viết `\Drupal\Core\Url::fromRoute()`, phải dùng `use Drupal\Core\Url;` và `Url::fromRoute()`).
   - Return type declarations
   - Parameter type hints

   ---

   ### B: Drupal API Violations

   #### Render API
   | Pattern xấu | Thay bằng |
   |---|---|
   | `'#markup' => '<tag>...'` | `'#type' => 'html_tag'` + `'#tag'` + `'#value'` |
   | `htmlspecialchars($val)` | `Html::escape($val)` |
   | `echo` / `print` trong controller | Return render array hoặc `Response` object |
   | Thiếu `#cache` (contexts/tags/max-age) | Thêm `#cache` property phù hợp |

   #### Dependency Injection
   | Pattern xấu | Thay bằng |
   |---|---|
   | `\Drupal::service('...')` trong class | Constructor injection via `__construct` |
   | `\Drupal::entityTypeManager()` | Inject `EntityTypeManagerInterface` |
   | `\Drupal::database()` | Inject `Connection` |
   | `\Drupal::messenger()->addMessage()` | Inject `MessengerInterface` (hoặc dùng `$this->messenger()` trong FormBase/ControllerBase) |
   | `\Drupal::currentUser()` | Inject `AccountProxyInterface` |
   | `\Drupal::config()` | Inject `ConfigFactoryInterface` |
   | `\Drupal::logger()` | Inject `LoggerChannelFactoryInterface` |

   > **Ngoại lệ:** `\Drupal::service()` chấp nhận trong `.module` files (procedural context).

   #### Security
   | Pattern xấu | Thay bằng |
   |---|---|
   | Direct SQL string concatenation | `$connection->select()` / `$connection->query()` with placeholders |
   | `$_GET`, `$_POST`, `$_REQUEST` | `$request->query->get()`, `$request->request->get()` |
   | `drupal_set_message()` | `$this->messenger()->addMessage()` hoặc inject `MessengerInterface` |

   #### Deprecated Functions (đã xoá trong D11)
   | Deprecated | Thay bằng |
   |---|---|
   | `drupal_set_message()` | `\Drupal::messenger()->addMessage()` |
   | `drupal_render()` | `\Drupal::service('renderer')->render()` |
   | `entity_load()` | `\Drupal::entityTypeManager()->getStorage()->load()` |
   | `format_date()` | `\Drupal::service('date.formatter')->format()` |
   | `db_query()` / `db_select()` | Inject `Connection` service |
   | `file_create_url()` | `\Drupal::service('file_url_generator')->generateAbsoluteString()` |
   | `file_url_transform_relative()` | `\Drupal::service('file_url_generator')->transformRelative()` |

   ---

   ### C: Drupal 11 Best Practices

   #### PHP 8.3 Compliance
   - Missing `declare(strict_types=1);`
   - Sử dụng `constructor property promotion` khi khả thi
   - Sử dụng `readonly` properties (trừ service property trong form class — gây lỗi AJAX serialization)
   - `match` expression thay `switch` khi phù hợp
   - `enum` thay cho class constants khi biểu diễn tập giá trị cố định

   #### Hooks — OOP Migration (D11.1+)
   | Pattern cũ | Pattern mới |
   |---|---|
   | `function mymodule_form_alter()` trong `.module` | Class trong `src/Hook/` với `#[Hook('form_alter')]` attribute |
   | `template_preprocess_HOOK()` | Preprocess callback trong `hook_theme()` definition (D11.3+) |
   | `hook_theme()` với `'file'` / `'includes'` keys | Xoá `file`/`includes`, dùng PSR-4 autoload (D11.3+) |

   > **Lưu ý:** Procedural hooks vẫn hỗ trợ backward compatibility. Chỉ migrate khi refactor.

   #### Form API
   - `#markup` cho dynamic content → chuyển sang `html_tag`
   - Client-side JS tạo/xoá form element → chuyển sang `#ajax` + `$form_state`
   - Thiếu `#limit_validation_errors` trên AJAX buttons → thêm để tránh full form validation
   - Sử dụng `#states` cho conditional show/hide thay vì custom JS

   #### JavaScript (jQuery 4)
   - jQuery `$.isArray()` → `Array.isArray()` (removed in jQuery 4)
   - jQuery `$.trim()` → `String.prototype.trim()` (removed in jQuery 4)
   - jQuery `$.isFunction()` → `typeof fn === 'function'` (removed in jQuery 4)
   - `Drupal.behaviors` + `once()` pattern bắt buộc
   - Không HTML/CSS trong JS files

   #### Code Quality
   - Unused `use` statements
   - Unused variables
   - `@deprecated` methods đang dùng
   - Missing PHPDoc cho public methods
   - Functions quá dài (> 50 lines) → cân nhắc tách

   ---

   ### D: Controller & Twig Separation (Rule 6)

   #### Page-Level Template Pattern
   | Pattern xấu | Thay bằng |
   |---|---|
   | Controller trả nhiều `#theme` lồng nhau (`header`, `table`, `card`) | Một `#theme => 'module_page_name'` duy nhất |
   | `#attributes => ['class' => ['btn-primary']]` trong Controller | Để CSS classes trong Twig template |
   | `#type => 'table'` + `#header` + `#rows` trong Controller | `{% for %}` loop trong Twig |
   | `#type => 'html_tag'` cho badge/span/icon | `<span class="badge ...">` trực tiếp trong Twig |
   | `#type => 'inline_template'` | Twig template file riêng |

   #### Entity Route Upcasting (BẮT BUỘC)
   | Pattern xấu | Thay bằng |
   |---|---|
   | Lấy ID từ `$routeMatch->getParameter('entity_id')` rồi tự gọi `$storage->load($id)` trong Controller/Form | Khai báo `options.parameters.entity_id.type: entity:entity_type` trong `*.routing.yml` để Drupal tự động load và truyền Entity (upcast) vào class |

   #### Cách refactor
   1. Tạo `hook_theme()` entry mới trong `.module` với variables chỉ chứa dữ liệu thuần
   2. Tạo file `.html.twig` chứa toàn bộ HTML/CSS/Bootstrap
   3. Controller chỉ return `['#theme' => '...', '#var1' => ..., '#var2' => ...]`

   #### URL Generation
   | Pattern xấu | Thay bằng |
   |---|---|
   | `'url' => '/orders/create'` (URL cứng) | `Url::fromRoute('route_name')->toString()` |
   | `href="/products"` trong Twig | `href="{{ path('route_name') }}"` hoặc truyền URL từ Controller |

3. **Apply fixes** với `replace_file_content` hoặc `multi_replace_file_content`. Specify `TargetLintErrorIds` khi có.

4. **Report** — tổng kết:
   - Số file đã scan
   - Số lỗi tìm thấy (theo category A/B/C)
   - Số lỗi đã fix
   - Lỗi cần user quyết định (nếu có)
